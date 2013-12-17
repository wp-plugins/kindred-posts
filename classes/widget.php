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
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args: Widget arguments.
	 * @param array $instance: Saved values from database.
	 */
	 /*
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		$RecommendedIDs = kp_RunRecommender(NULL, -1, $instance);
		if (count($RecommendedIDs) > 0){
		// Display the widget
			echo $before_widget;
			if ( !empty( $title ) && $title != ""){
				echo $before_title . $title . $after_title;
			}

			foreach($RecommendedIDs as $key => $val){
				if ($instance['orientation'] == "horizontal"){
					if ($instance['alignment'] == "left" || $instance['alignment'] == "right"){
					}
					echo "<div style=\"float:" . $instance['alignment'] . ";\">";
				}
				echo kp_display($val, $instance);
				if ($instance['orientation'] == "horizontal"){
					echo "</div>";
				}
			}

			echo $after_widget;
		}	
	}*/
	
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
		global $defaultNumPostsToRecommend, $defaultNumClosestUsersToUse, $kp_templates;
		
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
		
		// Check if the user has set the number of posts to recommend
		if (!isset($instance["numposts"]) || !is_int((int)$instance["numposts"])) {
			$numPostsToRecommend = $defaultNumPostsToRecommend;
		} else {
			$numPostsToRecommend = (int)$instance["numposts"];
		}
		
		// Check if recommendedPosts have been passed, if so, default to those
		// or else recommend some
		if (count($recommendedPosts) == 0) {
			// Check if $ip and $ua have been passed, if not, generate it
			if ($ip == "" && $ua == ""){
				$arr = kp_getUserData();
				extract($arr);
			}
			
			// Run the recommender
			$recommender = new kp_recommender($ip, $ua);
			$recommender->run($numPostsToRecommend, $defaultNumClosestUsersToUse);
			$recommendedPosts = $recommender->posts;
		}	
		
		$widgetHtml = "";
		if (count($recommendedPosts) > 0){
			$title = apply_filters("widget_title", $instance["title"]);
			
			// Start the data for the widget
			$data["kp_widget:before_widget"] = $args["before_widget"];
			$data["kp_widget:after_widget"] = $args["after_widget"];
			$data["kp_widget:title"] = $title;
			$data["kp_widget:title_exists"] = (!empty($title) && $title != "");
			$data["kp_widget:before_title"] = $args["before_title"];
			$data["kp_widget:after_title"] = $args["after_title"];
			
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
			
			$data["kp_widget:post_style"] = $post_style;
			$data["kp_widget:postimage_style"] = $postimage_style;
			$data["kp_widget:posttitle_style"] = $posttitle_style;
			$data["kp_widget:postauthor_style"] = $postauthor_style;
			$data["kp_widget:postdate_style"] = $postdate_style;
			$data["kp_widget:postteaser_style"] = $postteaser_style;
			$data["kp:By"] = __('By');
			$data["kp:on"] = __('on');
			$data["kp:On"] = __('On');
			
			if (!isset($instance["alignment"])){
				$alignment = $defaultAlignment;
			} else {
				$alignment = $instance["alignment"];
			}
			
			$data["kp_widget:alignment"] = $alignment;
			$data["kp_widget:orientation-horizontal"] = true;//($instance["orientation"] == "horizontal");
			$data["kp_widget:featureimage"] = $instance["featureimage"];
			$data["kp_widget:posttitle"] = $instance["posttitle"];
			$data["kp_widget:postauthor"] = $instance["postauthor"];
			$data["kp_widget:postdate"] = $instance["postdate"];
			$data["kp_widget:postteaser"] = $instance["postteaser"];
			
			$data["kp_recommender"] = kp_recommender::renderPosts($recommendedPosts, "", $data);
			
			if ($template == "" && isset($kp_templates["kp_widget"])){
				$template = $kp_templates["kp_widget"];
			}
			
			$widgetHtml = kp_renderer::render($template, $data);
		}
		
		if ($outputWidgetHtml) {
			echo $widgetHtml; // We echo the results and return them
		}
		
		return array("widgetHtml" => $widgetHtml, "recommender" => $recommender);
	}
} // End kp_widget class
?>