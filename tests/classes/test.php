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