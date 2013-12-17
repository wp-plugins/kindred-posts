<?php
/**
 * This file contains the templates used through Kindred Posts for rendering
 **/
$kp_templates = array();

// The following is a template for displaying the kp_recommender object
$kp_templates["kp_recommender"] = '
	<div class="kindredposts">
		{kp_recommendedPosts} {comment}Outputs a list of kp_recommenderPost objects here{/comment}
	</div>
';

// The following is a template for displaying the kp_recommendedPost object
$kp_templates["kp_recommendedPost"] = '
{if has_content}

{if kp_widget:orientation-horizontal}
<div style="float:{kp_widget:alignment};">
{/if kp_widget:orientation-horizontal}
<div class="kp_post" style="{kp_widget:post_style}" align="{kp_widget:alignment}">
	{if show_post_thumbnail}
		<span style="{kp_widget:postimage_style}"><a onclick="{ga_posttitle}" href="{post_url}">{post_thumbnail}</a></span><br />
	{/if show_post_thumbnail}
	
	<div style="text-align:left;">
	{if show_post_title}
		<span style="{kp_widget:posttitle_style}"><a onclick="{ga_posttitle}" href="{post_url}">{post_title}</a></span><br />
	{/if show_post_title}
	
	{if show_post_author}
		<span style="{kp_widget:postauthor_style}">{kp:By} 
			<span class="byline">
				<span class="author vcard">
					<a class="url fn n" onclick="{ga_author}" href="{author_user_url}" rel="author">{author_user_nicename}</a>
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

// The following is a template for displaying the kp_widget object
$kp_templates["kp_widget"] = '
	{kp_widget:before_widget}
	
	{if kp_widget:title_exists}
		{kp_widget:before_title}{kp_widget:title}{kp_widget:after_title}
	{/if kp_widget:title_exists}
	
	{kp_recommender} {comment}Outputs the kp_recommender object template here{/comment}
	{kp_widget:after_widget}
';
?>