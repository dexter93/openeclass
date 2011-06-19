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


/*===========================================================================
	detailsAll.php
	@last update: 05-12-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

	      original file: tracking/learnPath_detailsAllPath.php Revision: 1.11

	Claroline authors: Piraux Sebastien <pir@cerdecam.be>
                      Gioacchino Poletto <info@polettogioacchino.com>
==============================================================================
    @Description: This script displays the stats of all users of a course
                  for his progression into the sum of all learning paths of
                  the course

    @Comments:

    @todo:
==============================================================================
*/

require_once("../../include/lib/learnPathLib.inc.php");
$require_current_course = TRUE;
$require_prof = TRUE;

$TABLECOURSUSER	        = "cours_user";
$TABLEUSER              = "user";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

require_once("../../include/baseTheme.php");
$head_content = "";
$tool_content = "";

$navigation[] = array("url"=>"learningPathList.php?course=$code_cours", "name"=> $langLearningPaths);
$nameTools = $langTrackAllPathExplanation;

// display a list of user and their respective progress
$sql = "SELECT U.`nom`, U.`prenom`, U.`user_id`
	FROM `$TABLEUSER` AS U, `$TABLECOURSUSER` AS CU
	WHERE U.`user_id`= CU.`user_id`
	AND CU.`cours_id` = $cours_id
	ORDER BY U.`nom` ASC";

@$tool_content .= get_limited_page_links($sql, 30, $langPreviousPage, $langNextPage);
$usersList = get_limited_list($sql, 30);


$tool_content .= "
  <div id=\"operations_container\">
    <ul id=\"opslist\">
      <li>$langDumpUserDurationToFile: <a href='dumpuserlearnpathdetails.php?course=$code_cours'>$langcsvenc2</a></li>
      <li><a href='dumpuserlearnpathdetails.php?course=$code_cours&amp;enc=1253'>$langcsvenc1</a></li>
    </ul>
  </div>
";

	
// display tab header
$tool_content .= "
  <table width='99%' class='tbl_alt'>
  <tr>
    <th>&nbsp;</th>
    <th class='left'><div align='left'>$langStudent</div></th>
    <th width='120'>$langAm</th>
    <th>$langGroup</th>
    <th colspan='2'>$langProgress&nbsp;&nbsp;</th>
  </tr>\n";

mysql_select_db($currentCourseID);

// display tab content
$k=0;
foreach ($usersList as $user)
{
	// list available learning paths
	$sql = "SELECT LP.`learnPath_id` FROM `$currentCourseID`.lp_learnPath AS LP";

	$learningPathList = db_query_fetch_all($sql);

	$iterator = 1;
	$globalprog = 0;
	if ($k%2 == 0) {
		$tool_content .= "  <tr class=\"even\">\n";
	} else {
		$tool_content .= "  <tr class=\"odd\">\n";
	}
	foreach($learningPathList as $learningPath)
	{
		// % progress
		$prog = get_learnPath_progress($learningPath['learnPath_id'], $user['user_id']);
		if ($prog >= 0)
		{
			$globalprog += $prog;
		}
		$iterator++;
	}
	if($iterator == 1)
	{
		$tool_content .= '    <td class="center" colspan="8">'.$langNoLearningPath.'</td>'."\n".'  </tr>'."\n";
	}
	else
	{
		$total = round($globalprog/($iterator-1));
		$tool_content .= '    <td width="1"><img src="'.$themeimg.'/arrow.png" alt=""></td>'."\n"
		.'    <td><a href="detailsUser.php?course='.$code_cours.'&amp;uInfo='.$user['user_id'].'">'.$user['nom'].' '.$user['prenom'].'</a></td>'."\n"
		.'    <td class="center">'.uid_to_am($user['user_id']).'</td>'."\n"
		.'    <td align="center">'.user_groups($cours_id, $user['user_id']).'</td>'."\n"
		.'    <td class="right" width=\'120\'>'
		.disp_progress_bar($total, 1)
		.'</td>'."\n"
		.'    <td align="left" width=\'10\'>'.$total.'%</td>'."\n"
		.'</tr>'."\n";
	}
	$k++;
}

// foot of table
$tool_content .= '  </table>'."\n\n";
draw($tool_content, 2, '', $head_content);
?>
