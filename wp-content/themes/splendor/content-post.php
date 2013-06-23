<?php
/**
 * Post Content Template
 *
 * This template is the default page content template. It is used to display the content of the
 * `single.php` template file, contextually, as well as in archive lists or search results.
 *
 * @package WooFramework
 * @subpackage Template
 */
/**
 * Settings for this template file.
 *
 * This is where the specify the HTML tags for the title.
 * These options can be filtered via a child theme.
 *
 * @link http://codex.wordpress.org/Plugin_API#Filters
 */
global $woo_options;

$title_before = '<h1 class="title">';
$title_after = '</h1>';

if (!is_single()) {

  $title_before = '<h2 class="title">';
  $title_after = '</h2>';

  $title_before = $title_before . '<a href="' . get_permalink(get_the_ID()) . '" rel="bookmark" title="' . the_title_attribute(array('echo' => 0)) . '">';
  $title_after = '</a>' . $title_after;
}

$page_link_args = apply_filters('woothemes_pagelinks_args', array('before' => '<div class="page-link">' . __('Pages:', 'woothemes'), 'after' => '</div>'));

woo_post_before();
?>
<div <?php post_class(); ?>>

<?php
woo_post_inside_before();
if ($woo_options['woo_post_content'] != 'content' AND !is_singular())
  woo_image('width=' . $woo_options['woo_thumb_w'] . '&height=' . $woo_options['woo_thumb_h'] . '&class=thumbnail ' . $woo_options['woo_thumb_align']);
//the_title($title_before, $title_after);
woo_post_meta();
?>
  <div class="entry">
  <?php
  if ($woo_options['woo_post_content'] == 'content' || is_single()) {
    the_content(__('Continue Reading &rarr;', 'woothemes'));

    $cats = get_the_category_list();
    echo '<div class="cat-list"><span>KATEGORIER: </span>' . $cats . "</div>";
    echo '<div class="fix"></div>';
    $tags = get_the_tag_list("", ", &nbsp;");
    echo '<div class="cat-list"><span>ETIKETTER: </span>' . $tags . "</div>";
  } else {


    $title = $post->post_title;
    $permalink = get_permalink($post->ID);
    $excerpt = strip_tags($post->post_content);
    $excerpt = mb_substr($excerpt, 0, 200);
    $img = get_the_post_thumbnail($post->ID, 'medium');

    echo '<h2>' . $title . '</h2>';
    echo $img;
    echo '<p>' . $excerpt . '</p>';
    echo '<a href=' . $permalink . '">Läs mer &raquo;</a>';

    
    
  }
  if ($woo_options['woo_post_content'] == 'content' || is_singular())
    wp_link_pages($page_link_args);
  ?>
  </div><!-- /.entry -->
  <div class="fix"></div>
    <?php
    woo_post_inside_after();
    ?>
</div><!-- /.post -->
<?php
woo_post_after();
$comm = $woo_options['woo_comments'];
if (( $comm == 'post' || $comm == 'both' ) && is_single()) {
  comments_template();
}
?>