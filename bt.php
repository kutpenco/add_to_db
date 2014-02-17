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
// Batting list
//
// ------------------------------------------------------------------
// ------------------------------------------------------------------
// Read batting list excel sheet
// ------------------------------------------------------------------
$bt_list_file_handle = @fopen('bt_list.csv', "r") or exit("Unable to open file!");
$i = 0;
while(!feof($bt_list_file_handle))
{
    //$match_id = get_match_id_from_date(
    $curr_line = fgets($bt_list_file_handle);
    $split = array();
    $split = preg_split('%[,]+%', $curr_line);
    foreach ($split as $key => $val) {
        switch ($key)
            {
            case 0: $bt_list_date[$i]         = format_date($val); break;
            case 1: $bt_list_inn_type[$i]     = $val; break;
            case 2: $bt_list_bt_id[$i]        = get_player_id($val); break;
            case 3: $bt_list_num_runs[$i]     = $val; break;
            case 4: $bt_list_num_balls[$i]    = $val; break;
            case 5: $bt_list_num_4s[$i]       = $val; break;
            case 6: $bt_list_num_6s[$i]       = $val; break;
            case 7: $bt_list_out_id[$i]       = get_out_id($val); break;
            case 8: $bt_list_bl_id[$i]        = get_player_id($val); break;
            case 9: $bt_list_fl_id[$i]        = get_player_id($val); break;
            }
    }
    $i = $i + 1;
}

fclose($bt_list_file_handle);

// ------------------------------------------------------------------
// Find the match id
// ------------------------------------------------------------------
$date_old = $bt_list_date[0];
$order = 0;
foreach ($bt_list_date as $key=>$val) {
    $bt_list_match_id[$key] = get_match_id_from_date($val);
    if ($val == $date_old) {
        $order = $order + 1;
    } else {
        $order = 1;
    }
    $bt_list_order[$key] = $order;
    $date_old = $val;
}

// ------------------------------------------------------------------
// VALIDATION
// ------------------------------------------------------------------
$found_error = 0;
foreach ($bt_list_match_id as $key=>$val) {
    if ($bt_list_bt_id[$key]==50000) {
        echo "ERROR: Could not find the bat ID for match ID $key<br/>";
        $found_error = 1;
    }
    if ($bt_list_bl_id[$key]==50000) {
        echo "ERROR: Could not find the bowl ID for match ID $key<br/>";
        $found_error = 1;
    }
    if ($bt_list_fl_id[$key]==50000) {
        echo "ERROR: Could not find the fielder ID for match ID $key<br/>";
        $found_error = 1;
    }
}

// ------------------------------------------------------------------
// Print
// ------------------------------------------------------------------
if ($debug_msg!=0) {
    foreach ($bt_list_date as $key=>$val) {
        $bt_id = $bt_list_bt_id[$key];
        $bl_id = $bt_list_bl_id[$key];
        $fl_id = $bt_list_fl_id[$key];
        // Better
        if ($bt_id==0) {
            $bt_name = "Unsure";
        } else if ($bt_id==50000) {
            $bt_name = "not_found";
        } else {
            $bt_name = $player_list_name[$bt_id];
        }
        // Bowler
        if ($bl_id==0) {
            $bl_name = "Unsure";
        } else if ($bl_id==50000) {
            $bl_name = "not_found";
        } else {
            $bl_name = $player_list_name[$bl_id];
        }
        // Fielder
        if ($fl_id==0) {
            $fl_name = "Unsure";
        } else if ($fl_id==50000) {
            $fl_name = "not_found";
        } else {
            $fl_name = $player_list_name[$fl_id];
        }
        echo "match_id==$bt_list_match_id[$key],
              date==$val,
              order==$bt_list_order[$key],
              bt_name==$bt_name,
              num_runs==$bt_list_num_runs[$key],
              num_balls==$bt_list_num_balls[$key],
              num_4s==$bt_list_num_4s[$key],
              num_6s==$bt_list_num_6s[$key],
              out_id==$bt_list_out_id[$key],
              bl_name==$bl_name,
              fl_name==$fl_name<br/>";
    }
}

// ------------------------------------------------------------------
// Add batting list
// ------------------------------------------------------------------
if ($found_error==0) {
    foreach ($bt_list_date as $key=>$val) {
        $put_match_id     = $bt_list_match_id[$key];
        $put_order        = $bt_list_order[$key];
        $put_bt_id        = $bt_list_bt_id[$key];
        $put_num_runs     = $bt_list_num_runs[$key];
        $put_num_balls    = $bt_list_num_balls[$key];
        $put_num_4s       = $bt_list_num_4s[$key];
        $put_num_6s       = $bt_list_num_6s[$key];
        $put_out_id       = $bt_list_out_id[$key];
        $put_bl_id        = $bt_list_bl_id[$key];
        $put_fl_id        = $bt_list_fl_id[$key];

        if ($bt_list_inn_type[$key]==1) {
            $bt_list_field="`match_id`, `order`, `bt_id`, `num_runs`, `num_balls`, `num_4s`, `num_6s`, `out_id`, `bl_id`, `fl_id`";
            $bt_list_value="$put_match_id, $put_order, $put_bt_id, $put_num_runs, $put_num_balls, $put_num_4s, $put_num_6s, $put_out_id, $put_bl_id, $put_fl_id";
            $SQL="INSERT INTO `$main_database`.`inn1_bt_list` ($bt_list_field) VALUES ($bt_list_value)";
            echo "$SQL<br/>";
            $result_sql = FALSE;
            //$result_sql=mysql_query($SQL);
            if ($result_sql==TRUE) {
                echo "Innings-1 update == SUCCESSFUL<br/>";
            } else {
                echo "Innings-1 update == FAILED<br/>";
            }
        } else {
            $bt_list_field="`match_id`, `order`, `bt_id`, `num_runs`, `num_balls`, `num_4s`, `num_6s`, `out_id`, `bl_id`, `fl_id`";
            $bt_list_value="$put_match_id, $put_order, $put_bt_id, $put_num_runs, $put_num_balls, $put_num_4s, $put_num_6s, $put_out_id, $put_bl_id, $put_fl_id";
            $SQL="INSERT INTO `$main_database`.`inn2_bt_list` ($bt_list_field) VALUES ($bt_list_value)";
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