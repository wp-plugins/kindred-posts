<?php 
// 
//  rss-function.php
//  easy-sign-up
// 
// Graciously lifted from the easy-sign-up plugin which was taken from the codex at wordpress.org
// http://wordpress.org/extend/plugins/easy-sign-up/
//
// Get RSS Feed(s)
include_once(ABSPATH . WPINC . '/feed.php');

// Get a SimplePie feed object from the specified feed source.
$rss = fetch_feed('http://feeds.feedburner.com/AiSpork');
if (!is_wp_error( $rss ) ) : // Checks that the object is created correctly 
    // Figure out how many total items there are, but limit it to 5. 
    $maxitems = $rss->get_item_quantity(5); 

    // Build an array of all the items, starting with element 0 (first element).
    $rss_items = $rss->get_items(0, $maxitems); 
endif;
?>

<ul id="easy-rss">
    <?php if ($maxitems == 0) echo '<li>' . __('No items') . '.</li>';
    else
    // Loop through each feed item and display each item as a hyperlink.
    foreach ( $rss_items as $item ) : ?>
    <li>
        <a href='<?php echo $item->get_permalink(); ?>'
        title='<?php _e('Posted '.$item->get_date('j F Y | g:i a')); ?>'>
        <?php _e($item->get_title()); ?></a>
    </li>
    <?php endforeach; ?>
</ul>
<ul>
  <li>
    <a href="http://feeds.feedburner.com/AiSpork" rel="alternate" type="application/rss+xml"><img src="http://www.feedburner.com/fb/images/pub/feed-icon16x16.png" alt="" style="vertical-align:middle;border:0"/></a>&nbsp;<a href="http://feeds.feedburner.com/AiSpork" rel="alternate" type="application/rss+xml"><?php _e('Subscribe with RSS'); ?></a>
  </li>
  <li>
	<a href="http://twitter.com/AiSpork"><img src="<?php echo plugins_url('', __FILE__ ).'/images/twitter.png'; ?>"></a>&nbsp;<a href="http://twitter.com/AiSpork"><?php _e('Follow Ai Spork on Twitter'); ?></a>
  </li>
</ul>