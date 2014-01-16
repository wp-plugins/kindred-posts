<?php
/**
 *
 * Start kp_test class
 *
 **/
class kp_test {
	public $testName; // The name of the test
	public $test; // The test that we are running
	public $successMsg; // The message to display when the test passes
	public $failureMsg; // The message to display when the test fails

	/**
	 * The constructor
	 *
	 * @param string $testName: The name of the test
	 * @param bool $test: The test to run
	 * @param bool $verbose: Indicates we display the messages
	 * @param string $successMsg: The message to display if the $test passes
	 * @param string $failureMsg: The message to display if the $test fails
	 * @return bool: Indicates if $test is successful
	 **/	
	public function __construct($testName, $test, $successMsg = "", $failureMsg = "") {
		$this->testName = $testName;
		$this->test = $test;
		$this->successMsg = $successMsg;
		$this->failureMsg = $failureMsg;
	}

	/**
	 * Output the test
	 * 
	 * @param bool $verbose: Indicates if we should output messages
	 * @return string
	 **/
	public function render($verbose = true){
		$template = "{TestName}: <span style=\"color:{Color}\">{Msg}</span><br />"; // Define the template to use when rendering the test object
	
		$data = array();
		$data["TestName"] = $this->testName;
		if ($this->test) {
			$data["Msg"] = $this->successMsg;
			$data["Color"] = "black";
		} else {
			$data["Msg"] = $this->failureMsg;
			$data["Color"] = "red";
		}	
	
		if ($verbose) {
			echo kp_renderer::render($template, $data);
		}
		
		return kp_renderer::render($template, $data);
	}
} // End kp_test class

/**
 *
 * Start kp_testData class
 *
 **/
class kp_testData {
	public $testPostIDs = array(); // An array of test posts that were inserted into the database
	
	/**
	 * Construct the kp_testData object
	 **/
	public function __construct() {
		$this->testPostIDs = array(); // Store an array of test Post IDs
	}
	
	/**
	 * Delete test posts from database
	 *
	 * @return array: Holds the IDs of the posts that were deleted
	 **/
	public function deleteAllTestPosts() {
		$deleteIDs = array(); // Stores the IDs of the posts that were deleted successfully
		
		foreach ($this->testPostIDs as $postID => $val) {
			// Force delete the posts in the array
			if (wp_delete_post($postID, true)) {
				$deletedIDs[$postID] = true;
			}
		}
		
		foreach ($deleteIDs as $postID) {
			unset($this->testPostIDs[$postID]);
		}

		return $deletedIDs;
	}	
	
	/**
	 * Insert test posts into database
	 *
	 * @return array: Holds the IDs of the posts inserted
	 **/
	public function insertTestPosts($numToInsert = 10) {
		$i = count($this->testPostIDs) + 1;
		while ($i <= $numToInsert){
			$testPost = array(
				'post_title'    => 'My post ' . $i,
				'post_content'  => 'This is my post ' . $i,
				'post_status'   => 'publish',
				'post_author'   => 1,
				'post_category' => array()
			);
			
			$postID = wp_insert_post($testPost);
			if ($postID != 0){
				$this->testPostIDs[$postID] = get_post($postID);
			}
			
			$i++;
		}
			
		return $this->testPostIDs;
	}
} // End kp_testData class

/**
 *
 * Start the kp_testUser class
 *
 **/
class kp_testUser {
	public $ipAddress = ""; // The user's IP Addres
	public $userAgent = ""; // The user's User Agent
	public $visitedPostIDs = array();
	public $interestedInPostIDs = array();
	
	public $recommender = null; // The user's personal recommender object
	
	/**
	 * Construct the kp_testUser
	 *
	 * @param array $visitedPostIDs: The posts that the user has visited
	 * @param array $interestedInPostIDs: The posts that the user is interested in
	 **/
	public function __construct($visitedPostIDs = array(), $interestedInPostIDs = array()) {
		$this->interestedInPostIDs = $interestedInPostIDs;
		
		$this->generateIpAddress();
		$this->generateUserAgent();
		$this->generateRecommender();
		
		// Save each visited post for the user
		foreach ($visitedPostIDs as $postID) {
			$this->savePostVisit($postID);
		}
	}
	
	/**
	 * Delete the posts that this user has visited
	 *
	 * @return null
	 **/
	public function deleteVisitData() {
		kp_resetVisitData($this->ipAddress, $this->userAgent);
	}
	
	/**
	 * Generate the user's IP Address
	 *
	 * @return null
	 **/
	public function generateIpAddress() {
		$this->ipAddress = rand(1, 100000000);
		return null;
	}
	
	public function generateInterests() {
		// Stub, may be useful for randomly generating data
	}
	
	/**
	 * Generate the user's recommender object
	 *
	 * @return null
	 **/
	public function generateRecommender() {
		$this->recommender = new kp_recommender($this->ipAddress, $this->userAgent);
	}
	
	/**
	 * Generate the user's User Agent
	 *
	 * @return null
	 **/	
	public function generateUserAgent() {
		$this->userAgent = rand(1, 100000000);
		return null;
	}	

	public function generateVisits() {
		// Stub, may be useful for randomly generating data
	}
	
	/**
	 * Save a visit from this user to a postID
	 *
	 * @param int $postID: The post to visit
	 * @return null
	 **/
	public function savePostVisit($postID) {
		$this->visitedPostIDs[$postID] = true;
		$this->recommender->saveVisit($postID);
	}
} // End kp_testUser class
?>