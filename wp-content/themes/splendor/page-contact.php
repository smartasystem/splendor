<?php
/*
  Template Name: Kontaktsida
 */

global $woo_options;
get_header();
?>
<?php woo_content_before(); ?>
<div id="content" class="col-full">
  <div id="main-sidebar-container">    
    <?php woo_main_before(); ?>
    <div id="main">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
          <?php $slug = $post->post_name; //echo $slug; ?>
          <h1><?php the_title();?></h1>
          <?php the_content(); ?>
          <?php
        endwhile;
      endif;
      ?>

      <section class="" id="contact-info">
        <?php
        $args = array('post_type' => 'visitkort', 'posts_per_page' => 100);
        $loop = new WP_Query($args);
        while ($loop->have_posts()) : $loop->the_post();
          $name = get_field('namn');
          $position = get_field('titel');
          $email = get_field('email');
          $phone = get_field('telefon');
          $img = get_field('bild');
          $department = get_field('avdelning');
          if($department == $slug): ?>
            <section class="contact-info-box">
              <img class="contact-info-img" src="<?php echo $img; ?>" alt="">
              <div class="contact-info-name contact-info"><?php echo $name; ?></div>
              <div class="contact-info-position contact-info"><?php echo $position; ?></div>
              <div class="contact-info-phone contact-info">Direkt: <?php echo $phone; ?></div>
              <div class="contact-info-email contact-info"><a href="mailto:<?php echo $email; ?>">E-post<!--?php echo $email; ?--></a></div>
            </section>
          <?php endif; ?>
        <?php endwhile; wp_reset_query();?>
      </section>

    </div> <!-- main -->
    <?php get_sidebar(); ?>
    <?php woo_main_after(); ?>
  </div><!-- /#main-sidebar-container -->         
</div><!-- /#content -->
<?php woo_content_after(); ?>
<?php get_footer(); ?>