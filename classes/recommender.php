<?php
/**
 * Start kp_recommender class
 **/
class kp_recommender {
	public $ipAddress = ""; // The IP Address of the user making the request
	public $userAgent = ""; // The user's user agent
	public $posts = array(); // An array of posts that were recommended for this IP Address
	public $template = ""; 	// A template of how the posts should be rendered
							// In order to render the posts, the template must contain {Posts}
	
	public function __construct($ipAddress = "", $userAgent = ""){
		$this->ipAddress = $ipAddress;
		$this->userAgent = $userAgent;
	}
	
	/**
	 * Get the distance between two user's visit data
	 *
	 * @param array $user1
	 * @param array $user2
	 * @return float: The distance
	 **/	
	public function getDistance($user1, $user2) {
		$dist = 0;
		
		if (count($user1) <= 1 || count($user2) <= 1) {
			return 100.0;
		}
		
		foreach ($user1 as $postID1 => $NumVisits1) {
			// Find the $postID1 in the other user
			if (isset($user2[$postID1])) {
				$NumVisits2 = $user2[$postID1];
			} else {
				$NumVisits2 = 0;
			}
			
			// Compare the distance
			$dist += pow($NumVisits1 - $NumVisits2, 2);
			unset($user1[$postID1]);
			unset($user2[$postID1]);
		}
		
		foreach ($user2 as $postID2 => $NumVisits2) {
			// We went through $user1 so we know $NumVisits1 = 0
			$NumVisits1 = 0;
			
			// Compare the distance
			$dist += pow($NumVisits1 - $NumVisits2, 2);
		}
		
		if ($dist < 0) {
			return 0.0;
		} 
		
		return sqrt($dist);
	}
	
	/**
	 * Check if $this->posts contains $postID
	 *
	 * @param int $postID: The post we are interested in
	 * @return bool
	 **/
	function isPostRecommended($postID){
		foreach ($this->posts as $post) {
			if ($post->post_id == $postID) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Render the recommended posts, either using $this->template or the rendered posts
	 *
	 * @param string $template: The template to use to render (must contain {kp_recommendedPosts} to generate a list of posts)
	 * @param Array $data: Data that should be used in rendering
	 * @return string: The rendered Html
	 **/	
	public function render($template = "", $data = array()) {
		return kp_recommender::renderPosts($this->posts, $template, $data);
	}
	
	/**
	 * Render the recommended posts, either using $this->template or the rendered posts
	 *
	 * @param Array $posts: An array of recommendedPost objects to render
	 * @param string $template: The template to use to render (must contain {kp_recommendedPosts} to generate a list of posts)
	 * @param Array $data: Data that should be used in rendering
	 * @return string: The rendered Html
	 **/
	public static function renderPosts($posts, $template = "", $data = array()) {
		global $kp_templates;
		if ($template == "" && isset($kp_templates["kp_recommender"])){
			$template = $kp_templates["kp_recommender"];
		}
		
		$postsHtml = "";
		foreach ($posts as $post) {
			$postsHtml .= $post->render("", $data);
		}
		$data["kp_recommendedPosts"] = $postsHtml;
		
		return kp_renderer::render($template, $data);
	}
	
	/**
	 * Run the recommendation engine and fill $posts with recommendedPosts objects
	 *
	 * @param int $numToRecommend: The number of posts to recommend
	 * @param int $numClosestUsersToUse: The number of users to use when recommending posts (more is less efficient)
	 * @return null
	 **/
	public function run($numPostsToRecommend = 5, $numClosestUsersToUse = -1){
		// Get the unique posts and counts for each user
		global $visitTbl, $wpdb, $defaultNumClosestUsersToUse, $maxPastUpdateDate;
		
		// Check if the user is currently on a post, if not, set the current post to -1
		if (!isset($curr_post_id)){
			$curr_post_id = -1; // This is so $curr_post_id can be used later
		}
		
		// Check if a value was passed in the function or default to the config value set in numClosestUsers
		if ($numClosestUsersToUse < 0){
			$numClosestUsersToUse = $defaultNumClosestUsersToUse;
		}
		
		// Determine if we are test mode and an admin, if so, display the test mode data
		$isTestMode = (get_option('AdminTestMode', "false") == "true" && current_user_can('edit_theme_options') && current_user_can('edit_plugins'));
		if ($isTestMode) {
			$testModeValue = "1";		
		} else {
			$testModeValue = "0";
		}		
		
		// Reset the recomended posts
		$this->posts = array();
		
		// Get the user's visit data
		$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $visitTbl WHERE IP = %s", $this->ipAddress), OBJECT);
		$userVisits = unserialize($user->Visits);		

		// Set up the closest number of users
		$closestUsers = array();
		
		$sql = "
			SELECT * 
			FROM $visitTbl 
			WHERE 
				TestData = '" . $testModeValue . "' AND 
				IP != %s AND (
					UpdateDate > ADDDATE(NOW(), INTERVAL %d DAY) OR CreateDate > ADDDATE(NOW(), INTERVAL %d DAY)
				)";						
		
		// Get the rest of the users within the past Max Update Date (ignore test mode data)
		$otherUsers = $wpdb->get_results($wpdb->prepare($sql, array($this->ipAddress, -1*$maxPastUpdateDate, -1*$maxPastUpdateDate)), OBJECT);		
		
		
		foreach ($otherUsers as $otherUser) {
			// Get the distance between the user and the other users
			$dist = $this->getDistance($userVisits, unserialize($otherUser->Visits));

			if (count($closestUsers) < $numClosestUsersToUse) {
				array_push($closestUsers, array($otherUser->Visits, $dist));
				
			} else {
				// Find the max dist and replace it
				$maxDist = -1;
				$maxInd = 0;
				
				$i = 0;
				foreach($closestUsers as $tempUser) {
					if ($tempUser[1] > $maxDist && $tempUser[1] > $dist) {
						$maxDist = $tempUser[1];
						$maxInd = $i;
					}
					$i = i + 1;
				}
				
				// Unset that array element and push our new close user
				if ($maxDist != -1) {
					unset($closestUsers[$maxInd]);
					$closestUsers[$maxInd] = array($otherUser->Visits, $dist);
				}
			}
		}

		if (count($closestUsers) > 0) {
			// Get the top visit posts from the $closestUsers
			$visitCounts = array(); // will contain {1=>5, 2=>3, 5=>150, post_id=>total visit number}
			
			foreach ($closestUsers as $key => $user) {
				$user[0] = unserialize($user[0]);
			
				foreach ($user[0] as $id => $visitCount) {
					if (!isset($visitCounts[$id])) {
						$visitCounts[$id] = 0;
					}

					$visitCounts[$id] += $visitCount;
				}
			}
			
			// Sort the final array by the counts
			arsort($visitCounts);
			
			// Get the pages and ignore them
			$ignoreIDs = array();
			
			if (kp_checkPro()) {
				$ignoreIDs = kp_ignoreIDs();	
			} 
			
			// Add the pages to the ignore list
			$pages = get_pages();
			foreach ($pages as $page) {
				$ignoreIDs[$page->ID] = true;
			}
			
			// Get a list of deleted posts and add them to the ignore list
			$deletedPosts = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_status != 'publish' AND post_parent = '0'", OBJECT);
			foreach ($deletedPosts as $post) {
				$ignoreIDs[$post->ID] = true;
			}
			
			// Add the posts that the user has already visited
			if ($userVisits != null && count($userVisits) > 0) {
				foreach ($userVisits as $postID => $numVisits) {
					$ignoreIDs[$postID] = true;
				}
			}
			
			$i = 0;
			foreach($visitCounts as $id => $visitCount) {
				// Check that the post isn't in the Ignore list and that we currently aren't on the post
				// If we are in test mode, we may recommend the current post
				if ($i < $numPostsToRecommend && !isset($ignoreIDs[$id]) && ($isTestMode || (!$isTestMode && $curr_post_id != $id)) && $id != "") {			
					array_push($this->posts, new kp_recommendedPost($id));
					$i = $i + 1;
				}
			}
		} else {
			// Need to do something
		}

		return null;
	}

	/**
	 * Save a visit to the post the user is currently at using the user's ip address and the post viewed
	 *
	 * @return null
	 **/
	public function saveVisit($postID) {
		global $visitTbl, $firstPost, $wpdb;
		
		// Check if user is bot or if they have pro version option
		if (!kp_isUserVisitValid($this->ipAddress, $this->userAgent)) {
			return;
		}

		$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $visitTbl WHERE IP=%s", $this->ipAddress), OBJECT);
		
		// Get the row_id for this particular user
		if (isset($row->VisitID)) {
			// unserialize the row data
			$Visits = unserialize($row->Visits);
			
			// Add the new visit
			if (isset($Visits[$postID])) {
				$Visits[$postID] += 1;
			} else {
				$Visits[$postID] = 1;
			}
			
			// Update the row
			$wpdb->query($wpdb->prepare("UPDATE $visitTbl SET Visits=%s, UpdateDate=NOW(), DataSent='0' WHERE IP=%s", serialize($Visits), $this->ipAddress));
			
		} else {
			$Visits = array();
			$Visits[$postID] = 1;
			
			$wpdb->insert($visitTbl, array("Visits" => serialize($Visits), "IP" => $this->ipAddress, "UserAgent" => $this->userAgent));
		}	
	
		return null;
	}
} // End kp_recommender class
?>