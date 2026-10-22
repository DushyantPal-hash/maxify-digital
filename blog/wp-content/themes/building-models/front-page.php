<?php
/**
 * @package WordPress
 * @subpackage Building Models
 */
?>
<?php get_header(); ?>

<?php
// Show the selected frontpage content.
if (have_posts()) :
    while (have_posts()) : the_post();
        ?>
        <article id="page-<?php the_ID(); ?>" <?php post_class(); ?> role="article">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="main-content-box"> <?php the_content(); ?> </div>
                        <?php // @todo - remove comment after theme finalized ?> 
                        <?php // edit_post_link('edit', '<p>', '</p>'); ?>
                    </div>
                </div>
            </div>
        </article>
        <?php
    endwhile;
endif;
?>

<!-- START home services section -->
<?php get_template_part('template-parts/home/home', 'services'); ?>
<!-- END home services section -->

<!-- START home featured section -->
<?php get_template_part('template-parts/home/home', 'featured'); ?>
<!-- END -->

<!-- START Gallery section -->
<?php get_template_part('template-parts/section', 'gallery'); ?>
<!-- END -->

<?php get_footer(); ?>