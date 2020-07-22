<?php

/* ========================================================================
 * Opensemfe_options eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
 * Copyright 2020 Dimitris Mantzouranis - Semfe.gr
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * ======================================================================== */

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
//Default type
$defaults = '';
$active_exams = get_config('active_exams');
$active_teaching = get_config('active_teaching');

$semfe_id = '';
if (isset($_GET['reset_semfe_options'])) {
    unset($_SESSION['active_exams']);
    unset($_SESSION['active_teaching']);
    redirect_to_home_page('modules/admin/semfe_options.php');
}
if (isset($_POST['import'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
    }
    validateUploadedFile($_FILES['semfeFile']['name'], 2);
    if (get_file_extension($_FILES['semfeFile']['name']) == 'pdf') {
        $file_name = $_FILES['semfeFile']['name'];        
        if (!is_dir('courses/semfe_data')) {
            make_dir('courses/semfe_data');
        }
        if (move_uploaded_file($_FILES['semfeFile']['tmp_name'], "courses/semfe_data/$file_name")) {
            require_once 'modules/admin/extconfig/externals.php';
            $connector = AntivirusApp::getAntivirus();
            if($connector->isEnabled() == true ){
                $output=$connector->check("courses/semfe_data/$file_name");
                if($output->status==$output::STATUS_INFECTED){
                    AntivirusApp::block($output->output);
                }
            }
                $semfe_options = unserialize(base64_decode($base64_str));
                $new_semfe_id = Database::get()->query("INSERT INTO semfe_options (name, type) VALUES(?s, ?s)", $semfe_options->name, $semfe_options->type)->lastInsertID;
                rename("$webDir/courses/semfe_data/temp/".intval($semfe_options->id), "$webDir/courses/semfe_data/temp/$new_semfe_id");
                recurse_copy("$webDir/courses/semfe_data/temp","$webDir/courses/semfe_data");
                removeDir("$webDir/courses/semfe_data/temp");
                Session::Messages($langFileInstalled);
        }
    } else {
        Session::Messages($langUnwantedFiletype);
    }
    redirect_to_home_page('modules/admin/semfe_options.php');
}
if (isset($_POST['optionsSave'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
    }
    upload_images();
    clear_default_settings();
    $serialized_data = serialize($_POST);
    Database::get()->query("UPDATE semfe_options SET type = ?s WHERE id = ?d", $serialized_data, $semfe_id);
    redirect_to_home_page('modules/admin/semfe_options.php');
} elseif (isset($_GET['delFileId'])) {
    $semfe_id = intval($_GET['delFileId']);
    $semfe_options = Database::get()->querySingle("SELECT * FROM semfe_options WHERE id = ?d", $semfe_id);
    $semfe_options_type = unserialize($semfe_options->type);
    @removeDir("$webDir/courses/semfe_data/$semfe_id");
    Database::get()->query("DELETE FROM semfe_options WHERE id = ?d", $semfe_id);
    if($_GET['delFileId'] == $active_exams) {
        Database::get()->query("UPDATE config SET value = ?d WHERE `key` = ?s", 0, 'active_exams');
    } elseif($_GET['delFileId'] == $active_teaching && $_GET['delFileId'] != $active_exams) {
        Database::get()->query("UPDATE config SET value = ?d WHERE `key` = ?s", 0, 'active_teaching');
    } elseif($_GET['delFileId'] != $active_teaching) {
        unset($_SESSION['active_teaching']);
    } else {
        unset($_SESSION['active_exams']);
    }
    redirect_to_home_page('modules/admin/semfe_options.php');
} elseif (isset($_POST['active_semfe_options'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
        Database::get()->query("UPDATE config SET value = ?d WHERE `key` = ?s", $_POST['active_semfe_options'], 'active_exams');
        unset($_SESSION['active_exams']);
    redirect_to_home_page('modules/admin/semfe_options.php');     
} else {
    $pageName = $langSemfeSettings;
    $navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
    load_js('spectrum');
    load_js('bootstrap-slider');
    $head_content .= "
    <script>
            $('.uploadExams').click(function (e)
            {
                e.preventDefault();
                bootbox.dialog({
                    title: '$langImport',
                    message: '<div class=\"row\">'+
                                '<div class=\"col-sm-12\">'+
                                    '<form id=\"uploadExamsForm\" class=\"form-horizontal\" role=\"form\" enctype=\"multipart/form-data\" method=\"post\">'+
                                        '<div class=\"form-group\">'+
                                        '<div class=\"col-sm-12\">'+
                                            '<input id=\"semfeFile\" name=\"examsFile\" type=\"file\">'+
                                            '<input name=\"import\" type=\"hidden\">'+
                                        '</div>'+
                                        '</div>". addslashes(generate_csrf_token_form_field()) ."'+
                                    '</form>'+
                                '</div>'+
                            '</div>',                          
                    buttons: {
                        success: {
                            label: '$langUpload',
                            className: 'btn-success',
                            callback: function (d) {
                                var examsFile = $('#examsFile').val();
                                if(examsFile != '') {
                                    $('#uploadExamsForm').submit();
                                } else {
                                    $('#examsFile').closest('.form-group').addClass('has-error');
                                    $('#examsFile').after('<span class=\"help-block\">$langTheFieldIsRequired</span>');
                                    return false;
                                }
                            }
                        },
                        cancel: {
                            label: '$langCancel',
                            className: 'btn-default'
                        }                        
                    }
                });
            });
            var optionsExamsSaveCallback = function (d) {
                var examsOptionsName = $('#examsOptionsName').val();
                if (examsOptionsName) {
                    var input = $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'examsOptionsName').val(examsOptionsName);
                    $('#semfe_options_form').append($(input)).submit();
                } else {
                    $('#examsOptionsName').closest('.form-group').addClass('has-error');
                    $('#examsOptionsName').after('<span class=\"help-block\">$langTheFieldIsRequired</span>');
                    return false;
                }
            };
                $('#examsOptionsName').keypress(function (e) {
                    if (e.which == 13) {
                        e.preventDefault();
                        optionsExamsSaveCallback();
                    }
                });
            });
            $('select#exams_selection').change(function ()
            {
                var cur_val = $(this).val();
                if (cur_val == '$active_exams') {
                    $('a#exams_enable').addClass('hidden');
                } else {
                    $('a#exams_enable').removeClass('hidden');
                }
                if (cur_val == 0) {
                    $('a#exams_delete').addClass('hidden');
                } else {
                    $('a#exams_delete').removeClass('hidden');
                    var formAction = $('a#exams_delete').closest('form').attr('action');
                    var newValue = $('select#exams_selection').val();
                    var newAction = formAction.replace(/(delFileId=).*/, '$1'+newValue);
                    $('a#exams_delete').closest('form').attr('action', newAction);
                }                
            });            
            $('a.exams_enable').click(function (e)
            {
                e.preventDefault();
                $('#exams_selection').submit();
            });
        });
            $('.uploadTeaching').click(function (e)
            {
                e.preventDefault();
                bootbox.dialog({
                    title: '$langImport',
                    message: '<div class=\"row\">'+
                                '<div class=\"col-sm-12\">'+
                                    '<form id=\"uploadTeachingForm\" class=\"form-horizontal\" role=\"form\" enctype=\"multipart/form-data\" method=\"post\">'+
                                        '<div class=\"form-group\">'+
                                        '<div class=\"col-sm-12\">'+
                                            '<input id=\"semfeFile\" name=\"teachingFile\" type=\"file\">'+
                                            '<input name=\"import\" type=\"hidden\">'+
                                        '</div>'+
                                        '</div>". addslashes(generate_csrf_token_form_field()) ."'+
                                    '</form>'+
                                '</div>'+
                            '</div>',                          
                    buttons: {
                        success: {
                            label: '$langUpload',
                            className: 'btn-success',
                            callback: function (d) {
                                var teachingFile = $('#teachingFile').val();
                                if(teachingFile != '') {
                                    $('#uploadTeachingForm').submit();
                                } else {
                                    $('#teachingFile').closest('.form-group').addClass('has-error');
                                    $('#teachingFile').after('<span class=\"help-block\">$langTheFieldIsRequired</span>');
                                    return false;
                                }
                            }
                        },
                        cancel: {
                            label: '$langCancel',
                            className: 'btn-default'
                        }                        
                    }
                });
            });
            var optionsTeachingSaveCallback = function (d) {
                var teachingOptionsName = $('#teachingOptionsName').val();
                if (teachingOptionsName) {
                    var input = $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'teachingOptionsName').val(teachingOptionsName);
                    $('#semfe_options_form').append($(input)).submit();
                } else {
                    $('#teachingOptionsName').closest('.form-group').addClass('has-error');
                    $('#teachingOptionsName').after('<span class=\"help-block\">$langTheFieldIsRequired</span>');
                    return false;
                }
            };
                $('#teachingOptionsName').keypress(function (e) {
                    if (e.which == 13) {
                        e.preventDefault();
                        optionsTeachingSaveCallback();
                    }
                });
            });
            $('select#teaching_selection').change(function ()
            {
                var cur_val = $(this).val();
                if (cur_val == '$active_exams') {
                    $('a#teaching_enable').addClass('hidden');
                } else {
                    $('a#teaching_enable').removeClass('hidden');
                }
                if (cur_val == 0) {
                    $('a#teaching_delete').addClass('hidden');
                } else {
                    $('a#teaching_delete').removeClass('hidden');
                    var formAction = $('a#teaching_delete').closest('form').attr('action');
                    var newValue = $('select#teaching_selection').val();
                    var newAction = formAction.replace(/(delFileId=).*/, '$1'+newValue);
                    $('a#teaching_delete').closest('form').attr('action', newAction);
                }                
            });            
            $('a.teaching_enable').click(function (e)
            {
                e.preventDefault();
                $('#teaching_selection').submit();
            });
        });        
    </script>";
    $all_exams = Database::get()->queryArray("SELECT * FROM semfe_options WHERE type = ?d", "exams");
    $all_teaching = Database::get()->queryArray("SELECT * FROM semfe_options WHERE type = ?d", "teaching");    
    $exams_arr[0] = "---- $langDefaultExamsSettings ----";
    $teaching_arr[0] = "---- $langDefaultTeachingSettings ----";
    foreach ($all_exams as $row) {
        $exams_arr[$row->id] = $row->name;
    }
    foreach ($all_teaching as $row) {
        $teaching_arr[$row->id] = $row->name;
    }    

    if ($semfe_id) {
        $semfe_options = Database::get()->querySingle("SELECT * FROM semfe_options WHERE id = ?d", $semfe_id);
        $semfe_options_type = unserialize($semfe_options->type);
    }
    initialize_settings();
    $activate_class = ($semfe_id != 0) ? "" : " hidden";
    $activate_exams_btn = "<a href='#' class='exams_enable btn btn-success btn-xs$activate_class' id='exams_enable'>$langActivate</a>";
    $activate_teaching_btn = "<a href='#' class='teaching_enable btn btn-success btn-xs$activate_class' id='teaching_enable'>$langActivate</a>";
    $del_class = ($semfe_id != 0) ? "" : " hidden";
    $delete_exams_btn = "
                    <form class='form-inline' style='display:inline;' method='post' action='$_SERVER[SCRIPT_NAME]?delFileId=$semfe_id'>
                        <a class='confirmAction btn btn-danger btn-xs$del_class' id='exams_delete' data-title='$langConfirmDelete' data-message='$langSemfeExamsDelete' data-cancel-txt='$langCancel' data-action-txt='$langDelete' data-action-class='btn-danger'>$langDelete</a>
                    </form>";
    $delete_teaching_btn = "
                    <form class='form-inline' style='display:inline;' method='post' action='$_SERVER[SCRIPT_NAME]?delFileId=$semfe_id'>
                        <a class='confirmAction btn btn-danger btn-xs$del_class' id='teaching_delete' data-title='$langConfirmDelete' data-message='$langSemfeTeachingDelete' data-cancel-txt='$langCancel' data-action-txt='$langDelete' data-action-class='btn-danger'>$langDelete</a>
                    </form>";

    $urlSemfeData = $urlAppend . 'courses/semfe_data/' . $semfe_id;
    
    $tool_content .= action_bar(array(
        array('title' => $langExamsImport,
            'url' => "#",
            'icon' => 'fa-upload',
            'class' => 'uploadExams',
            'level' => 'primary-label'),
        array('title' => $langTeachingImport,
            'url' => "#",
            'icon' => 'fa-upload',
            'class' => 'uploadTeaching',
            'level' => 'primary-label'),                            
        array('title' => $langBack,
            'url' => "{$urlAppend}modules/admin/index.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')
        ),false);
    @$tool_content .= "
    <div class='form-wrapper'>
        <div class='row margin-bottom-fat'>
            <div class='col-sm-3 text-right'>
                <strong>$langActiveExams:</strong>
            </div>
            <div class='col-sm-9'>
            ".$exams_arr[$active_exams]."
            </div>
        </div>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post' id='exams_selection'>
            <div class='form-group'>
                <label for='bgColor' class='col-sm-3 control-label'>$langAvailableExams:</label>
                <div class='col-sm-9'>
                    ".  selection($exams_arr, 'active_semfe_options', $semfe_id, 'class="form-control form-submit" id="exams_selection"')."
                </div>
            </div>
            ". generate_csrf_token_form_field() ."
        </form>
        <div class='form-group margin-bottom-fat'>
            <div class='col-sm-9 col-sm-offset-3'>
                $activate_exams_btn
                $delete_exams_btn
            </div>
        </div>
    </div>";
    @$tool_content .= "
    <div class='form-wrapper'>
        <div class='row margin-bottom-fat'>
            <div class='col-sm-3 text-right'>
                <strong>$langActiveTeaching:</strong>
            </div>
            <div class='col-sm-9'>
            ".$teaching_arr[$active_teaching]."
            </div>
        </div>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post' id='teaching_selection'>
            <div class='form-group'>
                <label for='bgColor' class='col-sm-3 control-label'>$langAvailableTeaching:</label>
                <div class='col-sm-9'>
                    ".  selection($teaching_arr, 'active_semfe_options', $semfe_id, 'class="form-control form-submit" id="teaching_selection"')."
                </div>
            </div>
            ". generate_csrf_token_form_field() ."
        </form>
        <div class='form-group margin-bottom-fat'>
            <div class='col-sm-9 col-sm-offset-3'>
                $activate_teaching_btn
                $delete_teaching_btn
            </div>
        </div>
    </div>";
    
}

function clear_default_settings() {
    global $defaults;
    foreach ($defaults as $setting => $option_array) {
        foreach ($option_array as $option){
            if(isset($_POST[$option]) && $_POST[$option] == $setting) unset($_POST[$option]);
        }
    }
    if(isset($_POST['examsOptionsName'])) unset($_POST['examsOptionsName']);
    if(isset($_POST['teachingOptionsName'])) unset($_POST['teachingOptionsName']);    
    if(isset($_POST['optionsSave'])) unset($_POST['optionsSave']); //unnecessary submit button value
}
function initialize_settings() {
    global $semfe_options_type, $defaults;

    foreach ($defaults as $setting => $option_array) {
        foreach ($option_array as $option){
            if(!isset($semfe_options_type[$option])) $semfe_options_type[$option] = $setting;
        }
    }    
}
draw($tool_content, 3, null, $head_content);
