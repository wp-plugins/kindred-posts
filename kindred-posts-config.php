<?php
/**
 * This file sets up global variables needed for the Kindred Posts Plugin
 **/

global $wpdb, $visitTbl, $postTbl, $defaultNumClosestUsersToUse, $maxPastUpdateDate, $defaultNumPostsToRecommend;
global $showTitle, $showAuthor, $showDate, $showExcerpt, $showFeaturedImage, $firstPost, $defaultOrientation, $defaultAlignment;

$visitTbl = $wpdb->prefix . "kindred_posts_visits";
$postTbl = $wpdb->prefix . "posts";
$defaultNumClosestUsersToUse = 5; 	// Select the x number of closest users to recommend a post
						// More will result in more accurate predictions but is much less efficient
$maxPastUpdateDate = 365; 	// The number of days in the past to look for visits
$defaultNumPostsToRecommend = 5;	// The number of posts to recommend to the user

// The following is information about who maintains the plugin
$maintainerName = "Ai Spork"; 
$maintainerUrl = "http://aispork.com";
$maintainerEmail = "info@aispork.com";
$version = "1.0";

$pluginUrl = "http://aispork.com/kindred-posts";
$premiumVersionUrl = "http://aispork.com/kindred-posts-premium";
$helpUrl = "http://aispork.com/forums/";
$supportForumUrl = "http://aispork.com/forums/";

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