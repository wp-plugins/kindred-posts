<?php
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
		$this->recommender->saveVisit($postID, true);
	}
} // End kp_testUser class