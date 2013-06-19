<?php
/**
 * Template Name: Sortimentsida
 *
 *
 * @author Magnus Strand
 */
!empty($_REQUEST['cat']) ? $cat = $_REQUEST['cat'] : $cat = '';
!empty($_REQUEST['type']) ? $type = $_REQUEST['type'] : $type = 'salj';
global $woo_options;
get_header();
?>
<?php woo_content_before(); ?>
<div id="content" class="col-full">
  <div id="main-sidebar-container">    
    <?php woo_main_before(); ?>
    <div id="main">                     
      <?php
      if (have_posts()) {
        while (have_posts()) {
          the_post();
          ?>
          <div <?php post_class(); ?> >
<?php
    the_content();
    require_once 'class.plantview2controller.php';

    $controller = new PlantView2Controller();

    $controller->_dispatch();
?>
           </div><!-- /.post -->
          <?php
        }
      }
      ?>     
    </div><!-- /#main -->
    <?php woo_main_after(); ?>
    <?php //get_sidebar();   ?>
  </div><!-- /#main-sidebar-container -->         
  <div id="sidebar">
    <?php include_once 'snippet_list_fynd.php'; ?>
    <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("fyndhyllan")) : endif; ?>    
  </div>  
</div><!-- /#content -->
<?php woo_content_after(); ?>
<?php get_footer(); ?>