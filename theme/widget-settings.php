<?php
/**
 * This file contains functions related to the Widget Settings page in Admin
 **/
 
/**
 * Back-end widget form.
 *
 * @see WP_Widget::form()
 *
 * @param array $instance Previously saved values for this widget from database.
 * @return null
 **/ 
function kp_widgetSettings($widgetObj, $instance) {
	global $kp_defaultPostTitle, $kp_defaultPostAuthor, $kp_defaultPostDate, $kp_defaultPostTeaser, $kp_defaultFeaturedImage, $kp_defaultOrientation, $defaultNumPostsToRecommend, $kp_defaultAlignment;
	
	$names = array();
	$ids = array();
	
	$fields = array("previouslysaved", "title", "numposts", "posttitle", "postauthor", "postdate", "postteaser", "featureimage", "orientation");
	
	foreach ($fields as $field) {
		$names[$field] = $widgetObj->get_field_name($field);
		$ids[$field] = $widgetObj->get_field_id($field);
	}
	
	// Check if the widget has been saved previously
	// if it hasn't, then use the default values
	$previouslysaved = isset($instance["previouslysaved"]) && (string)$instance["previouslysaved"] == "1";
	
	if (isset($instance['title'])) {
		$title = $instance['title'];
	} else {
		$title = __('Recommended Posts', 'text_domain');
	}
	$title = esc_attr($title);
	
	if (isset($instance['numposts'])) {
		$numPosts = $instance['numposts'];
	} else {
		$numPosts = __((string)$defaultNumPostsToRecommend, "text_domain");
	}
	$numPosts = esc_attr($numPosts);
	
	$postTitleChecked = $instance["posttitle"] || (!$previouslysaved && $kp_defaultPostTitle);
	$postAuthorChecked = $instance["postauthor"] || (!$previouslysaved && $kp_defaultPostAuthor);
	$postDateChecked = $instance["postdate"] || (!$previouslysaved && $kp_defaultPostDate);
	$postTeaserChecked = $instance["postteaser"] || (!$previouslysaved && $kp_defaultPostTeaser);
	$featureImageChecked = $instance["featureimage"] || (!$previouslysaved && $kp_defaultFeaturedImage);
	 
	if (!isset($instance["orientation"])) {
		$orientation = __($kp_defaultOrientation, "text_domain");
	} else {
		$orientation = $instance["orientation"];
	}
	
	$orientationHorizontalChecked = ($orientation == "horizontal");
	$orientationVerticalChecked = ($orientation == "vertical");
	
	// Output the options with CSS
	if (isset($instance["alignment"])) {
		$alignment = $instance["alignment"];
	} else {
		$alignment = $kp_defaultAlignment;
	}
	
	// Get the different types of posts that we should recommend
	$postTypes = kp_getRecommendablePostTypes();
	$checkAllPostTypes = false;
	// If they have previously saved, determine if they have at least 1 post type checked or else default to checking them all
	if ($previouslysaved) {
		$checkAllPostTypes = true;
		$i = 0;
		while ($i < count($postTypes)) {
			if (isset($instance["posttypes-" . $postTypes[$i]]) && (string)$instance["posttypes-" . $postTypes[$i]] == "1") {
				$checkAllPostTypes = false;
			}
			$i = $i + 1;
		}
	}
	?>
	<input type="hidden" id="<?php echo $ids["previouslysaved"]; ?>" name="<?php echo $names["previouslysaved"]; ?>" value="1" />
	
	<?php // Title of Widget ?>
	<p>
		<label for="<?php echo $ids["title"]; ?>"><?php _e("Widget Title"); ?></label> 
		<input class="widefat" id="<?php echo $ids["title"]; ?>" name="<?php echo $names["title"]; ?>" type="text" value="<?php echo $title; ?>" />
	</p>
	
	<?php // Number of Posts ?>
	<p>
		<label for="<?php echo $ids["numposts"]; ?>"><?php _e("Number of Recommendations to display"); ?></label> 
		<input class="widefat" id="<?php echo $ids["numposts"]; ?>" name="<?php echo $names["numposts"]; ?>" type="text" value="<?php echo $numPosts; ?>" />
	</p>
	
	<?php // Display the different post types that the user can select ?>
	<div style="margin-top:20px;">
	<?php _e('Recommend the following types of posts'); ?>
	<table>
	<?php
	$i = 0;
	while ($i < count($postTypes)) {
		$postType = $postTypes[$i];

		$isChecked = $checkAllPostTypes || (isset($instance["posttypes-" . $postType]) && (string)$instance["posttypes-" . $postType] == "1");
		$isChecked = $isChecked || (!$previouslysaved && strtolower($postType) == "post");
		?>
		<tr>
		<td>
			<input type="checkbox" name="<?php echo $widgetObj->get_field_name("posttypes-$postType"); ?>" value="1" id="<?php echo $widgetObj->get_field_id("posttypes-$postType"); ?>"<?php checked($isChecked); ?> />
			<label for="<?php echo $widgetObj->get_field_id("posttypes-$postType"); ?>"><?php echo ucwords($postType); ?> &nbsp; </label>
			&nbsp;
		</td>
		<?php
		$i = $i + 1;
		
		if ($i < count($postTypes)) {
			$postType = $postTypes[$i];
			
			$isChecked = $checkAllPostTypes || (isset($instance["posttypes-" . $postType]) && (string)$instance["posttypes-" . $postType] == "1");
			$isChecked = $isChecked || (!$previouslysaved && strtolower($postType) == "post");
			?>
			<td>
				<input type="checkbox" name="<?php echo $widgetObj->get_field_name("posttypes-$postType"); ?>" value="1" id="<?php echo $widgetObj->get_field_id("posttypes-$postType"); ?>"<?php checked($isChecked); ?> />
				<label for="<?php echo $widgetObj->get_field_id("posttypes-$postType"); ?>"><?php echo ucwords($postType); ?> &nbsp; </label>
				&nbsp;
			</td>
		<?php
		}
		
		$i = $i + 1;
		?>
		</tr>
	<?php
	}
	?>
	</table>
	</div>
	
	<?php // Display Orientation Radio Buttons ?>
	<div style="margin-top:20px;">
	<?php _e('List Recommendations'); ?> 
	<table>
		<tr>
			<td>
				<input type="radio" name="<?php echo $names["orientation"]; ?>" id="<?php echo $ids["orientation"]; ?>-horizontal" value="horizontal" <?php checked($orientationHorizontalChecked); ?> /> 
				<label for="<?php echo $ids["orientation"]; ?>-horizontal"><?php _e('Horizontally'); ?></label> &nbsp;
			</td>
			<td align="center">
				<img src="<?php echo plugins_url('', __FILE__ ).'/../images/horz.png'; ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<input type="radio" name="<?php echo $names["orientation"]; ?>" id="<?php echo $ids["orientation"]; ?>-vertical" value="vertical" <?php checked($orientationVerticalChecked); ?> /> 
				<label for="<?php echo $ids["orientation"]; ?>-vertical"><?php _e('Vertically'); ?></label> &nbsp;
			</td>
			<td align="center">
				<img src="<?php echo plugins_url('', __FILE__ ).'/../images/vert.png'; ?>" />
			</td>
		</tr>
	</table>
	</div>
	
	<?php // Display options for each recommendation ?>
	<div style="margin-top:20px;">
	<?php _e("For each Recommendation, display"); ?>
	<table>
	<tr>
	<?php // Display Post Title ?>
	<td>
		<input id="<?php echo $ids["posttitle"]; ?>" name="<?php echo $names["posttitle"]; ?>" type="checkbox" class="checkbox" value="1" <?php checked($postTitleChecked); ?> /> &nbsp; 
		<label for="<?php echo $ids["posttitle"]; ?>"><?php _e("Title"); ?></label>
		&nbsp;
	</td>
	
	
	<?php // Display Post Author ?>
	<td>
		<input id="<?php echo $ids["postauthor"]; ?>" name="<?php echo $names["postauthor"]; ?>" type="checkbox" class="checkbox" value="1" <?php checked($postAuthorChecked); ?> /> &nbsp; 
		<label for="<?php echo $ids["postauthor"]; ?>"><?php _e("Author"); ?></label>
		&nbsp;
	</td>
	
	</tr>
	<tr>
	
	<?php // Display Post Date ?>
	<td>
		<input id="<?php echo $ids["postdate"]; ?>" name="<?php echo $names["postdate"]; ?>" type="checkbox" class="checkbox" value="1" <?php checked($postDateChecked); ?> /> &nbsp; 
		<label for="<?php echo $ids["postdate"]; ?>"><?php _e("Publish Date"); ?></label>
		&nbsp;
	</td>
	
	<?php // Display Post Excerpt ?>
	<td>
		<input id="<?php echo $ids["postteaser"]; ?>" name="<?php echo $names["postteaser"]; ?>" type="checkbox" class="checkbox" value="1" <?php checked($postTeaserChecked); ?> /> &nbsp; 
		<label for="<?php echo $ids["postteaser"]; ?>"><?php _e("Excerpt"); ?></label>
		&nbsp;
	</td>

	</tr>
	<tr>
	
	<?php // Display Featured Image ?>
	<td>
		<input id="<?php echo $ids["featureimage"]; ?>" name="<?php echo $names["featureimage"]; ?>" type="checkbox" class="checkbox" value="1" <?php checked($featureImageChecked); ?> /> &nbsp; 
		<label for="<?php echo $ids["featureimage"]; ?>"><?php _e("Featured Image"); ?></label>
		&nbsp;
	</td>
	<td>
	</td>
	
	</tr>
	</table>
	</div>
	
	<?php // Display Alignment of Post ?>
	<p>
		<?php _e('Within the widget, posts are'); ?> <br />
		<input type="radio" name="<?php echo $widgetObj->get_field_name( 'alignment' ); ?>" id="<?php echo $widgetObj->get_field_id( 'alignment' ); ?>-left" value="left"<?php checked( $alignment, "left" ); ?> />
		<label for="<?php echo $widgetObj->get_field_id( 'alignment' ); ?>-left"><?php _e('Aligned to the Left'); ?></label><br />
		
		<input type="radio" name="<?php echo $widgetObj->get_field_name( 'alignment' ); ?>" id="<?php echo $widgetObj->get_field_id( 'alignment' ); ?>-center" value="center"<?php checked( $alignment, "center" ); ?> />
		<label for="<?php echo $widgetObj->get_field_id( 'alignment' ); ?>-center"><?php _e('Centered'); ?></label><br />
		
		<input type="radio" name="<?php echo $widgetObj->get_field_name( 'alignment' ); ?>" id="<?php echo $widgetObj->get_field_id( 'alignment' ); ?>-right" value="right"<?php checked( $alignment, "right" ); ?> /> 
		<label for="<?php echo $widgetObj->get_field_id( 'alignment' ); ?>-right"><?php _e('Aligned to the Right'); ?></label><br />
	</p>
	
	<?php
	return null;
}
?>