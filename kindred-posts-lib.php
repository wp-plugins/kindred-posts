<?php
/**
 * This file contains various functions required for the Kindred Posts plugin
 **/

/**
 * Check if the user has the premium version of the plugin
 *
 * @return bool: Indicates if they do.
 **/
function kp_checkPro(){
	// If you mess with this function, you run the risk of the plugin not working properly.
	// Additional file(s) are required to have the premium version.
	global $HavePro;
	return $HavePro;
}

/**
 * Check if a string is a valid ip address
 *
 * @param string $ip: The string to check
 * @return bool: Indicates if the string is a valid ip address
 **/
function kp_checkIP($ip) {
	return !empty($ip);
}

/**
 * Determine the user's IP Address
 *
 * @return string: The user's IP Address
 **/
function kp_determineIP() {
	$serverIP = $_SERVER["SERVER_ADDR"];
	
	if (kp_checkIP($_SERVER["HTTP_CLIENT_IP"]) && $_SERVER["HTTP_CLIENT_IP"] != $serverIP) {
		return $_SERVER["HTTP_CLIENT_IP"];
	}

	foreach (explode(",",$_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip) {
		if (kp_checkIP(trim($ip)) && trim($ip) != $serverIP) {
			return $ip;
		}
	}
	
	if (kp_checkIP($_SERVER["HTTP_X_FORWARDED"]) && $_SERVER["HTTP_X_FORWARDED"] != $serverIP) {
		return $_SERVER["HTTP_X_FORWARDED"];
		
	} elseif (kp_checkIP($_SERVER["HTTP_X_CLUSTER_CLIENT_IP"]) && $_SERVER["HTTP_X_CLUSTER_CLIENT_IP"] != $serverIP) {
		return $_SERVER["HTTP_X_CLUSTER_CLIENT_IP"];
		
	} elseif (kp_checkIP($_SERVER["HTTP_FORWARDED_FOR"]) && $_SERVER["HTTP_FORWARDED_FOR"] != $serverIP) {
		return $_SERVER["HTTP_FORWARDED_FOR"];
		
	} elseif (kp_checkIP($_SERVER["HTTP_FORWARDED"]) && $_SERVER["HTTP_FORWARDED"] != $serverIP) {
		return $_SERVER["HTTP_FORWARDED"];
		
	} elseif (kp_checkIP($_SERVER["REMOTE_ADDR"]) && $_SERVER["REMOTE_ADDR"] != $serverIP) {
		return $_SERVER["REMOTE_ADDR"];
		
	} else {
		return $serverIP;
	}
}

/**
 * Get the user's information
 *
 * @return array
 **/
function kp_getUserData(){
	// Get the user from the visit table (if they exist)
	$ip = kp_determineIP();
	if (strlen($ip) > 63) {
		$ip = substr($ip, 0, 63);
	}

	// Save the user agent so we can ignore Bots in our recommendations
	$ua = "";
	if (isset($_SERVER['HTTP_USER_AGENT'])){
		$ua = $_SERVER['HTTP_USER_AGENT'];
		if (strlen($ua) > 127){
			$ua = substr($ua, 0, 127);
		}
	}

	return array("ip" => $ip, "ua" => $ua);
}

/**
 * Checks if the user agent of the user belongs to a bot
 *
 * @param string $ua: The user's user agent
 * @return bool: Indicate whether the user is a bot
 **/
function kp_isUserBot($ua){
	global $botArr; // Set this in configuration
	foreach ($botArr as $key => $val){
		if (strstr(strtolower($ua), $val)){
			return true;
		}
	}
	
	return false;
}

/**
 * Checks if the user visit is valid
 *
 * @param string $ip: The ip address of the user
 * @param string $ua: The user's user agent
 * @return bool: Indicates if the visit is valid
 **/
function kp_isUserVisitValid($ip, $ua){
	if (kp_checkPro()){
		// We want to check if the user is a bot as well that is why there is an IF statement
		if (kp_isUserVisitValidPro($ip)) {
			return false;
		}
	}
	// Check if the user agent contains bot
	// If it does, return false
	if (get_option('AttemptToBlockBotVisits', "true") == "true"){
		return !kp_isUserBot($ua);
	}
	
	return true;
}

function kp_pluginActions($links, $file) {
 	if( $file == "kindred-posts/kindred-posts-index.php" && function_exists("admin_url")) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=kp' ) . '">' . __('Settings') . '</a>';
		
		array_unshift($links, $settings_link); // before other links
	}
	
	return $links;
}

/**
 * Prepare a string for Google Analytics
 * 
 * @param string $str: The string to use in within Google Analytics
 * @return string
 **/
function kp_prepareGoogleAnalytics($str = ""){
	if (kp_checkPro()){
		return kp_addGoogleAnalytics($str);
	}
	
	return "";
}

function kp_registerSettingsPage(){
	add_submenu_page("options-general.php", "Kindred Posts", "Kindred Posts", "edit_plugins", "kp", "kp_settingsPage"); 
	
	// call register settings function
	add_action("admin_init", "kp_registerSettings");
}

function kp_registerSettings(){
	register_setting("kp_settings", "FirstSave");
	register_setting("kp_settings", "CollectStatistics");
	register_setting("kp_settings", "AttemptToBlockBotVisits");
	register_setting("kp_settings", "AdminTestMode");
	
	if (kp_checkPro()){
		kp_prepareProSettings();
	}
}

/**
 * Save a visit to the post
 *
 * @param WP_Post $postObject: The post being generated
 * @return null
 **/
function kp_saveVisit($postObject) {
	global $wp_query, $firstPost;
	
	// Check that we are on a Page or Post
	// !is_single() // Don't save the post when we aren't on a single post page is being displayed.
	// !is_page() // Don't save the post when we aren't on a single page is being displayed.
	// !$firstPost // Don't save the visit after the first post in the loop
	if (!is_single() && !is_page() && !$firstPost) {
		return;	
	}
	// TODO: Create a better way to check if the user is visiting the post

	// Check if we want to collect statistics
	if (get_option('CollectStatistics', "true") == "false"){
		return;
	}
	
	// Check if we are in test mode and if the current user is an admin, don't collect their visit data
	if (get_option('AdminTestMode', "false") == "true" && current_user_can('edit_theme_options') && current_user_can('edit_plugins')) {
		return;
	}
	
	$firstPost = false;
	
	$arr = kp_getUserData();
	extract($arr);
	
	$recommender = new kp_recommender($ip, $ua);
	$recommender->saveVisit($wp_query->post->ID);

	return null;
}
?>