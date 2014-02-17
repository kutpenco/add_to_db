<?php
// ------------------------------------------------------------------
// PHP script to format date
// ------------------------------------------------------------------
// ------------------------------------------------------------------
// Format date from 01-jun-12 to 2012-06-01
// ------------------------------------------------------------------
function format_date($old_date_format) {
    $split_date = array();
    $split_date = preg_split('%[-]+%', $old_date_format);
    foreach ($split_date as $date_key => $date_val) {
        if ($date_key==0) {
            // this is the day
            $new_date_format = "$date_val";
        } else if ($date_key==1) {
            // this is month
            if (preg_match("/jan/i", $date_val)) {
                $new_date_format = "01-$new_date_format";
            } else if (preg_match("/feb/i", $date_val)) {
                $new_date_format = "02-$new_date_format";
            } else if (preg_match("/mar/i", $date_val)) {
                $new_date_format = "03-$new_date_format";
            } else if (preg_match("/apr/i", $date_val)) {
                $new_date_format = "04-$new_date_format";
            } else if (preg_match("/may/i", $date_val)) {
                $new_date_format = "05-$new_date_format";
            } else if (preg_match("/jun/i", $date_val)) {
                $new_date_format = "06-$new_date_format";
            } else if (preg_match("/jul/i", $date_val)) {
                $new_date_format = "07-$new_date_format";
            } else if (preg_match("/aug/i", $date_val)) {
                $new_date_format = "08-$new_date_format";
            } else if (preg_match("/sep/i", $date_val)) {
                $new_date_format = "09-$new_date_format";
            } else if (preg_match("/oct/i", $date_val)) {
                $new_date_format = "10-$new_date_format";
            } else if (preg_match("/nov/i", $date_val)) {
                $new_date_format = "11-$new_date_format";
            } else if (preg_match("/dec/i", $date_val)) {
                $new_date_format = "12-$new_date_format";
            } else {
                echo "ERROR: No date known for $date_val<br />";
            }
        } else if ($date_key==2) {
            // this is year
            if (preg_match("/^9/i", $date_val)) {
                $new_date_format = "19$date_val-$new_date_format";
            } else {
                $new_date_format = "20$date_val-$new_date_format";
            }
        } else {
            echo "ERROR: No array<br />";
        }
    }
    return $new_date_format;
}
// ------------------------------------------------------------------
// Get out id
// ------------------------------------------------------------------
function get_out_id($how_out_text) {
    // 1 not out
    // 2 bowled
    // 3 caught
    // 4 lbw
    // 5 dnb
    // 6 run out
    // 7 retired
    // 8 stumped
    // 9 c&b
    // 10 hit wicket
    if(preg_match('/Not Out/i',$how_out_text)) {
        return 1;
    } else if(preg_match('/Bowled/i',$how_out_text)) {
        return 2;
    } else if(preg_match('/Ct/i',$how_out_text)) {
        return 3;
    } else if(preg_match('/LBW/i',$how_out_text)) {
        return 4;
    } else if(preg_match('/DNB/i',$how_out_text)) {
        return 5;
    } else if(preg_match('/Run Out/i',$how_out_text)) {
        return 6;
    } else if(preg_match('/Retd/i',$how_out_text)) {
        return 7;
    } else if(preg_match('/Stumped/i',$how_out_text)) {
        return 8;
    } else if(preg_match('/C&B/i',$how_out_text)) {
        return 9;
    } else if(preg_match('/Hit Wicket/i',$how_out_text)) {
        return 10;
    } else {
        echo "ERROR: Could not find how out ID for $how_out_text<br />";
        return 0;
    }
    echo "ERROR: Could not find how out ID for $how_out_text<br />";
    return 0;
}
// ------------------------------------------------------------------
// Get ground id
// ------------------------------------------------------------------
function get_ground_id($use_ground_name) {
    $SQL = "SELECT * FROM ground_list";
    $sql_result = mysql_query($SQL);
    while($db_field = mysql_fetch_array($sql_result)) {
        $local_ground_list_name[$db_field["id"]] = $db_field["name"];
    }
    foreach ($local_ground_list_name as $local_id => $local_ground_name) {
        if(preg_match("/$use_ground_name/i",$local_ground_name)) {
            return $local_id;
        }
    }
    return 0;
}
// ------------------------------------------------------------------
// Get match type id
// ------------------------------------------------------------------
function get_match_type_id($use_match_type_name) {
    if(preg_match('/fr/i',$use_match_type_name)) {
        return 0;
    } else if (preg_match('/l-bz1/i',$use_match_type_name)) {
        return 1;
    } else if (preg_match('/l-bz2/i',$use_match_type_name)) {
        return 2;
    } else {
        return 3;
    }
    return 3;
}
// ------------------------------------------------------------------
// Get result id
// ------------------------------------------------------------------
function get_result_id($result_name_) {
    if(preg_match('/won/i',$result_name_)) {
        return 0;
    } else if (preg_match('/lost/i',$result_name_)) {
        return 1;
    } else if (preg_match('/abandoned/i',$result_name_)) {
        return 2;
    } else if (preg_match('/tie/i',$result_name_)) {
        return 3;
    } else {
        return 4;
    }
}
// ------------------------------------------------------------------
// Get team id from the name
// ------------------------------------------------------------------
function get_team_id_from_name($use_team_name) {
    $SQL = "SELECT * FROM team_list";
    $sql_result = mysql_query($SQL);
    while($db_field = mysql_fetch_array($sql_result)) {
        $local_team_list_name[$db_field["id"]] = $db_field["name"];
    }
    foreach ($local_team_list_name as $local_id => $local_team_name) {
        if(preg_match("/$use_team_name/",$local_team_name)) {
            return $local_id;
        }
    }
    return 0;
}
// ------------------------------------------------------------------
// Get player id
// ------------------------------------------------------------------
function get_player_id($use_player_name) {
    $SQL = "SELECT * FROM player_list";
    $sql_result = mysql_query($SQL);
    while($db_field = mysql_fetch_array($sql_result)) {
        $local_1_name = $db_field["first_name"];
        $local_2_name = $db_field["last_name"];
        $local_player_list_name[$db_field["id"]] = "$local_1_name $local_2_name";
    }
    if (preg_match('/unsure/i',$use_player_name)) {
        return 0;
    } else {
        foreach ($local_player_list_name as $local_id => $local_player_name) {
            if(preg_match("/$use_player_name/i",$local_player_name)) {
                return $local_id;
            }
        }
    }
    return 50000;
}
// ------------------------------------------------------------------
// Get next match id
// ------------------------------------------------------------------
function get_next_match_id() {
    $max_id = 0;
    $SQL = "SELECT * FROM match_list";
    $sql_result = mysql_query($SQL);
    while($db_field = mysql_fetch_array($sql_result)) {
        $curr_id = $db_field["id"];
        if ($curr_id >= $max_id) {
            $max_id = $curr_id;
        }
    }
    return ($max_id + 1);
}
// ------------------------------------------------------------------
// Get home or away
// ------------------------------------------------------------------
function get_home_away($home_away_txt_) {
    if(preg_match('/home/i',$home_away_txt_)) {
        return 0;
    } else {
        return 1;
    }
}
// ------------------------------------------------------------------
// Get match id from date
// ------------------------------------------------------------------
function get_match_id_from_date($use_date_) {
    $SQL = "SELECT * FROM match_list";
    $sql_result = mysql_query($SQL);
    while($db_field = mysql_fetch_array($sql_result)) {
        $curr_id   = $db_field["id"];
        $curr_date = $db_field["date"];
        if(preg_match("/$use_date_/i",$curr_date)) {
            return $curr_id;
        }
    }
    return 50000;
}
// ------------------------------------------------------------------
// Get team id from the date and innings type
// ------------------------------------------------------------------
function get_team_id_from_date($use_date_, $use_inn_type_) {
    $SQL = "SELECT * FROM match_list";
    $sql_result = mysql_query($SQL);
    while($db_field = mysql_fetch_array($sql_result)) {
        $curr_date = $db_field["date"];
        if(preg_match("/$use_date_/i",$curr_date)) {
            if ($use_inn_type_) {
                return $db_field["inn1_team_id"];
            } else {
                return $db_field["inn2_team_id"];
            }
        }
    }
    return 50000;
}
// ------------------------------------------------------------------
// Get next player id
// ------------------------------------------------------------------
function get_next_player_id() {
    $max_id = 0;
    $SQL = "SELECT * FROM player_list";
    $sql_result = mysql_query($SQL);
    while($db_field = mysql_fetch_array($sql_result)) {
        $curr_id = $db_field["id"];
        if ($curr_id >= $max_id) {
            $max_id = $curr_id;
        }
    }
    return ($max_id + 1);
}
// ------------------------------------------------------------------
// Get first name from string of full name
// ------------------------------------------------------------------
function get_first_name($full_name_) {
    $regexp = '/^(\S+)\s(\S+)$/';
    preg_match($regexp, $full_name_, $matches);
    return $matches[1];
}
// ------------------------------------------------------------------
// Get last name from string of full name
// ------------------------------------------------------------------
function get_last_name($full_name_) {
    $regexp = '/^(\S+)\s(\S+)$/';
    preg_match($regexp, $full_name_, $matches);
    return $matches[2];
}
// ------------------------------------------------------------------
// --CODE ENDS HERE--
?>