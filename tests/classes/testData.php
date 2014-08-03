<?php
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