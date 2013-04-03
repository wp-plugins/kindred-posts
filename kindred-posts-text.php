<?php
// This file sets up the functions required for various text used within the plugin 

// This currently appears on the Settings page
function kp_FieldSetText(){
	_e('<p>By enabling this plugin, a widget that recommends posts to users has been added in the <a href="' . admin_url( 'widgets.php') . '">Widget</a> menu that you can drag and drop into your theme.</p>');
}

function kp_ProText(){
	_e('<p>This plugin recommends posts to your site visitors based on their past browsing history on your site. Your site uses artificial intelligence and statistics to learn how visitors are currently accessing your site and recommends posts that they may find interesting.</p>'); 
}

function kp_ProText2(){
	return '
You are currently using the free version of Kindred Posts. A premium version of this plugin is available for $12.99. Some special features include:
<ol>
	<li>More options that control the look and feel of your Kindred Posts widget</li>
	<li>More control over your visit data</li>
	<li>Integration with <a href="http://www.google.com/analytics/">Google Analytics</a></li>
</ol>
		';
}

function kp_SupportText(){
	global $SupportForumURL;
	return 'Have a question or need support? Check out our <a href="' . $SupportForumURL . '">support forum</a> to find answers to your questions.';
}
?>