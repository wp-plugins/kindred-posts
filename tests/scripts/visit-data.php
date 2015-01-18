<?php
// Load configuration, functions, and classes for the plugin
include_once("..\..\..\..\..\wp-load.php");
include_once("..\..\kindred-posts-index.php");

echo "<h1>Visit Data</h1>";
echo "<p>The number of visits is an estimate.</p>";

$visits = $wpdb->get_results("SELECT * FROM $visitTbl ORDER BY CreateDate DESC", OBJECT);
foreach ($visits as $userVisits) {
	$visits = unserialize($userVisits->Visits);
	$visitStr = "";
	foreach ($visits as $postId => $numVisits) {
		if ($postId != "") {
			$posts = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID=$postId AND post_status='publish'", OBJECT);

			$visitStr .= "" . $posts[0]->post_title . " => ";
			$visitStr .= "" . ceil($numVisits / 3) . " <br />"; // Estimate of the number of visits
		}
	}

	echo "IP Address: " . $userVisits->IP . "<br />";
	echo "User Agent: " . $userVisits->UserAgent . "<br />";
	echo "Visits (Post ID => Number of Visits):<br />" . $visitStr;
	echo "<hr />";
}