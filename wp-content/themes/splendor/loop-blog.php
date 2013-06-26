<?php
/**
 * Loop - Blog
 *
 * This is the loop file used on the "Blog" page template.
 *
 * @package WooFramework
 * @subpackage Template
 */
global $more; $more = 0; 

woo_loop_before();

// Fix for the WordPress 3.0 "paged" bug.
$paged = 1;
if ( get_query_var( 'paged' ) ) { $paged = get_query_var( 'paged' ); }
if ( get_query_var( 'page' ) ) { $paged = get_query_var( 'page' ); }
$paged = intval( $paged );

$query_args = array(
					'post_type' => 'post', 
					'paged' => $paged
				);

$query_args = apply_filters( 'woo_blog_template_query_args', $query_args ); // Do not remove. Used to exclude categories from displaying here.

remove_filter( 'pre_get_posts', 'woo_exclude_categories_homepage', 10 );

query_posts( $query_args );
		
if ( have_posts() ) { $count = 0;
?>

<div class="fix"></div>

<?php
  //krillo print the page contents first then list all the blogg items
  echo '<h3 class="page-blogg-list">' . $posts[0]->post_content . '</h3>';
	while ( have_posts() ) { the_post(); $count++;
		woo_get_template_part( 'content', get_post_type() );

	} // End WHILE Loop
} else {
	get_template_part( 'content', 'noposts' );
} // End IF Statement

woo_loop_after();
woo_pagenav();
wp_reset_query();   //krillo - reset is set down here to be able to preserve the pagination..
?>
