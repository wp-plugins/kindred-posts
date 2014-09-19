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
		$data = array_merge(kp_renderer::returnTemplateData($this->post), $data);
		$data["kp:trackingcode"] = kp_prepareTrackingCode($data);
	
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
		return kp_renderer::render($template, $data);
	}
} // End kp_recommendedPost class
?>