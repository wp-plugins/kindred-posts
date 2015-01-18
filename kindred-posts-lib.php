<?php
/**
 * This file contains functions required for the Kindred Posts plugin
 **/

/**
 * Check if a string is a valid ip address
 *
 * @param string $ip: The string to check
 * @return bool: Indicates if the string is a valid ip address
 **/
function kp_checkIP($ip) {
	return (isset($ip) && !empty($ip));
}

/**
 * Delete IPs that we have blocked from the visit table
 *
 * @return null
 *
 * @since 1.3.0
 */
function kp_deleteBlockedIPs() {
	global $visitTbl, $wpdb;
	
	$sql = "DELETE FROM $visitTbl";
	$blockedIPs = kp_getBlockedIPs();
	
	if (count($blockedIPs) > 0) {
		$params = array();
		$first = true;
		$hasMultiple = false;
		foreach ($blockedIPs As $key => $val) {
			if ($first) {
				$sql .= " WHERE ";
				$first = false;
			}
			
			if ($hasMultiple) {
				$sql .= " OR ";
			}
			
			$sql .= " ip=%s";
			$params[] = $key;
			$hasMultiple = true;
		}
		
		$wpdb->query($wpdb->prepare($sql, $params));
	}
	
	return null;
}

/**
 * Delete bots from the visit table
 *
 * @return null
 *
 * @since 1.3.0
 */
function kp_deleteBots() {
	global $kp_botArray, $visitTbl, $wpdb;
	
	$sql = "DELETE FROM $visitTbl";
	
	if (count($kp_botArray) > 0) {
		$params = array();
		$first = true;
		$hasMultiple = false;
		foreach ($kp_botArray as $bot) {
			if ($first) {
				$sql .= " WHERE ";
				$first = false;
			}
			
			if ($hasMultiple) {
				$sql .= " OR ";
			}
			
			$sql .= " UserAgent LIKE %s ";
			$params[] = "%" . $bot . "%";
			$hasMultiple = true;
		}
		
		$wpdb->query($wpdb->prepare($sql, $params));
	}
	
	return null;
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
 * Return a list of blocked IP addresses
 *
 * @return array
 * 
 * @since 1.3.0
 */
function kp_getBlockedIPs() {
	$blockedIPs = get_option("kp_BlockedIps", "");	
	$ipArr = array();
	
	// Check each IP line by line
	$splitArr = explode("\n", $blockedIPs);
	foreach ($splitArr as $key => $val) {
		if (trim($val) != "" && strpos($val, "#") !== false) {
			$ipLine = explode("#", $val);
			if (trim($ipLine[0]) != "") {
				$ipArr[trim($ipLine[0])] = true;
			}
		} else if (trim($val) != "") {
			$ipArr[trim($val)] = true;
		}
	}
	
	return $ipArr;
}

/**
 * Return query for finding the bots in the database
 * 
 * @param bool $getCount: Indicates if we should return the count
 * @return array<string, array<string>>: Return the sql and a list of parameters for the sql string
 *
 * @since 1.3.0
 */
function kp_getBotSql($getCount = false){
	global $kp_botArray, $visitTbl;
	
	if ($getCount) {
		$sql = "SELECT COUNT(*) FROM $visitTbl WHERE TestData = '0'";
	} else {
		$sql = "SELECT * FROM $visitTbl WHERE TestData = '0'";
	}
	
	$params = array();
	$tempSql = "";
	
	if (count($kp_botArray) > 0) {
		$hasMultiple = false;
		foreach ($kp_botArray as $bot) {
			if ($hasMultiple) {
				$tempSql .= " OR ";
			}
			
			$tempSql .= " UserAgent LIKE %s";
			$params[] = "%" . $bot . "%";
			$hasMultiple = true;
		}
	} else {
		// if we don't have any blocked IPs then don't return anything
		$tempSql = "'true' = 'false'";
	}
	
	if ($tempSql != "") {
		$sql = $sql . " AND (" . $tempSql . ")";
	}
	
	return array("sql" => $sql, "params" => $params);
} 

/**
 * Return query for finding the ignored Ip address in the database
 * 
 * @param bool $getCount: Indicates if we should return the count
 * @return array<string, array<string>>: Return the sql and a list of parameters for the sql string
 *
 * @since 1.3.0
 */
function kp_getIgnoredIPsSql($getCount = false){
	global $visitTbl;

	if ($getCount) {
		$sql = "SELECT COUNT(*) FROM $visitTbl WHERE TestData = '0'";
	} else {
		$sql = "SELECT * FROM $visitTbl WHERE TestData = '0'";
	}
	
	$blockedIPs = kp_getBlockedIPs();
	$params = array();
	$tempSql = "";
	
	if (count($blockedIPs) > 0) {
		$hasMultiple = false;
		foreach ($blockedIPs As $key => $val) {
			if ($hasMultiple) {
				$tempSql .= " OR ";
			}
			
			$tempSql .= " ip=%s";
			$params[] = $key;
			$hasMultiple = true;
		}
	} else {
		// if we don't have any blocked IPs then don't return anything
		$tempSql = "'true' = 'false'";
	}
	
	if ($tempSql != "") {
		$sql = $sql . " AND (" . $tempSql . ")";
	}
	
	return array("sql" => $sql, "params" => $params);
}

/**
 * Get the different post types that we can recommend
 * 
 * @return array<string>
 */
function kp_getRecommendablePostTypes() {
	$recommendablePostTypes = array();
	$postTypes = get_post_types(array("public" => true), "names", "and");
	foreach ($postTypes as $postType) {
		if ($postType != "attachment") {
			$recommendablePostTypes[] = $postType;
		}
	}
	
	return $recommendablePostTypes;
}

/**
 * Retrieve a list of recommended posts for the user agent and ip specified
 *
 * @param int $numPostsToRecommend: The number of posts to recommend ($defaultNumPostsToRecommend recommendations will be generated if $recommendedPosts is empty)
 * @param string $ip: The ip address of the user to recommend posts for (if blank, this will be found)
 * @param string $ua: The user agent of the user to recommend posts for (if blank, this will be found)
 * @param array<string> $recommendablePostTypes: An array of post types to recommend (if empty, recommend all post types)
 * @return array<kp_recommendedPost>: An array of kp_recommendedPosts objects
 *
 * @since 1.2.5
 */
function kp_getRecommendedWP_Posts($numPostsToRecommend = -1, $ip = "", $ua = "", $recommendablePostTypes = array()) {
	$recommender = kp_runRecommender($numPostsToRecommend, $ip, $ua, $recommendablePostTypes);
	return $recommender->getRecommendedWP_Posts();
}

/**
 * Get the user's information
 *
 * @return array
 **/
function kp_getUserData() {
	// Get the user from the visit table (if they exist)
	$ip = kp_determineIP();
	
	// Trim the ip if they passed an invalid address
	if (strlen($ip) > 63) {
		$ip = substr($ip, 0, 63);
	}

	// We get the user agent and save it so we can ignore bots in our recommendations
	$ua = "";
	if (isset($_SERVER["HTTP_USER_AGENT"])) {
		$ua = $_SERVER["HTTP_USER_AGENT"];
		
		if (strlen($ua) > 127) {
			$ua = substr($ua, 0, 127);
		}
	}

	return array("ip" => $ip, "ua" => $ua);
}

/**
 * Checks if the user is an admin
 *
 * @return bool: Indicates whether the user is an admin
 **/
function kp_isUserAdmin() {
	return current_user_can("edit_theme_options") && current_user_can("edit_plugins");
}
 
/**
 * Checks if the user agent of the user belongs to a bot
 *
 * @param string $ua: The user's user agent
 * @return bool: Indicate whether the user is a bot
 **/
function kp_isUserBot($ua) {
	global $kp_botArray; // Set this in configuration
	
	// Check if the user is a bot by cycling through the list of known bots
	foreach ($kp_botArray as $bot) {
		if (strstr(strtolower($ua), strtolower($bot))) {
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
function kp_isUserVisitValid($ip, $ua) {
	// If we find that the ip matches a blocked ip, return false
	$blockedIPs = kp_getBlockedIPs();
	if (isset($blockedIPs[$ip]) && $blockedIPs[$ip] == true) {
		return false;
	}

	// If the user agent contains a bot user agent, return false
	if (get_option("kp_AttemptToBlockBotVisits", "true") == "true") {
		return !kp_isUserBot($ua);
	}
	
	return true;
}

/**
 * Include all files required by the plugin
 *
 * @return void
 * 
 * @since 1.3.0
 */
function kp_load() {
	kp_loadConfig();
	kp_updateOptions();
	
	kp_loadClasses();
	kp_loadDb();
	kp_loadThemes();
}

/**
 * Include class files required by the plugin
 *
 * @return void
 * 
 * @since 1.3.0
 */
function kp_loadClasses() {
	include_once(plugin_dir_path( __FILE__ ) . "classes/recommendedpost.php"); // The kp_recommendedPost class
	include_once(plugin_dir_path( __FILE__ ) . "classes/recommender.php"); // The kp_recommender class
	include_once(plugin_dir_path( __FILE__ ) . "classes/renderer.php"); // The kp_renderer class
	include_once(plugin_dir_path( __FILE__ ) . "classes/widget.php"); // The kp_widget class
}

/**
 * Include configuration files required by the plugin
 *
 * @return void
 * 
 * @since 1.3.0
 */
function kp_loadConfig() {
	include_once(plugin_dir_path( __FILE__ ) . "kindred-posts-config.php");
}

/**
 * Include database files and database functions required by the plugin
 *
 * @return void
 * 
 * @since 1.3.0
 */
function kp_loadDb() {
	include_once(plugin_dir_path( __FILE__ ) . "db/db.php");
}

/**
 * Include files and functions required to theme the plugin
 *
 * @return void
 * 
 * @since 1.3.0
 */
function kp_loadThemes() {
	include_once(plugin_dir_path( __FILE__ ) . "theme/admin-settings.php"); // The admin settings page
	include_once(plugin_dir_path( __FILE__ ) . "theme/widget-settings.php"); // The widget settings form
	include_once(plugin_dir_path( __FILE__ ) . "theme/templates.php"); // The templates used to render objects in the plugin
}

/**
 * Initialize the actions and filters for the plugin
 *
 * @return void
 * 
 * @since 1.3.0
 */
function kp_loadWP() {
	// Use the following action to add extra submenus and menu options to the admin panel's menu structure. It runs after the basic admin panel menu structure is in place.
	add_action("admin_menu", "kp_registerSettingsPage");

	// Create the database table for the plugin when first registering the plugin
	register_activation_hook(__FILE__, "kp_updateDatabase"); 

	// Check if we need to update the database
	add_action("plugins_loaded", "kp_dbCheck"); 

	// The filter is applied to the list of links to display on the plugins page (beside the activate/deactivate links).
	add_filter("plugin_action_links", "kp_pluginActions", 10, 2);

	// Register a function to save visits for each post
	add_action("the_post", "kp_saveVisit");

	// Register a function for initializing the widget
	add_action("widgets_init", create_function("", 'register_widget("kp_widget");')); 

	// Register a function for adding styles to the head for the admin settings page
	add_action("admin_head", "kp_settingsHead"); 
}

/**
 * Check if the user wants to delete the bots and ignored ip address
 * 
 * @return bool: Indicates if the bots were removed
 * 
 * @since 1.3.0
 */
function kp_performDeleteVisitChecks(){
	if (isset($_POST["delete"]) && $_POST["delete"] == "bot"){
		kp_deleteBots();
		kp_deleteBlockedIPs();
		return true;
	}
	
	return false;
}

/**
 * Displays the settings link under the Plugin title on the Plugin page
 *
 * @param array $links: The link actions that currently exist under the title
 * @param string $file: The index of the plugin
 * @return array: The updated link actions
 **/
function kp_pluginActions($links, $file) {
	// Check that we are on the plugin pages and create the link for the settings
 	if( $file == "kindred-posts/kindred-posts-index.php" && function_exists("admin_url")) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=kindred-posts' ) . '">' . __('Settings') . '</a>';
		array_unshift($links, $settings_link); // after other links
	}
	
	return $links;
}

/**
 * Prepare the tracking code string
 * 
 * @param array $postData: The post data to append the tracking code to
 * @return string
 *
 * @since 1.3.0
 */
function kp_prepareTrackingCode($postData = null) {
	if (get_option("kp_Tracking", "") == "custom") {
		$trackingCode = get_option("kp_TrackingCode", "");
		return kp_renderer::render($trackingCode, $postData); 
	}
	
	return "";
}

/**
 * Register the settings used in the plugin
 *
 * @return void
 */
function kp_registerSettings() {
	register_setting("kp_settings", "kp_CollectStatistics");
	register_setting("kp_settings", "kp_AttemptToBlockBotVisits");
	register_setting("kp_settings", "kp_AdminTestMode");	
	register_setting("kp_feedback", "kp_HideFeedbackBox");
	register_setting("kp_feedback", "kp_FeedbackMsg");
	register_setting("kp_advancedSettings", "kp_Tracking");
	register_setting("kp_advancedSettings", "kp_TrackingCode");
	register_setting("kp_advancedSettings", "kp_BlockedIps");
	
	// TODO: Migrate the settings to have kp_ prefix
}

/**
 * Register the settings page within the admin section
 *
 * @return void
 */
function kp_registerSettingsPage(){
	// Register the settings page in the Admin section
	add_submenu_page("options-general.php", "Kindred Posts", "Kindred Posts", "edit_plugins", "kindred-posts", "kp_settingsPage"); 
	
	// call the registerSettings function
	add_action("admin_init", "kp_registerSettings");
}

/**
 * Render a widget using the template in theme\template.php if $template is blank. Using the default parameters renders a functional widget.
 *
 * @param int $numPostsToRecommend: The number of posts to recommend ($defaultNumPostsToRecommend recommendations will be generated if $recommendedPosts is empty)
 * @param array $recommendedPosts: The posts to recommend (if empty, recommendations will be generated)
 * @param string $template: The template to use to render the widget (if blank, will be generated using theme\template.php)
 * @param string $ip: The ip address of the user to recommend posts for (if blank, this will be found)
 * @param string $ua: The user agent of the user to recommend posts for (if blank, this will be found)
 * @param bool $outputWidgetHtml: Indicates if we should output the html once it is rendered
 * @param string $widgetTitle: The title of the widget 
 * @param string $post_style: Used in rendering the widget
 * @param string $postimage_style: Used in rendering the widget
 * @param string $posttitle_style: Used in rendering the widget
 * @param string $postauthor_style: Used in rendering the widget
 * @param string $postdate_style: Used in rendering the widget
 * @param string $postteaser_style: Used in rendering the widget
 * @param string $before_widget: Used in rendering the widget
 * @param string $after_widget: Used in rendering the widget
 * @param string $before_title: Used in rendering the widget
 * @param string $after_title: Used in rendering the widget
 * @param string $alignment: Used in rendering the widget - defaults to vertical
 * @param bool $show_featuredimage: Used in rendering the widget (shows posts' featured image)
 * @param bool $show_posttitle: Used in rendering the widget (shows posts' title)
 * @param bool $show_postauthor: Used in rendering the widget (shows posts' author)
 * @param bool $show_postdate: Used in rendering the widget (shows posts' post date)
 * @param bool $show_postteaser: Used in rendering the widget (shows posts' teaser, deprecated?)
 * @param array<string> $recommendablePostTypes: An array of post types to recommend (if empty, recommend all post types)
 * @param string $trackingCode: Used in rendering the tracking code for posts
 * @param bool $testModeValue: Indicates if we are in test mode and what value to look for
 * @return array: ("widgetHTML" => the html for the widget, "recommender" => the recommender if it was created)
 **/
function kp_renderWidget($numPostsToRecommend = -1, $recommendedPosts = array(), $template = "", $ip = "", $ua = "", $outputWidgetHtml = true, $widgetTitle = "", $post_style = "padding-top:10px;padding-bottom:10px;", $postimage_style = "display:inline;", $posttitle_style = "display:inline;", $postauthor_style = "display:inline;", $postdate_style = "display:inline;", $postteaser_style = "display:inline;", $before_widget = "", $after_widget = "", $before_title = "", $after_title = "", $alignment = "", $show_featuredimage = false, $show_posttitle = true, $show_postauthor = true, $show_postdate = true, $show_postteaser = false, $recommendablePostTypes = array(), $trackingCode = "", $testModeValue = null) {
	global $kp_templates, $kp_defaultAlignment;
	
	// Don't show the widget if we are in test mode and if the user isn't an admin
	if (get_option("kp_AdminTestMode", "false") == "true" && !kp_isUserAdmin()) {
		return array("widgetHtml" => "", "recommender" => null);
	}
	
	// If $template is blank then use the template in theme\templates.php
	if ($template == "" && isset($kp_templates["kp_widget"])){
		$template = $kp_templates["kp_widget"];
	}
	
	// If recommendedPosts have been passed default to those or else recommend posts
	$recommender = null;
	if (count($recommendedPosts) == 0) {
		$recommender = kp_runRecommender($numPostsToRecommend, $ip, $ua, $recommendablePostTypes, $testModeValue);
		$recommendedPosts = $recommender->posts;
	}
	
	$widgetHtml = ""; // Stores the widget that we will render/return
	
	// Render any recommendations to show
	if (count($recommendedPosts) > 0){
		$widgetTitle = apply_filters("widget_title", $widgetTitle);
		
		// Start the data for the widget
		$data = array();
		$data["isTestMode"] = (get_option("kp_AdminTestMode", "false") == "true" && kp_isUserAdmin());
		$data["kp_widget:before_widget"] = $before_widget;
		$data["kp_widget:after_widget"] = $after_widget;
		$data["kp_widget:title"] = $widgetTitle;
		$data["kp_widget:title_exists"] = (!empty($widgetTitle) && $widgetTitle != "");
		$data["kp_widget:before_title"] = $before_title;
		$data["kp_widget:after_title"] = $after_title;
		
		$data["kp_widget:post_style"] = $post_style;
		$data["kp_widget:postimage_style"] = $postimage_style;
		$data["kp_widget:posttitle_style"] = $posttitle_style;
		$data["kp_widget:postauthor_style"] = $postauthor_style;
		$data["kp_widget:postdate_style"] = $postdate_style;
		$data["kp_widget:postteaser_style"] = $postteaser_style;
		$data["kp:By"] = __('By');
		$data["kp:on"] = __('on');
		$data["kp:On"] = __('On');
		
		if (!isset($alignment) || $alignment == ""){
			$alignment = $kp_defaultAlignment;
		}
		
		$data["kp_widget:alignment"] = $alignment;
		$data["kp_widget:orientation-horizontal"] = ($instance["orientation"] == "horizontal");
		$data["kp_widget:featureimage"] = $show_featuredimage;
		$data["kp_widget:posttitle"] = $show_posttitle;
		$data["kp_widget:postauthor"] = $show_postauthor;
		$data["kp_widget:postdate"] = $show_postdate;
		$data["kp_widget:postteaser"] = $show_postteaser;
		$data["kp:trackingcode"] = $trackingCode;
		
		// Render each recommended post
		$data["kp_recommender"] = kp_recommender::renderPosts($recommendedPosts, "", $data);
		
		// Render the HTML for the widget
		$widgetHtml = kp_renderer::render($template, $data);
	}

	// Echo the results if $outputWidgetHtml is set to true
	if ($outputWidgetHtml) {
		echo $widgetHtml;
	}
	
	// Return the results and the recommender object
	return array("widgetHtml" => $widgetHtml, "recommender" => $recommender);
}

/**
 * Construct a recommender object and list of recommended posts for the user agent and ip specified
 *
 * @param int $numPostsToRecommend: The number of posts to recommend ($defaultNumPostsToRecommend recommendations will be generated if $recommendedPosts is empty)
 * @param array $recommendedPosts: The posts to recommend (if empty, recommendations will be generated)
 * @param string $ip: The ip address of the user to recommend posts for (if blank, this will be found)
 * @param string $ua: The user agent of the user to recommend posts for (if blank, this will be found)
 * @param array<string> $recommendablePostTypes: An array of post types to recommend (if empty, recommend all post types)
 * @param bool $testModeValue: Indicates if we are in test mode and what value to look for
 * @return kp_recommender: object with posts to recommend (to use, call $recommender->posts)
 *
 * @since 1.2.5
 */
function kp_runRecommender($numPostsToRecommend = -1, $ip = "", $ua = "", $recommendablePostTypes = array(), $testModeValue = null) {
	global $defaultNumPostsToRecommend, $defaultNumClosestUsersToUse; 
	
	// Check if $ip and $ua have been passed, if not, generate them
	if ($ip == "" && $ua == "") {
		$arr = kp_getUserData();
		extract($arr);
	}
	
	// Check if the user has set the number of posts to recommend
	if ($numPostsToRecommend <= 0) {
		$numPostsToRecommend = $defaultNumPostsToRecommend;
	}		
	
	// Run the recommender
	$recommender = new kp_recommender($ip, $ua);
	$recommender->run($numPostsToRecommend, $defaultNumClosestUsersToUse, $recommendablePostTypes, $testModeValue);
	
	return $recommender;
}

/**
 * Save a visit to the post or page.
 *
 * @param WP_Post $postObject: The post being generated
 * @return null
 **/
function kp_saveVisit($postObject) {
	global $wp_query, $kp_firstPost;
	
	// Check that we are on a Page or Post
	// !is_single() -- Don't save the post when we aren't on a single post page
	// !is_page() -- Don't save the post when we aren't on a single page being
	// !$kp_firstPost -- Don't save the visit after the first post in the loop
	if (!is_single() && !is_page() && !$kp_firstPost) {
		return;	
	}
	
	// TODO: Create a better way to check if the user is visiting the post

	// Check if we want to collect statistics
	if (get_option("kp_CollectStatistics", "true") == "false") {
		return;
	}
	
	// If we are in test mode and if the current user is an admin, don't collect their visit data
	if (get_option("kp_AdminTestMode", "false") == "true" && kp_isUserAdmin()) {
		return;
	}
	// At this point, a non-admin user's visit will be tracked even if we are in test mode
	
	$kp_firstPost = false; // Set this to false to short circuit when kp_saveVisit is called on the next $postObject in the loop
	
	// Get the user's data and save the visit
	$arr = kp_getUserData();
	extract($arr);
	
	$recommender = new kp_recommender($ip, $ua);
	$recommender->saveVisit($wp_query->post->ID);

	return null;
}

/**
 * Update the option name and value to the new option name
 *
 * @param string $optionName: The option to update
 * @param string $newName: The name of the option to update to
 * @return null
 *
 * @since 1.3.1
 */ 
function kp_updateOption($optionName, $newName) {
	$optionValue = get_option($optionName);
	
	if ($optionValue !== FALSE) {
		delete_option($optionName);
		update_option($newName, $optionValue);
	}
}

/**
 * Update the options so they are more namespaced and won't conflict with other options in the site
 *
 * @return null
 *
 * @since 1.3.1
 */ 
function kp_updateOptions() {
	if (get_option("kp_OptionsNamespaced", "false") == "true") {
		return;
	}

	kp_updateOption("AdminTestMode", "kp_AdminTestMode");
	kp_updateOption("AttemptToBlockBotVisits", "kp_AttemptToBlockBotVisits");
	kp_updateOption("CollectStatistics", "kp_CollectStatistics");
	kp_updateOption("TrackGA", "kp_TrackGA");
	kp_updateOption("FirstSave", "kp_FirstSave");
	kp_updateOption("RecommendPosts", "kp_RecommendPosts");
	kp_updateOption("RecommendPages", "kp_RecommendPages");
	kp_updateOption("BlockedIPs", "kp_BlockedIPs");
	kp_updateOption("HideFeedbackBox", "kp_HideFeedbackBox");
	kp_updateOption("FeedbackMsg", "kp_FeedbackMsg");
	
	update_option("kp_OptionsNamespaced", "true");
}
?>