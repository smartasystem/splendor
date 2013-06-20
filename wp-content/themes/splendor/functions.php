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
function rep_scripts() {
  wp_enqueue_script("jquery");
}

add_action('wp_enqueue_scripts', 'rep_scripts');



add_action( 'woo_sidebar_inside_before', 'searchfield_in_menu', 10 );
function searchfield_in_menu() {
  echo <<<EOD
<div class="clear"></div>
  <div id="plant-search">
    <h3>Hitta snabbt i vårt sortiment</h3>
    <form action="/" method="GET">
      <input type="hidden" value="7" name="page_id">
      <input type="hidden" value="2" name="vy">
      <input type="text" size="20" name="sok" id="sok" placeholder="Svenskt eller latinskt namn" class="field">
      <div class="clear"></div>
      <input type="submit" value="Sök" name="submit1">
    </form>
  </div>
<div class="clear"></div>
EOD;
  
  
}



