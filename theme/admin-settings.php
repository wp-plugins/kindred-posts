<?php
/**
 * Add the AiSpork logo to the head (this will only appear on the settings page of the AiSpork page)
 *
 * @return null
 **/
function kp_settingsHead(){
	if ($_SERVER["SCRIPT_NAME"] != null && stristr($_SERVER["SCRIPT_NAME"], "options-general.php") !== FALSE) {
		if ($_GET["page"] != null && $_GET["page"] == "kindred-posts") {
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
	global $visitTbl, $wpdb, $kp_currentVersion;
	global $defaultNumPostsToRecommend, $pluginUrl, $premiumVersionUrl, $helpUrl, $supportForumUrl, $maintainerUrl;

	$proUpdateAvailable = false;
	$headerText = __('Kindred Posts by Ai Spork');
	if (kp_checkPro()) {
		$headerText = __('Kindred Posts Premium by Ai Spork');
		
		// Set up how we update the Premium version of the plugin
		$proUpdateAvailable = kp_proUpdateAvailable();
		$needBasicVersionUpdate = $kp_currentVersion->needBasicUpdate;
		kp_proCheckUpdate('settings_page');
	}
	
	$CollectStatistics = get_option('CollectStatistics', "true");
	$AttemptToBlockBotVisits = get_option('AttemptToBlockBotVisits', "true");
	$AdminTestMode = get_option('AdminTestMode', "false");	
	
	// Check if the admin wants to switch to test mode
	if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == "true") {
		if ($AdminTestMode == "true") {
			// They switched to test mode
			// Check if we have test mode data already, if so, do not insert more
			if (!kp_haveTestModeData()) {
				kp_insertTestModeData();
			} 
		} else {
			// They removed test mode
			kp_deleteTestModeData();
		}
	}
	
	$alert = "";
	if (isset($_SESSION["kp_alert"])) {
		$alert = $_SESSION["kp_alert"];
		unset($_SESSION["kp_alert"]);
	}
	
	// Check if the admin wanted to delete the visit data
	$Deleted = false;
	if (isset($_POST['delete']) && $_POST['delete'] == "true"){
		kp_resetVisitData();
		$alert = __("Visit data has been removed");
	}
?>
	<div class="wrap">

	<h2><?php echo $headerText; ?></h2>	
	
	<?php 
	if ($alert != "") {
	?>
	<div id="setting-error-settings_updated" class="updated settings-error">
		<p>
			<strong><?php echo $alert; ?></strong>
		</p>
	</div>	
	<?php 
	} else if ($needBasicVersionUpdate) {
	?>
	<div id="setting-error-settings_updated" class="updated settings-error">
		<p>
			<strong><?php _e('You are running a version of Kindred Posts that may be out-of-date. <a href="plugins.php">Click here to visit the plugin page and update now</a>'); ?></strong>
		</p>
	</div>		
	<?php
	} else if ($proUpdateAvailable) {
	?>
	<div id="setting-error-settings_updated" class="updated settings-error">
		<p>
			<strong><?php kp_proOutputVersionInformation(); ?></strong>
		</p>
	</div>		
	<?php
	}
	?>	
	
	<p>
		<a target="_top" href="<?php echo $helpUrl; ?>"><?php _e('Support Forum'); ?></a>
		&nbsp; | &nbsp;
		<a target="_top" href="<?php echo $pluginUrl; ?>"><?php _e('Plugin Homepage'); ?></a>
		&nbsp; | &nbsp;
		<a target="_top" href="<?php echo $maintainerUrl; ?>"><?php _e('Ai Spork Homepage'); ?></a>
	<p>
	
	<h3><strong><?php _e('Plugin Status:'); ?>
	<?php
	if (get_option('CollectStatistics', "true") == "true") { 
	?>
		<span style="color:green;">
			<?php 
			_e('Collecting Data'); 
			if (get_option('AdminTestMode', "false") == "true") { 
				_e(': In Test Mode');
			}
			?>
		</span>
	<?php
	} else { 
	?>
		<span style="color:red;">
			<?php 
			_e('Not Collecting Data'); 
			if (get_option('AdminTestMode', "false") == "true") { 
				_e(': In Test Mode');
			}
			?>
		</span>
	<?php 
	} 
	?></strong></h3>
	
	<?php // Start left-side container ?>
	<div class="postbox-container" style="width:65%; min-width:300px;">
	<?php 
	// Start Settings box
	kp_settingsBox();
	// End Settings box 

	// Start User Visit Data box
	kp_userVisitBox();
	// End User Visit Data box 
	
	// Determine if they have the latest version of Kindred Posts Premium
	if (kp_checkPro()) {
		kp_proOutputVersionInformation();
	}
	?>
	</div>
	<?php // End left-side container  ?>
	
	<?php // Start right-side containers ?>
	<div class="postbox-container" style="float:right; margin-left:15px; margin-right:15px; width:300px;">
	<?php 
	// Start Found Bug Box 
	if (false) {
		kp_foundBugBox();
	}
	// End Found Bug Box 
	
	// Start Feedback Box
	kp_feedbackBox();
	// End Feedback Box
 
	// Start Premium Version Box 
	kp_premiumVersionBox();
	// End Premium Version Box 
	// End Right Column 
	?>
	</div>
	</div>
<?php
}
/**
 *
 * The following functions render each box on the setting page
 * 
 **/
function kp_feedbackBox() {
	global $feedbackUrl;
	
	$hideFeedbackBox = (get_option("HideFeedbackBox", "false") == "true");
	$feedbackMsg = get_option("FeedbackMsg", "");
?>
	<div id="feedbackBox" class="postbox">
	<h3 class="hndle" style="font-size:15px; padding:7px 10px 7px 10px; cursor:default; <?php if ($hideFeedbackBox && $feedbackMsg == "") { ?>margin-bottom:0px;<?php } ?>">
		<span><?php _e('How are we doing?'); ?></span>
		
		<?php if (!$hideFeedbackBox) { ?>
		<a href="#" onclick="var hide='true'; toggleFeedbackBox(hide, ''); return false;" style="float:right;"><?php _e('Hide Form'); ?></a>
		<?php } else { ?>
		<a href="#" onclick="var hide='false'; toggleFeedbackBox(hide, ''); return false;" style="float:right;"><?php _e('Show Form'); ?></a>
		<?php } ?>		
	</h3>

	<div class="inside" id="feedbackForm"<?php if ($hideFeedbackBox && $feedbackMsg == "") { ?> style="display:none;"<?php } ?>>
	<div style="width:285px;">
	<?php if (!$hideFeedbackBox) { ?>
	<div>
	
	<form action="#" method="POST" onsubmit="return submitFeedback();">
		<input type="hidden" name="confirm" id="confirm" value="false" />
		<p><em><?php _e('All fields are optional'); ?></em></p>
		
		<p>
			<label for="improve"><?php _e('Where do you think we can improve?'); ?></label><br />
			<textarea id="improve" name="improve" style="width:95%;"></textarea>
		</p>
		
		<p>
			<label for="next"><?php _e('What would you like to see in the next version of Kindred Posts?'); ?></label><br />
			<textarea id="next" name="next" style="width:95%;"></textarea>
		</p>
		
		<h4>
			<?php _e('Additional Information:'); ?>
			<a href="#" id="OptionalInformationShow" onclick="document.getElementById('OptionalInformation').style.display='block'; this.style.display='none'; document.getElementById('OptionalInformationHide').style.display='inline'; return false;"><?php _e('Show'); ?></a> 
			<a href="#" id="OptionalInformationHide" onclick="document.getElementById('OptionalInformation').style.display='none';this.style.display='none'; document.getElementById('OptionalInformationShow').style.display='inline'; return false;" style="display:none;"><?php _e('Hide'); ?></a>
		</h4>
		
		<div id="OptionalInformation" style="display:none;">
			<p>
				<label for="websites"><?php _e('What website(s) do you plan to use Kindred Posts on?'); ?></label><br />
				<textarea id="websites" name="websites" style="width:95%;"></textarea>
			</p>
			
			<p>
				<label for="why"><?php _e('Why did you choose Kindred Posts?'); ?></label><br />
				<textarea id="why" name="why" style="width:95%;"></textarea>
			</p>		
		
			<p><?php _e('If you would like us to personally contact you about your feedback.'); ?></p>
			<p>
				<label for="name"><?php _e('Your Name'); ?></label><br />
				<input type="text" id="name" name="name" style="width:95%;" />
			</p>
			
			<p>
				<label for="email"><?php _e('Your Email'); ?></label><br />
				<input type="text" id="email" name="email" style="width:95%;" />
			</p>
		</div>
		<p align="center">
			<input type="submit" value="<?php _e('Send Feedback'); ?>" class="button-primary" />
		</p>
	</form>
	</div>
	<?php 
	} else if ($feedbackMsg != "") { 
	?>
	<span class="FeedbackMsg"><?php echo $feedbackMsg; ?></span>
	<?php
	} 
	?>
	<form method="post" action="options.php" id="HideFeedbackForm">
		<?php 
		settings_fields('kp_feedback');
		do_settings_sections('kp_feedback');
		?>	
		<input type="hidden" id="HideFeedbackBox" name="HideFeedbackBox" value="false" />
		<input type="hidden" id="FeedbackMsg" name="FeedbackMsg" value="" />
	</form>
	</div>
	</div>
	</div>
	
	<script type="text/javascript">
	function clearFeedbackForm() {
		jQuery("#improve").val("");
		jQuery("#next").val("");
		jQuery("#websites").val("");
		jQuery("#why").val("");
		jQuery("#name").val("");
		jQuery("#email").val("");
	}
	
	function toggleFeedbackBox(hide, msg) {
		jQuery("#HideFeedbackBox").val(hide);
		jQuery("#FeedbackMsg").val(msg);
		jQuery("#HideFeedbackForm").submit();
	}
	
	function postFeedback() {
		var data = {
			"improve": jQuery("#improve").val(), 
			"next": jQuery("#next").val(),
			"websites": jQuery("#websites").val(),
			"why": jQuery("#why").val(),
			"name": jQuery("#name").val(),
			"email": jQuery("#email").val()
		};
		var feedbackMsg = "<?php _e("Thank you for your feedback!"); ?>";
		
		try {
			jQuery.ajax({
				type: "POST",
				url: "<?php echo $feedbackUrl; ?>",
				data: data,
				crossDomain: true,	 
				dataType: "text"
			}).fail(function() {
				alert(feedbackMsg);
				clearFeedbackForm();
				toggleFeedbackBox("true", feedbackMsg);
			}).done(function() {
				alert(feedbackMsg);
				clearFeedbackForm();
				toggleFeedbackBox("true", feedbackMsg);
			});
		} catch(e) {}
	}
	
	function submitFeedback() {
		if (confirm("<?php _e("Your privacy is important to us. Ai Spork does not and will never give out any of the information you have provided here. Please press OK to submit this feedback."); ?>")) { 
			jQuery("#confirm").val("true");
			postFeedback();
		}
		
		return false;
	}	
	</script>
<?php
}

function kp_foundBugBox() {
?>
	<div id="foundBugBox" class="postbox">
	<h3 class="hndle" style="font-size:15px; padding:7px 10px 7px 10px; cursor:default;"><span><?php _e('Found a Bug?'); ?></span></h3>
	<div class="inside">
	<div style="width: 285px;">
		<p><?php _e(kp_stringSupport()); ?></p>
	</div>
	</div>
	</div>
<?php
}

function kp_premiumVersionBox() {
	global $premiumVersionUrl;
	
	if (kp_checkPro()){
		return;
	}
?>
	<div id="premiumVersionBox" class="postbox">
	<h3 class="hndle" style="font-size:15px;padding:7px 10px 7px 10px; cursor:default;"><span><?php _e('Premium Version Information'); ?></span></h3>
	<div class="inside">
	<div style="width: 285px; margin: 0 auto 0px auto;">
	<?php 
		_e(kp_stringPro2());
	?>
	<div align="center">
		<a href="<?php echo $premiumVersionUrl; ?>" class="button-primary"><?php _e('Get the Premium Version') ?></a>
	</div>
	</div>
	</div>
	</div>
<?php 
}

function kp_rssBox() {
?>
	<div id="rssBox" class="postbox">
	<h3 class="hndle" style="font-size:15px;padding:7px 10px 7px 10px; cursor:default;"><span><?php _e('Ai Spork News'); ?></span></h3>
	<div class="inside">
		<div style="width: 285px; margin: 0 auto 15px auto;">
		<?php include_once(plugin_dir_path( __FILE__ ) . "../kindred-posts-rss.php"); ?>
		</div>
	</div>
	</div>
<?php
}

function kp_settingsBox() {
	$CollectStatistics = get_option('CollectStatistics', "true");
	$AttemptToBlockBotVisits = get_option('AttemptToBlockBotVisits', "true");
	$AdminTestMode = get_option('AdminTestMode', "false");	

?>
	<div id="settingsBox" class="postbox">
	<h3 class="hndle" style="font-size:15px;padding:7px 10px 7px 10px; cursor:default;">
		<span>
			<?php _e('Settings'); ?>
		</span>
	</h3>
	<div class="inside">

	<form method="post" action="options.php">
	
	<?php 
	kp_stringFieldSet(); 
	?>
	
	<input type="hidden" name="FirstSave" value="false" id="FirstSave" />
	<?php 
	settings_fields('kp_settings');
	do_settings_sections('kp_settings');
	?>
	<hr />
	<p>
		<input type="checkbox" name="AdminTestMode" value="true" id="AdminTestMode"<?php if ($AdminTestMode == "true") { ?> checked="checked"<?php } ?> />
		<label for="AdminTestMode"><?php _e('In Test Mode - '); ?><a id="SeeMoreLink" href="#" onclick="document.getElementById('TestModeDescription').style.display = 'inline'; this.style.display = 'none'; return false;"><?php _e('Show More'); ?></a>
		<span id="TestModeDescription" style="display:none;"><?php _e('Test mode allows administrators to see how the plugin will behave for website visitors using fake visit data. While in test mode, the widget will only appear to administrators that can edit plugins and themes. NOTE: Even within test mode, data can still be collected about website visitors.'); ?> <a href="#" onclick="document.getElementById('TestModeDescription').style.display = 'none'; document.getElementById('SeeMoreLink').style.display = 'inline'; return false;"><?php _e('Show Less'); ?></a></span>
		</label>
		<br />
	</p>	
	
	<p>
		<input type="radio" name="CollectStatistics" value="true" id="CollectStatistics1"<?php if ($CollectStatistics == "true") { ?> checked="checked"<?php } ?> />
		<label for="CollectStatistics1"><?php _e('Collect User Visit data'); ?></label>
		<br />
		
		<input type="radio" name="CollectStatistics" value="false" id="CollectStatistics2"<?php if ($CollectStatistics != "true") { ?> checked="checked"<?php } ?> />
		<label for="CollectStatistics2"><?php _e('<strong>STOP</strong> collecting User Visit data'); ?></label>
	</p>
	
	<p>
		<input type="radio" name="AttemptToBlockBotVisits" value="true"<?php if ($AttemptToBlockBotVisits == "true") { ?> checked="checked"<?php } ?> id="AttemptToBlockBotVisits1" />
		<label for="AttemptToBlockBotVisits1"><?php _e('Ignore Bot Visits such as the Googlebot, Bingbot, etc. <em>(Recommended)</em>'); ?></label>
		<br />
		
		<input type="radio" name="AttemptToBlockBotVisits" value="false"<?php if ($AttemptToBlockBotVisits == "false") { ?> checked="checked"<?php } ?> id="AttemptToBlockBotVisits2" />
		<label for="AttemptToBlockBotVisits2"><?php _e('Record Bot Visits'); ?></label>
		<br />
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
<?php
}

function kp_userVisitBox() {
	global $visitTbl, $wpdb;
?>
	<div id="userVisitBox" class="postbox">
	<h3 class="hndle" style="font-size:15px;padding:7px 10px 7px 10px; cursor:default;"><span><?php _e('User Visit Data'); ?></span></h3>
	<div class="inside">
	<?php
	if (kp_checkPro()){
		kp_performUserChecks();
	}
	?>
	<p>
		<?php _e('Number of Unique Visitors: ');
		$visitor_count = $wpdb->get_var("SELECT COUNT(*) FROM $visitTbl WHERE TestData='0'");
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
					(SELECT CreateDate AS Date FROM $visitTbl WHERE TestData='0' ORDER BY CreateDate DESC LIMIT 1) 
						UNION
					(SELECT UpdateDate AS Date FROM $visitTbl WHERE TestData='0' ORDER BY UpdateDate DESC LIMIT 1)
				) d
			ORDER BY d.Date DESC LIMIT 1"
		);
		if ($last_visit) {
			echo date("F j, Y, g:i a", strtotime($last_visit)); 
		} else {
			echo __("No visits yet");
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