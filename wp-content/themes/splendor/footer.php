<?php
/**
 * Footer Template
 *
 * @author Kristain Erendi
 * @package Splendor
 * @subpackage Template
 */
global $woo_options;
$page_promo = get_page_by_title("start");
$pageId = $page_promo->ID;
woo_footer_top();
woo_footer_before();
?>
<div id="footer-puffs">
  <div id="puff1" class="puff">
    <img src="<?php the_field("bild1", $pageId); ?>" alt="">
    <h3><?php the_field("rubrik1", $pageId); ?></h3>
    <p><?php the_field("text1", $pageId); ?></p>
    <a href="<?php the_field("lank1", $pageId); ?>"><?php the_field("lanktext1", $pageId); ?></a>
  </div>
  <div id="puff2" class="puff">
    <img src="<?php the_field("bild2", $pageId); ?>" alt="">
    <h3><?php the_field("rubrik2", $pageId); ?></h3>
    <p><?php the_field("text2", $pageId); ?></p>
    <a href="<?php the_field("pagelink2", $pageId); ?>"><?php the_field("lanktext2", $pageId); ?></a>
  </div>
  <div id="puff3" class="puff">
    <img src="<?php the_field("bild3", $pageId); ?>" alt="">
    <h3><?php the_field("rubrik3", $pageId); ?></h3>
    <p><?php the_field("text3", $pageId); ?></p>
    <a href="<?php the_field("pagelink3", $pageId); ?>"><?php the_field("lanktext3", $pageId); ?></a>
  </div>
  <div id="puff4" class="puff">
    <img src="<?php the_field("bild4", $pageId); ?>" alt="">
    <h3><?php the_field("rubrik4", $pageId); ?></h3>
    <p><?php the_field("text4", $pageId); ?></p>
    <a href="<?php the_field("pagelink4", $pageId); ?>"><?php the_field("lanktext4", $pageId); ?></a>
  </div>
</div>
<div class="fix"></div>
<div id="footer" class="col-full">	
  <?php woo_footer_inside(); ?>    
  <div id="copyright" class="col-left">
    <?php woo_footer_left(); ?>
  </div>

  <div id="credit" class="col-right">
    <p><a href="<?php echo get_permalink(get_page_by_path('kontakta-oss')) ?>">Kontakta oss</a> | <a href="<?php echo get_permalink(get_page_by_path('om-oss')) ?>">Om oss</a> | <a href="<?php echo get_permalink(get_page_by_path('jobba-hos-oss')) ?>">Jobba hos oss</a></p>
  </div>

</div><!-- /#footer  -->

<?php woo_footer_after(); ?>    

</div><!-- /#wrapper -->

<div class="fix"></div><!--/.fix-->

<?php wp_footer(); ?>
<?php woo_foot(); ?>
</body>
</html>