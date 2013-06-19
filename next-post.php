<?php
/*
Plugin Name: Next Post
Plugin URI: http://adampatterson.ca
Description: Provides shortcodes and template tags for next/previous navigation in custom post types.
Version: 1
License: GPLv2
Author: Adam Patterson
Author URI: http://adampatterson.ca
*/

add_action('admin_menu', 'next_post_add_posts');

register_activation_hook(__FILE__, 'next_post_activation');
function next_post_activation() {
  // remove old options
	$oldoptions = array();
	$oldoptions[] = get_option('next_post__before_prev_link');
	$oldoptions[] = get_option('next_post__prev_link_text');
	$oldoptions[] = get_option('next_post__after_prev_link');
	
	$oldoptions[] = get_option('next_post__before_parent_link');
	$oldoptions[] = get_option('next_post__parent_link_text');
	$oldoptions[] = get_option('next_post__after_parent_link');
	
	$oldoptions[] = get_option('next_post__before_next_link');
	$oldoptions[] = get_option('next_post__next_link_text');
	$oldoptions[] = get_option('next_post__after_next_link');
	
	$oldoptions[] = get_option('next_post__exclude');
	
	delete_option('next_post__before_prev_link');
	delete_option('next_post__prev_link_text');
	delete_option('next_post__after_prev_link');

	delete_option('next_post__before_parent_link');
	delete_option('next_post__parent_link_text');
	delete_option('next_post__after_parent_link');

	delete_option('next_post__before_next_link');
	delete_option('next_post__next_link_text');
	delete_option('next_post__after_next_link');

	delete_option('next_post__exclude');
	
	// set defaults
	$options = array();
	$options['before_prev_link'] = '<div class="alignleft">';
	$options['prev_link_text'] = __('Previous:', 'next-post').' %title%';
	$options['after_prev_link'] = '</div>';
	
	$options['before_parent_link'] = '<div class="aligncenter">';
	$options['parent_link_text'] = __('Up one level:', 'next-post').' %title%';
	$options['after_parent_link'] = '</div>';
	
	$options['before_next_link'] = '<div class="alignright">';
	$options['next_link_text'] = __('Next:', 'next-post').' %title%';
	$options['after_next_link'] = '</div>';
	
	$options['exclude'] = '';
	$options['loop'] = 0;
	
	// set new option
	add_option('next_post', array_merge($oldoptions, $options), '', 'yes');
}

// when uninstalled, remove option
register_uninstall_hook( __FILE__, 'next_post_delete_options' );

function next_post_delete_options() {
	delete_option('next_post');
}

// i18n
if (!defined('WP_PLUGIN_DIR'))
	define('WP_PLUGIN_DIR', dirname(dirname(__FILE__))); 

function next_post_plugin_actions($links, $file) {
 	if ($file == 'next-post/next-post.php' && function_exists("admin_url")) {
		$settings_link = '<a href="' . admin_url('options-general.php?post=next-post') . '">' . __('Settings', 'next-post') . '</a>';
		array_unshift($links, $settings_link); 
	}
	return $links;
}
add_filter('plugin_action_links', 'next_post_plugin_actions', 10, 2);

add_action('admin_init', 'register_next_post_options' );
function register_next_post_options(){
	register_setting( 'next_post', 'next_post' );
}

function next_post_add_posts() {
    // Add a new submenu under Options:
	$css = add_options_page('Next post', 'Next post', 'manage_options', 'next-post', 'next_post_options');
	add_action("admin_head-$css", 'next_post_css');
}

function next_post_css() { ?>
<style type="text/css">
#next-post, #parent-post, #previous-post { float: left; width: 30%; margin-right: 5%; }
#next-post { margin-right: 0; }
</style>
<?php 
}


// displays the options post content
function next_post_options() { ?>	
    <div class="wrap">
	<form method="post" id="next_post_form" action="options.php">
		<?php settings_fields('next_post');
		$options = get_option('next_post'); ?>

    <h2><?php _e( 'Next post Options', 'next-post'); ?></h2>
    
	<p><?php _e("On the first and last posts in the sequence:", 'next-post'); ?><br />
    <label><input type="radio" name="next_post[loop]" id="loop" value="1" <?php checked('1', $options['loop']); ?> />
		<?php _e("Loop around, showing links back to the beginning or end", 'next-post'); ?></label><br />
	<label><input type="radio" name="next_post[loop]" id="loop" value="0" <?php checked('0', $options['loop']); ?> />
		<?php _e("Omit the empty link", 'next-post'); ?></label>	
	</p>

    <p><label><?php _e("Exclude posts: ", 'next-post'); ?><br />
    <input type="text" name="next_post[exclude]" id="exclude" 
		value="<?php echo $options['exclude']; ?>" /><br />
	<small><?php _e("Enter post IDs separated by commas.", 'next-post'); ?></small></label></p>
       
    <div id="previous-post">
    <h3><?php _e("Previous post Display:", 'next-post'); ?></h3>
    <p><label><?php _e("Before previous post link: ", 'next-post'); ?><br />
    <input type="text" name="next_post[before_prev_link]" id="before_prev_link" 
		value="<?php echo esc_html($options['before_prev_link']); ?>" />  </label></p>
    
    <p><label><?php _e("Previous post link text: <small>Use %title% for the post title</small>", 'next-post'); ?><br />
    <input type="text" name="next_post[prev_link_text]" id="prev_link_text" 
		value="<?php echo esc_html($options['prev_link_text']); ?>" />  </label></p>
    
    <p><label><?php _e("After previous post link: ", 'next-post'); ?><br />
    <input type="text" name="next_post[after_prev_link]" id="after_prev_link" 
	value="<?php echo esc_html($options['after_prev_link']); ?>" />  </label></p>
    <p><?php _e('Shortcode:'); ?> <strong>[previous_custom_post]</strong><br />
    <?php _e('Template tag:'); ?> <strong>&lt;?php previous_custom_post(); ?&gt;</strong></p>
    </div>

    <div id="next-post">
    <h3><?php _e("Next post Display:", 'next-post'); ?></h3>
    <p><label><?php _e("Before next post link: ", 'next-post'); ?><br />
    <input type="text" name="next_post[before_next_link]" id="before_next_link" 
		value="<?php echo esc_html($options['before_next_link']); ?>" />  </label></p>
    
    <p><label><?php _e("Next post link text: <small>Use %title% for the post title</small>", 'next-post'); ?><br />
    <input type="text" name="next_post[next_link_text]" id="next_link_text" 
		value="<?php echo esc_html($options['next_link_text']); ?>" />  </label></p>
    
    <p><label><?php _e("After next post link: ", 'next-post'); ?><br />
    <input type="text" name="next_post[after_next_link]" id="after_next_link" 
		value="<?php echo esc_html($options['after_next_link']); ?>" />  </label></p>
    <p><?php _e('Shortcode:'); ?> <strong>[next_custom_post]</strong><br />
    <?php _e('Template tag:'); ?> <strong>&lt;?php next_custom_post(); ?&gt;</strong></p>
    </div>
    
	<p class="submit">
	<input type="submit" name="submit" class="button-primary" value="<?php _e('Update Options', 'next-post'); ?>" />
	</p>
	</form>
	</div>
<?php 
} // end function next_post_options() 

// make the magic happen
function flatten_post_list($exclude = '') {
    global $post, $wpdb;

    if ( empty( $post ) )
        return null;

    //	Get the list of post types. Default to current post type
    if ( empty($post_type) )
        $post_type = "'$post->post_type'";

   $args = 'sort_column=menu_order&sort_order=asc&posts_per_page=100&post_type='.$post_type;
   $postlist = get_posts($args);
   $myposts = array();

   if (!empty($exclude)) {
       $excludes = split(',', $exclude);
       foreach ($postlist as $thispost) {
           if (!in_array($thispost->ID, $excludes)) {
               $myposts[] += $thispost->ID;
           }
       }
   }
   else {
       foreach ($postlist as $thispost) {
           $myposts[] += $thispost->ID;
       }
   }
   return $myposts;
}

function get_next_custom_post() {
	global $post;
	$options = get_option('next_post');
	$exclude = $options['exclude'];
	$postlist = flatten_post_list($exclude);
	$current = array_search($post->ID, $postlist);
	$nextID = $postlist[$current+1];
	
	if (!isset($nextID)) 
		if ($options['loop'])
			$nextID = $postlist[0];
		else 
			return '';
	
	$before_link = stripslashes($options['before_next_link']);
	$linkurl = get_permalink($nextID);
	$title = get_the_title($nextID);
	$linktext = $options['next_link_text'];
	if (strpos($linktext, '%title%') !== false) 
		$linktext = str_replace('%title%', $title, $linktext);
	$after_link = stripslashes($options['after_next_link']);
	
	$link = $before_link . '<a href="' . $linkurl . '" title="' . $title . '">' . $linktext . '</a>' . $after_link;
	return $link;
} 

function get_previous_custom_post() {
	global $post;
	$options = get_option('next_post');
	$exclude = $options['exclude'];
	$postlist = flatten_post_list($exclude);

	$current = array_search($post->ID, $postlist);

	$prevID = $postlist[$current-1];

	if (!isset($prevID))
	 	if ($options['loop'])
			$prevID = $postlist[count($postlist) - 1];
		else 
			return '';
		
	$before_link = stripslashes($options['before_prev_link']);
	$linkurl = get_permalink($prevID);
	$title = get_the_title($prevID);
	$linktext = $options['prev_link_text'];
	if (strpos($linktext, '%title%') !== false) 
		$linktext = str_replace('%title%', $title, $linktext);
	$after_link = stripslashes($options['after_prev_link']);
	
	$link = $before_link . '<a href="' . $linkurl . '" title="' . $title . '">' . $linktext . '</a>' . $after_link;
	return $link;
} 

function next_custom_post() {
	echo get_next_custom_post();
}
function previous_custom_post() {
	echo get_previous_custom_post();
}

// shortcodes
add_shortcode('next_custom_post', 'get_next_custom_post');
add_shortcode('previous_custom_post', 'get_previous_link');

// pre-3.1 compatibility, lifted from wp-includes/formatting.php
if (!function_exists('esc_html')) {
	function esc_html( $text ) {
		$safe_text = wp_check_invalid_utf8( $text );
		$safe_text = _wp_specialchars( $safe_text, ENT_QUOTES );
		return apply_filters( 'esc_html', $safe_text, $text );
	}
}
?>
