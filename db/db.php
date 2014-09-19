<?php
/**
 * Check if we need to update the database
 *
 * @return null
 **/
function kp_dbCheck() {
	global $kp_DbVersion;
    if (get_option("kp_DbVersion", "") != $kp_DbVersion) {
        kp_updateDatabase();
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
 * @param string $ip: The IP to remove from the visit table
 * @param string $ua: The User Agent to remove from the visit table
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
	
	// If both are blank, then delete all the rows
	} else {
		$wpdb->query("DELETE FROM $visitTbl");
	}
	
	return null;
}

/**
 * Handle creating and updating the database tables used in the plugin
 *
 * @return null
 *
 * @since 1.3.0
 */
function kp_updateDatabase() {
	global $visitTbl;
	global $wpdb;
	global $kp_DbVersion;
	
	$currentVersion = get_option("kp_DbVersion", "0.0");
	
	$sql = "";
	$newVersion = "0.0";
	if ($currentVersion == "0.0") {
		$newVersion = "1.1";
		$sql = "CREATE TABLE IF NOT EXISTS $visitTbl (
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
	}
	// In the future we will add more if statements here to alter / add tables based on version
	if ($currentVersion == "1.1") {
		// Run the update get from 1.1 to x.x
	}
	
	// Run the database update and call this function again to see if we need to make more updates
	if ($sql != "") {
		require_once(ABSPATH . "wp-admin/includes/upgrade.php");
		dbDelta($sql);
		update_option("kp_DbVersion", $newVersion);
		kp_updateDatabase();
	}
	
	return null;
}