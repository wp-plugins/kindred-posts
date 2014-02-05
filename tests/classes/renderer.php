<?php
class kp_test_renderer {
	public function __construct(){ }

	public function runTests(){
		$this->test1(); // no template or data
		$this->test2(); // no data
		$this->test3(); // no template
		$this->test4(); // template and data (incomplete data)
		$this->test5(); // template and data (complete data)
		$this->test6(); // template and data (JIT complete data)
		$this->test7(); // Comments
		$this->test8(); // if statements (is true)
		$this->test9(); // if statements (is false)
		$this->test10(); // if/else statements (render if statement)
		$this->test11(); // if/else statements (render else statement)
		$this->test12(); // nested if/else statements (render if/if statement)
		$this->test13(); // nested if/else statements (render if/else statement)
		$this->test14(); // nested if/else statements (render else/if statement)
		$this->test15(); // nested if/else statements (render else/else statement)
		$this->test16(); // if statements (with regular expressions as keys)
	}
	
	/**
	 * Test if the renderer works with no template or data
	 **/
	public function test1(){
		try {
			$expected = "";
			$test = (kp_renderer::render() == $expected);
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 1", $test, "renderer passed with empty template/data", "renderer failed with empty template/data");
		$testObj->render();	
	}
	
	/**
	 * Test if the renderer works with a template but no data
	 **/
	public function test2(){
		try {
			$template = "{a1}{a2}";
			$test = (kp_renderer::render($template) == $template);
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 2", $test, "renderer passed with empty data", "renderer failed with empty data");
		$testObj->render();	
	}

	/**
	 * Test if the renderer works with data but no template
	 **/
	public function test3(){
		try {
			$template = "";
			$data = array();
			$data["."] = "HI";
			$test = (kp_renderer::render($template, $data) == $template);
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 3", $test, "renderer passed with empty template", "renderer failed with empty template");
		$testObj->render();	
	}

	/**
	 * Test if the renderer works with data and template (incomplete data)
	 **/
	public function test4(){
		try {
			$template = "{Var1} {Var2}";
			$data = array();
			$data["Var1"] = "Hello";
			$test = (kp_renderer::render($template, $data) == "Hello {Var2}");
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 4", $test, "renderer passed with template and data (incomplete data)", "renderer failed with template and data (incomplete data)");
		$testObj->render();	
	}

	/**
	 * Test if the renderer works with data and template (complete data)
	 **/
	public function test5(){
		try {
			$template = "{Var1} {Var2}";
			$data = array();
			$data["Var1"] = "Hello";
			$data["Var2"] = "World";
			$test = (kp_renderer::render($template, $data) == "Hello World");
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 5", $test, "renderer passed with template and data  (complete data)", "renderer failed with template and data  (complete data)");
		$testObj->render();	
	}
	
	/**
	 * Test if the renderer works with data and template
	 **/
	public function test6(){
		try {
			$template = "{Var1} {Var2}";
			$key1 = "Var1";
			$val1 = "Hello";
			$key2 = "Var2";
			$val2 = "World";
			$test = (kp_renderer::render($template, array($key2=>$val2, $key1=>$val1)) == "Hello World");
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 6", $test, "renderer passed with template and inline data", "renderer failed with template and inline data");
		$testObj->render();	
	}
	
	/**
	 * Test if the renderer works removes a comment
	 **/
	public function test7() {
		try {
			$template = "{comment} {/comment}";
			$test = (kp_renderer::render($template) == "");
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 7", $test, "renderer passed with comments", "renderer failed with comments");
		$testObj->render();
	}
	
	/**
	 * Test if the renderer works with if statements (is true)
	 **/
	public function test8() {
		try {
			$expected = "asdf";
			$template = '{if kp_widget:orientation-horizontal}' . $expected . '{/if kp_widget:orientation-horizontal}';
			$data = array("kp_widget:orientation-horizontal" => true);
			$test = (kp_renderer::render($template, $data) == $expected);
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 8", $test, "renderer passed if statements (is true)", "renderer failed if statements (is true)");
		$testObj->render();
	}

	/**
	 * Test if the renderer works with if statements (but false)
	 **/
	public function test9() {
		try {
			$expected = "asdf";
			$template = "{if Is:True-True}asdf {/if Is:True-True}" . $expected;
			$data = array("Is:True-True" => false);
			$test = (kp_renderer::render($template, $data) === $expected);
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 9", $test, "renderer passed if statements (is false)", "renderer failed if statements (is false)");
		$testObj->render();
	}
	
	/**
	 * Test if the renderer works with if/else statements (render if statement)
	 **/
	public function test10() {
		try {
			$expected = "asdf";
			$template = "{if IsTrue}" . $expected . "{else IsTrue}dfgh{/if IsTrue}";
			$data = array("IsTrue" => true);
			$test = (kp_renderer::render($template, $data) == $expected);
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 10", $test, "renderer passed if/else statements (render if statement)", "renderer failed if statements (render if statement)");
		$testObj->render();
	}

	/**
	 * Test if the renderer works with if/else statements (render else statement)
	 **/
	public function test11() {
		try {
			$expected = "dfgh";
			$template = "{if IsTrue}asdf{else IsTrue}" . $expected . "{/if IsTrue}";
			$data = array("IsTrue" => false);
			$test = (kp_renderer::render($template, $data) == $expected);
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 11", $test, "renderer passed if/else statements (render else statement)", "renderer failed if statements (render else statement)");
		$testObj->render();
	}
	
	/**
	 * Test if the renderer works with nested if/else statements (render if/if statement)
	 **/
	public function test12() {
		try {
			$expected = "asdf";
			$template = "{if IsTrue1}{if IsTrue2}" . $expected . "{else IsTrue2}{/if IsTrue2}{else IsTrue1}{if IsTrue2}{else IsTrue2}{/if IsTrue2}{/if IsTrue1}";
			$data = array("IsTrue1" => true, "IsTrue2" => true);
			$test = (kp_renderer::render($template, $data) == $expected);
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 12", $test, "renderer passed if/else statements (render if/if statement)", "renderer failed if/else statements (render if/if statement)");
		$testObj->render();
	}

	/**
	 * Test if the renderer works with nested if/else statements (render if/else statement)
	 **/
	public function test13() {
		try {
			$expected = "asdf";
			$template = "{if IsTrue1}{if IsTrue2}{else IsTrue2}" . $expected . "{/if IsTrue2}{else IsTrue1}{if IsTrue2}{else IsTrue2}{/if IsTrue2}{/if IsTrue1}";
			$data = array("IsTrue1" => true, "IsTrue2" => false);
			$test = (kp_renderer::render($template, $data) == $expected);
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 13", $test, "renderer passed if/else statements (render if/else statement)", "renderer failed if/else statements (render if/else statement)");
		$testObj->render();
	}	
	
	/**
	 * Test if the renderer works with nested if/else statements (render else/if statement)
	 **/
	public function test14() {
		try {
			$expected = "asdf";
			$template = "{if IsTrue1}{if IsTrue2}{else IsTrue2}{/if IsTrue2}{else IsTrue1}{if IsTrue2}" . $expected . "{else IsTrue2}{/if IsTrue2}{/if IsTrue1}";
			$data = array("IsTrue1" => false, "IsTrue2" => true);
			$test = (kp_renderer::render($template, $data) == $expected);
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 14", $test, "renderer passed if/else statements (render else/if statement)", "renderer failed if/else statements (render else/if statement)");
		$testObj->render();
	}		
	
	/**
	 * Test if the renderer works with nested if/else statements (render else/else statement)
	 **/
	public function test15() {
		try {
			$expected = "asdf";
			$template = "{if IsTrue1}{if IsTrue2}{else IsTrue2}{/if IsTrue2}{else IsTrue1}{if IsTrue2}{else IsTrue2}" . $expected . "{/if IsTrue2}{/if IsTrue1}";
			$data = array("IsTrue1" => false, "IsTrue2" => false);
			$test = (kp_renderer::render($template, $data) == $expected);
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 15", $test, "renderer passed if/else statements (render else/else statement)", "renderer failed if/else statements (render else/else statement)");
		$testObj->render();
	}
	
	/**
	 * Test if the renderer works with if statements (with regular expressions as keys)
	 **/
	public function test16() {
		try {
			$expected = "asdf";
			$key = "{\d}";
			$template = "{if " . $key . "}" . $expected . "{/if " . $key . "}";
			$data = array($key => true);
			$test = (kp_renderer::render($template, $data) == $expected);
		} catch (Exception $e) {
			$test = false;
		}
		
		$testObj = new kp_test("Test 16", $test, "renderer passed if statements (with regular expressions as keys)", "renderer failed if statements (with regular expressions as keys)");
		$testObj->render();
	}
} // End kp_test_renderer class
?>