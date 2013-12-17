<?php
// Load configuration, functions, and classes for the plugin
include_once("..\..\..\..\wp-load.php");
include_once("..\kindred-posts-index.php");

// Load the test classes
include_once('classes\recommendedpost.php');
include_once('classes\recommender.php');
include_once('classes\renderer.php');
include_once('classes\test.php');
include_once('classes\widget.php');

$testPostID = 1; // A sample post to use for testing
$numTestsToInsert = -1; // Set to <= 0 if you want to insert data
$testRenderer = true;
$testRecommendedPost = false;
$testRecommender = false;
$testWidget = false;

if ($numTestsToInsert > 0) {
	echo "<div>Inserting $numTestsToInsert Test Post(s)...</div>";
	$testDataObj = new kp_testData();
	$testDataObj->insertTestPosts($numTestsToInsert);
}

if ($testRenderer) {
	// Test the renderer class
	echo "<div>Testing classes\\renderer</div>";
	$renderTestObj = new kp_test_renderer();
	$renderTestObj->runTests();
}

if ($testRecommendedPost) {	
	// Test the recommendedPost class
	echo "<div>Testing classes\\recommendedPost</div>";
	$recommendedPostTestObj = new kp_test_recommendedPost($testPostID);
	$recommendedPostTestObj->runTests();
}

if ($testRecommender) {
	// Test the recommender class
	echo "<div>Testing classes\\recommender</div>";
	$recommenderTestObj = new kp_test_recommender();
	$recommenderTestObj->runTests();
}

if ($testWidget) {
	// Test the widget class
	echo "<div>Testing classes\\widget</div>";
	$recommenderTestObj = new kp_test_widget();
	$recommenderTestObj->runTests();
}
?>