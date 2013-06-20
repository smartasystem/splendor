<?php
/**
 * Footer Template
 *
 * Here we setup all logic and XHTML that is required for the footer section of all screens.
 *
 * @package WooFramework
 * @subpackage Template
 */
global $woo_options;
woo_footer_top();
woo_footer_before();
?>
<div id="footer-puffs">
  <div id="puff1" class="puff">
    <img src="http://splendor.dev/wp-content/uploads/2013/06/Danger_Mouse_003_430-100x100.jpg" alt="">
    <h3>Växtnyhter</h3>
    <p>jhe jhweb uisjh ssjhwjbk kabkb kbakbwjbaj ja</p>
    <a href="">Ladda hem PDF</a>
  </div>
  <div id="puff2" class="puff">
    <img src="http://splendor.dev/wp-content/uploads/2013/06/Danger_Mouse_003_430-100x100.jpg" alt="">
    <h3>Växtnyhter</h3>
    <p>jhe jhweb uisjh ssjhwjbk kabkb kbakbwjbaj ja</p>
    <a href="">Ladda hem PDF</a>
  </div>
  <div id="puff3" class="puff">
    <img src="http://splendor.dev/wp-content/uploads/2013/06/Danger_Mouse_003_430-100x100.jpg" alt="">
    <h3>Växtnyhter</h3>
    <p>jhe jhweb uisjh ssjhwjbk kabkb kbakbwjbaj ja</p>
    <a href="">Ladda hem PDF</a>
  </div>
  <div id="puff4" class="puff">
    <img src="http://splendor.dev/wp-content/uploads/2013/06/Danger_Mouse_003_430-100x100.jpg" alt="">
    <h3>Växtnyhter</h3>
    <p>jhe jhweb uisjh ssjhwjbk kabkb kbakbwjbaj ja</p>
    <a href="">Ladda hem PDF</a>
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