<?php
// ------------------------------------------------------------------
// PHP to read the ARM excel sheet and add match
// ------------------------------------------------------------------

// ------------------------------------------------------------------
// Debug
// ------------------------------------------------------------------

// Debug enable/disable
$debug_msg = 0;

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
// Read match list excel sheet
// ------------------------------------------------------------------
$match_list_file_handle = @fopen('match_list.csv', "r") or exit("Unable to open file!");

$i = get_next_match_id();

while(!feof($match_list_file_handle))
{
    $curr_line = fgets($match_list_file_handle);
    $split = array();
    $split = preg_split('%[,]+%', $curr_line);
    foreach ($split as $key => $val) {
        switch ($key)
            {
            case 0: $match_list_date[$i]         = format_date($val); break;
            case 5: $match_list_type_id[$i]      = get_match_type_id($val); break;
            case 3: $match_list_ground_id[$i]    = get_ground_id($val); break;
            case 1: $match_list_inn1_team_id[$i] = get_team_id_from_name($val); break;
            case 2: $match_list_inn2_team_id[$i] = get_team_id_from_name($val); break;
            case 14: $match_list_result_id[$i]   = get_result_id($val); break;
            case 4: $match_list_home_away[$i]    = get_home_away($val); break;
            case 6: $match_list_num_overs[$i]    = $val; break;
            case 7: $match_list_overs_type[$i]   = $val; break;
            // Miscellaneous
            case 9: $match_list_inn1_b[$i]       = $val; break;
            case 10: $match_list_inn1_lb[$i]     = $val; break;
            case 12: $match_list_inn2_b[$i]      = $val; break;
            case 13: $match_list_inn2_lb[$i]     = $val; break;
            }
    }
    $match_list_description[$i]  = "ARMBLR";
    $match_list_is_legacy[$i]    = 0;
    $match_list_status_id[$i]    = 2;
    $i = $i + 1;
}

fclose($match_list_file_handle);

// VALIDATION
$found_error = 0;
foreach ($match_list_date as $key=>$val) {
    if ($match_list_ground_id[$key]==0) {
        echo "ERROR: Could not find a ground ID for match ID $key<br/>";
        $found_error = 1;
    }
    if ($match_list_inn1_team_id[$key]==0) {
        echo "ERROR: Could not find a innings 1 team ID for match ID $key<br/>";
        $found_error = 1;
    }
    if ($match_list_inn2_team_id[$key]==0) {
        echo "ERROR: Could not find a innings 2 team ID for match ID $key<br/>";
        $found_error = 1;
    }
}

if ($debug_msg!=0) {
    foreach ($match_list_date as $key=>$val) {
        $ground_id    = $match_list_ground_id[$key];
        $inn1_team_id = $match_list_inn1_team_id[$key];
        $inn2_team_id = $match_list_inn2_team_id[$key];
        if ($ground_id!=0) {
            $echo_ground_name    = $ground_list_name[$ground_id];
        } else {
            $echo_ground_name    = "dummy_ground";
        }
        if ($inn1_team_id!=0) {
            $echo_inn1_team_name = $team_list_name[$inn1_team_id];
        } else {
            $echo_inn1_team_name = "dummy_team1_name";
        }
        if ($inn2_team_id!=0) {
            $echo_inn2_team_name = $team_list_name[$inn2_team_id];
        } else {
            $echo_inn2_team_name = "dummy_team2_name";
        }
        echo "id==$key,
              date==$val,
              inn1_name==$echo_inn1_team_name,
              inn2_name==$echo_inn2_team_name,
              gnd_name==$echo_ground_name,
              match_type==$match_list_type_id[$key],
              result_id==$match_list_result_id[$key],
              home_away==$match_list_home_away[$key],
              num_overs==$match_list_num_overs[$key],
              overs_type==$match_list_overs_type[$key],
              inn1_b==$match_list_inn1_b[$key],
              inn1_lb==$match_list_inn1_lb[$key],
              inn2_b==$match_list_inn2_b[$key],
              inn2_lb==$match_list_inn2_lb[$key]<br/>";
    }
}
// ------------------------------------------------------------------
// ------------------------------------------------------------------
// Add match list
// ------------------------------------------------------------------

if ($found_error==0) {
    foreach ($match_list_date as $key=>$val) {
        $put_id           = $key;
        $put_date         = $val;
        $put_type_id      = $match_list_type_id[$key];
        $put_ground_id    = $match_list_ground_id[$key];
        $put_inn1_team_id = $match_list_inn1_team_id[$key];
        $put_inn2_team_id = $match_list_inn2_team_id[$key];
        $put_result_id    = $match_list_result_id[$key];
        $put_home_away    = $match_list_home_away[$key];
        $put_num_overs    = $match_list_num_overs[$key];
        $put_overs_type   = $match_list_overs_type[$key];
        $put_description  = $match_list_description[$key];
        $put_is_legacy    = $match_list_is_legacy[$key];
        $put_status_id    = $match_list_status_id[$key];

        $match_list_field="`id`, `date`, `type_id`, `ground_id`, `inn1_team_id`, `inn2_team_id`, `result_id`, `home_away`, `num_overs`, `overs_type`, `description`, `is_legacy`, `status_id`";
        $match_list_value="$put_id, '$put_date', $put_type_id, $put_ground_id, $put_inn1_team_id, $put_inn2_team_id, $put_result_id, $put_home_away, $put_num_overs, $put_overs_type, '$put_description', $put_is_legacy, $put_status_id";
        $SQL="INSERT INTO `$main_database`.`match_list` ($match_list_field) VALUES ($match_list_value)";
        echo "$SQL<br/>";
        $result_sql = FALSE;
        //$result_sql=mysql_query($SQL);
        if ($result_sql==TRUE) {
            echo "Match list update == SUCCESSFUL<br/>";
        } else {
            echo "Match list update == FAILED<br/>";
        }
    }
}
// ------------------------------------------------------------------
// Add innings 1 extra list
// ------------------------------------------------------------------

if ($found_error==0) {
    foreach ($match_list_date as $key=>$val) {
        $put_match_id = $key;
        $put_num_lb   = $match_list_inn1_lb[$key];
        $put_num_b    = $match_list_inn1_b[$key];

        $xt_list_field="`match_id`, `num_lb`, `num_b`";
        $xt_list_value="$put_match_id, $put_num_lb, $put_num_b";
        $SQL="INSERT INTO `$main_database`.`inn1_xt_list` ($xt_list_field) VALUES ($xt_list_value)";
        echo "$SQL<br/>";
        //$result_sql=mysql_query($SQL);
    }
}
// ------------------------------------------------------------------
// Add innings 2 extra list
// ------------------------------------------------------------------

if ($found_error==0) {
    foreach ($match_list_date as $key=>$val) {
        $put_match_id = $key;
        $put_num_lb   = $match_list_inn2_lb[$key];
        $put_num_b    = $match_list_inn2_b[$key];

        $xt_list_field="`match_id`, `num_lb`, `num_b`";
        $xt_list_value="$put_match_id, $put_num_lb, $put_num_b";
        $SQL="INSERT INTO `$main_database`.`inn2_xt_list` ($xt_list_field) VALUES ($xt_list_value)";
        echo "$SQL<br/>";
        //$result_sql=mysql_query($SQL);
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