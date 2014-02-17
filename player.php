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
// Read team list
// ------------------------------------------------------------------
$SQL = "SELECT * FROM team_list";
$sql_result = mysql_query($SQL);
while($db_field = mysql_fetch_array($sql_result)) {
  $team_list_name[$db_field["id"]] = $db_field["name"];
}
// ------------------------------------------------------------------
// ------------------------------------------------------------------
// Global
// ------------------------------------------------------------------
$g_player_arr = array();

// ------------------------------------------------------------------
// Read batting list
// ------------------------------------------------------------------
$bt_list_file_handle = @fopen('bt_list.csv', "r") or exit("Unable to open file!");
$use_id = get_next_player_id(); // *** DO NOT REUSE THIS VARIABLE FOR ANYTHING ELSE ***
$found_error = 0;
while(!feof($bt_list_file_handle))
{
    $curr_line = fgets($bt_list_file_handle);
    $split = array();
    $split = preg_split('%[,]+%', $curr_line);
    $bt_list_date;
    $bt_list_bt_name;
    $bt_list_bt_inn_type;
    $bt_list_bl_name;
    $bt_list_fl_name;
    $bt_list_bl_inn_type;
    $bt_list_fl_inn_type;
    foreach ($split as $key => $val) {
        switch ($key)
            {
            case 0: $bt_list_date         = format_date($val); break;
            case 1: $bt_list_bt_inn_type  = $val; break;
            case 2: $bt_list_bt_name      = $val; break;
            case 9: $bt_list_bl_name      = $val; break;
            case 8: $bt_list_fl_name      = $val; break;
            }
    }
    $bt_list_bl_inn_type = ($bt_list_bt_inn_type==1) ? 2 : 1;
    $bt_list_fl_inn_type = ($bt_list_bt_inn_type==1) ? 2 : 1;

    // Process batsman
    if (!preg_match('/unsure/i',$bt_list_bt_name)) { // not unsure
        if (get_player_id($bt_list_bt_name)==50000) { // player doesn't exist in DB
            if (!array_key_exists($bt_list_bt_name, $g_player_arr)) { // player already not in the list
                $team_id    = get_team_id_from_date($bt_list_date, $bt_list_bt_inn_type);
                if ($team_id==50000) {
                    $found_error = 1;
                    echo "ERROR: $bt_list_bt_name team_id not found<br/>";
                }
                $player_id  = $use_id;
                $f_name     = get_first_name($bt_list_bt_name);
                $l_name     = get_last_name($bt_list_bt_name);
                $player_profile = array(
                                        'id'         => $player_id,
                                        'team_id'    => $team_id,
                                        'first_name' => $f_name,
                                        'last_name'  => $l_name
                                        );
                $g_player_arr[$bt_list_bt_name] = $player_profile;
                $use_id++;
            }
        }
    }
    // Process bowler
    if (!preg_match('/unsure/i',$bt_list_bl_name)) { // not unsure
        if (get_player_id($bt_list_bl_name)==50000) { // player doesn't exist in DB
            if (!array_key_exists($bt_list_bl_name, $g_player_arr)) { // player already not in the list
                $team_id    = get_team_id_from_date($bt_list_date, $bt_list_bl_inn_type);
                if ($team_id==50000) {
                    $found_error = 1;
                    echo "ERROR: $bt_list_bl_name team_id not found<br/>";
                }
                $player_id  = $use_id;
                $f_name     = get_first_name($bt_list_bl_name);
                $l_name     = get_last_name($bt_list_bl_name);
                $player_profile = array(
                                        'id'         => $player_id,
                                        'team_id'    => $team_id,
                                        'first_name' => $f_name,
                                        'last_name'  => $l_name
                                        );
                $g_player_arr[$bt_list_bl_name] = $player_profile;
                $use_id++;
            }
        }
    }
    // Process fielder
    if (!preg_match('/unsure/i',$bt_list_fl_name)) { // not unsure
        if (get_player_id($bt_list_fl_name)==50000) { // player doesn't exist in DB
            if (!array_key_exists($bt_list_fl_name, $g_player_arr)) { // player already not in the list
                $team_id    = get_team_id_from_date($bt_list_date, $bt_list_fl_inn_type);
                if ($team_id==50000) {
                    $found_error = 1;
                    echo "ERROR: $bt_list_fl_name team_id not found<br/>";
                }
                $player_id  = $use_id;
                $f_name     = get_first_name($bt_list_fl_name);
                $l_name     = get_last_name($bt_list_fl_name);
                $player_profile = array(
                                        'id'         => $player_id,
                                        'team_id'    => $team_id,
                                        'first_name' => $f_name,
                                        'last_name'  => $l_name
                                        );
                $g_player_arr[$bt_list_fl_name] = $player_profile;
                $use_id++;
            }
        }
    }
}

fclose($bt_list_file_handle);

// ------------------------------------------------------------------
// Read bowling list
// ------------------------------------------------------------------
$bl_list_file_handle = @fopen('bl_list.csv', "r") or exit("Unable to open file!");
while(!feof($bl_list_file_handle))
{
    $curr_line = fgets($bl_list_file_handle);
    $split = array();
    $split = preg_split('%[,]+%', $curr_line);
    $bl_list_date;
    $bl_list_bl_name;
    $bl_list_bl_inn_type;
    foreach ($split as $key => $val) {
        switch ($key)
            {
            case 0: $bl_list_date         = format_date($val); break;
            case 1: $bl_list_bl_inn_type  = $val; break;
            case 2: $bl_list_bl_name      = $val; break;
            }
    }

    // Process bowler
    if (!preg_match('/unsure/i',$bl_list_bl_name)) { // not unsure
        if (get_player_id($bl_list_bl_name)==50000) { // player doesn't exist in DB
            if (!array_key_exists($bl_list_bl_name, $g_player_arr)) { // player already not in the list
                $team_id    = get_team_id_from_date($bl_list_date, $bl_list_bl_inn_type);
                if ($team_id==50000) {
                    $found_error = 1;
                    echo "ERROR: $bl_list_bl_name team_id not found<br/>";
                }
                $player_id  = $use_id;
                $f_name     = get_first_name($bl_list_bl_name);
                $l_name     = get_last_name($bl_list_bl_name);
                $player_profile = array(
                                        'id'         => $player_id,
                                        'team_id'    => $team_id,
                                        'first_name' => $f_name,
                                        'last_name'  => $l_name
                                        );
                $g_player_arr[$bl_list_bl_name] = $player_profile;
                $use_id++;
            }
        }
    }
}

fclose($bl_list_file_handle);

// ------------------------------------------------------------------
// Check player
// ------------------------------------------------------------------
if ($debug_msg!=0) {
    foreach ($g_player_arr as $key=>$val) {
        $player_id = $val['id'];
        $team_id   = $val['team_id'];
        $f_name    = $val['first_name'];
        $l_name    = $val['last_name'];
        echo "id==$player_id,
              team_id==$team_id,
              f_name==$f_name,
              l_name==$l_name<br/>";
    }
}
// ------------------------------------------------------------------
// ------------------------------------------------------------------
// Add list
// ------------------------------------------------------------------
if ($found_error==0) {
    foreach ($g_player_arr as $key=>$val) {
        $put_id          = $val['id'];
        $put_team_id     = $val['team_id'];
        $put_first_name  = $val['first_name'];
        $put_last_name   = $val['last_name'];

        $player_list_field="`id`, `team_id`, `first_name`, `last_name`";
        $player_list_value="$put_id, $put_team_id, '$put_first_name', '$put_last_name'";
        $SQL="INSERT INTO `$main_database`.`player_list` ($player_list_field) VALUES ($player_list_value)";
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
// Close session
// ------------------------------------------------------------------
//
mysql_close($main_db_handle);
//
// ------------------------------------------------------------------

// --CODE ENDS HERE--
?>