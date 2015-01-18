<?php
/**
 * Add the AiSpork logo to the head (this will only appear on the settings page of the AiSpork page)
 *
 * @return null
 **/
function kp_settingsHead(){
	if ($_SERVER["SCRIPT_NAME"] != null && stristr($_SERVER["SCRIPT_NAME"], "options-general.php") !== FALSE) {
		if ($_GET["page"] != null && $_GET["page"] == "kindred-posts") {
			echo "<link rel=\"shortcut icon\" href=\"" . plugins_url("", __FILE__ ) . "/../images/icon.ico\" />";
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
	global $kp_pluginUrl, $kp_helpUrl, $kp_donationUrl;
	global $kp_defaultCollectStatistics, $kp_defaultAttemptToBlockBotVisits, $kp_defaultAdminTestMode;
	
	$CollectStatistics = get_option("kp_CollectStatistics", (string) $kp_defaultCollectStatistics);
	$AttemptToBlockBotVisits = get_option("kp_AttemptToBlockBotVisits", (string) $kp_defaultAttemptToBlockBotVisits);
	$AdminTestMode = get_option("kp_AdminTestMode", (string) $kp_defaultAdminTestMode);	
	
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
	
	// Check if the admin wanted to delete visit data
	if (isset($_POST["delete"]) && $_POST["delete"] == "all") {
		kp_resetVisitData();
		$alert = __("Visit data has been removed");
	}

	if (kp_performDeleteVisitChecks()){
		$alert = __("Visit data from bots or ignored IP addresses have been removed");
	}
?>
	<div class="wrap">

	<h2><?php _e("Kindred Posts by Ai Spork"); ?></h2>	
	
	<?php 
	// Display the alert
	if ($alert != "") {
	?>
	<div id="setting-error-settings_updated" class="updated settings-error">
		<p>
			<strong><?php echo $alert; ?></strong>
		</p>
	</div>	
	<?php 
	}
	?>	
	
	<div>
		<a target="_blank" href="<?php echo $kp_pluginUrl; ?>"><?php _e('Plugin Homepage'); ?></a>
		&nbsp; | &nbsp;
		<a target="_blank" href="<?php echo $kp_helpUrl; ?>"><?php _e('Support Forum'); ?></a>
		&nbsp; | &nbsp;
		<a target="_blank" href="<?php echo $kp_donationUrl; ?>" style="color:#DF01D7;"><?php _e('Donate'); ?></a>
	</div>
	
	<h3><strong><?php _e('Plugin Status:'); ?>
	<?php
	if (get_option("kp_CollectStatistics", "true") == "true") { 
	?>
		<span style="color:green;">
			<?php 
			_e('Collecting Data'); 
			if (get_option("kp_AdminTestMode", "false") == "true") { 
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
			if (get_option("kp_AdminTestMode", "false") == "true") { 
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
	
	// Start Advanced Settings Box
	kp_advancedSettingsBox();
	// End Advanced Settings Box
	?>
	</div>
	<?php // End left-side container  ?>
	
	<?php // Start right-side containers ?>
	<div class="postbox-container" style="float:right; margin-left:15px; margin-right:15px; width:300px;">
	<?php 
	// Start User Visit Data box
	kp_userVisitBox();
	// End User Visit Data box 
	
	// Start Found Bug Box 
	kp_foundBugBox();
	// End Found Bug Box 	
	
	// Start Feedback Box
	kp_feedbackBox();
	// End Feedback Box
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
function kp_advancedSettingsBox() {
	$kp_Tracking = get_option("kp_Tracking", "none");
	$kp_TrackingCode = get_option("kp_TrackingCode", "");
	$kp_BlockedIps = get_option("kp_BlockedIps", "");

	if ($kp_BlockedIps == __("xxx.xxx.xxx.xxx # Example IP Address")) {
		$kp_BlockedIps = "";
	}
?>
	<div id="advancedSettingsBox" class="postbox">
	<h3 class="hndle" style="font-size:15px; padding:7px 10px 7px 10px; cursor:default;"><span><?php _e("Advanced Settings"); ?></span></h3>
	<div class="inside">
	
	<form method="post" action="options.php">
	<?php 
	settings_fields("kp_advancedSettings"); // Outputs a nonce for the form
	?>
	<div>
		<input type="radio" name="kp_Tracking" value="none"<?php checked($kp_Tracking == "none"); ?> id="kp_Tracking-none" />
		<label for="kp_Tracking-none"><?php _e("Do not track clicks on recommended posts with analytics"); ?></label>
	</div>
	
	<div>
		<input type="radio" name="kp_Tracking" value="custom"<?php checked($kp_Tracking == "custom"); ?> id="kp_Tracking-custom" />
		<label for="kp_Tracking-custom"><?php _e("Analytics tracking code"); ?> &nbsp; </label>
		<input type="text" name="kp_TrackingCode" value="<?php echo $kp_TrackingCode; ?>" id="kp_TrackingCode" style="width:50%;" onkeyup="kp_removeDoubleQuotes(this);" />
		<script type="text/javascript">
		function kp_removeDoubleQuotes(elem) {
			elem.value = elem.value.replace('"', "'");
		}
		</script>
		
		<div style="display:inline;">
			 &nbsp; 
			 <a href="#" id="OptionalInformationShowLink" onclick="this.style.display = 'none'; document.getElementById('OptionalInformationShowLink').style.display = 'none'; document.getElementById('OptionalInformationHideDiv').style.display = 'block'; document.getElementById('OptionalInformationHideLink').style.display = 'inline'; return false;" style="display:inline;"><?php _e("What is this?"); ?></a>
			 
			 <a href="#" id="OptionalInformationHideLink" onclick="this.style.display= 'none'; document.getElementById('OptionalInformationHideDiv').style.display = 'none'; document.getElementById('OptionalInformationShowLink').style.display = 'inline'; return false;" style="display:none;"><?php _e("Hide"); ?></a>
		</div>
		
		<div id="OptionalInformationHideDiv" style="margin-left:25px; display:none;">
			<?php _e("Tracking code is added to the onclick event for links. For example, for Google Analytics use: <pre>try { _gaq.push(['_trackEvent', '" . __('Kindred Posts') . "', '" . __("Click") . "', '{post_slug}']); } catch (e) {}; </pre><div>You can track the following information:<ul style=\"margin-left:15px;\"><li>{post_id}: The id of the post</li><li>{post_url}: The permalink of the post</li><li>{post_title}: The title of the post</li><li>{post_author}: The author of the post</li><li>{post_slug}: A url friendly version of {post_title}</li></ul></div>"); ?>
		</div>
		
		<div style="margin-left:25px;">
			<?php _e("NOTE: You can only use single quotes within the tracking code."); ?>
		</div>
	</div>

	<h4><label for="kp_BlockedIps"><?php _e("Ignore visits from the following IP Addresses"); ?></label></h4>
	<ol>
		<li><?php _e("Only put one IP Address per line"); ?></li>
		<li><?php _e("You can add comments by putting a # after the IP Address"); ?></li>
	</ol>
	
	<textarea name="kp_BlockedIps" id="kp_BlockedIps" rows="5" style="width:100%;" placeholder="<?php _e("xxx.xxx.xxx.xxx # Example IP Address"); ?>"><?php echo $kp_BlockedIps; ?></textarea><br />
	<?php _e("Your current IP Address is " . kp_determineIP()); ?>	
	
	<p class="submit">
		<input type="submit" class="button-primary" value="<?php _e("Save Changes"); ?>" />
    </p>
	
	</form>
	</div>
	</div>
<?php
}
 
function kp_feedbackBox() {
	global $kp_feedbackUrl;
	
	$hideFeedbackBox = (get_option("kp_HideFeedbackBox", "false") == "true");
	$feedbackMsg = get_option("kp_FeedbackMsg", "");
?>
	<div id="feedbackBox" class="postbox">
	<h3 class="hndle" style="font-size:15px; padding:7px 10px 7px 10px; cursor:default; <?php if ($hideFeedbackBox && $feedbackMsg == "") { ?>margin-bottom:0px;<?php } ?>">
		<span><?php _e("How are we doing?"); ?></span>
		
		<?php if (!$hideFeedbackBox) { ?>
		<a href="#" onclick="var hide='true'; toggleFeedbackBox(hide, ''); return false;" style="float:right;"><?php _e('Hide'); ?></a>
		<?php } else { ?>
		<a href="#" onclick="var hide='false'; toggleFeedbackBox(hide, ''); return false;" style="float:right;"><?php _e('Show'); ?></a>
		<?php } ?>		
	</h3>

	<div class="inside" id="feedbackForm"<?php if ($hideFeedbackBox && $feedbackMsg == "") { ?> style="display:none;"<?php } ?>>
	<div style="width:285px;">
	<?php 
	if (!$hideFeedbackBox) { 
	?>
	<div>
	
	<form action="#" method="POST" onsubmit="return submitFeedback();">
		<input type="hidden" name="confirm" id="confirm" value="false" />
		<p><em><?php _e("All fields are optional"); ?></em></p>
		
		<p>
			<label for="improve"><?php _e("Where do you think we can improve?"); ?></label><br />
			<textarea id="improve" name="improve" style="width:95%;"></textarea>
		</p>
		
		<p>
			<label for="next"><?php _e("What would you like to see in the next version of Kindred Posts?"); ?></label><br />
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
	}
	?>
	<form method="post" action="options.php" id="HideFeedbackForm">
		<?php 
		settings_fields("kp_feedback"); // Outputs a nonce for the form
		?>	
		<input type="hidden" id="kp_HideFeedbackBox" name="kp_HideFeedbackBox" value="false" />
		<input type="hidden" id="kp_FeedbackMsg" name="kp_FeedbackMsg" value="" />
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
				url: "<?php echo $kp_feedbackUrl; ?>",
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
	global $kp_supportForumUrl;
?>
	<div id="foundBugBox" class="postbox">
	<h3 class="hndle" style="font-size:15px; padding:7px 10px 7px 10px; cursor:default;"><span><?php _e('Found a Bug?'); ?></span></h3>
	<div class="inside">
	<div style="width: 285px;">
		<p><?php _e('Have a question or need support? Check out our <a href="' . $kp_supportForumUrl . '">support forum</a> to find answers to your questions.'); ?></p>
	</div>
	</div>
	</div>
<?php
}

function kp_settingsBox() {
	$CollectStatistics = get_option("kp_CollectStatistics", "true");
	$AttemptToBlockBotVisits = get_option("kp_AttemptToBlockBotVisits", "true");
	$AdminTestMode = get_option("kp_AdminTestMode", "false");	
?>
	<div id="settingsBox" class="postbox">
	<h3 class="hndle" style="font-size:15px;padding:7px 10px 7px 10px; cursor:default;">
		<span>
			<?php _e("Settings"); ?>
		</span>
	</h3>
	<div class="inside">
	
	<form method="post" action="options.php">
	<?php 
	settings_fields("kp_settings"); // Outputs a nonce for the form
	?>	
	
	<p><?php _e('By enabling this plugin, a widget that recommends posts to users has been added in the <a href="' . admin_url( 'widgets.php') . '">Widget</a> menu that you can drag and drop into your theme.'); ?></p>
	<hr />
	<p>
		<input type="checkbox" name="kp_AdminTestMode" value="true" id="kp_AdminTestMode"<?php checked($AdminTestMode == "true"); ?> />
		<label for="kp_AdminTestMode"><?php _e('Enable Test Mode - '); ?><a id="SeeMoreLink" href="#" onclick="document.getElementById('TestModeDescription').style.display = 'inline'; this.style.display = 'none'; return false;"><?php _e('What is this?'); ?></a>
		<span id="TestModeDescription" style="display:none;"><?php _e('Test mode allows administrators to see how the plugin will behave for website visitors using fake visit data. While in test mode, the widget will only appear to administrators that can edit plugins and themes. NOTE: Even within test mode, data can still be collected about website visitors.'); ?> <a href="#" onclick="document.getElementById('TestModeDescription').style.display = 'none'; document.getElementById('SeeMoreLink').style.display = 'inline'; return false;"><?php _e('Hide'); ?></a></span>
		</label>
		<br />
	</p>	
	
	<p>
		<input type="radio" name="kp_CollectStatistics" value="true" id="CollectStatistics-true"<?php checked($CollectStatistics == "true"); ?> />
		<label for="CollectStatistics-true"><?php _e('Collect User Visit data'); ?></label>
		<br />
		
		<input type="radio" name="kp_CollectStatistics" value="false" id="CollectStatistics-false"<?php checked($CollectStatistics != "true"); ?> />
		<label for="CollectStatistics-false"><?php _e('<strong>STOP</strong> collecting User Visit data'); ?></label>
	</p>
	
	<p>
		<input type="radio" name="kp_AttemptToBlockBotVisits" value="true" id="AttemptToBlockBotVisits-true"<?php checked($AttemptToBlockBotVisits == "true"); ?> />
		<label for="AttemptToBlockBotVisits-true"><?php _e('Ignore Bot Visits such as the Googlebot, Bingbot, etc. <em>(Recommended)</em>'); ?></label>
		<br />
		
		<input type="radio" name="kp_AttemptToBlockBotVisits" value="false" id="AttemptToBlockBotVisits-false"<?php checked($AttemptToBlockBotVisits == "false"); ?> />
		<label for="AttemptToBlockBotVisits-false"><?php _e('Record Bot Visits'); ?></label>
		<br />
	</p>
	
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

	$visitorCount = $wpdb->get_var("SELECT COUNT(*) FROM $visitTbl WHERE TestData='0'");
	$lastVisit = $wpdb->get_var(
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

	// Output the Number of Bots or Blocked IP visits
	$botSql = kp_getBotSql(true);
	$botVisits = $wpdb->get_var($wpdb->prepare($botSql["sql"], $botSql["params"]));
	
	$ignoreSql = kp_getIgnoredIPsSql(true);
	$ignoredIPsVisits = $wpdb->get_var($wpdb->prepare($ignoreSql["sql"], $ignoreSql["params"]));
	$badVisitCount = $botVisits + $ignoredIPsVisits;
	?>
	<div id="userVisitBox" class="postbox">
	<h3 class="hndle" style="font-size:15px;padding:7px 10px 7px 10px; cursor:default;"><span><?php _e("User Visit Data"); ?></span></h3>
	<div class="inside">
	<p>
		<?php 
		_e("Estimated number of Unique Visitors: " . $visitorCount);
		?> <br />
		<?php 
		_e("Last Visit Date: ");
		if ($lastVisit) {
			echo date("F j, Y, g:i a", strtotime($lastVisit)); 
		} else {
			echo __("No visits yet");
		}
		?>
	</p>

	<?php _e("You currently have"); ?> <strong><?php echo $badVisitCount; ?></strong> <?php
	if ($badVisitCount != 1) { 
		_e("visits from Bots or Ignored IP Addresses.");
	} else {
		_e("visit from a Bot or an Ignored IP Address."); 
	} 
	
	// Only show the delete ignored ips / bots button if we have them
	if ($badVisitCount > 0) {
	?> 
	<form method="POST" target="_self" onsubmit="return confirm('<?php _e("Are you sure you want to delete Bot and Ignored IP address data? This cannot be undone."); ?>');">
	<p style="text-align:center;">
		<input type="hidden" name="delete" value="bot" />
		<input type="submit" class="button-primary" value="<?php _e("Delete Bot / Ignored IP Address Visit Data"); ?>" />
	</p>
	</form>
	<?php
	}
	
	// Show delete button only if we have visits
	if ($visitorCount > 0) {
	?>
	<form method="POST" target="_self" onsubmit="return confirm('<?php _e("Are you sure you want to delete your data? This cannot be undone"); ?>');">
	<p style="text-align:center;">	
		<input type="hidden" name="delete" value="all" />
		<input type="submit" class="button-primary" value="<?php _e("Delete ALL Visitor Data"); ?>" />
	</p>
	</form>
	<?php
	}
	?>
	</div>
	</div>	
<?php
}
?>