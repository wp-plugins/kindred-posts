<?php
class kp_test_recommendedPost {
	public $rpObj; // The recommended post object
	public $post; // The WP_Post object to use
	
	public function __construct($postID){
		$this->post = get_post($postID);
	}
	
	public function runTests(){
		$this->test1();
		$this->test2();
		$this->test3();
		$this->test4();
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
			$template = "{author_user_nicename} {author_user_url} {post_date} {post_url} {post_excerpt} {post_title} {has_thumbnail} {post_thumbnail} {ga_posttitle} {ga_author}";
			$this->rpObj = new kp_recommendedPost($this->post);
			
			$data = array();
			$data["author_user_nicename"] = strtoupper(get_the_author_meta('user_nicename', $this->post->post_author));
			$data["author_user_url"] = get_the_author_meta('user_url', $this->post->post_author);
			$data["post_date"] = strtoupper(date("F j, Y", strtotime($this->post->post_date)));
			$data["post_url"] = get_permalink($this->post->ID);
			$data["post_excerpt"] = $this->post->post_excerpt;
			$data["post_title"] = $this->post->post_title;
			$data["has_thumbnail"] = has_post_thumbnail($this->post->ID);
			$data["post_thumbnail"] = get_the_post_thumbnail( $this->post->ID, array(150, 150) );
			$data["ga_posttitle"] = kp_prepareGoogleAnalytics($this->post->post_title);
			$data["ga_author"] = kp_prepareGoogleAnalytics($data["author_user_nicename"]);			
			
			$test = ($this->rpObj->render($template) == kp_renderer::render($template, $data));
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 4", $test, "recommendedPost object rendered template (with data)", "recommendedPost object didn't render template (with data)");
		$testObj->render();	
	}		
}
?>