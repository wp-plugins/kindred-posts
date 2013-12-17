<?php
/**
 * Create the tables used in the plugin
 *
 * @return null
 **/
function kp_createTable() {
	global $visitTbl;

	$sql = "CREATE TABLE $visitTbl (
		VisitID bigint(20) NOT NULL AUTO_INCREMENT,
		IP varchar(64),
		Visits longtext,
		UserAgent varchar(128),
		DataSent int(1) NOT NULL DEFAULT '0',
		CreateDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		UpdateDate TIMESTAMP DEFAULT '0000-00-00 00:00:00',
		UNIQUE KEY VisitID (VisitID)
	);";

	require_once(ABSPATH . "wp-admin/includes/upgrade.php");
	dbDelta($sql);
 
	return null;
}

/**
 * Delete the visit data in the database
 *
 * @return null
 **/
function kp_resetVisitData($ip = "", $ua = "") {
	global $wpdb, $visitTbl;
	
	if ($ip != "" && $ua != "") {
		$wpdb->query($wpdb->prepare("DELETE FROM $visitTbl WHERE IP = %s AND UserAgent = %s", $ip, $ua));
		
	} else if ($ip != "") {
		$wpdb->query($wpdb->prepare("DELETE FROM $visitTbl WHERE IP = %s", $ip));
		
	} else if ($ua != "") {
		$wpdb->query($wpdb->prepare("DELETE FROM $visitTbl WHERE UserAgent = %s", $ua));
		
	} else {
		$wpdb->query("DELETE FROM $visitTbl");
	}
	
	return null;
}
?>