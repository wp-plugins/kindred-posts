<?php
/**
 * This file contains various functions required for the Kindred Posts plugin
 **/

/**
 * Check if the user has the premium version of the plugin
 *
 * @return bool: Indicates if they do.
 **/
function kp_checkPro() {
	// If you mess with this function, you run the risk of the plugin not working properly.
	// Additional file(s) are required to have the premium version.
	global $kp_havePro;
	return $kp_havePro;
}

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
 * Retrieve a list of recommended posts for the user agent and ip specified
 *
 * @param int $numPostsToRecommend: The number of posts to recommend ($defaultNumPostsToRecommend recommendations will be generated if $recommendedPosts is empty)
 * @param string $ip: The ip address of the user to recommend posts for (if blank, this will be found)
 * @param string $ua: The user agent of the user to recommend posts for (if blank, this will be found)
 * @return Array: An array of kp_recommendedPosts objects
 *
 * @since 1.2.5
 */
function kp_getRecommendedWP_Posts($numPostsToRecommend = -1, $ip = "", $ua = "") {
	$recommender = kp_runRecommender($numPostsToRecommend, $ip, $ua);
	return $recommender->getRecommendedWP_Posts();
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
 * Checks if the user is an admin
 *
 * @return bool: Indicates whether the user is an admin
 **/
function kp_isUserAdmin() {
	return current_user_can('edit_theme_options') && current_user_can('edit_plugins');
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

/**
 * Register the settings used in the plugin
 *
 * @return void
 */
function kp_registerSettings(){
	register_setting("kp_settings", "FirstSave");
	register_setting("kp_settings", "CollectStatistics");
	register_setting("kp_settings", "AttemptToBlockBotVisits");
	register_setting("kp_settings", "AdminTestMode");
	register_setting("kp_feedback", "HideFeedbackBox");
	register_setting("kp_feedback", "FeedbackMsg");
	
	if (kp_checkPro()){
		kp_prepareProSettings();
	}
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
 * @return array: ("widgetHTML" => the html for the widget, "recommender" => the recommender if it was created)
 **/
function kp_renderWidget($numPostsToRecommend = -1, $recommendedPosts = array(), $template = "", $ip = "", $ua = "", $outputWidgetHtml = true, $widgetTitle = "", $post_style = "padding-top:10px;padding-bottom:10px;", $postimage_style = "display:inline;", $posttitle_style = "display:inline;", $postauthor_style = "display:inline;", $postdate_style = "display:inline;", $postteaser_style = "display:inline;", $before_widget = "", $after_widget = "", $before_title = "", $after_title = "", $alignment = "", $show_featuredimage = false, $show_posttitle = true, $show_postauthor = true, $show_postdate = true, $show_postteaser = false) {
	global $kp_templates;
	
	// Check if we are in test mode and if the user is an admin, if they aren't, don't show the widget
	if (get_option('AdminTestMode', "false") == "true" && !kp_isUserAdmin()) {
		return array("widgetHtml" => "", "recommender" => null);
	}
	
	// Determine if we should use the template in theme\templates.php
	if ($template == "" && isset($kp_templates["kp_widget"])){
		$template = $kp_templates["kp_widget"];
	}
	
	// Check if recommendedPosts have been passed, if so, default to those or else recommend some
	$recommender = null;
	if (count($recommendedPosts) == 0) {
		$recommender = kp_runRecommender($numPostsToRecommend, $ip, $ua);
		$recommendedPosts = $recommender->posts;
	}
	
	$widgetHtml = "";
	
	// if we have recommendations to show, render them
	if (count($recommendedPosts) > 0){
		$widgetTitle = apply_filters("widget_title", $widgetTitle);
		
		// Start the data for the widget
		$data = array();
		$data["isTestMode"] = (get_option("AdminTestMode", "false") == "true" && kp_isUserAdmin());
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
			$alignment = $defaultAlignment;
		}
		
		$data["kp_widget:alignment"] = $alignment;
		$data["kp_widget:orientation-horizontal"] = true;//($instance["orientation"] == "horizontal"); // Need to consider switching this to ($alignment == "horizontal");
		$data["kp_widget:featureimage"] = $show_featuredimage;
		$data["kp_widget:posttitle"] = $show_posttitle;
		$data["kp_widget:postauthor"] = $show_postauthor;
		$data["kp_widget:postdate"] = $show_postdate;
		$data["kp_widget:postteaser"] = $show_postteaser;
		
		// Render each recommended post
		$data["kp_recommender"] = kp_recommender::renderPosts($recommendedPosts, "", $data);
		
		// Render the HTML for the widget
		$widgetHtml = kp_renderer::render($template, $data);
	}

	if ($outputWidgetHtml) {
		echo $widgetHtml; // We echo the results and return them
	}
	
	return array("widgetHtml" => $widgetHtml, "recommender" => $recommender);
}

/**
 * Construct a recommender object and list of recommended posts for the user agent and ip specified
 *
 * @param int $numPostsToRecommend: The number of posts to recommend ($defaultNumPostsToRecommend recommendations will be generated if $recommendedPosts is empty)
 * @param array $recommendedPosts: The posts to recommend (if empty, recommendations will be generated)
 * @param string $ip: The ip address of the user to recommend posts for (if blank, this will be found)
 * @param string $ua: The user agent of the user to recommend posts for (if blank, this will be found)
 * @return kp_recommender: object with posts to recommend (to use, call $recommender->posts)
 *
 * @since 1.2.5
 */
function kp_runRecommender($numPostsToRecommend = -1, $ip = "", $ua = "") {
	global $defaultNumPostsToRecommend, $defaultNumClosestUsersToUse; 
	
	// Check if $ip and $ua have been passed, if not, generate them
	if ($ip == "" && $ua == ""){
		$arr = kp_getUserData();
		extract($arr);
	}
	
	// Check if the user has set the number of posts to recommend
	if ($numPostsToRecommend <= 0) {
		$numPostsToRecommend = $defaultNumPostsToRecommend;
	}		
	
	// Run the recommender
	$recommender = new kp_recommender($ip, $ua);
	$recommender->run($numPostsToRecommend, $defaultNumClosestUsersToUse);
	
	return $recommender;
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
	if (get_option('AdminTestMode', "false") == "true" && kp_isUserAdmin()) {
		return;
	}
	
	$firstPost = false;
	
	$arr = kp_getUserData();
	extract($arr);
	
	$recommender = new kp_recommender($ip, $ua);
	$recommender->saveVisit($wp_query->post->ID);

	return null;
}

/**
 * Output the version number for a 5 digit number
 *
 * @param int $versionInt: The version to convert, takes the form <major version number><minor version number><3 digits for each bug fix version>
 * @return string: A string of the form <major>.<minor>.<bug fix version>
 **/
function kp_versionNumberToString($versionInt) {
	$versionStr = (string)$versionInt;
	return substr($versionStr, 0, 1) . "." . substr($versionStr, 1, 1) . "." . substr($versionStr, 2); 
}
?>