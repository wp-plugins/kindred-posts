<?php
class kp_test_widget {
	public function __construct() { }

	public function runTests() {
		/**
		 * 	Test rendering
		 *		- Render widget with/out Title
		 *		- The correct number of posts are recommended (if the number of existing posts exists the number of posts to recommend)
		 *		- Different types of recommended posts
		 **/
		$this->test1(); // Construct the kp_widget
		$this->test2(); // Widget -> Title (with/out content)
		$this->test3(); // Widget -> Number of Posts
		$this->test4(); // Widget -> different post types
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
			$widgetResults = $widgetObj->widget(array(), $widgetInstance, false, "", array(), array(), $user1->ipAddress, $user1->userAgent, true);
			$recommender = $widgetResults["recommender"];
			$test3a = (count($recommender->posts) == $expectedNumPosts);
			$testObj = new kp_test("Test 3a", $test3a, "kp_widget passed standard Number of Posts test", "kp_widget failed standard Number of Posts test");
			$testObj->render();

			// Test that a string falls back to the default
			$expectedNumPosts = $defaultNumPostsToRecommend;
			$widgetInstance = array("numposts" => "asd4");
			$widgetResults = $widgetObj->widget(array(), $widgetInstance, false, "", array(), array(), $user1->ipAddress, $user1->userAgent, true);
			$recommender = $widgetResults["recommender"];
			$test3b = (count($recommender->posts) == $expectedNumPosts);
			$testObj = new kp_test("Test 3b", $test3b, "kp_widget passed fallback Number of Posts test", "kp_widget failed fallback Number of Posts test");
			$testObj->render();
			
			// Test a close user. User 1 has visited the same post as User 2. User 2 has explored the website more than User 1 so display all the other posts that User 2 has seen.
			$expectedNumPosts = count($user2->visitedPostIDs)-1;
			$widgetInstance = array("numposts" => (wp_count_posts('post', 'readable')->publish + 50));
			// Recommend for User 1 
			$widgetResults = $widgetObj->widget(array(), $widgetInstance, false, "", array(), array(), $user1->ipAddress, $user1->userAgent, true);
			$recommender = $widgetResults["recommender"];
			$test3c = (count($recommender->posts) == $expectedNumPosts);
			$testObj = new kp_test("Test 3c", $test3b, "kp_widget passed cloning Number of Posts test", "kp_widget failed cloning Number of Posts test");
			$testObj->render();			
		
			$test = $test3a && $test3b && test3c;
			
			$user1->deleteVisitData();
			$user2->deleteVisitData();
			$testData->deleteAllTestPosts();
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 3", $test, "kp_widget passed Number of Posts tests", "kp_widget failed Number of Posts tests");
		$testObj->render();	
	}
	
	function test4() {
		$test = true;
		
		// Prepare the test data
		$testData = new kp_testData();
		$testPostIDs = $testData->insertTestPosts(3, "post");
		$testPageIDs = $testData->insertTestPosts(3, "page");
		$postKeys = array_keys($testPostIDs);
		$pageKeys = array_keys($testPageIDs);
		$user1 = new kp_testUser(array($postKeys[0], $pageKeys[0], $attachmentKeys[0]), array()); 
		$user2 = new kp_testUser(array($postKeys[0], $postKeys[1], $postKeys[2], $pageKeys[0], $pageKeys[1], $pageKeys[2]), array());
		
		try {
			// Recommend only posts
			$test4a = true;
			$widgetObj = new kp_widget();
			$widgetInstance = array("posttypes-post" => true, "posttypes-page" => false, "numposts" => 6);
			$widgetResults = $widgetObj->widget(array(), $widgetInstance, false, "", array(), array(), $user1->ipAddress, $user1->userAgent);
			$recommender = $widgetResults["recommender"];
			foreach ($recommender->posts as $recommendedPost) {
				$test4a = $test4a && ($recommendedPost->post->post_type == "post");
			}
			$test = $test && $test4a;
			
			// Recommend only page (don't specify what to do with posts or pages)
			$test4b = true;
			$widgetObj = new kp_widget();
			$widgetInstance = array("posttypes-page" => true, "numposts" => 6);
			$widgetResults = $widgetObj->widget(array(), $widgetInstance, false, "", array(), array(), $user1->ipAddress, $user1->userAgent);
			$recommender = $widgetResults["recommender"];
			foreach ($recommender->posts as $recommendedPost) {
				$test4b = $test4b && ($recommendedPost->post->post_type == "page");
			}
			$test = $test && $test4b;
			
			// Recommend only kp_test_custom and pages but not posts
			$test4c = true;
			$widgetObj = new kp_widget();
			$widgetInstance = array("posttypes-post" => true, "posttypes-page" => true, "numposts" => 10);
			$widgetResults = $widgetObj->widget(array(), $widgetInstance, false, "", array(), array(), $user1->ipAddress, $user1->userAgent);
			$recommender = $widgetResults["recommender"];
			foreach ($recommender->posts as $recommendedPost) {
				$test4c = $test4c && ($recommendedPost->post->post_type == "post" || $recommendedPost->post->post_type == "page");
			}
			$test = $test && $test4c;
			
			// Don't set any post types, default to all post types
			$test4d = true;
			$widgetObj = new kp_widget();
			$widgetInstance = array("numposts" => 10);
			$widgetResults = $widgetObj->widget(array(), $widgetInstance, false, "", array(), array(), $user1->ipAddress, $user1->userAgent);
			$recommender = $widgetResults["recommender"];
			foreach ($recommender->posts as $recommendedPost) {
				$test4d = $test4d && ($recommendedPost->post->post_type == "post" || $recommendedPost->post->post_type == "page");
			}
			$test = $test && $test4d;
		} catch (Exception $e) {
		}
		// Remove the test data
		$user1->deleteVisitData();
		$user2->deleteVisitData();
		$testData->deleteAllTestPosts();
	
		$testObj = new kp_test("Test 4", $test, "kp_widget passed different post types tests", "kp_widget failed different post types tests");
		$testObj->render();	
	}
} // End kp_test_widget class
?>