<?php
/* Template Name: About */
?>
<?php get_header(); ?>

<?php global $custom; ?>
<?php if (have_posts()) the_post(); ?>

<!-- Start Main Page Section -->
<section class="page-content about-main-container" role="main" aria-labelledby="about">

    <article id="page-<?php the_ID(); ?>" <?php post_class(); ?> role="article">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <?php $title_switch = get_post_meta($id, 'custom_page_title_switch', true); 
                    if ($title_switch != 'off') { ?>
                    <div class="title-container">
                        <?php
                        $icon_id = get_post_meta(get_the_ID(), 'content_title_icon', true);
                        $icon_url = wp_get_attachment_image_src($icon_id, 'icon-thumb');
                        if (!empty($icon_url)) {
                            $icon_img_url = $icon_url[0];
                            $icon_alt = get_post_meta($icon_id, '_wp_attachment_image_alt', true);
                            ?>
                            <img class="icon-thumb" src="<?php echo $icon_img_url; ?>" alt="<?php echo $icon_alt; ?>"  /> 
                            <?php
                        }
                        ?>
                        <h2 class="main-title">
                            <?php
                            the_custom_title();
                            ?>
                        </h2>
                    </div>
                    <?php } ?>
                </div>
                <!-- Page Content -->
                <div class="<?php echo (has_post_thumbnail()) ? 'col-md-7' : 'col-md-12'; ?>">
                    <div class="post-content">

                        <div class="main-content-box"> <?php the_content(); ?> </div>
                        <?php if (strlen(get_post_meta(get_the_ID(), 'button_title', true))): ?>

                            <?php
                            $button_link_1 = get_post_meta(get_the_ID(), 'button_link', true);
                            ?>
                    <p><a href="<?php echo $button_link_1; ?>" class="button"><?php echo get_post_meta(get_the_ID(), 'button_title', true); ?></a></p>
                        <?php endif; ?>
                        <?php edit_post_link('edit', '<p>', '</p>'); ?>
                    </div>
                </div>

                <!-- Featured Image -->
                <?php if (has_post_thumbnail()) { ?>
                    <div class="col-md-5">
                        <div class="page-thumb" role="img">
                            <?php the_post_thumbnail('page_featured', array('class' => 'featured-image')); ?>
                        </div>
                    </div>
                <?php } ?>

            </div>
        </div>
    </article>
    <!-- END Single CPT -->
</section>

<!-- START Gallery section -->
<?php get_template_part('template-parts/section', 'contact'); ?>
<!-- END -->
    
<!-- START Gallery section -->
<?php get_template_part('template-parts/section', 'gallery'); ?>
<!-- END -->

<!-- END Page Content -->
<?php get_footer(); ?>