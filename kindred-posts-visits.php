<?php
function kp_ConstructBotSQLString($GetCount = false){
	global $BotArr, $VisitTbl;
	if ($GetCount){
		$sql = "SELECT COUNT(*) FROM $VisitTbl";
	} else {
		$sql = "SELECT * FROM $VisitTbl";
	}
	if (count($BotArr) == 0){
		$sql .= " WHERE true = false";
	}
	$First = true;
	$HasMultiple = false;
	foreach ($BotArr As $key => $val){
		if ($First){
			$sql .= " WHERE ";
			$First = false;
		}
		if ($HasMultiple){
			$sql .= " OR ";
		}
		$sql .= " UserAgent LIKE '%" . $val . "%'";
		$HasMultiple = true;
	}
	return $sql;
}

function kp_IsUserBot($ua){
	global $BotArr;
	foreach ($BotArr as $key => $val){
		if (strstr(strtolower($ua), $val)){
			return true;
		}
	}
	return false;
}

function kp_IsUserVisitValid($ip, $ua){
	if (kp_CheckPro()){
		// We want to check if the user is a bot as well that is why there is an IF statement
		if (kp_IsUserVisitValidPro($ip)) {
			return false;
		}
	}
	// Check if the user agent contains bot
	// If it does, return false
	if (get_option('AttemptToBlockBotVisits', "true") == "true"){
		return !kp_IsUserBot($ua);
	}
	return true;
}

function kp_SaveVisit($query){
	global $wp_query;
	global $VisitTbl, $wpdb;
	global $FirstPost;

	// Check that we are on a Page or Post
	if (!is_single() && !is_page() && !$FirstPost) {
		return;	
	}

	if (get_option('CollectStatistics', "true") == "false"){
		return;
	}
	$FirstPost = false;
	
	// Get the user from the visit table (if they exist)
	$ip = "";
	if (isset($_SERVER['REMOTE_ADDR'])){
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	// Save the user agent so we can ignore Bots in our recommendations
	$ua = "";
	if (isset($_SERVER['HTTP_USER_AGENT'])){
		$ua = $_SERVER['HTTP_USER_AGENT'];
		if (strlen($ua) > 128){
			$ua = substr($ua, 0, 128);
		}
	}
	// Check if user is bot or if they have pro version option
	if (!kp_IsUserVisitValid($ip, $ua)){
		return;
	}
	
	$id = $wp_query->post->ID;
	
	$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $VisitTbl WHERE IP=%s", $ip), ARRAY_A);
	// Get the row_id
	if (isset($row["VisitID"])){
		// unserialize the row data
		$Visits = unserialize($row["Visits"]);
		// Add the new visit
		if (isset($Visits[$id])){
			$Visits[$id] += 1;
		} else {
			$Visits[$id] = 1;
		}
		// Update the row
		$wpdb->query($wpdb->prepare("UPDATE $VisitTbl SET Visits=%s, UpdateDate=NOW(), DataSent='0' WHERE IP=%s", serialize($Visits), $ip));
	} else {
		$Visits = array();
		$Visits[$id] = 1;
		$rows_affected = $wpdb->insert($VisitTbl, array('Visits' => serialize($Visits), 'IP' => $ip, 'UserAgent' => $ua));
	}
}
?>