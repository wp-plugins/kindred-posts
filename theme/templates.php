<?php
/**
	This file contains the templates used through Kindred Posts for rendering

	The following code is the default value for each 'template' variable used in the Kindred Posts widget. If you are interested in modifying how the widget is displayed, you should copy the following into custom-templates.php.
	
	==== START ====
	
	$kp_templates["kp_widget"] = '
		{kp_widget:before_widget}
		
		{if kp_widget:title_exists}
			{kp_widget:before_title}{kp_widget:title}{kp_widget:after_title}
		{/if kp_widget:title_exists}
		
		{kp_recommender} {comment}Outputs the kp_recommender object template here{/comment}
		{if isTestMode}<p>Kindred Posts is in Test Mode</p>{/if isTestMode}
		{kp_widget:after_widget}
	';
	
	$kp_templates["kp_recommender"] = '
		<div class="kindredposts">
			{kp_recommendedPosts} {comment}Outputs a list of kp_recommenderPost objects here{/comment}
		</div>
	';
	
	$kp_templates["kp_recommendedPost"] = '
	{if has_content}

	{if kp_widget:orientation-horizontal}
	<div style="float:{kp_widget:alignment};">
	{/if kp_widget:orientation-horizontal}
	<div class="kp_post" style="{kp_widget:post_style}" align="{kp_widget:alignment}">
		{if show_post_thumbnail}
			<span style="{kp_widget:postimage_style}"><a onclick="{kp:trackingcode}" href="{post_url}">{post_thumbnail}</a></span><br />
		{/if show_post_thumbnail}
		
		<div>
		{if show_post_title}
			<span style="{kp_widget:posttitle_style}"><a onclick="{kp:trackingcode}" href="{post_url}">{post_title}</a></span><br />
		{/if show_post_title}
		
		{if show_post_author}
			<span style="{kp_widget:postauthor_style}">{kp:By} 
				<span>
					<span class="author vcard">
						<a class="url fn n" onclick="{kp:trackingcode}" href="{author_user_url}" rel="author">{author_user_nicename}</a>
					</span>
				</span>
				
				{if show_post_date}
					<span style="{kp_widget:postdate_style}"> {kp:on} 
						<span class="entry-date">
							<time class="entry-date" datetime="{post_date}">{post_date_nice}</time>
						</span>
					</span>
				{/if show_post_date}
			</span><br />
		{else show_post_author}
			{if show_post_date}
				<span style="{kp_widget:postdate_style}"> {kp:On} 
					<span class="entry-date">
						<time class="entry-date" datetime="{post_date}">{post_date_nice}</time>
					</span>
				</span><br />
			{/if show_post_date}	
		{/if show_post_author}
		
		{if show_post_teaser}
			<span style="{kp_widget:postteaser_style}">{post_excerpt}</span><br />
		{/if show_post_teaser}
		</div>
	</div>
	{if kp_widget:orientation-horizontal}
	</div>
	{/if kp_widget:orientation-horizontal}

	{/if has_content}
	';	
	
	==== END ====
**/
global $kp_templates;

// Initialize the template variables
$kp_templates = array();
$kp_templates["kp_widget"] = '';
$kp_templates["kp_recommender"] = '';
$kp_templates["kp_recommendedPost"] = '';

// Include the custom-templates.php file if it is exists and use it to overwrite different template variables
try {
	if (file_exists(plugin_dir_path( __FILE__ ) . "custom-templates.php")) {
		include(plugin_dir_path( __FILE__ ) . "custom-templates.php");
	}
} catch (Exception $e) { }

// If we have blank template variables, initialize them to their default values

/**
 * This controls how the Kindred Posts widget will be displayed within your theme.
 *		This is composed of the $kp_templates["kp_recommender"] template
 **/
if (isset($kp_templates["kp_widget"]) && $kp_templates["kp_widget"] == '') {
	$kp_templates["kp_widget"] = '
		{kp_widget:before_widget}
		
		{if kp_widget:title_exists}
			{kp_widget:before_title}{kp_widget:title}{kp_widget:after_title}
		{/if kp_widget:title_exists}
		
		{kp_recommender} {comment}Outputs the kp_recommender object template here{/comment}
		{if isTestMode}<p><a href="./wp-admin/options-general.php?page=kindred-posts">Kindred Posts</a> is in Test Mode</p>{/if isTestMode}
		{kp_widget:after_widget}
	';
}

/**
 * This controls how {kp_recommender} will be displayed within $kp_templates["kp_widget"]
 *		This is composed of the $kp_templates["kp_recommendedPost"] template
 **/
if (isset($kp_templates["kp_recommender"]) && $kp_templates["kp_recommender"] == "") {
	$kp_templates["kp_recommender"] = '
		<div class="kindredposts">
			{kp_recommendedPosts} {comment}Outputs a list of kp_recommenderPost objects here{/comment}
		</div>
	';
}

/**
 * This controls how {kp_recommendedPosts} will be displayed within $kp_templates["kp_recommender"]
 **/
if (isset($kp_templates["kp_recommendedPost"]) && $kp_templates["kp_recommendedPost"] == "") {
	$kp_templates["kp_recommendedPost"] = '
	{if has_content}

	{if kp_widget:orientation-horizontal}
	<div style="float:{kp_widget:alignment};">
	{/if kp_widget:orientation-horizontal}
	<div class="kp_post" style="{kp_widget:post_style}" align="{kp_widget:alignment}">
		{if show_post_thumbnail}
			<span style="{kp_widget:postimage_style}"><a onclick="{kp:trackingcode}" href="{post_url}">{post_thumbnail}</a></span><br />
		{/if show_post_thumbnail}
		
		<div>
		{if show_post_title}
			<span style="{kp_widget:posttitle_style}"><a onclick="{kp:trackingcode}" href="{post_url}">{post_title}</a></span><br />
		{/if show_post_title}
		
		{if show_post_author}
			<span style="{kp_widget:postauthor_style}">{kp:By} 
				<span>
					<span class="author vcard">
						<a class="url fn n" onclick="{kp:trackingcode}" href="{author_user_url}" rel="author">{author_user_nicename}</a>
					</span>
				</span>
				
				{if show_post_date}
					<span style="{kp_widget:postdate_style}"> {kp:on} 
						<span class="entry-date">
							<time class="entry-date" datetime="{post_date}">{post_date_nice}</time>
						</span>
					</span>
				{/if show_post_date}
			</span><br />
		{else show_post_author}
			{if show_post_date}
				<span style="{kp_widget:postdate_style}"> {kp:On} 
					<span class="entry-date">
						<time class="entry-date" datetime="{post_date}">{post_date_nice}</time>
					</span>
				</span><br />
			{/if show_post_date}	
		{/if show_post_author}
		
		{if show_post_teaser}
			<span style="{kp_widget:postteaser_style}">{post_excerpt}</span><br />
		{/if show_post_teaser}
		</div>
	</div>
	{if kp_widget:orientation-horizontal}
	</div>
	{/if kp_widget:orientation-horizontal}

	{/if has_content}
	';
}