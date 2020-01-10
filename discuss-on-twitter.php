<?php
/**
 * Plugin Name: Discuss on Twitter
 * Description: Use Twitter as the commenting system for your blog
 * Author: Nick Grossman
 * Author URI: https://nickgrossman.is
 * Version: 0.3
 */

/*
*
* The button itself
*
*/
function discuss_on_twitter_button() {
  $id = get_the_ID();
  $permalink = get_the_permalink();
  wp_enqueue_script('twitter', 'https://platform.twitter.com/widgets.js');
  $dashicons_url = includes_url() . 'css/dashicons.min.css';
  wp_enqueue_style('dashicons', $dashicons_url);
  $dot_style_url = plugins_url('styles.css', __FILE__);
  wp_enqueue_style('discuss-on-twitter', $dot_style_url, array(), '0.6');
  $author_handle = get_option('wtt_twitter_username');
  $tweet_id = get_post_meta($id, '_jd_wp_tweet_id', true);
?>

<div class="discuss-on-twitter">
  <?php if($author_handle): ?>

  <?php if ($tweet_id) : ?>
    <a class="discuss-on-twitter-button reply-on-twitter" href="https://twitter.com/intent/tweet?in_reply_to=<?php echo $tweet_id; ?>">
      <span class="dashicons dashicons-twitter"></span>
      Discuss on Twitter
    </a>
     <a class="discuss-on-twitter-button view-reactions" href="https://twitter.com/<?php echo $author_handle; ?>/status/<?php echo urlencode($tweet_id); ?>" target="_twitter-<?php echo $id; ?>">
      View Discussions
    </a>
    <?php else : ?>
      <a class="discuss-on-twitter-button reply-on-twitter" href="https://twitter.com/intent/tweet?url=<?php echo $permalink; ?>&text=@<?php echo $author_handle; ?>">
      <span class="dashicons dashicons-twitter"></span>
      Discuss on Twitter
    </a>
    <a class="discuss-on-twitter-button view-reactions" href="https://twitter.com/search?q=<?php echo urlencode($permalink); ?>" target="_twitter-<?php echo $id; ?>">
      View Discussion
    </a>
  <?php endif; ?>

  <?php else: ?>
    <a class="discuss-on-twitter-button view-and-reply" href="https://twitter.com/search?q=<?php echo urlencode($permalink); ?>" target="_twitter-<?php echo $id; ?>">
      View Discussions on Twitter
    </a>
  <?php endif; ?>
 </div>



<?php
}

/*
*
* Add link to settings page from plugins page
*
*/
add_filter( 'plugin_row_meta', 'dot_plugin_row_meta', 10, 2 );
function dot_plugin_row_meta( $links, $file ) {
    if ( plugin_basename( __FILE__ ) == $file ) {
        $row_meta = array(
          'docs'    => '<a href="' . esc_url( '/wp-admin/options-general.php?page=discuss-on-twitter' ) . '" aria-label="' . esc_attr__( 'Docs and Instructions', 'domain' ) . '">' . esc_html__( 'View details', 'domain' ) . '</a>'
        );

        return array_merge( $links, $row_meta );
    }
    return (array) $links;
}

/*
*
* Settings page for this plugin
*
*/
function dot_register_options_page() {
  add_options_page('Discuss on Twitter', 'Discuss on Twitter', 'manage_options', 'discuss-on-twitter', 'dot_options_page');
}
add_action('admin_menu', 'dot_register_options_page');

function dot_options_page()
{
  $wpt_active = False;
  $active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
  if ( in_array( 'wp-to-twitter/wp-to-twitter.php', $active_plugins ) ) {
      $wpt_active = True;
  }
  ?>

  <h1>Discuss on Twitter</h1>
  <p>This plugin allows you to use Twitter as the commenting system for you blog.  See examples at <a href="http://nickgrossman.is">NickGrossman.is</a> and <a href="https://avc.com">AVC.com</a></p>

  <h2>Basic Installation:</h2>

  <p>The simplest way to start is to install and activate this plugin, which will enable a "View Reactions on Twitter" button as a template tag.</p>

  <p>Include the following template code where you would like the "View Reactions on Twitter" button to appear (single.php or similar):</p>

  <pre>
    <code><?php $code_sample = htmlspecialchars("<?php if (function_exists('discuss_on_twitter_button')) { discuss_on_twitter_button(); } ?>"); echo $code_sample; ?></code>
  </pre>

  <p>Inserting this button tag will link to a Twitter search looking for the URL of your post.</p>

  <p>Note: the Simple installation does not enable auto-tweeting of new posts, or the prompting of direct replies to a tweet about the post.  But it is the easiest way to get started.</p>

  <h2>Advanced Installation</h2>

  <p>The advanced installation enables auto-tweeting of new posts from your account and direct connection between the "Reply on Twitter" button and the tweet about the post.</p>

  <p><b>Standing on the shoulders of giants</b>: The advanced installation builds upon the awesome <a href="https://wordpress.org/plugins/wp-to-twitter/" target="_blank">WP to Twitter</a> plugin by <a href="http://www.joedolson.com/">Joseph C. Dolson</a>.</p>

  <?php if (!$wpt_active): ?>
    <a class="button" href="/wp-admin/plugin-install.php?s=WP+to+Twitter&tab=search&type=term" target="_blank">Install WP to Twitter to continue with advanced installation</a>
  <?php endif; ?>

  <?php if($wpt_active):?>

    <p><span style="background: yellow">WP to Twitter is Active.  Sweet!</span></p>

    <ol>
      <li>Install and activate the <a href="https://wordpress.org/plugins/wp-to-twitter/" target="_blank">WP to Twitter</a> plugin.</li>

      <li>2. Set up your <a href="https://developer.twitter.com/" target="_blank">Twitter Developer account</a> and create a new app for your site.  This may require waiting for approval from Twitter if you are setting up your developer account for the first time.</li>

      <li>3. In the WP to Twitter settings under "Twitter Connection" configure your API keys and secrets, as per the WP to Twitter documentation.</li>
    </ol>

    <p>In the WP to Twitter settings under "Basic Settings", ensure that "Update when Posts are published" is checked.  This will ensure that a new tweet is posted to your timeline when you publish a post. (see screenshots)</p>

    <p>In the Wordpress create/edit post screen, you'll notice a new sidebar menu called "WP to Twitter".  This includes an option to "Tweet" or "Don't Tweet" when a new post is published. (see screenshots)</p>

    <p>For new posts, where tweets are created upon publishing, the "Reply on Twitter" button will tee-up a reply to the original tweet.</p>

    <p>For older posts which do not have a tweet associated with them, the "Reply on Twitter" button will @mention your account, including the link to the post, but will not be formed as a reply to a tweet about the post.</p>

  <?php endif; ?>

  <?php
}


/*
*
* Save Tweet ID to DB
*
*/
function dot_save_tweet_id($connection, $id) {
  update_post_meta( $id, '_jd_wp_tweet_id', $connection->body->id);
}
add_action('wpt_tweet_posted', 'dot_save_tweet_id', 1, 2);
/*
*  hooks into:
*  wp-to-twitter/wp-to-twitter.php line 487:
*  do_action( 'wpt_tweet_posted', $connection, $id );
*/


