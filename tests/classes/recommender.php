<?php
class kp_test_recommender {
	public $recObj; // The recommender object
	
	public function __construct() { }

	public function runTests() {
		$isTestMode = get_option("kp_AdminTestMode", "false");
		$this->test1();
		$this->test2();
		$this->test3();
		$this->test4();
		$this->test5(); // (1 user)
		$this->test6(); // (4 users, 1 user closely related to 1 other user)
		$this->test7(); // (5 users, 1 user closely related to 2 other users)
		$this->test8(); // Test kp_runRecommender returns the same results as the the recommender
		update_option("kp_AdminTestMode", $isTestMode);
	}
	
	/**
	 * Construct the recommender
	 **/
	public function test1() {
		$ip = "";
		$ua = "";
		try {
			$this->recObj = new kp_recommender($ip, $ua);
			$test = true;
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 1", $test, "recommender object constructed", "recommender object not constructed");
		$testObj->render();
	}
	
	/**
	 * Construct the recommender and attempt to render the template
	 **/
	public function test2() {
		$ip = "";
		$ua = "";
		$template = "{kp_recommendedPosts}s";
		try {
			$this->recObj = new kp_recommender($ip, $ua);
			$test = ($this->recObj->render($template) == "s");
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 2", $test, "recommender object rendered", "recommender object not rendered");
		$testObj->render();
	}
	
	/**
	 * Test the recommender->saveVisit
	 **/	
	public function test3() {
		kp_resetVisitData();
		
		$ip = "";
		$ua = "";
		$template = "{Posts}";
		try {
			$this->recObj = new kp_recommender($ip, $ua, $template);
			$this->recObj->saveVisit(1);
			$test = true;
			
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 3", $test, "recommender object visit saved", "recommender object not visit saved");
		$testObj->render();	
		
		kp_resetVisitData();
	}
	
	/**
	 * Test the recommender->getDistance
	 **/
	public function test4() {
		kp_resetVisitData();
		
		$ip = "1";
		$ua = "1";
		$template = "{Posts}";
		try {	
			$this->recObj = new kp_recommender($ip, $ua, $template);
			$dist = $this->recObj->getDistance(array(1=>1), array(1=>1));
			$test = ($dist == 100.0);
			
			if ($test) {
				$dist = $this->recObj->getDistance(array(1=>1, 2=>1), array(1=>1, 2=>1));
				$test = ($dist == 0);
			}

			if ($test) {
				$dist = $this->recObj->getDistance(array(1=>1, 2=>1, 3=>1), array(1=>1, 2=>1));
				$test = ($dist == 1);
			}
			
			if ($test) {
				$dist = $this->recObj->getDistance(array(1=>2, 2=>1, 3=>1), array(1=>1, 2=>1));
				$test = ($dist == sqrt(2));
			}			
			
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 4", $test, "recommender object distance calculated", "recommender object distance not calculated");
		$testObj->render();

		kp_resetVisitData();		
	}
	
	/**
	 * Test the recommender->run (with 1 user so no recommendations)
	 **/
	public function test5() {
		kp_resetVisitData();
		
		try {
			// Set up test data in database
			$testData = new kp_testData();
			$testPostIDs = $testData->insertTestPosts(10);
			$tK = array_keys($testPostIDs);
			
			$user1 = new kp_testUser(array($tK[0], $tK[1], $tK[2]), array()); 
			$user1->recommender->run(1, 1);
			$test = (count($user1->recommender->posts) == 0);
			
			$user1->deleteVisitData();
			$testData->deleteAllTestPosts();
			
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 5", $test, "recommender object running correctly", "recommender object not running correctly");
		$testObj->render();	
		
		kp_resetVisitData();
	}	
	
	/**
	 * Test the recommender->run (4 users, 1 user closely related to 1 other user)
	 **/
	public function test6() {
		kp_resetVisitData();
		
		try {
			// Set up test data in database
			$testData = new kp_testData();
			$testPostIDs = $testData->insertTestPosts(10);
			$tK = array_keys($testPostIDs);
			
			// User Group #1
			$user1 = new kp_testUser(array($tK[0], $tK[1], $tK[2]), array()); 
			$user2 = new kp_testUser(array($tK[0], $tK[1], $tK[2], $tK[3]), array()); 
			
			// User Group #2
			$user3 = new kp_testUser(array($tK[4], $tK[5], $tK[6]), array()); 		
			$user4 = new kp_testUser(array($tK[4], $tK[5], $tK[6]), array());
			
			$user1->recommender->run(1, 1);

			$test = ($user1->recommender->posts[0]->post_id == $tk[3]);
			
			$user1->deleteVisitData();
			$user2->deleteVisitData();
			$user3->deleteVisitData();
			$user4->deleteVisitData();			
			$testData->deleteAllTestPosts();
			
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 6", $test, "recommender object running correctly", "recommender object not running correctly");
		$testObj->render();	
		
		kp_resetVisitData();
	}
	
	/**
	 * Test the recommender->run (5 users, 1 user closely related to 2 other users)
	 **/
	public function test7() {
		kp_resetVisitData();
		
		try {
			// Set up test data in database
			$testData = new kp_testData();
			$testPostIDs = $testData->insertTestPosts(10);
			$tK = array_keys($testPostIDs);
			
			// User Group #1
			$user1 = new kp_testUser(array($tK[0], $tK[1]), array()); 
			$user2 = new kp_testUser(array($tK[0], $tK[1], $tK[2], $tK[3]), array()); 
			$user3 = new kp_testUser(array($tK[0], $tK[1], $tK[2], $tK[5]), array()); 
			
			// User Group #2
			$user4 = new kp_testUser(array($tK[4], $tK[5], $tK[6]), array()); 		
			$user5 = new kp_testUser(array($tK[4], $tK[5], $tK[6]), array());
			
			$user1->recommender->run(2, 2, array(), true);

			$test = true;
			// Check if the post #2 and if #3 or #5 were recommended
			$test = ($test && ($user1->recommender->isPostRecommended($tk[2]) && ($user1->recommender->isPostRecommended($tk[3]) || $user1->recommender->isPostRecommended($tk[5]))));
			
			$user1->deleteVisitData();
			$user2->deleteVisitData();
			$user3->deleteVisitData();
			$user4->deleteVisitData();			
			$user5->deleteVisitData();			
			$testData->deleteAllTestPosts();
			
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 7", $test, "recommender object running correctly", "recommender object not running correctly");
		$testObj->render();	
		
		kp_resetVisitData();
	}
	
	/**
	 * Test kp_runRecommender returns the same results as the the recommender
	 **/
	public function test8() {
		kp_resetVisitData();
		
		try {
			// Set up test data in database
			$testData = new kp_testData();
			$testPostIDs = $testData->insertTestPosts(10);
			$tK = array_keys($testPostIDs);
			
			// User Group #1
			$user1 = new kp_testUser(array($tK[0], $tK[1], $tK[2]), array()); 
			$user2 = new kp_testUser(array($tK[0], $tK[1], $tK[2], $tK[3]), array()); 
			
			// User Group #2
			$user3 = new kp_testUser(array($tK[4], $tK[5], $tK[6]), array()); 		
			$user4 = new kp_testUser(array($tK[4], $tK[5], $tK[6]), array());
			
			// Get the recommendation from the recommender
			$user1->recommender->run(1, 1);
			
			// Get the recommendation from the lib function
			$posts = kp_getRecommendedWP_Posts(1, $user1->ipAddress, $user1->userAgent);

			$test = ($user1->recommender->posts[0]->post_id == $posts[0]->post_id);
			
			$user1->deleteVisitData();
			$user2->deleteVisitData();
			$user3->deleteVisitData();
			$user4->deleteVisitData();			
			$testData->deleteAllTestPosts();
			
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 8", $test, "results from recommendation function match results from recommendation engine", "results from recommendation function do not match results from recommendation engine");
		$testObj->render();
		
		kp_resetVisitData();	
	}
} // End kp_test_recommender
?>