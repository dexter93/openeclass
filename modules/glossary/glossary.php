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
$helpTopic = 'Glossary';

include '../../include/baseTheme.php';

/*
 * *** The following is added for statistics purposes **
 */
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_GLOSSARY');

mysql_select_db($mysqlMainDb);

if ($is_adminOfCourse) {
        load_js('tools.js');
}

$nameTools = $langGlossary;

/********************************************
 *Actions*
********************************************/

if ($is_adminOfCourse) {
        if (isset($_POST['url'])) {
                $url = trim($_POST['url']);
                if (!empty($url)) {
                        $url = canonicalize_url($url);
                }
        } else {
                $url = '';
        }

        if (isset($_POST['submit_expand'])) {
                db_query("UPDATE cours SET expand_glossary = " . (isset($_POST['expand'])? 1: 0));
                invalidate_glossary_cache();
                $tool_content .= "<div class='success'>$langQuotaSuccess</div>";
        }


        if (isset($_POST['submit'])) {
                db_query("INSERT INTO glossary SET term = " .
                        autoquote(trim($_POST['term'])) . ", definition = " .
                        autoquote(trim($_POST['definition'])) . ", url = " .
                        autoquote($url) . ", `order` = " .
                        findorder($cours_id ) .", datestamp = NOW(), course_id = $cours_id");
                invalidate_glossary_cache();
                $tool_content .= "<div class='success'>$langGlossaryAdded</div>";
        }
        if (isset($_POST['edit_submit'])) {
                $id = intval($_POST['id']);
                $sql = db_query("UPDATE glossary SET term = " .
                        autoquote(trim($_POST['term'])) . ", definition = " .
                        autoquote(trim($_POST['definition'])) . ", url = " .
                        autoquote($url) . ",
                                datestamp = NOW()
                                WHERE id = $id AND course_id = $cours_id");
                invalidate_glossary_cache();
                if (mysql_affected_rows() > 0) {
                        $tool_content .= "<div class='success'>$langGlossaryUpdated</div><br />";    
                }
        }
        if (isset($_GET['delete'])) {
                $sql = db_query("DELETE FROM glossary WHERE id = '$_GET[delete]' AND course_id = $cours_id");
                invalidate_glossary_cache();
                if (mysql_affected_rows() > 0) {
                        $tool_content .= "<div class='success'>$langGlossaryDeleted</div><br />";    
                }
        }
        $tool_content .= "
       <div id='operations_container'>
         <ul id='opslist'>
           <li><a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;add=1'>$langAddGlossaryTerm</a></li>
           <li><a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;config=1'>$langConfig</a></li>
           <li>$langGlossaryToCsv (<a href='dumpglossary.php?course=$code_cours'>UTF8</a>&nbsp;-&nbsp;<a href='dumpglossary.php?course=$code_cours&amp;enc=1253'>Windows 1253</a>)</li>  
         </ul>
       </div>";

    // display configuration form
    if (isset($_GET['config']))  {
        $navigation[] = array("url" => "$_SERVER[PHP_SELF]?course=$code_cours", "name" => $langGlossary);
        $nameTools = $langConfig;
        list($expand) = mysql_fetch_row(db_query("SELECT expand_glossary FROM `$mysqlMainDb`.cours
                                                         WHERE cours_id = $cours_id"));
        $checked = $expand? ' checked="1"': '';
        $tool_content .= "
              <form action='$_SERVER[PHP_SELF]?course=$code_cours' method='post'>
               <fieldset>
                 <legend>$langConfig</legend>
                 <table class='tbl' width='100%'>
                 <tr>
                   <th>$langGlossaryExpand:</th>
                   <td>
                     <input type='checkbox' name='expand' value='yes'$checked>
                   </td>
                 </tr>
                 <tr>
                   <th>&nbsp;</th>
                   <td class='right'><input type='submit' name='submit_expand' value='$langSubmit'></td>
                 </tr>
                 </table>
               </fieldset>
              </form>\n";    
    }
    
    // display form for adding a glossary term
    if (isset($_GET['add']))  {
        $term = $definition = $url = '';
        $navigation[] = array("url" => "$_SERVER[PHP_SELF]?course=$code_cours", "name" => $langGlossary);
        $nameTools = $langAddGlossaryTerm;
        
        $tool_content .= "
              <form action='$_SERVER[PHP_SELF]?course=$code_cours' method='post'>
               <fieldset>
                 <legend>$langAddGlossaryTerm</legend>
                 <table class='tbl' width='100%'>
                 <tr>
                   <th width='90'>$langGlossaryTerm:</th>
                   <td>
                     <input type='text' name='term' value='$term' size='60'>
                   </td>
                 </tr>
                 <tr>
                   <th valign='top'>$langGlossaryDefinition:</th>
                   <td>\n";
        $tool_content .= text_area('definition', 4, 60, $definition);
        $tool_content .= "\n
                   </td>
                 </tr>
                 <tr>
                   <th>$langGlossaryUrl:</th>
                 <td>
                 <input type='text' name='url' value='$url' size='50'>
                 </td>
                 </tr>
                 <tr>
                   <th>&nbsp;</th>
                   <td class='right'><input type='submit' name='submit' value='$langSubmit'></td>
                 </tr>
                 </table>
               </fieldset>
              </form>\n";    
    }
    
    // display form for editiong a glossary term
    if (isset($_GET['edit']))  {
        $navigation[] = array("url" => "$_SERVER[PHP_SELF]?course=$code_cours", "name" => $langGlossary);
        $nameTools = $langEditGlossaryTerm;
        
        $sql = db_query("SELECT term, definition, url FROM glossary WHERE id='$_GET[edit]'");
        $data = mysql_fetch_array($sql);
        
        $tool_content .= "
               <form action='$_SERVER[PHP_SELF]?course=$code_cours' method='post'>
               <fieldset>
                 <legend>$langModify</legend>
                 <table class='tbl' width='100%'>
                 <tr>
                   <th width='90'>$langGlossaryTerm:</th>
                   <td><input type='text' name='term' value='$data[term]' size='60'></td>
                 </tr>
                 <tr>
                   <th valign='top'>$langGlossaryDefinition:</th>
                   <td valign='top'>\n";
        $tool_content .= text_area('definition', 4, 60, $data['definition']);
        $tool_content .= "\n
                   </td>
                 </tr>
                 <tr><th>$langGlossaryUrl:</th>
                 <td>
                 <input type='text' name='url' value='$data[url]' size='50'>
                 </td>
                 </tr>
                 <tr>
                   <th>&nbsp;</th>
                   <td class='right'>
                    <input type='submit' name='edit_submit' value='$langModify'>
                    <input type = 'hidden' name='id' value='$_GET[edit]'>
                   </td>
                 </tr>
                 </table>
               </fieldset>
               <br />\n";    
    }
}

/*************************************************
// display glossary
*************************************************/

$sql = db_query("SELECT id, term, definition, url FROM glossary WHERE course_id = '$cours_id'");
if (mysql_num_rows($sql) > 0) { 
	$tool_content .= "
	       <script type='text/javascript' src='../auth/sorttable.js'></script>
  <table class='sortable' id='t2' width='100%'>";
	$tool_content .= "
	       <tr>
		 <th><div align='left'>$langGlossaryTerm</div></th>
		 <th><div align='left'>$langGlossaryDefinition</div></th>";
	    if ($is_adminOfCourse) {
		 $tool_content .= "
		 <th width='20'>$langActions</th>";
	    }
	$tool_content .= "
	       </tr>";
	$i=0;
	while ($g = mysql_fetch_array($sql)) {
		if ($i%2) {
		   $rowClass = "class='odd'";
		} else {
		   $rowClass = "class='even'";
		}
		if (!empty($g['url'])) {
		    $urllink = "<br /><span class='smaller'>(<a href='" . q($g['url']) .
			       "' target='_blank'>" . q($g['url']) . "</a>)</span>";
		} else {
		    $urllink = '';
		}

		if (!empty($g['definition'])) {
		    $definition_data = "" . q($g['definition']) ."";
		} else {
		    $definition_data = '-';
		}

	    $tool_content .= "
	       <tr $rowClass>
		 <th width='150'>" . q($g['term']) . "</th> 
                 <td><em>$definition_data</em>$urllink</td>";
	    if ($is_adminOfCourse) {
		$tool_content .= "
		 <td align='center' valign='top' width='50'><a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;edit=$g[id]'>
		    <img src='$themeimg/edit.png' /></a>
                    <a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;delete=$g[id]' onClick=\"return confirmation('" .
                        js_escape($langConfirmDelete) . "');\">
		    <img src='$themeimg/delete.png' /></a>
		 </td>";
	    }
	    $tool_content .= "
	       </tr>";
	    $i++;
	}
	$tool_content .= "
	       </table>
	     
	       <br />\n";

} else {
	$tool_content .= "<p class='alert1'>$langNoResult</p>";
}

draw($tool_content, 2, '', $head_content);


/*******************************************/
function findorder($course_id)
{
    $sql = db_query("SELECT MAX(`ORDER`) FROM glossary WHERE course_id = $course_id");
    list($maxorder) = mysql_fetch_row($sql);
    if ($maxorder > 0) {
        $maxorder++;
        return $maxorder;
    } else {
        $maxorder = 1;
        return $maxorder;
    }                         
}
