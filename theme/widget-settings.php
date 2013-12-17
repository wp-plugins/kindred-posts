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
	global $defaultOrientation;

	$fieldIDTitle = $widgetObj->get_field_id("title");
	$fieldNameTitle = $widgetObj->get_field_name("title");
	$fieldIDNumPosts = $widgetObj->get_field_id("numposts");
	$fieldNameNumPosts = $widgetObj->get_field_name("numposts");
	
	if (isset($instance['title'])) {
		$title = $instance['title'];
	} else {
		$title = __('Recommended Posts', 'text_domain');
	}
	$title = esc_attr($title);
	
	if (isset($instance['numposts'])) {
		$numPosts = $instance['numposts'];
	} else {
		$numPosts = __('3', 'text_domain');
	}
	$numPosts = esc_attr($numPosts);
	 
	if (!isset( $instance['orientation'])) {
		$orientation = __($defaultOrientation, 'text_domain');
	} else {
		$orientation = $instance['orientation'];
	}

	// Title of Widget
	?>
	<p>
	<label for="<?php echo $fieldIDTitle; ?>"><?php _e( 'Widget Title' ); ?></label> 
	<input class="widefat" id="<?php echo $fieldIDTitle; ?>" name="<?php echo $fieldNameTitle; ?>" type="text" value="<?php echo $title; ?>" />
	</p>
	
	<?php // Number of Posts ?>
	<p>
	<label for="<?php echo $fieldIDNumPosts; ?>"><?php _e( 'Number of Posts to Display' ); ?></label> 
	<input class="widefat" id="<?php echo $fieldIDNumPosts; ?>" name="<?php echo $fieldNameNumPosts; ?>" type="text" value="<?php echo $numPosts; ?>" />
	</p>
	
	<?php // Display Featured Image ?>
	<p>
	<input id="<?php echo $widgetObj->get_field_id( 'featureimage' ); ?>" name="<?php echo $widgetObj->get_field_name( 'featureimage' ); ?>" type="checkbox" class="checkbox" value="1" <?php checked( $instance['featureimage'], true ); ?> /> &nbsp; <label for="<?php echo $widgetObj->get_field_id( 'featureimage' ); ?>"><?php _e( 'Display Featured Image' ); ?></label>
	</p>
	
	<?php // Display Post Title ?>
	<p>
	<input id="<?php echo $widgetObj->get_field_id( 'posttitle' ); ?>" name="<?php echo $widgetObj->get_field_name( 'posttitle' ); ?>" type="checkbox" class="checkbox" value="1" <?php checked( $instance['posttitle'], true ); ?> /> &nbsp; <label for="<?php echo $widgetObj->get_field_id( 'posttitle' ); ?>"><?php _e( 'Display Post Title' ); ?></label>
	</p>
	
	<?php // Display Post Author ?>
	<p>
	<input id="<?php echo $widgetObj->get_field_id( 'postauthor' ); ?>" name="<?php echo $widgetObj->get_field_name( 'postauthor' ); ?>" type="checkbox" class="checkbox" value="1" <?php checked( $instance['postauthor'], true ); ?> /> &nbsp; <label for="<?php echo $widgetObj->get_field_id( 'postauthor' ); ?>"><?php _e( 'Display Post Author' ); ?></label>
	</p>
	
	<?php // Display Post Date ?>
	<p>
	<input id="<?php echo $widgetObj->get_field_id( 'postdate' ); ?>" name="<?php echo $widgetObj->get_field_name( 'postdate' ); ?>" type="checkbox" class="checkbox" value="1" <?php checked( $instance['postdate'], true ); ?> /> &nbsp; <label for="<?php echo $widgetObj->get_field_id( 'postdate' ); ?>"><?php _e( 'Display Post Date' ); ?></label>
	</p>
	
	<?php // Display Post Teaser ?>
	<p>
	<input id="<?php echo $widgetObj->get_field_id( 'postteaser' ); ?>" name="<?php echo $widgetObj->get_field_name( 'postteaser' ); ?>" type="checkbox" class="checkbox" value="1" <?php checked( $instance['postteaser'], true ); ?> /> &nbsp; <label for="<?php echo $widgetObj->get_field_id( 'postteaser' ); ?>"><?php _e( 'Display Post Teaser' ); ?></label> 
	</p>
	
	<?php // Display Orientation Radio Buttons ?>
	<? _e('List Posts'); ?> 
	<table>
		<tr>
			<td>
				<input type="radio" name="<?php echo $widgetObj->get_field_name( 'orientation' ); ?>" id="<?php echo $widgetObj->get_field_id( 'orientation' ); ?>" value="horizontal"<?php checked( $orientation, "horizontal" ); ?> /> <?php _e('Horizontally'); ?>
			</td>
			<td align="center">
				<img src="<?php echo plugins_url('', __FILE__ ).'/../images/horz.png'; ?>" />
			</td>
		</tr>
		<tr>
			<td>
	<input type="radio" name="<?php echo $widgetObj->get_field_name( 'orientation' ); ?>" id="<?php echo $widgetObj->get_field_id( 'orientation' ); ?>" value="vertical"<?php checked( $orientation, "vertical" ); ?> /> <?php _e('Vertically'); ?> 
			</td>
			<td align="center">
				<img src="<?php echo plugins_url('', __FILE__ ).'/../images/vert.png'; ?>" />
			</td>
		</tr>
	</table>
	<?php
	if (kp_checkPro()){
		kp_outputProOptions($widgetObj, $instance);
	}
	
	return null;
}
?>