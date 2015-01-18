<?php
/**
 * This plugin is maintained by Ai Spork LLC (http://aispork.com)
 * If you have any questions about this plugin, please contact us at info@aispork.com
 */

/**
 * This file sets up global variables needed for the Kindred Posts Plugin
 **/
global $kp_CodeVersion, $kp_DbVersion;
global $wpdb, $visitTbl, $defaultNumClosestUsersToUse, $maxPastUpdateDate, $defaultNumPostsToRecommend, $numTestModeFakeUsers, $numTestModeFakeVisits, $trackUserAgent;
global $kp_defaultPostTitle, $kp_defaultPostAuthor, $kp_defaultPostDate, $kp_defaultPostTeaser;
global $kp_defaultFeaturedImage, $kp_defaultOrientation, $kp_defaultAlignment, $kp_firstPost;
global $kp_defaultCollectStatistics, $kp_defaultAttemptToBlockBotVisits, $kp_defaultAdminTestMode;
global $kp_botArray;
global $kp_pluginUrl, $kp_helpUrl, $kp_supportForumUrl, $kp_feedbackUrl, $kp_donationUrl;

$kp_CodeVersion = "1.3.1"; // The version of code
$kp_DbVersion = "1.1"; // The database version

// An easy way to get the database table
$visitTbl = $wpdb->prefix . "kindred_posts_visits";

$defaultNumClosestUsersToUse = 5; 	// Select the x number of closest users to recommend a post. More will result in more accurate predictions but is much less efficient
$maxPastUpdateDate = 365; 	// The number of days in the past to look for visits
$defaultNumPostsToRecommend = 3;	// The number of posts to recommend to the user
$trackUserAgent = true; // Track the User Agent when tracking visits

$numTestModeFakeUsers = 10; // The number of fake users to insert into the database when switching to test mode
$numTestModeFakeVisits = 10; // The number of fake visits that each user makes

// Helpful Urls
$kp_pluginUrl = "http://aispork.com/kindred-posts";
$kp_helpUrl = "http://support.aispork.com/";
$kp_supportForumUrl = "http://support.aispork.com/";
$kp_feedbackUrl = "http://aispork.com/feedback/kindred-posts/";
$kp_donationUrl = "http://aispork.com/donate/";

// List of keywords that will denote a bot, case-insensitive
$kp_botArray = array(
	"bot", "spider", "curl", "crawler"
);

// Default Admin Settings
$kp_defaultCollectStatistics = true;
$kp_defaultAttemptToBlockBotVisits = true;
$kp_defaultAdminTestMode = false;

// Default Display Options for the widgets, this can be changed in the settings for each widget
$kp_defaultPostTitle = true;
$kp_defaultPostAuthor = true;
$kp_defaultPostDate = true;
$kp_defaultPostTeaser = false;
$kp_defaultFeaturedImage = false;
$kp_defaultOrientation = "vertical";
$kp_defaultAlignment = "left";

$kp_firstPost = true; // Used to track only the first post saved
?>