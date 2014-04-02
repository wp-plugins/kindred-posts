<?php
/**
 * This file loads all the files necessary to run Kindred Posts
 **/

// Contains plugin configuration
include_once(plugin_dir_path( __FILE__ ) . "kindred-posts-config.php");

// Contains functions required for the plugin
include_once(plugin_dir_path( __FILE__ ) . "kindred-posts-lib.php");

// Include each of the classes used
include_once(plugin_dir_path( __FILE__ ) . "classes/recommendedpost.php"); // The kp_recommendedPost class
include_once(plugin_dir_path( __FILE__ ) . "classes/recommender.php"); // The kp_recommender class
include_once(plugin_dir_path( __FILE__ ) . "classes/renderer.php"); // The kp_renderer class
include_once(plugin_dir_path( __FILE__ ) . "classes/widget.php"); // The kp_widget class

// Contains functions related to the database
include_once(plugin_dir_path( __FILE__ ) . "db/db.php");

// Check if we have the premium version and include file(s)
try {
	$kp_havePro = file_exists(plugin_dir_path( __FILE__ ) . "pro/pro-loader.php");
	if ($kp_havePro){
		include_once(plugin_dir_path( __FILE__ ) . "pro/pro-loader.php");
	}
} catch (Exception $e) {
	$kp_havePro = false;	// This will break things if it is set to true and the necessary functions don't exist
}

// Contains the functions for displaying forms and widgets on the site
include_once(plugin_dir_path( __FILE__ ) . "theme/admin-settings.php"); // The admin settings page
include_once(plugin_dir_path( __FILE__ ) . "theme/widget-settings.php"); // The widget settings form
include_once(plugin_dir_path( __FILE__ ) . "theme/templates.php"); // The templates used to render objects in the plugin
?>