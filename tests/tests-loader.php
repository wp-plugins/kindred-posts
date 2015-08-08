<?php
// Load configuration, functions, and classes for the plugin
include_once("../../../../wp-load.php");
include_once("../kindred-posts-index.php");

// Check that the user is an admin
if (!kp_isUserAdmin()) {
	die("You do not have access to this page, please log in as admin");
}

// Load the test classes
include_once('classes\recommendedpost.php');
include_once('classes\recommender.php');
include_once('classes\renderer.php');
include_once('classes\test.php');
include_once('classes\testData.php');
include_once('classes\testUser.php');
include_once('classes\widget.php');
include_once('classes\plugin.php');

$testPostID = 1; // A sample post to use for testing
$numTestsToInsert = -1; // Set to <= 0 if you want to insert data

$testRenderer = false || (isset($_GET["testAll"]) && ($_GET["testAll"] == "true"));
$testRecommendedPost = false || (isset($_GET["testAll"]) && ($_GET["testAll"] == "true"));
$testRecommender = false || (isset($_GET["testAll"]) && ($_GET["testAll"] == "true"));
$testWidget = false || (isset($_GET["testAll"]) && ($_GET["testAll"] == "true"));
$testPlugin = false || (isset($_GET["testAll"]) && ($_GET["testAll"] == "true"));

$testRenderer = $testRenderer || (isset($_GET["testRenderer"]) && ($_GET["testRenderer"] == "true"));
$testRecommendedPost = $testRecommendedPost || (isset($_GET["testRecommendedPost"]) && ($_GET["testRecommendedPost"] == "true"));
$testRecommender = $testRecommender || (isset($_GET["testRecommender"]) && ($_GET["testRecommender"] == "true"));
$testWidget = $testWidget || (isset($_GET["testWidget"]) && ($_GET["testWidget"] == "true"));
$testPlugin = $testPlugin || (isset($_GET["testPlugin"]) && ($_GET["testPlugin"] == "true"));

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

if ($testPlugin) {
	// Test the plugin
	echo "<div>Testing Plugin</div>";
	$recommenderTestObj = new kp_test_plugin();
	$recommenderTestObj->runTests();
}
?>
