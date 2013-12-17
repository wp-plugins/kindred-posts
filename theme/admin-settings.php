<?php
/**
 * Add the AiSpork logo to the head (this will only appear on the settings page of the AiSpork page)
 *
 * @return null
 **/
function kp_settingsHead(){
	if ($_SERVER["SCRIPT_NAME"] != null && stristr($_SERVER["SCRIPT_NAME"], "options-general.php") !== FALSE) {
		if ($_GET["page"] != null && $_GET["page"] == "kp") {
			echo "<link rel=\"shortcut icon\" href=\"" . plugins_url('', __FILE__ ) . "/../images/icon.ico\" />";
		}
	}
	
	return null;
}

/**
 * Output the settings page
 *
 * @return Html: The form
 **/
function kp_settingsPage(){
	global $visitTbl, $wpdb;
	global $defaultNumPostsToRecommend, $pluginUrl, $premiumVersionUrl, $helpUrl, $supportForumUrl, $maintainerUrl;
	$Deleted = false;
	if (isset($_POST['delete']) && $_POST['delete'] == "true"){
		$wpdb->query("DELETE FROM $visitTbl");
		$Deleted = true;
	}
?>
	<div class="wrap">
	<div id="yoast-icon" style="background: url(<?php echo plugins_url('', __FILE__ )."/../images/icon.png"; ?>) no-repeat;" class="icon32"><br></div>
	<h2><?php _e('Kindred Posts by Ai Spork'); ?></h2>	
	
	<p>
		<a target="_top" href="<?php echo $helpUrl; ?>"><?php _e('Support Forum'); ?></a>
		&nbsp; | &nbsp;
		<a target="_top" href="<?php echo $pluginUrl; ?>"><?php _e('Plugin Homepage'); ?></a>
		&nbsp; | &nbsp;
		<a target="_top" href="<?php echo $maintainerUrl; ?>"><?php _e('Ai Spork Homepage'); ?></a>
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
	
	<?php kp_stringFieldSet(); ?>
	
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
	if (kp_checkPro()){
		kp_outputProSettings();
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
	if (kp_checkPro()){
		kp_performUserChecks();
	}
	?>
	<p>
		<?php _e('Number of Unique Visitors: ');
		$visitor_count = $wpdb->get_var("SELECT COUNT(*) FROM $visitTbl");
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
					(SELECT CreateDate AS Date FROM $visitTbl ORDER BY CreateDate DESC LIMIT 1) 
						UNION
					(SELECT UpdateDate AS Date FROM $visitTbl ORDER BY UpdateDate DESC LIMIT 1)
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
	if (kp_checkPro()){
		kp_outputAdditionalProSettings();
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
<?php if (!kp_checkPro()){ ?>
	<div class="postbox-container" style="float:right;margin-right:15px;">
	<div id="settings" class="postbox">
	<h3 class="hndle" style="font-size:15px;padding:7px 10px 7px 10px; cursor:default;"><span><?php _e('Premium Version Information'); ?></span></h3>
	<div class="inside">
		<div style="width: 285px; margin: 0 auto 15px auto;">
	<?php 
		_e(kp_stringPro2());
	?>		
	<form action="<?php echo $premiumVersionUrl; ?>">
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
		<p><?php _e(kp_stringSupport()); ?></p>
		</div>
	</div>
	</div>
	</div>
	
	<div class="postbox-container" style="float:right;margin-right:15px;">
	<div id="settings" class="postbox">
	<h3 class="hndle" style="font-size:15px;padding:7px 10px 7px 10px; cursor:default;"><span><?php _e('Ai Spork News'); ?></span></h3>
	<div class="inside">
		<div style="width: 285px; margin: 0 auto 15px auto;">
		<?php include_once(plugin_dir_path( __FILE__ ) . "../kindred-posts-rss.php"); ?>
		</div>
	</div>
	</div>
	</div>
<?php
}

/**
 *
 * The following functions set up strings for the settings page
 *
 **/
function kp_stringFieldSet(){
	_e('<p>By enabling this plugin, a widget that recommends posts to users has been added in the <a href="' . admin_url( 'widgets.php') . '">Widget</a> menu that you can drag and drop into your theme.</p>');
}

function kp_stringPro(){
	_e('<p>This plugin recommends posts to your site visitors based on their past browsing history on your site. Your site uses artificial intelligence and statistics to learn how visitors are currently accessing your site and recommends posts that they may find interesting.</p>'); 
}

function kp_stringPro2(){
	return '
You are currently using the free version of Kindred Posts. A premium version of this plugin is available for $12.99. Some special features include:
<ol>
	<li>More options that control the look and feel of your Kindred Posts widget</li>
	<li>More control over your visit data</li>
	<li>Integration with <a href="http://www.google.com/analytics/">Google Analytics</a></li>
</ol>
		';
}

function kp_stringSupport(){
	global $supportForumUrl;
	return 'Have a question or need support? Check out our <a href="' . $supportForumUrl . '">support forum</a> to find answers to your questions.';
}
?>