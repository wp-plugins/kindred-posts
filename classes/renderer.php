<?php
/**
 * Start kp_renderer class
 **/
class kp_renderer {
	/**
	 * Prepare a string such as <key> for use in a regular expression
	 *
	 * @param string $str: The string to prepare
	 * @return string
	 **/
	public static function prepareStringForRegex($str) {
		return str_ireplace("/", "\/", preg_quote($str));
	}
	
	/**
	 * Render the template with data
	 *
	 * @param string $template: The template to use
	 * @param array $data: The data to use to render the template
	 * @return $template: The Html string
	 **/
	public static function render($template = "", $data = array()) {
		// Remove any comments in $template
		kp_renderer::renderComment($template);
		
		// Render the if/then statements
		kp_renderer::renderIf($template, $data);		
		
		// Render the strings within the $template
		kp_renderer::renderVariables($template, $data);
		
		return $template;
	}
	
	/**
	 * Render comments in $template delimited by {comment}blah blah blah{/comment}
	 *
	 * @param string &$template: The template to render
	 * @return null
	 **/
	public static function renderComment(&$template) {
		$template = preg_replace("/\{comment\}(.*)?\{\/comment\}/m", "", $template);
	}
	
	/**
	 * Render the if/then statements in $template recursively
	 *
	 * @param string &$template: The template to render
	 * @param array $data: The data to use to render the template
	 * @return string: The rendered template
	 **/
	public static function renderIf(&$template = "", $data = array()) {
		foreach ($data as $key => $val){
			if ($key != "") {
				// Create {if <key>} statement and escape the characters
				$ifStmt = kp_renderer::prepareStringForRegex("{if " . $key . "}");
				
				// Create {else <key>} statement and escape the characters
				$elseStmt = kp_renderer::prepareStringForRegex("{else " . $key . "}");

				// Create {/if <key>} statement and escape the characters
				$endIfStmt = kp_renderer::prepareStringForRegex("{/if " . $key . "}");
				
				// Find matches for the if statements
				$pattern = "/" . $ifStmt . "(?P<IF>.*)(|" . $elseStmt . "(?P<ELSE>.*))" . $endIfStmt . "/msU";
				preg_match($pattern, $template, $matches);

				$pattern = "/" . $ifStmt . "(.*)" . $endIfStmt . "/msU";
				$stmt = "";
				
				// Depending on $val and matches, replace portions of $template with a match
				if (count($matches) > 0 && isset($matches["IF"]) && ($val === true || $val == true || $val == 1)) {
					// Replace the if/else/end if block with the IF portion of the if/else block
					$stmt = $matches["IF"];

				} else if (count($matches) > 0 && isset($matches["ELSE"]) && (!$val || $val === false || $val == false || $val == 0)) {
					// Replace the if/else/end if block with the ELSE portion of the if/else block		
					$stmt = $matches["ELSE"];
				}
				
				$template = preg_replace($pattern, $stmt, $template);
			}
		}
	}
	
	/**
	 * Render $template variables within $data for example: $data[key] = value and {key} is replaced with value
	 *
	 * @param string &$template: Render the template with variables
	 * @param array $data: The array of string variables
	 * @return null
	 **/
	public static function renderVariables(&$template, $data = array()) {
		foreach ($data as $key => $val){
			if ($key != "") {
				$template = str_ireplace("{" . $key . "}", $val, $template);
			}
		}	
	}
	
	/**
	 * Return data for a template using a post
	 *
	 * @param WP_Post $post: The post to return data for
	 * @return array<string>: Array of post information
	 *
	 * @since 1.3.0
	 */
	public static function returnTemplateData($post = null) {
		$data = array();
		if ($post == null) {
			return $data;
		}
		
		$data["post_id"] = $post->ID;
		$data["author_user_nicename"] = strtoupper(get_the_author_meta("user_nicename", $post->post_author));
		$data["post_author"] = get_the_author_meta("user_nicename", $post->post_author);
		$data["post_slug"] = $post->post_name;
		$data["author_user_url"] = get_author_posts_url($post->post_author); //get_the_author_meta('user_url', $this->post->post_author);
		$data["post_date"] = strtotime($post->post_date);
		$data["post_date_nice"] = strtoupper(date("F j, Y", strtotime($post->post_date)));
		$data["post_url"] = get_permalink($post->ID);
		$data["post_excerpt"] = $post->post_excerpt;
		$data["post_title"] = $post->post_title;
		$data["has_thumbnail"] = (int)has_post_thumbnail($post->ID);
		$data["post_thumbnail"] = get_the_post_thumbnail($post->ID, 'thumbnail');
		
		return $data;
	}	
} // End kp_renderer class
?>