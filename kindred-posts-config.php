<?php
/**
 * This file sets up global variables needed for the Kindred Posts Plugin
 **/

global $wpdb, $visitTbl, $postTbl, $defaultNumClosestUsersToUse, $maxPastUpdateDate, $defaultNumPostsToRecommend, $numTestModeFakeUsers, $numTestModeFakeVisits;
global $showTitle, $showAuthor, $showDate, $showExcerpt, $showFeaturedImage, $firstPost, $defaultOrientation, $defaultAlignment;
global $kp_code_version, $kp_db_version;

$kp_code_version = "1.2.2"; // The version of code
$kp_db_version = "1.1"; // The database version

$visitTbl = $wpdb->prefix . "kindred_posts_visits";
$postTbl = $wpdb->prefix . "posts";
$defaultNumClosestUsersToUse = 5; 	// Select the x number of closest users to recommend a post
						// More will result in more accurate predictions but is much less efficient
$maxPastUpdateDate = 365; 	// The number of days in the past to look for visits
$defaultNumPostsToRecommend = 3;	// The number of posts to recommend to the user

$numTestModeFakeUsers = 10; // The number of fake users to insert into the database when switching to test mode
$numTestModeFakeVisits = 10; // The number of fake visits that each user makes

// The following is information about who maintains the plugin
$maintainerName = "Ai Spork"; 
$maintainerUrl = "http://aispork.com";
$maintainerEmail = "info@aispork.com";

$pluginUrl = "http://aispork.com/kindred-posts";
$premiumVersionUrl = "http://aispork.com/kindred-posts-premium";
$helpUrl = "http://aispork.com/forums/";
$supportForumUrl = "http://aispork.com/forums/";
$feedbackUrl = "http://aispork.com/feedback/kindred-posts/";

// List of keywords that will denote a bot, must be lower case
$botArr = array(
	"bot",
	"spider"
);

// Default Display Options for the widgets, this can be changed in the settings for each widget
$showTitle = true;
$showAuthor = true;
$showDate = true;
$showExcerpt = true;
$showFeaturedImage = true;
$defaultOrientation = "vertical";
$defaultAlignment = "center";

$firstPost = true; // Used to track only the first post saved
?>