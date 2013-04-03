<?php
function kp_Display($PostID, $WidgetOptions = null){
	global $ShowTitle, $ShowAuthor, $ShowDate, $ShowExcerpt, $ShowImages;
	// Get the Post details
	$PostDetails = get_post($PostID);
	$PostStr = "";
	$PostStrImg = "";
	$postimage_class = "display:inline;";
	$posttitle_class = "display:inline;";
	$postauthor_class = "display:inline;";
	$postdate_class = "display:inline;";
	$postteaser_class = "display:inline;";
	if (kp_CheckPro()){
		$arr = kp_GetProWidgetOptions($WidgetOptions);
		$postimage_class = $arr['postimage_style'];
		$posttitle_class = $arr['posttitle_style'];
		$postauthor_class = $arr['postauthor_style'];
		$postdate_class = $arr['postdate_style'];
		$postteaser_class = $arr['postteaser_style'];
	}
	
	if ($WidgetOptions['featureimage'] == 1 && has_post_thumbnail( $PostID )){
		$PostStrImg = "<span style=\"" . $postimage_class . "\"><a onclick=\"" . kp_PrepareGoogleAnalytics($PostDetails->post_title) . "\" href=\"" . get_permalink($PostID) . "\">" . get_the_post_thumbnail( $PostID, array(150, 150) ) . "</a></span><br />";
	}
	
	if ($WidgetOptions['posttitle'] == 1){
		$PostStr .= "<span style=\"" . $posttitle_class . "\"><a onclick=\"" . kp_PrepareGoogleAnalytics($PostDetails->post_title) . "\" href=\"" . get_permalink($PostID) . "\">" . $PostDetails->post_title . "</a></span><br />";
	}
	if ($WidgetOptions['postauthor'] == 1){
		$PostStr .= "<span style=\"" . $postauthor_class . "\">" . __('By') . " <span class=\"author vcard fn\"><a onclick=\"" . kp_PrepareGoogleAnalytics(strtoupper(get_the_author_meta('user_nicename', $PostDetails->post_author))) . "\" href=\"" . get_the_author_meta('user_url', $PostDetails->post_author) ."\">" . strtoupper(get_the_author_meta('user_nicename', $PostDetails->post_author)) . "</a></span>";
		if ($WidgetOptions['postdate'] == 1){
			$PostStr .= "<span style=\"" . $postdate_class . "\"> " . __('on') . " <span class=\"date time published\">" . strtoupper(date("F j, Y", strtotime($PostDetails->post_date))) . "</span></span>";
		}
		$PostStr .= "</span><br />";
	} else {
		if ($WidgetOptions['postdate'] == 1){
			$PostStr .= "<span style=\"" . $postdate_class . "\">" . __('On') . " <span class=\"date time published\">" . strtoupper(date("F j, Y", strtotime($PostDetails->post_date)))  . "</span></span><br />";
		}
	}

	if ($WidgetOptions['postteaser'] == 1){
		$PostStr .= "<span style=\"" . $postteaser_class . "\">" . $PostDetails->post_excerpt . "</span><br />";
	}
	
	if ($PostStr != ""){
		if (!isset($WidgetOptions['alignment'])){
			$alignment = "center";
		} else {
			$alignment = $WidgetOptions['alignment'];
		}
		$PostStr = "<div class=\"kp_post\" style=\"padding-top:10px; padding-bottom:10px;\" align=\"" . $alignment . "\">" . $PostStrImg . "<div align=\"left\">" . $PostStr . "</div></div>";
	}
	return $PostStr;
}
?>