<?php
/**
 * Start kp_recommendedPost class
 **/
class kp_recommendedPost {
	public $post = null; // The WP_Post object
	
	public function __construct($post = null) {
		// Check if they passed in a post id, if so, get the post
		if (is_int($post)) {
			$this->post = get_post($post);
		} else {
			$this->post = $post;
		}
	}
	
	/**
	 * Return this object's data in an array to be used in the kp_renderer
	 * 
	 * @param Array $data: The data to include
	 * @return array
	 **/
	public function getPostTemplateData($data = array()) {
		$data["author_user_nicename"] = strtoupper(get_the_author_meta('user_nicename', $this->post->post_author));
		$data["author_user_url"] = get_author_posts_url($this->post->post_author); //get_the_author_meta('user_url', $this->post->post_author);
		$data["post_date"] = strtotime($this->post->post_date);
		$data["post_date_nice"] = strtoupper(date("F j, Y", strtotime($this->post->post_date)));
		$data["post_url"] = get_permalink($this->post->ID);
		$data["post_excerpt"] = $this->post->post_excerpt;
		$data["post_title"] = $this->post->post_title;
		$data["has_thumbnail"] = (int)has_post_thumbnail($this->post->ID);
		$data["post_thumbnail"] = get_the_post_thumbnail( $this->post->ID, array(150, 150) );
		
		$data["ga_posttitle"] = kp_prepareGoogleAnalytics($this->post->post_title);
		$data["ga_author"] = kp_prepareGoogleAnalytics($data["author_user_nicename"]);
	
		// Start the widget optons
		$data["show_post_thumbnail"] = (int)($data["kp_widget:featureimage"] == 1 && $data["has_thumbnail"]);
		$data["show_post_title"] = (int)($data["kp_widget:posttitle"] == 1);
		$data["show_post_author"] = (int)($data["kp_widget:postauthor"] == 1);
		$data["show_post_date"] = (int)($data["kp_widget:postdate"] == 1);
		$data["show_post_teaser"] = (int)($data["kp_widget:postteaser"] == 1);
		$data["has_content"] = $data["show_post_title"] || $data["show_post_thumbnail"] || $data["show_post_author"] || $data["show_post_date"] || $data["show_post_teaser"];
	
		return $data;
	}
	
	/**
	 * Render the post object
	 *
	 * @param string $template: The template to use when rendering the object
	 * @param array $data: Additional data we want to pass into the template
	 * @return string: The rendered Html
	 **/
	public function render($template = "", $data = array()) {
		global $kp_templates;
		if ($template == "" && isset($kp_templates["kp_recommendedPost"])){
			$template = $kp_templates["kp_recommendedPost"];
		}
		$data = $this->getPostTemplateData($data);
		/*
		echo "\n\n\n\n5\n\n\n\n";
		echo $template;
		
		print_r($data);
		echo kp_renderer::render($template, $data);
		*/
		return kp_renderer::render($template, $data);
	}
} // End kp_recommendedPost class
?>