<?php
// ------------------------------------------------------------------
// PHP to read the ARM excel sheet and add match
// ------------------------------------------------------------------

// ------------------------------------------------------------------
// Debug
// ------------------------------------------------------------------

// Debug enable/disable
$debug_msg = 1;

// ------------------------------------------------------------------
// ------------------------------------------------------------------
//
//  DATABASE CONNECTION
//
// ------------------------------------------------------------------
// ------------------------------------------------------------------
// Initialize DB variables
// ------------------------------------------------------------------
$main_user_name = "root";
$main_password  = "";
$main_database  = "clubcricket";
$main_server    = "localhost";

$main_db_handle = mysql_connect($main_server, $main_user_name, $main_password);
$main_db_found  = mysql_select_db($main_database, $main_db_handle);
// ------------------------------------------------------------------
// ------------------------------------------------------------------
// Functions
// ------------------------------------------------------------------
include 'functions.php';
// ------------------------------------------------------------------
// ------------------------------------------------------------------
// Read player list
// ------------------------------------------------------------------
$SQL = "SELECT * FROM player_list";
$sql_result = mysql_query($SQL);

while($db_field = mysql_fetch_array($sql_result)) {
    $f_name = $db_field["first_name"];
    $l_name = $db_field["last_name"];
    $player_list_name[$db_field["id"]] = "$f_name $l_name";
}
// ------------------------------------------------------------------
// ------------------------------------------------------------------
// Read ground list
// ------------------------------------------------------------------
$SQL = "SELECT * FROM ground_list";
$sql_result = mysql_query($SQL);

while($db_field = mysql_fetch_array($sql_result)) {
    $ground_list_name[$db_field["id"]] = $db_field["name"];
}
// ------------------------------------------------------------------
// ------------------------------------------------------------------
// Read team list
// ------------------------------------------------------------------
$SQL = "SELECT * FROM team_list";
$sql_result = mysql_query($SQL);
while($db_field = mysql_fetch_array($sql_result)) {
    $team_list_name[$db_field["id"]] = $db_field["name"];
}
// ------------------------------------------------------------------
// ------------------------------------------------------------------
//
// Bowling list
//
// ------------------------------------------------------------------
// ------------------------------------------------------------------
// Read bowling list excel sheet
// ------------------------------------------------------------------
$bl_list_file_handle = @fopen('bl_list.csv', "r") or exit("Unable to open file!");
$i = 0;
while(!feof($bl_list_file_handle))
{
    $curr_line = fgets($bl_list_file_handle);
    $split = array();
    $split = preg_split('%[,]+%', $curr_line);
    foreach ($split as $key => $val) {
        switch ($key)
            {
            case 0: $bl_list_date[$i]         = format_date($val); break;
            case 1: $bl_list_inn_type[$i]     = $val; break;
            case 2: $bl_list_bl_id[$i]        = get_player_id($val); break;
            case 3: $bl_list_num_overs[$i]    = $val; break;
            case 4: $bl_list_num_maidens[$i]  = $val; break;
            case 5: $bl_list_num_runs[$i]     = $val; break;
            case 6: $bl_list_num_wickets[$i]  = $val; break;
            case 7: $bl_list_num_wd[$i]       = $val; break;
            case 8: $bl_list_num_nb[$i]       = $val; break;
            }
    }
    $i = $i + 1;
}

fclose($bl_list_file_handle);

// ------------------------------------------------------------------
// Find the match id
// ------------------------------------------------------------------
$date_old = $bl_list_date[0];
$order = 0;
foreach ($bl_list_date as $key=>$val) {
    $bl_list_match_id[$key] = get_match_id_from_date($val);
    if ($val == $date_old) {
        $order = $order + 1;
    } else {
        $order = 1;
    }
    $bl_list_order[$key] = $order;
    $date_old = $val;
}

// ------------------------------------------------------------------
// VALIDATION
// ------------------------------------------------------------------
$found_error = 0;
foreach ($bl_list_match_id as $key=>$val) {
    if ($bl_list_bl_id[$key]==50000) {
        echo "ERROR: Could not find the bowl ID for match ID $key<br/>";
        $found_error = 1;
    }
}
// ------------------------------------------------------------------
// Print
// ------------------------------------------------------------------
if ($debug_msg!=0) {
    foreach ($bl_list_date as $key=>$val) {
        $bl_id = $bl_list_bl_id[$key];
        // Bowler
        if ($bl_id==0) {
            $bl_name = "Unsure";
        } else if ($bl_id==50000) {
            $bl_name = "not_found";
        } else {
            $bl_name = $player_list_name[$bl_id];
        }
        echo "match_id==$bl_list_match_id[$key],
              date==$val,
              order==$bl_list_order[$key]
              bl_name==$bl_name,
              num_overs==$bl_list_num_overs[$key],
              num_maidens==$bl_list_num_maidens[$key],
              num_runs==$bl_list_num_runs[$key],
              num_wickets==$bl_list_num_wickets[$key],
              num_wd==$bl_list_num_wd[$key],
              num_nb==$bl_list_num_nb[$key]<br/>";
    }
}

if ($found_error==0) {
    foreach ($bl_list_date as $key=>$val) {
        $put_match_id     = $bl_list_match_id[$key];
        $put_bl_id        = $bl_list_bl_id[$key];
        $put_order        = $bl_list_order[$key];
        $put_num_overs    = $bl_list_num_overs[$key];
        $put_num_maidens  = $bl_list_num_maidens[$key];
        $put_num_runs     = $bl_list_num_runs[$key];
        $put_num_wickets  = $bl_list_num_wickets[$key];
        $put_num_nb       = $bl_list_num_nb[$key];
        $put_num_wd       = $bl_list_num_wd[$key];

        if ($bl_list_inn_type[$key]==1) {
            $bl_list_field="`match_id`, `bl_id`, `order`, `num_overs`, `num_maidens`, `num_runs`, `num_wickets`, `num_nb`, `num_wd`";
            $bl_list_value="$put_match_id, $put_bl_id, $put_order, $put_num_overs, $put_num_maidens, $put_num_runs, $put_num_wickets, $put_num_nb, $put_num_wd";
            $SQL="INSERT INTO `$main_database`.`inn1_bl_list` ($bl_list_field) VALUES ($bl_list_value)";
            echo "$SQL<br/>";
            $result_sql = FALSE;
            //$result_sql=mysql_query($SQL);
            if ($result_sql==TRUE) {
                echo "Innings-1 update == SUCCESSFUL<br/>";
            } else {
                echo "Innings-1 update == FAILED<br/>";
            }
        } else {
            $bl_list_field="`match_id`, `bl_id`, `order`, `num_overs`, `num_maidens`, `num_runs`, `num_wickets`, `num_nb`, `num_wd`";
            $bl_list_value="$put_match_id, $put_bl_id, $put_order, $put_num_overs, $put_num_maidens, $put_num_runs, $put_num_wickets, $put_num_nb, $put_num_wd";
            $SQL="INSERT INTO `$main_database`.`inn2_bl_list` ($bl_list_field) VALUES ($bl_list_value)";
            echo "$SQL<br/>";
            $result_sql = FALSE;
            //$result_sql=mysql_query($SQL);
            if ($result_sql==TRUE) {
                echo "Innings-2 update == SUCCESSFUL<br/>";
            } else {
                echo "Innings-2 update == FAILED<br/>";
            }
        }
    }
}
// ------------------------------------------------------------------
// Close session
// ------------------------------------------------------------------
//
mysql_close($main_db_handle);
//
// ------------------------------------------------------------------

// --CODE ENDS HERE--
?>