<?php
/*
Plugin Name: Kindred Posts
Plugin URI: http://aispork.com/kindred-posts
Description: Use artificial intelligence to recommend content to your site visitors
Version: 1.3.2
Author: Ai Spork LLC
Author URI: http://aispork.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
	
	Copyright 2015  Ai Spork LLC (email : info@aispork.com)

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

// Load the functions required by the plugin
include_once(plugin_dir_path( __FILE__ ) . "kindred-posts-lib.php");

// Include the other files with functions/classes/etc
kp_load();

// Register various hooks and actions for the plugin
kp_loadWP();
?>