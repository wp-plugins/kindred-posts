<?php
/**
 * Start kp_widget class
 **/
class kp_widget extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 **/
	public function __construct() {
		parent::__construct(
	 		'kp_widget', // Base ID of class
			'Kindred Posts', // Name of widget
			array('description' => __('Recommend Posts to visitors', 'text_domain'), ) // Args
		);
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance: Previously saved values for this widget from database.
	 * @return null
	 **/
	public function form($instance) {
		return kp_widgetSettings($this, $instance);
	}	

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $newInstance: Values just sent to be saved.
	 * @param array $oldInstance: Previously saved values from database.
	 *
	 * @return array: Updated safe values to be saved.
	 */
	public function update($newInstance, $oldInstance) {
		global $defaultOrientation; 
		
		// $Instance stores the values that we want to save in the database
		$instance = array();
		$instance['previouslysaved'] = (isset($newInstance['previouslysaved'] ) ? 1 : 0);
		$instance['title'] = strip_tags($newInstance['title']);
		$instance['numposts'] = strip_tags($newInstance['numposts']);
		$instance['featureimage'] = (isset($newInstance['featureimage'] ) ? 1 : 0);  
		$instance['posttitle'] = (isset($newInstance['posttitle'] ) ? 1 : 0);  
		$instance['postauthor'] = (isset($newInstance['postauthor'] ) ? 1 : 0);  
		$instance['postdate'] = (isset($newInstance['postdate'] ) ? 1 : 0);  
		$instance['postteaser'] = (isset($newInstance['postteaser'] ) ? 1 : 0);
		
		// Check if they set an orientation for the widget, if not, use $defaultOrientation
		if (!isset($newInstance["orientation"])){
			$instance["orientation"] = $defaultOrientation;
		} else {
			$instance["orientation"] = strip_tags($newInstance["orientation"]);
		}

		// Save pro widget options
		if (kp_checkPro()){
			$instance = kp_saveProOptions($instance, $newInstance);
		}

		return $instance;
	}
	
	/**
	 * Generates the widget html on the page
	 *
	 * @param Array $args: The widget arguments
	 * @param Array $instance: Settings related to this instance of the Widget
	 * @param boolean $outputWidgetHtml: Indicates if we should output the widget Html right away
	 * @param string $template: The template to use to render the widget
	 * @param Array $data: Data to use to render the widget
	 * @param Array $recommendedPosts: An array of posts to display (if none, run the recommender)
	 * @param string $ip: The IP Address of the user rendering the widget
	 * @param string $ua: The User Agent of the user rendering the widget
	 * @return string: The Html for the widget
	 **/
	public function widget($args, $instance, $outputWidgetHtml = true, $template = "", $data = array(), $recommendedPosts = array(), $ip = "", $ua = "") {
		global $defaultNumPostsToRecommend, $kp_templates;
		
		// Check if we are in test mode and if the user is an admin, if they aren't, don't show the widget
		if (get_option('AdminTestMode', "false") == "true" && (!current_user_can('edit_theme_options') || !current_user_can('edit_plugins'))) {
			return array("widgetHtml" => "", "recommender" => null);
		}
		
		// Check if some of the widget arguments have been passed, if not, fix them
		// so we don't run into any problems rendering the template
		if (!isset($args["before_widget"])) {
			$args["before_widget"] = "";
		}
		
		if (!isset($args["after_widget"])) {
			$args["after_widget"] = "";
		}
		
		if (!isset($args["before_title"])) {
			$args["before_title"] = "";
		}	
		
		if (!isset($args["after_title"])) {
			$args["after_title"] = "";
		}

		if (!isset($instance["title"])) {
			$instance["title"] = "";
		}		
		
		if (!isset($instance["featureimage"])) {
			$instance["featureimage"] = false;
		}

		if (!isset($instance["posttitle"])) {
			$instance["posttitle"] = false;
		}	

		if (!isset($instance["postauthor"])) {
			$instance["postauthor"] = false;
		}	

		if (!isset($instance["postdate"])) {
			$instance["postdate"] = false;
		}	

		if (!isset($instance["postteaser"])) {
			$instance["postteaser"] = false;
		}			
		
		if ($template == "" && isset($kp_templates["kp_widget"])){
			$template = $kp_templates["kp_widget"];
		}
		
		// Check if the user has set the number of posts to recommend
		if (!isset($instance["numposts"]) || !is_int((int)$instance["numposts"])) {
			$numPostsToRecommend = $defaultNumPostsToRecommend;
		} else {
			$numPostsToRecommend = (int)$instance["numposts"];
		}
		
		$widgetTitle = apply_filters("widget_title", $instance["title"]);
		
		// Start data for this specific widget
		$post_style = "padding-top:10px;padding-bottom:10px;";
		$postimage_style = "display:inline;";
		$posttitle_style = "display:inline;";
		$postauthor_style = "display:inline;";
		$postdate_style = "display:inline;";
		$postteaser_style = "display:inline;";	
		
		if (kp_checkPro()){
			$arr = kp_getProWidgetOptions($instance);
			extract($arr);
		}
		
		if (!isset($instance["alignment"])){
			$alignment = $defaultAlignment;
		} else {
			$alignment = $instance["alignment"];
		}
		
		return kp_renderWidget($numPostsToRecommend, $recommendedPosts, $template, $ip, $ua, $outputWidgetHtml, $widgetTitle, $post_style, $postimage_style, $posttitle_style, $postauthor_style, $postdate_style, $postteaser_style, $args["before_widget"], $args["after_widget"], $args["before_title"], $args["after_title"], $alignment, $instance["featureimage"], $instance["posttitle"], $instance["postauthor"], $instance["postdate"], $instance["postteaser"]);
	}
} // End kp_widget class
?>