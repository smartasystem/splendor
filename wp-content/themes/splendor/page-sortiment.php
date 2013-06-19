<?php
/**
 * Template Name: Fynd kategori sida
 *
 * This is the category list page for Fyndhyllan.
 * Fyndhyllan is a buy and sell market aka "mini blocket"
 *
 * @author Kristain Erendi
 * @package Smakformat
 * @subpackage Template
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