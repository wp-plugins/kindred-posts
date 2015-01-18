<?php
class kp_test_recommendedPost {
	public $rpObj; // The recommended post object
	public $post; // The WP_Post object to use
	
	public function __construct($postID){
		$this->post = get_post($postID);
	}
	
	public function runTests() {
		$this->test1();
		$this->test2();
		$this->test3();
		$this->test4();
		$this->test5();
	}
	
	/**
	 * Construct a recommendedPost object with a post
	 **/	
	public function test1(){
		try {
			$this->rpObj = new kp_recommendedPost($this->post);
			$test = true;
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 1", $test, "recommendedPost object constructed", "recommendedPost object not constructed");
		$testObj->render();
	}
	
	/**
	 * Construct a recommendedPost object with a template and data defined by user
	 * Checks getTemplateData() when templateData exists
	 **/ 
	public function test2(){
		try {
			$template = "{a1}{a2}";
			$templateData = array();
			$templateData["a1"] = "1";
			$templateData["a2"] = "a";
			$this->rpObj = new kp_recommendedPost($this->post);
			$test = ($this->rpObj->render($template, $templateData) == kp_renderer::render($template, $templateData));
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 2", $test, "recommendedPost object rendered template (with template/data)", "recommendedPost object didn't render template (with template/data)");
		$testObj->render();	
	}
	
	/**
	 * Construct a recommendedPost object with a template (but use the post's data)
	 * Checks getTemplateData() when templateData doesn't exist
	 **/ 
	public function test3(){
		// Construct the recommendedPost object (rpObj) then create a template that will be filled in by the rpObj from the post's details
		try {
			$this->rpObj = new kp_recommendedPost($this->post);
			$expected_author_user_nicename = strtoupper(get_the_author_meta('user_nicename', $this->post->post_author));
			$expected_post_title = $this->post->post_title;
			$expectedTemplate = $expected_author_user_nicename . $expected_post_title;
			$test = ($this->rpObj->render("{author_user_nicename}{post_title}") == $expectedTemplate);
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 3", $test, "recommendedPost object rendered template (with template)", "recommendedPost object didn't render template (with template)");
		$testObj->render();	
	}

	/**
	 * Construct a recommendedPost object with a post and double check the data 
	 * Checks generateTemplateData($data = array())
	 **/ 
	public function test4(){
		// Construct the recommendedPost object (rpObj) then create a template that will be filled in by the rpObj from the post's details
		// use that same template and known post properties to see how they compare
		try {
			$template = "{author_user_nicename} {author_user_url} {post_date} {post_date_nice} {post_url} {post_excerpt} {post_title} {has_thumbnail} {post_thumbnail} {kp:trackingcode}";
			$this->rpObj = new kp_recommendedPost($this->post);
			
			$data = kp_renderer::returnTemplateData($this->post);
			$data["kp:trackingcode"] = kp_prepareTrackingCode($data);

			$test = ($this->rpObj->render($template) == kp_renderer::render($template, $data));
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 4", $test, "recommendedPost object rendered template (with data)", "recommendedPost object didn't render template (with data)");
		$testObj->render();	
	}

	/**
	 * Test Tracking Code
	 **/
	public function test5() {
		$test = true;
		$currentTrackingCode = get_option("kp_TrackingCode", "");
		$currentTracking = get_option("kp_Tracking", "");
		
		try {
			$this->rpObj = new kp_recommendedPost($this->post);
			update_option("kp_Tracking", "custom");
			
			// Test simple tracking: {post_id}
			$template = "{kp:trackingcode}";
			$expected = "" . $this->post->ID . "";
			$trackingCodeTemplate = "{post_id}";
			update_option("kp_TrackingCode", $trackingCodeTemplate);
			$data = kp_renderer::returnTemplateData($this->post);
			$data["kp:trackingcode"] = kp_renderer::render($trackingCodeTemplate, $data);
			$test5a = ($this->rpObj->render($template) == kp_renderer::render($template, $data) && kp_renderer::render($template, $data) == $expected);
			$test = $test5a && $test;
			$testObj = new kp_test("Test 5a", $test5a, "kp_widget passed simple Tracking Code test", "kp_widget failed simple Tracking Code test");
			$testObj->render();
			
			// Test <a href="#" onclick="{post_id}"></a>
			$template = "<a href=\"#\" onclick=\"{kp:trackingcode}\">Empty Link</a>";
			$expected = "<a href=\"#\" onclick=\"" . $this->post->ID . "\">Empty Link</a>";
			$trackingCodeTemplate = "{post_id}";
			update_option("kp_TrackingCode", $trackingCodeTemplate);
			$data = kp_renderer::returnTemplateData($this->post);
			$data["kp:trackingcode"] = kp_renderer::render($trackingCodeTemplate, $data);
			$test5b = ($this->rpObj->render($template) == kp_renderer::render($template, $data) && kp_renderer::render($template, $data) == $expected);
			$test = $test5b && $test;
			$testObj = new kp_test("Test 5b", $test5b, "kp_widget passed simple link test", "kp_widget failed simple link test");
			$testObj->render();
			
			// Test <a href="#" onclick="{post_slug}"></a> with single quote
			$template = "<a href=\"#\" onclick=\"{kp:trackingcode}\">Empty Link</a>";
			$expected = "<a href=\"#\" onclick=\"" . $this->post->post_name . "\">Empty Link</a>";
			$trackingCodeTemplate = "{post_slug}";
			update_option("kp_TrackingCode", $trackingCodeTemplate);
			$data = kp_renderer::returnTemplateData($this->post);
			$data["kp:trackingcode"] = kp_renderer::render($trackingCodeTemplate, $data);
			$test5c = ($this->rpObj->render($template) == kp_renderer::render($template, $data) && kp_renderer::render($template, $data) == $expected);
			$test = $test5c && $test;
			$testObj = new kp_test("Test 5c", $test5c, "kp_widget passed post_name test", "kp_widget failed post_name test");
			$testObj->render();
			
		} catch (Exception $e) {
			$test = false;
		}
		update_option("kp_TrackingCode", $currentTrackingCode);
		update_option("kp_Tracking", $currentTracking);
		
		$testObj = new kp_test("Test 5", $test, "kp_widget passed Tracking Code tests", "kp_widget failed Tracking Code tests");
		$testObj->render();	
	}	
} // End kp_test_recommendedPost class
?>