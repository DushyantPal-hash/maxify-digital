 <header class="entry-header container entry-header--style-2">
     <div class="<?php echo esc_attr((!has_post_thumbnail() && $args['has_sidebar']) ? "" : "max-w-screen-md mx-auto"); ?>">
         <?php get_template_part('template-parts/single-header/single-header-content',  null, ['has_excerpt' =>  true]); ?>
     </div>
     <?php ncmaz_post_thumbnail(); ?>
 </header><!-- .entry-header -->