<?php
/**
 * Template Name: Sortimentsida
 *
 *
 * @author Magnus Strand
 */
global $woo_options;
get_header();
?>
<?php woo_content_before(); ?>
<div id="content" class="col-full">
  <div id="main-sidebar-container">    
    <?php woo_main_before(); ?>
    
    <?php
        if (have_posts()) {
            the_post();         
            require_once 'class.plantview2controller.php';
            $controller = new PlantView2Controller();
            $controller->_dispatch();
        }
    ?>
    <?php woo_main_after(); ?>
  </div><!-- /#main-sidebar-container -->         
</div><!-- /#content -->
<?php woo_content_after(); ?>
<?php get_footer(); ?>