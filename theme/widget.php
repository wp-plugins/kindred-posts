<?php
/**
 * Display a Post using the WidgetOptions specified
 *
 * @param int $postID: The Post to display
 * @param Array $widgetOptions: The options to display
 * @return string: The Html for the post
 **/
function kp_display($postID, $widgetOptions = null){
	return kp_generateHtml($postID, $widgetOptions);
}

// replace $widgetOptions with various show_* variables
function kp_generateHtml($postID, $widgetOptions = null){
	// Get the Post details
	$postDetails = get_post($postID);
	$PostStr = "";
	$PostStrImg = "";
	$postimage_style = "display:inline;";
	$posttitle_style = "display:inline;";
	$postauthor_style = "display:inline;";
	$postdate_style = "display:inline;";
	$postteaser_style = "display:inline;";
	if (kp_checkPro()){
		$arr = kp_getProWidgetOptions($widgetOptions);
		
		$postimage_style = $arr['postimage_style'];
		$posttitle_style = $arr['posttitle_style'];
		$postauthor_style = $arr['postauthor_style'];
		$postdate_style = $arr['postdate_style'];
		$postteaser_style = $arr['postteaser_style'];
	}
	$postdate = strtoupper(date("F j, Y", strtotime($postDetails->post_date)));
	$posturl = get_permalink($postID);
	$authorurl = get_the_author_meta('user_url', $postDetails->post_author);
	$authorname = strtoupper(get_the_author_meta('user_nicename', $postDetails->post_author));
	
	if ($widgetOptions['featureimage'] == 1 && has_post_thumbnail($postID)){
		$GA_PostTitle = kp_prepareGoogleAnalytics($postDetails->post_title);
	
		$PostStrImg = "<span style=\"" . $postimage_style . "\"><a onclick=\"" . $GA_PostTitle . "\" href=\"" . $posturl . "\">" . get_the_post_thumbnail( $postID, array(150, 150) ) . "</a></span><br />";
	}
	
	if ($widgetOptions['posttitle'] == 1){
		$GA_PostTitle = kp_prepareGoogleAnalytics($postDetails->post_title);
		
		$PostStr .= "<span style=\"" . $posttitle_style . "\"><a onclick=\"" . $GA_PostTitle . "\" href=\"" . $posturl . "\">" . $postDetails->post_title . "</a></span><br />";
	}
	
	if ($widgetOptions['postauthor'] == 1){
		$GA_Author = kp_prepareGoogleAnalytics($authorname);
		
		$PostStr .= "<span style=\"" . $postauthor_style . "\">" . __('By') . " <span class=\"author vcard fn\"><a onclick=\"" . $GA_Author . "\" href=\"" . $authorurl ."\">" . $authorname . "</a></span>";
		if ($widgetOptions['postdate'] == 1){
			$PostStr .= "<span style=\"" . $postdate_style . "\"> " . __('on') . " <span class=\"date time published\">" . $postdate . "</span></span>";
		}
		$PostStr .= "</span><br />";
		
	} else {
		if ($widgetOptions['postdate'] == 1){
			$PostStr .= "<span style=\"" . $postdate_style . "\">" . __('On') . " <span class=\"date time published\">" . $postdate . "</span></span><br />";
		}
	}

	if ($widgetOptions['postteaser'] == 1){
		$PostStr .= "<span style=\"" . $postteaser_style . "\">" . $postDetails->post_excerpt . "</span><br />";
	}
	
	if ($PostStr != ""){
		if (!isset($widgetOptions['alignment'])){
			$alignment = $defaultAlignment;
		} else {
			$alignment = $widgetOptions['alignment'];
		}
		$PostStr = "<div class=\"kp_post\" style=\"padding-top:10px; padding-bottom:10px;\" align=\"" . $alignment . "\">" . $PostStrImg . "<div align=\"left\">" . $PostStr . "</div></div>";
	}
	return $PostStr;
}

/**
 * Generate the template for a post using the post data and widget options
 * 
 * @param array $postData: The post data
 * @param object $widgetOptions: The options for the widget that is generating the template
 * @return string
 **/
function kp_generateWidgetTemplate($postData = array(), $widgetOptions = null){
	global $defaultAlignment;
	
	extract($postData); // We need this for $has_thumbnail
	
	$post_style = "padding-top:10px;padding-bottom:10px;";
	$postimage_style = "display:inline;";
	$posttitle_style = "display:inline;";
	$postauthor_style = "display:inline;";
	$postdate_style = "display:inline;";
	$postteaser_style = "display:inline;";	
	
	if (kp_checkPro()){
		$arr = kp_getProWidgetOptions($widgetOptions);
		extract($arr);
	}
	
	$templateData = array();
	$templateData["post_style"] = $post_style;
	$templateData["postimage_style"] = $postimage_style;
	$templateData["posttitle_style"] = $posttitle_style;
	$templateData["postauthor_style"] = $postauthor_style;
	$templateData["postdate_style"] = $postdate_style;
	$templateData["postteaser_style"] = $postteaser_style;
	$templateData["By"] = __('By');
	$templateData["on"] = __('on');
	$templateData["On"] = __('On');
	
	$postStr = "";
	$postStrImg = "";	
	
	if ($widgetOptions['featureimage'] == 1 && $has_thumbnail){
		$postStrImg = "<span style=\"{postimage_style}\"><a onclick=\"{ga_posttitle}\" href=\"{posturl}\">{post_thumbnail}</a></span><br />";
	}
	
	if ($widgetOptions['posttitle'] == 1){
		$postStr .= "<span style=\"{posttitle_style}\"><a onclick=\"{ga_posttitle}\" href=\"{posturl}\">{post_title}</a></span><br />";
	}
	
	if ($widgetOptions['postauthor'] == 1){
		$postStr .= "<span style=\"{postauthor_style}\">{By} <span class=\"author vcard fn\"><a onclick=\"{ga_author}\" href=\"{authorurl}\">{authorname}</a></span>";
		if ($widgetOptions['postdate'] == 1){
			$postStr .= "<span style=\"{postdate_style}\"> {on} <span class=\"date time published\">{postdate}</span></span>";
		}
		$postStr .= "</span><br />";
		
	} else {
		if ($widgetOptions['postdate'] == 1){
			$postStr .= "<span style=\"{postdate_style}\">{On} <span class=\"date time published\">{postdate}</span></span><br />";
		}
	}

	if ($widgetOptions['postteaser'] == 1){
		$postStr .= "<span style=\"{postteaser_style}\">{post_excerpt}</span><br />";
	}
	
	if ($postStr != ""){
		if (!isset($widgetOptions['alignment'])){
			$alignment = $defaultAlignment;
		} else {
			$alignment = $widgetOptions['alignment'];
		}
		
		$postStr = "<div class=\"kp_post\" style=\"{post_style}\" align=\"{alignment}\">" . $postStrImg . "<div align=\"left\">" . $postStr . "</div></div>";
	}
	
	return kp_renderer::render($postStr, $templateData);
}
?>