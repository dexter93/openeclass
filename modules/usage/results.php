<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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



/*
 * Created on 1 June 2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once 'include/libchart/classes/libchart.php';

$usage_defaults = array (
    'u_stats_value' => 'visits',
    'u_interval' => 'daily',
    'u_module_id' => -1,
    'u_date_start' => strftime('%Y-%m-%d', strtotime('now -15 day')),
    'u_date_end' => strftime('%Y-%m-%d', strtotime('now')),
);

foreach ($usage_defaults as $key => $val) {
    if (!isset($_POST[$key])) {
        $$key = $val;
    } else {
        $$key = $_POST[$key];
    }
}

if ($u_module_id != -1) {
    $mod_where = " (module_id = '$u_module_id') ";
} else {
    $mod_where = " (1) ";
}


$date_fmt = '%d-%m-%Y';
$date_where = "(`date_time` BETWEEN '$u_date_start 00:00:00' AND '$u_date_end 23:59:59') ";
$date_what  = "DATE_FORMAT(MIN(`date_time`), '$date_fmt') AS date_start, DATE_FORMAT(MAX(`date_time`), '$date_fmt') AS date_end ";

switch ($u_interval) {
    case "summary":
        $date_group = '1';
        $date_what ='1';
    break;
    case "daily":
        $date_what .= ", DATE_FORMAT(`date_time`, '$date_fmt') AS `date` ";
        $date_group = " DATE(`date_time`) ";
    break;
    case "weekly":
        $date_what .= ", DATE_FORMAT(`date_time` - INTERVAL WEEKDAY(`date_time`) DAY, '$date_fmt') AS week_start ".
                      ", DATE_FORMAT(`date_time` + INTERVAL (6 - WEEKDAY(`date_time`)) DAY, '$date_fmt') AS week_end ";
        $date_group = " WEEK(`date_time`)";
    break;
    case "monthly":
        $date_what .= ", MONTH(`date_time`) AS `month` ";
        $date_group = " MONTH(`date_time`)";
    break;
    case "yearly":
        $date_what .= ", YEAR(`date_time`) AS `year` ";
        $date_group = " YEAR(`date_time`) ";
    break;
}


#check if statistics exist
$chart_content=0;

switch ($u_stats_value) {
    case "visits":
        $chart = new VerticalBarChart(300, 300);
        $dataSet = new XYDataSet();
        $query = "SELECT ".$date_what.", COUNT(*) AS cnt FROM actions
                         WHERE $date_where 
                         AND $mod_where 
                         AND course_id = $course_id
                        GROUP BY $date_group ORDER BY `date_time` ASC";
        
        $result = db_query($query);

        switch ($u_interval) {
            case "summary":
                 while ($row = mysql_fetch_assoc($result)) {
                    $dataSet->addPoint(new Point($langSummary, $row['cnt']));
                    $chart->width += 25;
                    $chart_content = 1;
                    }
            break;
            case "daily":
                    while ($row = mysql_fetch_assoc($result)) {
                        $dataSet->addPoint(new Point($row['date'], $row['cnt']));
                        $chart->width += 25;
                        $chart_content = 1;
                    }
            break;
            case "weekly":
                while ($row = mysql_fetch_assoc($result)) {                    
                    $dataSet->addPoint(new Point($row['week_start'].' - '.$row['week_end'], $row['cnt']));
                    $chart->width += 25;
                    $chart_content = 1;
                }
            break;
            case "monthly":
                while ($row = mysql_fetch_assoc($result)) {
                    $dataSet->addPoint(new Point($langMonths[$row['month']], $row['cnt']));
                    $chart->width += 25;
                    $chart_content = 1;
                }
            break;
            case "yearly":
                while ($row = mysql_fetch_assoc($result)) {
                    $dataSet->addPoint(new Point($row['year'], $row['cnt']));
                    $chart->width += 25;
                    $chart_content = 1;
                }
            break;
        }
        $chart->setTitle("$langVisits");

    break;
    case "duration":        
            
            $query = "SELECT ".$date_what." , SUM(duration) AS tot_dur
                FROM actions 
                WHERE $date_where 
                AND $mod_where
                AND course_id = $course_id
                GROUP BY ".$date_group." ORDER BY date_time ASC";

        $result = db_query($query);
        $chart = new VerticalBarChart(200, 300);
        $dataSet = new XYDataSet();
	switch ($u_interval) {
            case "summary":
                while ($row = mysql_fetch_assoc($result)) {
		    $row['tot_dur'] = round($row['tot_dur'] / 60);
		    $dataSet->addPoint(new Point($langSummary, $row['tot_dur']));
                    $chart->width += 25;
                    $chart_content=1;
                }
          break;
          case "daily":
             while ($row = mysql_fetch_assoc($result)) {
		 $row['tot_dur'] = round($row['tot_dur'] / 60);
                 $dataSet->addPoint(new Point($row['date'], $row['tot_dur']));
                 $chart->width += 25;
                 $chart_content=1;
             }
         break;
         case "weekly":
             while ($row = mysql_fetch_assoc($result)) {
		$row['tot_dur'] = round($row['tot_dur'] / 60);		
                $dataSet->addPoint(new Point($row['week_start'].' - '.$row['week_end'], $row['tot_dur']));
                $chart->width += 25;
                $chart_content=1;
             }
         break;
         case "monthly":
            while ($row = mysql_fetch_assoc($result)) {
		$row['tot_dur'] = round($row['tot_dur'] / 60);
                $dataSet->addPoint(new Point($langMonths[$row['month']], $row['tot_dur']));
                $chart->width += 25;
                $chart_content=1;
            }
         break;
         case "yearly":
            while ($row = mysql_fetch_assoc($result)) {
		$row['tot_dur'] = round($row['tot_dur'] / 60);
                $dataSet->addPoint(new Point($row['year'], $row['tot_dur']));
                $chart->width += 25;
                $chart_content=1;
            }
         break;
       }

    $chart->setTitle("$langDurationVisits");
    $tool_content .= "<p>$langDurationExpl</p>";

    break;
}
mysql_free_result($result);


$chart->setDataSet($dataSet);
$chart_path = 'courses/'.$course_code.'/temp/chart_'.md5(serialize($chart)).'.png';
$chart->render($webDir."/".$chart_path);

if ($chart_content) {
        $tool_content .= '<p align="center"><img src="'.$urlServer.$chart_path.'" /></p>';
} elseif (isset($btnUsage) and $chart_content == 0) {
        $tool_content .='<p class="alert1">'.$langNoStatistics.'</p>';
}