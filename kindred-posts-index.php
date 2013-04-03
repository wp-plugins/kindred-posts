<?php
/*
Plugin Name: Kindred Posts
Plugin URI: http://aispork.com/kindred-posts
Description: Automatically recommend your posts to your site visitors
Version: 1.0
Author: Ai Spork LLC
Author URI: http://aispork.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
	
	Copyright 2012  Ai Spork LLC (email : info@aispork.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	
*/

global $VisitTbl, $PostTbl, $wpdb, $NumClosestUsers, $MaxPastUpdateDate, $NumPostsRecommended;
global $ShowTitle, $ShowAuthor, $ShowDate, $ShowExcerpt, $ShowImages;
$VisitTbl = $wpdb->prefix . "kindred_posts_visits";
$PostTbl = $wpdb->prefix . "posts";
$NumClosestUsers = 5; 	// Select the x number of closest users to recommend a post
						// More is better but causes the page to load more slowly
$MaxPastUpdateDate = 365; // In days
$NumPostsRecommended = 5;	// The number of posts to recommend to the user

$MaintainerName = "Ai Spork"; 
$MaintainerAddress = "http://aispork.com";
$MaintainerEmail = "info@aispork.com";
$Version = "1.0";

$PluginURL = "http://aispork.com/kindred-posts";
$PremiumVersionURL = "http://aispork.com/kindred-posts-premium";
$HelpURL = "http://aispork.com/forums/";
$SupportForumURL = "http://aispork.com/forums/";

// List of keywords that will denote a bot, must be lower case
$BotArr = array(
	"bot",
	"spider"
);

// Default Display Options
$ShowTitle = true;
$ShowAuthor = true;
$ShowDate = true;
$ShowExcerpt = true;
$ShowFeaturedImage = true;
$orientation = "vertical";
$alignment = "center";
////
global $FirstPost;
$FirstPost = true; // Use to track only the first post saved

// Include other necessary functions
include_once( plugin_dir_path( __FILE__ ) . 'kindred-posts-display.php');
include_once( plugin_dir_path( __FILE__ ) . 'kindred-posts-visits.php');
include_once( plugin_dir_path( __FILE__ ) . 'kindred-posts-text.php');

// Check if we have the pro version
try{
	$HavePro = file_exists(plugin_dir_path( __FILE__ ) . 'kindred-posts-pro.php');
	if ($HavePro){
		include_once( plugin_dir_path( __FILE__ ) . 'kindred-posts-pro.php');
	}
} catch (Exception $e) {
	$HavePro = false;	// This will break things if it is set to true and the necessary functions don't exist
}

function kp_CheckPro(){
	global $HavePro;
	return $HavePro;
}

function kp_ConfigHead(){
?>
<link rel="shortcut icon" href="<?php echo plugins_url('', __FILE__ )."/"; ?>images/icon.ico" />
<?
}

function kp_CreateTable(){
	global $VisitTbl;

	$sql = "CREATE TABLE $VisitTbl (
	  VisitID bigint(20) NOT NULL AUTO_INCREMENT,
	  IP varchar(64),
	  Visits longtext,
	  UserAgent varchar(128),
	  DataSent int(1) NOT NULL DEFAULT '0',
	  CreateDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	  UpdateDate TIMESTAMP DEFAULT '0000-00-00 00:00:00',
	  UNIQUE KEY VisitID (VisitID)
	);";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);  
}

function kp_GetDistance($user1, $user2){
	$dist = 0;
	if (count($user1) <= 1 || count($user2) <= 1){
		return 100;
	}
	
	foreach ($user1 as $ID1 => $NumVisits1){
		// Find the $ID1 in the other user
		if (isset($user2[$ID1])){
			$NumVisits2 = $user2[$ID1];
		} else {
			$NumVisits2 = 0;
		}
		// Compare the distance
		$dist += pow($NumVisits1 - $NumVisits2, 2);
		unset($user1[$ID1]);
		unset($user2[$ID1]);
	}
	foreach ($user2 as $ID2 => $NumVisits2){
		// We went through $user1 so we know $NumVisits1 = 0
		$NumVisits1 = 0;
		// Compare the distance
		$dist += pow($NumVisits1 - $NumVisits2, 2);
	}
	if ($dist < 0){
		return 0;
	} 
	return sqrt($dist);
}

function kp_plugin_actions( $links, $file ) {
 	if( $file == 'kindred-posts/kindred-posts-index.php' && function_exists( "admin_url" ) ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=kp' ) . '">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link ); // before other links
	}
	return $links;
}

function kp_PrepareGoogleAnalytics($title = ""){
	if (kp_CheckPro()){
		return kp_AddGoogleAnalytics($title);
	}
	return "";
}

function kp_RunRecommender($row = NULL, $curr_post_id = -1, $WidgetOptions = null){
	// Get the unique posts and counts for each user
	global $VisitTbl, $wpdb, $NumClosestUsers, $MaxPastUpdateDate, $NumPostsRecommended;
	
	$RecommendedIDs = array();
	if (isset($WidgetOptions['numposts'])){
		$NumPostsRecommended = $WidgetOptions['numposts'];
	}

	$ip = "";
	if (isset($_SERVER['REMOTE_ADDR'])){
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	// If we didn't pass in the row, get it now
	if (!isset($row)){
		$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $VisitTbl WHERE IP=%s", $ip), ARRAY_A);
	}
	// Set up the closest number of users
	$ClosestUsers = array();
	// Get the rest of the users
	$other_users = $wpdb->get_results($wpdb->prepare("
		SELECT * 
		FROM $VisitTbl 
		WHERE IP != %s 
			AND (
			UpdateDate > ADDDATE(NOW(), INTERVAL %d DAY)
			OR
			CreateDate > ADDDATE(NOW(), INTERVAL %d DAY)
			)", array($ip, -1*$MaxPastUpdateDate, -1*$MaxPastUpdateDate)), ARRAY_A);
	
	foreach ($other_users as $user){
		// Get the distance between the user and the other users
		$dist = kp_GetDistance(unserialize($row["Visits"]), unserialize($user["Visits"]));

		if (count($ClosestUsers) < $NumClosestUsers){
			array_push($ClosestUsers, array($user["Visits"], $dist));
		} else {
			// Find the max dist and replace it
			$maxdist = -1;
			$maxind = 0;
			$i = 0;
			foreach($ClosestUsers as $temp){
				if ($temp[1] > $maxdist && $temp[1] > $dist){
					$maxdist = $temp[1];
					$maxind = $i;
				}
				$i = i + 1;
			}
			// Unset that array element and push our new close user
			if ($maxdist != -1){
				unset($ClosestUsers[$maxind]);
				$ClosestUsers[$maxind] = array($user["Visits"], $dist);
			}
		}
	}

	// What happens if we have an empty $ClosestUsers?
	if (count($ClosestUsers) > 0){
		// Get the top visit posts from the $ClosestUsers
		$VisitCounts = array(); // will contain {1=>5, 2=>3, 5=>150, post_id=>total visit number}
		foreach ($ClosestUsers as $key => $user){
			$user[0] = unserialize($user[0]);
			foreach ($user[0] as $id => $VisitCount){
				if (!isset($VisitCounts[$id])){
					$VisitCounts[$id] = 0;
				}
				$VisitCounts[$id] += $VisitCount;
			}
		}
		// Sort the final array by the counts
		arsort($VisitCounts);
		// Get the pages
		$IgnoreIDs = array();
		if (kp_CheckPro()){
			$IgnoreIDs = kp_IgnoreIDs();
		} else {
			$Pages = get_pages();
			foreach ($Pages as $Page){
				$IgnoreIDs[$Page->ID] = true;
			}
		}
		$i = 0;
		foreach($VisitCounts as $id => $VisitCount){
			if ($i < $NumPostsRecommended && $curr_post_id != $id && !isset($IgnoreIDs[$id])){
				array_push($RecommendedIDs, $id);
				$i = $i + 1;
			}
		}
	} else {
		// Need to do something
	}
	return $RecommendedIDs;
}

function kp_register_settings_page(){
	add_submenu_page( 'options-general.php', "Kindred Posts", "Kindred Posts", "edit_plugins", "kp", "kp_settings_page"); 
	
	//call register settings function
	add_action( 'admin_init', 'kp_register_settings' );
}

function kp_register_settings(){
	register_setting('kp_settings','FirstSave');
	register_setting('kp_settings','CollectStatistics');
	register_setting('kp_settings','AttemptToBlockBotVisits');
	register_setting('kp_settings','SendUsage');
	if (kp_CheckPro()){
		kp_PrepareProSettings();
	}
}

function kp_settings_page(){
	global $VisitTbl, $wpdb;
	global $NumPostsRecommended, $PluginURL, $PremiumVersionURL, $HelpURL, $SupportForumURL, $MaintainerAddress;
	$Deleted = false;
	if (isset($_POST['delete']) && $_POST['delete'] == "true"){
		$wpdb->query($wpdb->prepare("DELETE FROM $VisitTbl"));
		$Deleted = true;
	}
?>
	<div class="wrap">
	<div id="yoast-icon" style="background: url(<?php echo plugins_url('', __FILE__ )."/images/icon.png"; ?>) no-repeat;" class="icon32"><br></div>
	<h2><?php _e('Kindred Posts by Ai Spork'); ?></h2>	
	
	<p>
		<a target="_top" href="<?php echo $HelpURL; ?>"><?php _e('Support Forum'); ?></a>
		&nbsp; | &nbsp;
		<a target="_top" href="<?php echo $PluginURL; ?>"><?php _e('Plugin Homepage'); ?></a>
		&nbsp; | &nbsp;
		<a target="_top" href="<?php echo $MaintainerAddress; ?>"><?php _e('Ai Spork Homepage'); ?></a>
	<p>
	
	<?php if ($Deleted) { ?>
	<p class="Alert"><?php _e('Visit data has been removed'); ?></p>
	<?php } ?>
	
	<h3><strong>Plugin Status:
	<?php if (get_option('CollectStatistics', "true") == "true") { ?>
		<span style="color:green;"><?php _e('Collecting Data'); ?></span>
	<?php } else { ?>
		<span style="color:red;"><?php _e('Not Collecting Data'); ?></span>
	<?php } ?></strong></h3>
	
	<div class="postbox-container" style="width:65%;">
	<div id="settings" class="postbox">
	<h3 class="hndle" style="font-size:15px;padding:7px 10px 7px 10px; cursor:default;">
		<span>
			<?php _e('Settings'); ?>
		</span>
	</h3>
	<div class="inside">

	<form method="post" action="options.php">
	
	<?php kp_FieldSetText(); ?>
	
	<input type="hidden" name="FirstSave" value="false" id="FirstSave" />
	<?php settings_fields( 'kp_settings' ); ?> 
	<?php do_settings_sections('kp_settings'); ?> 
	<?php
	$options = get_option('CollectStatistics', "true");
	?>
	
	<p>
	<input type="radio" name="CollectStatistics" value="true" id="CollectStatistics1"<?php if ($options == "true") { ?> checked="checked"<?php } ?> /> <label for="CollectStatistics1"><?php _e('Collect User Visit data'); ?></label> <br />
	<input type="radio" name="CollectStatistics" value="false" id="CollectStatistics2"<?php if ($options != "true") { ?> checked="checked"<?php } ?> /> <label for="CollectStatistics2"><?php _e('<strong>STOP</strong> collecting User Visit data'); ?></label>
	</p>
	
	<?php $AttemptToBlockBotVisits = get_option('AttemptToBlockBotVisits', "true"); ?>
	<p>
		<input type="radio" name="AttemptToBlockBotVisits" value="true"<?php if ($AttemptToBlockBotVisits == "true") { ?> checked="checked"<?php } ?> id="AttemptToBlockBotVisits1" />
		<label for="AttemptToBlockBotVisits1"><?php _e('Ignore Bot Visits such as the Googlebot, Bingbot, etc. <em>(Recommended)'); ?></em></label> <br />
		<input type="radio" name="AttemptToBlockBotVisits" value="false"<?php if ($AttemptToBlockBotVisits == "false") { ?> checked="checked"<?php } ?> id="AttemptToBlockBotVisits2" />
		<label for="AttemptToBlockBotVisits2"><?php _e('Record Bot Visits'); ?></label> <br />
	</p>
	<?php
	if (kp_CheckPro()){
		kp_OutputProSettings();
	}
	?>
	<p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
	</form>
	</div>
	</div>
	
	<div id="settings" class="postbox">
	<h3 class="hndle" style="font-size:15px;padding:7px 10px 7px 10px; cursor:default;"><span><?php _e('User Visit Data'); ?></span></h3>
	<div class="inside">
	<?php
	if (kp_CheckPro()){
		kp_PerformUserChecks();
	}
	?>
	<p>
		<?php _e('Number of Unique Visitors: ');
		$visitor_count = $wpdb->get_var("SELECT COUNT(*) FROM $VisitTbl");
		if ($visitor_count){
			echo $visitor_count;
		} else {
			echo "0";
		}
		?> <br />
		<?php _e('Last Visit Date: ');
		$last_visit = $wpdb->get_var(
			"SELECT 
				d.Date AS Date
			 FROM
				(
					(SELECT CreateDate AS Date FROM $VisitTbl ORDER BY CreateDate DESC LIMIT 1) 
						UNION
					(SELECT UpdateDate AS Date FROM $VisitTbl ORDER BY UpdateDate DESC LIMIT 1)
				) d
			ORDER BY d.Date DESC LIMIT 1"
		);
		if ($last_visit) {
			echo date("F j, Y, g:i a", strtotime($last_visit)); 
		} else {
			echo "No visits yet";
		}
		?>
	</p>
	<?php 
	if (kp_CheckPro()){
		kp_OutputAdditionalProSettings();
	}
	?>
	<form method="post" target="_self" onsubmit="return confirm('<?php _e('Are you sure you want to delete your data? This cannot be undone'); ?>');">
		<input type="hidden" name="delete" value="true" />
		<input type="submit" class="button-primary" value="<?php _e('Delete ALL Visitor Data') ?>" />
	</form>

	</div>
	</div>
	</div>
	</div>
<?php if (!kp_CheckPro()){ ?>
	<div class="postbox-container" style="float:right;margin-right:15px;">
	<div id="settings" class="postbox">
	<h3 class="hndle" style="font-size:15px;padding:7px 10px 7px 10px; cursor:default;"><span><?php _e('Premium Version Information'); ?></span></h3>
	<div class="inside">
		<div style="width: 285px; margin: 0 auto 15px auto;">
	<?php 
		_e(kp_ProText2());
	?>		
	<form action="<?php echo $PremiumVersionURL; ?>">
		<input type="submit" class="button-primary" value="<?php _e('Get the Premium Version') ?>" />
	</form>
		</div>
	</div>
	</div>
	</div>
<?php } ?>
	
	<div class="postbox-container" style="float:right;margin-right:15px;">
	<div id="settings" class="postbox">
	<h3 class="hndle" style="font-size:15px;padding:7px 10px 7px 10px; cursor:default;"><span><?php _e('Found a Bug?'); ?></span></h3>
	<div class="inside">
		<div style="width: 285px; margin: 0 auto 15px auto;">
		<p><?php _e(kp_SupportText()); ?></p>
		</div>
	</div>
	</div>
	</div>
	
	<div class="postbox-container" style="float:right;margin-right:15px;">
	<div id="settings" class="postbox">
	<h3 class="hndle" style="font-size:15px;padding:7px 10px 7px 10px; cursor:default;"><span><?php _e('Ai Spork News'); ?></span></h3>
	<div class="inside">
		<div style="width: 285px; margin: 0 auto 15px auto;">
		<?php include_once('kindred-posts-rss.php'); ?>
		</div>
	</div>
	</div>
	</div>
<?php
}

class kp_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'kp_Widget', // Base ID
			'Kindred Posts', // Name
			array( 'description' => __( 'Display Kindred Posts for visitors', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
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
				echo kp_Display($val, $instance);
				if ($instance['orientation'] == "horizontal"){
					echo "</div>";
				}
			}

			echo $after_widget;
		}	
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['numposts'] = strip_tags( $new_instance['numposts'] );
		$instance['featureimage'] = ( isset( $new_instance['featureimage'] ) ? 1 : 0 );  
		$instance['posttitle'] = ( isset( $new_instance['posttitle'] ) ? 1 : 0 );  
		$instance['postauthor'] = ( isset( $new_instance['postauthor'] ) ? 1 : 0 );  
		$instance['postdate'] = ( isset( $new_instance['postdate'] ) ? 1 : 0 );  
		$instance['postteaser'] = ( isset( $new_instance['postteaser'] ) ? 1 : 0 ); 
		if (!isset($new_instance['orientation'])){
			$instance['orientation'] = "vertical";
		} else {
			$instance['orientation'] = strip_tags( $new_instance['orientation'] );
		}

		if (kp_CheckPro()){
			$instance = kp_SaveProOptions($instance, $new_instance);
		}

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		global $orientation;
		
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Recommended Posts', 'text_domain' );
		}
		if ( isset( $instance[ 'numposts' ] ) ) {
			$numposts = $instance[ 'numposts' ];
		}
		else {
			$numposts = __( '3', 'text_domain' );
		}
		if ( !isset( $instance['orientation'] ) ) {
			$orientation = __( 'vertical', 'text_domain' );
		} else {
			$orientation = $instance['orientation'];
		}

		// Title of Widget
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php // Number of Posts ?>
		<p>
		<label for="<?php echo $this->get_field_id( 'numposts' ); ?>"><?php _e( 'Number of Posts to Display' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'numposts' ); ?>" name="<?php echo $this->get_field_name( 'numposts' ); ?>" type="text" value="<?php echo esc_attr( $numposts ); ?>" />
		</p>
		<?php // Display Featured Image ?>
		<p>
		<input id="<?php echo $this->get_field_id( 'featureimage' ); ?>" name="<?php echo $this->get_field_name( 'featureimage' ); ?>" type="checkbox" class="checkbox" value="1" <?php checked( $instance['featureimage'], true ); ?> /> &nbsp; <label for="<?php echo $this->get_field_id( 'featureimage' ); ?>"><?php _e( 'Display Featured Image' ); ?></label>
		</p>
		<?php // Display Post Title ?>
		<p>
		<input id="<?php echo $this->get_field_id( 'posttitle' ); ?>" name="<?php echo $this->get_field_name( 'posttitle' ); ?>" type="checkbox" class="checkbox" value="1" <?php checked( $instance['posttitle'], true ); ?> /> &nbsp; <label for="<?php echo $this->get_field_id( 'posttitle' ); ?>"><?php _e( 'Display Post Title' ); ?></label>
		</p>
		<?php // Display Post Author ?>
		<p>
		<input id="<?php echo $this->get_field_id( 'postauthor' ); ?>" name="<?php echo $this->get_field_name( 'postauthor' ); ?>" type="checkbox" class="checkbox" value="1" <?php checked( $instance['postauthor'], true ); ?> /> &nbsp; <label for="<?php echo $this->get_field_id( 'postauthor' ); ?>"><?php _e( 'Display Post Author' ); ?></label>
		</p>
		<?php // Display Post Date ?>
		<p>
		<input id="<?php echo $this->get_field_id( 'postdate' ); ?>" name="<?php echo $this->get_field_name( 'postdate' ); ?>" type="checkbox" class="checkbox" value="1" <?php checked( $instance['postdate'], true ); ?> /> &nbsp; <label for="<?php echo $this->get_field_id( 'postdate' ); ?>"><?php _e( 'Display Post Date' ); ?></label>
		</p>
		<?php // Display Post Teaser ?>
		<p>
		<input id="<?php echo $this->get_field_id( 'postteaser' ); ?>" name="<?php echo $this->get_field_name( 'postteaser' ); ?>" type="checkbox" class="checkbox" value="1" <?php checked( $instance['postteaser'], true ); ?> /> &nbsp; <label for="<?php echo $this->get_field_id( 'postteaser' ); ?>"><?php _e( 'Display Post Teaser' ); ?></label> 
		</p>
		<?php // Display Orientation Radio Buttons ?>
		<? _e('List Posts'); ?> 
		<table>
			<tr>
				<td>
					<input type="radio" name="<?php echo $this->get_field_name( 'orientation' ); ?>" id="<?php echo $this->get_field_id( 'orientation' ); ?>" value="horizontal"<?php checked( $orientation, "horizontal" ); ?> /> <?php _e('Horizontally'); ?>
				</td>
				<td align="center">
					<img src="<?php echo plugins_url('', __FILE__ ).'/images/horz.png'; ?>" />
				</td>
			</tr>
			<tr>
				<td>
		<input type="radio" name="<?php echo $this->get_field_name( 'orientation' ); ?>" id="<?php echo $this->get_field_id( 'orientation' ); ?>" value="vertical"<?php checked( $orientation, "vertical" ); ?> /> <?php _e('Vertically'); ?> 
				</td>
				<td align="center">
					<img src="<?php echo plugins_url('', __FILE__ ).'/images/vert.png'; ?>" />
				</td>
			</tr>
		</table>
		<?php
		if (kp_CheckPro()){
			kp_OutputProOptions($this, $instance);
		}
	}

}
add_action('admin_menu', 'kp_register_settings_page');
register_activation_hook(__FILE__,'kp_CreateTable');
add_filter( 'plugin_action_links', 'kp_plugin_actions', 10, 2 );
add_action("the_post", "kp_SaveVisit");
add_action( 'widgets_init', create_function( '', 'register_widget( "kp_Widget" );' ) );
add_action('admin_head', 'kp_ConfigHead' );
?>