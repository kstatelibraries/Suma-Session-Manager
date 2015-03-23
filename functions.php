<?
function ShowEntries ($init, $offset=0, $entries_per_page=60, $and_where="", $hour_focus="") { 
    $q = 'SELECT `session`.*,count(`number`) as Counts FROM `session`,`count` WHERE session.fk_initiative = '.$init.' AND session.id = count.fk_session AND count.number = 1 '. $and_where .' GROUP BY fk_session ORDER BY `session`.`id` DESC LIMIT '.$offset.','.$entries_per_page;
print '<p>'.$q.'</p>';

//display forward and back controls by date or by sessions
if (isset($_REQUEST['date_search'])) {
    list ($year, $month, $day) = preg_split ("/\-/", $_REQUEST['date_search']);
    if (isset($day) && strlen($day)==2) {
        $next_date= date("Y-m-d", strtotime($_REQUEST['date_search'] . '+ 1 day'));
        $previous_date= date("Y-m-d", strtotime($_REQUEST['date_search'] . '- 1 day'));
    }
    
    print '<form action="?" method="post"><input type="hidden" name="date_search" value="'.$previous_date.'"><input type="submit" value="&laquo; '.$previous_date.'"></form>'.PHP_EOL;
    print '<form action="?" method="post"><input type="hidden" name="date_search" value="'.$next_date.'"><input type="submit" value="'.$next_date.' &raquo;"></form>'.PHP_EOL;
}
else {
    $next_offset_older = $offset+$entries_per_page;
    print '<form action="?" method="post"><input type="hidden" name="offset" value="'.$next_offset_older.'"><input type="submit" value="Previous '.$entries_per_page.' Entries"></form>'.PHP_EOL;
    
    if ($offset > 0) { 
        $next_offset_newer = $offset-$entries_per_page;
        if ($next_offset_newer < 0) { $next_offset_newer = 0; }
        print '<form action="?" method="post"><input type="hidden" name="offset" value="'.$next_offset_newer.'"><input type="submit" value="Next Newer '.$entries_per_page.' Entries"></form>'.PHP_EOL;
    }
}


$r = mysql_query($q);


while ($myrow = mysql_fetch_assoc($r)) {
    $headers = array_keys($myrow);
    if (isset($hour_focus) && (preg_match("/$hour_focus/", $myrow['start']))) {
        $class = ' class="hour-focus"';
    }
    else { $class =''; }

    $rows .= ' <tr'.$class.'>'.PHP_EOL;
    foreach ($headers as $k) {
        $rows .= '  <td class="'.$k.'">'.$myrow[$k].'</td>'.PHP_EOL;
    }
    if ($myrow['deleted'] == 0) {
        $rows .= '<td><form action="?" method="post"><input type="hidden" name="action" value="delete_session"><input type="hidden" name="session_id" value="' .$myrow['id'] .'"><input type="submit" value="Delete"></form></td>'.PHP_EOL;
    }
    elseif ($myrow['deleted'] == 1) {
        $rows .= '<td><form action="?" method="post"><input type="hidden" name="action" value="undelete_session"><input type="hidden" name="session_id" value="' .$myrow['id'] .'"><input type="submit" value="Undelete"></form></td>'.PHP_EOL;
    }
        
    $rows .= '  <td><form action="?" method="post"><input type="hidden" name="action" value="move_session"><input type="hidden" name="session_id" value="' .$myrow['id'] .'"><input type="hidden" name="transaction_id" value="'. $myrow['fk_transaction'] .'">Adjust Time by: ' . DisplayAdjustor() . '</form></td>'. PHP_EOL;
    $rows .= ' </tr>'.PHP_EOL;
} // end while myrow
$header = join('</th><th>',$headers);
$header = '<tr><th>'.$header.'</th></tr>'.PHP_EOL;
$rows = '<table>'. $header . $rows .'</table>'.PHP_EOL;
print ($rows);
} //end function ShowEntries

function DisplayAdjustor() {
    $opts = array ("-60 min" => "subtime 01:00:00",
                   "-30 min" => "subtime 00:30:00",
                   "-15 min" => "subtime 00:15:00",
                   "-10 min" => "subtime 00:10:00",
                   "-5 min"  => "subtime 00:05:00",
                   "+5 min"  => "addtime 00:05:00",
                   "+10 min" => "addtime 00:10:00",
                   "+15 min" => "addtime 00:15:00",
                   "+30 min" => "addtime 00:30:00",
                   "+60 min" => "addtime 01:00:00");
    $select = "<option>Choose One:</option>\n";

    foreach ($opts as $disp => $val) {
        $select .= "<option value=\"$val\">$disp</option>\n";
    }
    return '<select class="row-select" name="time_shift">'.$select.'</select> <button class="adjust-time">Go</button>'.PHP_EOL; 
}

function MoveSession($session_id, $transaction_id, $time_shift) {
    list($action, $hms) = split(" ", $time_shift);
    $q1 = 'UPDATE `session` SET `start` = '.$action.' (`start`, "'.$hms.'"), `end` = '.$action.'(`end`, "'.$hms.'") WHERE `id` = "'.$session_id.'"';
    $q2 = 'UPDATE `transaction` SET `start` = '.$action.' (`start`, "'.$hms.'"), `end` = '.$action.'(`end`, "'.$hms.'") WHERE `id` = "'.$transaction_id.'"';
    $q3 = 'UPDATE `count` SET `occurrence` = '.$action.' (`occurrence`, "'.$hms.'") WHERE `fk_session` = "'.$session_id.'"';
    $queries = array ($q1,$q2,$q3);
    foreach ($queries as $q) {
        if (mysql_query($q)) {
            print '<li>SUCCESS: '.$q.'</li>'.PHP_EOL;
        }
        else {
            print '<li>FAILED TO EXECUTE: '. $q .'</li>'.PHP_EOL;
        }   
    }
}


function DeleteUndelete($action, $id) {
    if ($action == "delete") { $dvalue = 1; }
    elseif ($action == "undelete") { $dvalue = 0; }
    $q = 'UPDATE session SET `deleted` = '.$dvalue.' WHERE `id` = "'.$id.'"';

    if (mysql_query($q)) {
        print '<p>SUCCESS: '.$q.'</p>'.PHP_EOL;
    }
    else {
        print '<p>FAILED TO EXECUTE: '. $q .'</p>'.PHP_EOL;
    }
} //end function DeleteUndelete


function ShowMultiHours($init) {
    $q = "SELECT CONCAT( DATE(`start`) , ' ', HOUR(`start`) ) AS DateHour, count( * ) AS HourCount FROM `session` WHERE fk_initiative = '".$init."' GROUP BY HOUR(`start`) , DATE(`start`) HAVING HourCount > 1 ORDER BY DateHour DESC";
    print "<p>$q</p>\n";;
    $r = mysql_query($q);
    if (mysql_num_rows($r) == 0) {
        print '<p>No hours with multiple entries found</p>';
    }
    else {
        while ($myrow = mysql_fetch_assoc($r)) {
            if (! ($headers)) 
                $headers = array_keys($myrow);
            $rows .=  '<tr>'.PHP_EOL;
            foreach ($headers as $k) {
                if ($k == "DateHour") {
                    list ($date,$hour) = preg_split("/ /",$myrow[$k]);
                    if ($hour < 10) { $display_hour = '0'.$hour; }
                    else { $display_hour = $hour; }
                    $myrow[$k] = '<a href="?date_search='.$date.'&hour_focus='.$date.' '.$display_hour.'">' .$date. ' '.$display_hour.'00</a>';
                }
                $rows .= '  <td class="'.$k.'">'.$myrow[$k].'</td>'.PHP_EOL;
            }
            $rows .= ' </tr>'.PHP_EOL;
        } // end while myrow
        $header = join('</th><th>',$headers);
        $header = '<tr><th>'.$header.'</th></tr>'.PHP_EOL;
        if ($table_id != '') { $id = ' id="'.$table_id.'"'; }
        $rows = '<table id="multi-hours">'.$header.$rows.'</table>'.PHP_EOL;
        print($rows);
    }
}


function SelectInitiative($default_init) {
global $sumaserver_url;
$url = $sumaserver_url . "/clientinit";
if ($json = file_get_contents($url)) {
$response = json_decode($json);
$opts = " <option value=\"\">Select an initiative</option>\n";
foreach ($response as $init) {
    if ($init->initiativeId == $default_init)  {
        $selected=" selected";
    }
    else { $selected = ""; }
$opts.=' <option value="'. $init->initiativeId.'"'.$selected.'>'. $init->initiativeTitle .'</option>\n';
}
$select = "<label for=\"initiative\">Initiative</label> <select name=\"initiative\" id=\"initiative-selector\">\n$opts</select>\n";
return ($select);
} //end if good response
else {
return '<div class="alert"><h3>Unable to connect to Suma Server</h3><p>Unable to connect to the Suma Server using the url defined as <strong>$sumaserver_url = '.$sumaserver_url.'</strong> in the <strong>config.php</strong> file. Please check this url.</p> <p>A useful test of correctness is this: if you can add <strong>/clientinit</strong> to the url, you should get a list of your suma initiatives, e.g. <a href="'.$url.'">'.$url.'</a>.' . PHP_EOL;
} //end if unable to reach sumaserver
} //end function SelectInitiative

?>