<?php
/**
 * Create the tables used in the plugin
 *
 * @return null
 **/
function kp_createTable() {
	global $visitTbl;
	global $wpdb;
	global $kp_db_version;
	
	$installed_ver = get_option("kp_db_version", "");
	if ($installed_ver != $kp_db_version) {
		$sql = "CREATE TABLE $visitTbl (
			VisitID bigint(20) NOT NULL AUTO_INCREMENT,
			IP varchar(64),
			Visits longtext,
			UserAgent varchar(128),
			TestData int(1) NOT NULL DEFAULT '0',
			DataSent int(1) NOT NULL DEFAULT '0',
			CreateDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			UpdateDate TIMESTAMP DEFAULT '0000-00-00 00:00:00',
			UNIQUE KEY VisitID (VisitID)
		);";

		require_once(ABSPATH . "wp-admin/includes/upgrade.php");
		dbDelta($sql);
		
		update_option("kp_db_version", $kp_db_version);
	}
	
	return null;
}

/**
 * Check if we need to update the database
 *
 * @return null
 **/
function kp_dbCheck() {
	global $kp_db_version;
    if (get_option('kp_db_version', "") != $kp_db_version) {
        kp_createTable();
    }
	
	return null;
}

/**
 * Delete test mode data from the database
 *
 * @return null
 **/
function kp_deleteTestModeData() {
	global $wpdb, $visitTbl;
	$wpdb->query("DELETE FROM $visitTbl WHERE TestData='1'");
	return null;
}

/**
 * Check if we have test data in the database
 *
 * @return bool
 **/
function kp_haveTestModeData() {
	global $wpdb, $visitTbl;
	
	$numRows = $wpdb->get_var("SELECT COUNT(*) FROM $visitTbl WHERE TestData='1'");
	return ($numRows > 0);
}

/**
 * Insert test mode data into the database
 *
 * @return null
 **/
function kp_insertTestModeData() {
	global $wpdb, $visitTbl, $numTestModeFakeUsers, $numTestModeFakeVisits;
	
	// Get a list of published posts in the database
	$args = array('post_status' => 'publish', 'orderby' => 'rand', 'posts_per_page' => '100');
	$posts = get_posts($args);
	
	// Save number of visits to a random set of posts from fake users into the database
	$i = 0;
	while ($i < $numTestModeFakeUsers) {
		$ipAddress = rand(1, 1000000000);
		$userAgent = rand(1, 1000000000);
		$visits = array();	
	
		$j = 0;
		while ($j < $numTestModeFakeVisits) {
			$post = $posts[rand(0, count($posts)-1)];
			if (!isset($visits[$post->ID])) {
				$visits[$post->ID] = 0;
			}
			$visits[$post->ID]++;			
			
			$j++;
		}

		$wpdb->insert($visitTbl, array("Visits" => serialize($visits), "IP" => $ipAddress, "UserAgent" => $userAgent, "TestData" => 1));		
		$i++;
	}
	
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