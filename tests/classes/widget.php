<?php
class kp_test_widget {
	public function __construct() { }

	public function runTests() {
		/**
		 * 	Test rendering
		 *		- Render widget with/out Title
		 *		- The correct number of posts are recommended (if the number of existing posts exists the number of posts to recommend)
		 *		- Each checkbox
		 *		- Horiztonal vs. Vertical
		 **/
		$this->test1(); // Construct the kp_widget
		$this->test2(); // Widget -> Title (with/out content)
		$this->test3(); // Widget -> Number of Posts
	}
	
	/**
	 * Test if the widget can be constructed
	 **/
	public function test1() {
		try {
			$widget = new kp_widget();
			$test = ($widget->name == 'Kindred Posts');
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 1", $test, "kp_widget constructed", "kp_widget not constructed");
		$testObj->render();	
	}
	
	/**
	 * Test widget -> Title (with/out content)
	 **/
	public function test2() {
		try {
			// Set up test data in database
			$testData = new kp_testData();
			$testPostIDs = $testData->insertTestPosts(1);
			$tK = array_keys($testPostIDs);
		
			$recommendedPosts = array(new kp_recommendedPost($tK[0]));
			$widgetObj = new kp_widget();		
		
			$expected = "asdf";
			$widgetInstance = array("title" => $expected);
			$template = "{kp_widget:title}";
			$widgetResults = $widgetObj->widget(array(), $widgetInstance, false, $template, array(), $recommendedPosts);
			extract($widgetResults);
			$test = ($widgetHtml == $expected);
			
			$expected = "";
			$widgetInstance = array();
			$template = "{kp_widget:title}";
			$widgetResults = $widgetObj->widget(array(), $widgetInstance, false, $template, array(), $recommendedPosts);
			extract($widgetResults);
			$test = ($test && ($widgetHtml == $expected));
			
			$testData->deleteAllTestPosts();
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 2", $test, "kp_widget passed title rendering", "kp_widget failed to render title");
		$testObj->render();	
	}
	 
	/**
	 * Test widget Number of Posts
	 **/
	public function test3() {
		global $defaultNumPostsToRecommend;

		try {
			// Set up test data in database
			$testData = new kp_testData();
			$testPostIDs = $testData->insertTestPosts(10);
			$tK = array_keys($testPostIDs);
			$user1 = new kp_testUser(array($tK[0]), array()); 
			$user2 = new kp_testUser(array($tK[0], $tK[1], $tK[2], $tK[3], $tK[4], $tK[5], $tK[6]), array()); 
		
			$widgetObj = new kp_widget();	
		
			// Test a standard number of posts
			$expectedNumPosts = 4;
			$widgetInstance = array("numposts" => 4);
			$widgetResults = $widgetObj->widget(array(), $widgetInstance, false, "", array(), array(), $user1->ipAddress, $user1->userAgent);
			$recommender = $widgetResults["recommender"];
			$test = (count($recommender->posts) == $expectedNumPosts);

			// Test that a string falls back to the default
			$expectedNumPosts = $defaultNumPostsToRecommend;
			$widgetInstance = array("numposts" => "asd4");
			$widgetResults = $widgetObj->widget(array(), $widgetInstance, false, "", array(), array(), $user1->ipAddress, $user1->userAgent);
			$recommender = $widgetResults["recommender"];
			$test = ($test && (count($recommender->posts) == $expectedNumPosts));
			
			// Test a close user. User 1 has visited the same post as User 2. User 2 has explored the website more than User 1 so display all the other posts that User 2 has seen.
			$expectedNumPosts = count($user2->visitedPostIDs)-1;
			$widgetInstance = array("numposts" => (wp_count_posts('post', 'readable')->publish + 50));
			// Recommend for User 1 
			$widgetResults = $widgetObj->widget(array(), $widgetInstance, false, "", array(), array(), $user1->ipAddress, $user1->userAgent);
			$recommender = $widgetResults["recommender"];
			$test = ($test && (count($recommender->posts) == $expectedNumPosts));			
		
			$user1->deleteVisitData();
			$user2->deleteVisitData();
			$testData->deleteAllTestPosts();
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 3", $test, "kp_widget passed Number of Posts tests", "kp_widget failed Number of Posts tests");
		$testObj->render();	
	}	 
} // End kp_test_widget class
?>