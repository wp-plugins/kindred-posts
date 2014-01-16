<?php
/*
Plugin Name: Kindred Posts
Plugin URI: http://aispork.com/kindred-posts
Description: Automatically recommend your posts to your site visitors
Version: 1.2.1
Author: Ai Spork LLC
Author URI: http://aispork.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
	
	Copyright 2013  Ai Spork LLC (email : info@aispork.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * This file will set up the plugin loading functions/classes/etc and registers hooks
 **/

// Load configuration, functions, and classes for the plugin
include_once( plugin_dir_path( __FILE__ ) . 'kindred-posts-loader.php');

// Register various hooks and actions for the plugin
add_action('admin_menu', 'kp_registerSettingsPage'); // Used to add extra submenus and menu options to the admin panel's menu structure. It runs after the basic admin panel menu structure is in place.
register_activation_hook(__FILE__, 'kp_createTable'); // Create the database table for the plugin when first registering the plugin
add_action('plugins_loaded', 'kp_dbCheck'); // Check if we need to update the database
add_filter('plugin_action_links', 'kp_pluginActions', 10, 2); // Applied to the list of links to display on the plugins page (beside the activate/deactivate links).
add_action("the_post", "kp_saveVisit"); // Register a hook to save visits for each post
add_action('widgets_init', create_function('', 'register_widget("kp_widget");')); // Register a hook for initializing the widget
add_action('admin_head', 'kp_settingsHead'); // Register a hook for adding styles to the head in admin settings page
?>