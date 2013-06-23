<?php

/**
 * Description: All below is added by //krillo and //Magnus
 * 1. cleanup the admin page
 * 
 * Date: 2013-06-19
 * Author: Kristian Erendi 
 * URI: http://reptilo.se 
 */
add_action('wp_dashboard_setup', 'hide_wp_welcome_panel');
add_action('wp_dashboard_setup', 'remove_dashboard_widgets');

function hide_wp_welcome_panel() {
  if (current_user_can('edit_theme_options'))
    $ah_clean_up_option = update_user_meta(get_current_user_id(), 'show_welcome_panel', false);
}

function remove_dashboard_widgets() {
  // Ta bort widgets i vänsterkolumnen
  remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal'); // Inkommande länkar
  remove_meta_box('dashboard_plugins', 'dashboard', 'normal'); // Tillägg
  remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal'); // Senaste kommentarer
  remove_meta_box('dashboard_right_now', 'dashboard', 'normal'); // Just nu
  // Ta bort widgets i högerkolumnen
  remove_meta_box('dashboard_primary', 'dashboard', 'side'); // WordPress Blogg
  remove_meta_box('dashboard_quick_press', 'dashboard', 'side'); // SnabbPress
  remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side'); // Senaste utkasten
  remove_meta_box('dashboard_secondary', 'dashboard', 'side'); // Andra WordPressnyheter
}

/**
 * Enqueue some java scripts
 */
function sp_scripts() {
  wp_enqueue_script("jquery");
}

add_action('wp_enqueue_scripts', 'sp_scripts');



add_action('woo_sidebar_inside_before', 'sp_searchfield_in_menu', 10);

function sp_searchfield_in_menu() {
  echo <<<EOD
<div class="clear"></div>
  <div id="plant-search">
    <h3>Hitta snabbt i vårt sortiment</h3>
    <form action="/" method="GET">
      <input type="hidden" value="7" name="page_id">
      <input type="hidden" value="2" name="vy">
      <input type="text" name="sok" id="sok" placeholder="Svenskt eller latinskt namn" class="field">
      <div class="clear"></div>
      <input type="submit" value="Sök" name="submit1">
    </form>
  </div>
<div class="clear"></div>
EOD;
}

/**
 * Display as a nice excerpt list
 * @param type $recent
 */
function rep_display_post_excerpt_li($nbr) {
  $args = array('numberposts' => $nbr);
  $recent_posts = wp_get_recent_posts($args);
  echo '<ul id="rep-recent-posts">';
  foreach ($recent_posts as $recent) {
    $title = $recent["post_title"];
    $permalink = get_permalink($recent["ID"]);
    $excerpt = strip_tags($recent["post_content"]);
    $excerpt = mb_substr($excerpt, 0, 200);
    $img_url = $recent["ID"];
    $img = get_the_post_thumbnail($recent["ID"], 'thumbnail');
    echo <<<POST
<li>
  {$img}
  <h2>{$title}</h2>
  <p>{$excerpt}</p>
  <a href="{$permalink}">Läs mer &raquo;</a>
</li>
POST;
  }
  echo '</ul>';
 }



/*
add_action( 'woo_sidebar_before', 'sp_breadcrumbs', 10 );
function sp_breadcrumbs() {
  woo_breadcrumbs();
}
*/

/*
add_action( 'woo_footer_before', 'footer_separator', 10 );
function footer_separator() {
  echo '<div class="clear"></div><div class="separator">Tjoho</div>';
}
*/
 
 
//custom post type
add_action('init', 'sp_create_post_type');

function sp_create_post_type() {
  register_post_type('visitkort',
          array(
              'labels' => array(
                  'name' => __('visitkort'),
                  'singular_name' => __('visitkort')
              ),
              'public' => true,
              'has_archive' => false,
          )
  );
}
 