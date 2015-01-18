<?php
class kp_test_plugin {
	public function __construct() {
	}
	
	public function runTests() {
		$this->test1();
	}
	
	public function checkOption($optionName) {
		$optionValue = get_option($optionName);
		if ($optionValue !== FALSE) {
			throw new Exception("Bad Option Value");
		}
		return true;
	}
	
	
	public function test1() {
		try {
			// Check that all the non-namespaced options have been removed
			
			$this->checkOption("AdminTestMode");
			$this->checkOption("AttemptToBlockBotVisits");
			$this->checkOption("CollectStatistics");
			$this->checkOption("TrackGA");
			$this->checkOption("FirstSave");
			$this->checkOption("RecommendPosts");
			$this->checkOption("RecommendPages");
			$this->checkOption("BlockedIPs");
			$this->checkOption("HideFeedbackBox");
			$this->checkOption("FeedbackMsg");		
			
			$test = true;
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 1", $test, "plugin options updated properly", "plugin options not updated properly");
		$testObj->render();		
	}
}