<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


$require_current_course = true;
$require_help = true;
$helpTopic = 'EBook';
$guest_allowed = true;

define('EBOOK', 2);

include '../../include/baseTheme.php';
include '../../include/lib/fileManageLib.inc.php';

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action_stats = new action();
$action_stats->record('MODULE_ID_EBOOK');
/**************************************/

mysql_select_db($mysqlMainDb);

$nameTools = $langEBook;

if ($is_adminOfCourse) {
        $tool_content .= "
   <div id='operations_container'>
     <ul id='opslist'>
       <li><a href='index.php?course=$code_cours&amp;create=1'>$langCreate</a>
     </ul>
   </div>";

        if (isset($_POST['delete']) or isset($_POST['delete_x'])) {
                $id = intval($_POST['id']);
                $r = db_query("SELECT title FROM ebook WHERE course_id = $cours_id AND id = $id");
                if (mysql_num_rows($r) > 0) {
                        list($title) = mysql_fetch_row($r);
                        db_query("DELETE FROM ebook_subsection WHERE section_id IN
                                         (SELECT id FROM ebook_section WHERE ebook_id = $id)");
                        db_query("DELETE FROM ebook_section WHERE ebook_id = $id");
                        db_query("DELETE FROM ebook WHERE id = $id");
                        $basedir = $webDir . 'courses/' . $currentCourseID . '/ebook/' . $id;
                        my_delete($basedir);
                        db_query("DELETE FROM document WHERE
                                 subsystem = ".EBOOK." AND
                                 subsystem_id = $id AND
                                 course_id = $cours_id");
                        $tool_content .= "\n    <p class='success'>" . q(sprintf($langEBookDeleted, $title)) . "</p>";
                }
        } elseif (isset($_GET['create'])) {
                $tool_content .= "
   <form method='post' action='create.php?course=$code_cours' enctype='multipart/form-data'>
     <fieldset>
     <legend>$langUpload</legend>
     
     <table width='100%' class='tbl'>
     <tr>
       <th>$langTitle:</th>
       <td><input type='text' name='title' size='53' /></td></tr>
     <tr>
       <th>$langZipFile:</th>
       <td><input type='file' name='file' size='53' /></td>
     </tr>
     <tr>
       <th>&nbsp;</th>
       <td class='right'><input type='submit' name='submit' value='$langSend' /></td>
     </tr>
     </table>
     </fieldset>
   </form>";
        } elseif (isset($_GET['down'])) {
                move_order('ebook', 'id', intval($_GET['down']), 'order', 'down', "course_id = $cours_id");
        } elseif (isset($_GET['up'])) {
                move_order('ebook', 'id', intval($_GET['up']), 'order', 'up', "course_id = $cours_id");
        }
}

$q = db_query("SELECT * FROM `ebook` WHERE course_id = $cours_id ORDER BY `order`");

if (mysql_num_rows($q) == 0) {
        $tool_content .= "\n    <p class='alert1'>$langNoEBook</p>\n";
} else {
        $tool_content .= "
     <script type='text/javascript' src='../auth/sorttable.js'></script>
     <table width='100%' class='sortable' id='t1'>
     <tr>
       <th colspan='2'><div align='left'>$langEBook</div></th>" .  ($is_adminOfCourse? "
       <th width='70' colspan='2' class='center'>$langActions</th>":
                                                     '') .  "
     </tr>\n";

        $k = 0;
        $num = mysql_num_rows($q);
        while ($r = mysql_fetch_array($q)) {
                $tool_content .= "
     <tr" . odd_even($k) . ">
       <td width='16' valign='top'>" .
                                 "<img style='padding-top:3px;' src='$themeimg/arrow.png' " .
                                 " alt='' /></td>
       <td><a href='show.php/$currentCourseID/$r[id]/'>" .
                                 q($r['title']) . "</a>
       </td>" . tools($r['id'], $r['title'], $k, $num) . "
     </tr>\n";
                $k++;
        }
        $tool_content .= "
     </table>\n";
}

draw($tool_content, 2, null, $head_content);

function tools($id, $title, $k, $num)
{
        global $is_adminOfCourse, $langModify, $langDelete, $langMove, $langDown, $langUp, $langEBookDelConfirm,
               $code_cours, $themeimg;

        if (!$is_adminOfCourse) {
                return '';
        } else {
                $num--;
                return "\n        <td width='60' class='center'>\n<form action='$_SERVER[PHP_SELF]?course=$code_cours' method='post'>\n" .
                       "<input type='hidden' name='id' value='$id' />\n<a href='edit.php?course=$code_cours&amp;id=$id'>" .
                       "<img src='$themeimg/edit.png' alt='$langModify' title='$langModify' />" .
                       "</a>&nbsp;<input type='image' src='$themeimg/delete.png'
                                         alt='$langDelete' title='$langDelete' name='delete' value='$id'
                                         onclick=\"javascript:if(!confirm('".
                       js_escape(sprintf($langEBookDelConfirm, $title)) ."')) return false;\" />" .
                       "</form></td>\n        <td class='right' width='40'>" .
                       (($k < $num)? "<a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;down=$id'>
                                      <img class='displayed' src='$themeimg/down.png'
                                           title='$langMove $langDown' alt='$langMove $langDown' /></a>":
                                     '') . 
                       (($k > 0)? "<a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;up=$id'>
                                   <img class='displayed' src='$themeimg/up.png'
                                        title='$langMove $langUp' alt='$langMove $langUp' /></a>":
                                  '') . '</td>';
        }
}
