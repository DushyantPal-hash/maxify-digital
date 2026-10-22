<?php
/* Template Name: Services */
?>
<?php get_header(); ?>

<?php if (have_posts()) the_post(); ?>
<?php $button_label = get_post_meta(get_the_ID(), 'posts_button_title', true); ?>
<section class="page-content about-main-container" role="main">
    <article role="main">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <?php
                    $title_switch = get_post_meta($id, 'content_page_title_switch', true);
                    if ($title_switch != 'off') {
                        ?>
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
                    </div>
                </div>
            <?php } ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="main-content-box"> <?php the_content(); ?> </div>
                    <?php // @todo remove comment //edit_post_link('edit', '<p>', '</p>'); ?>
                </div>
            </div>
        </div>
    </article>
</section>
<?php
// Fetch All Tutorials Categories & List their posts
$service_categories = get_terms(array(
    'taxonomy' => 'service_type',
    'parent' => 0
        ));

foreach ($service_categories as $service_category) {
    ?>
    <section class="service-list-section" role="main">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="title-container">
                        <?php
                        $icon_id = get_term_meta($service_category->term_id, 'category-image-id', true);
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
                            <?php echo $service_category->name; ?>
                        </h2>
                    </div>
                </div>
            </div>
            <?php if (strlen(trim($service_category->description)) > 0) { ?>
                <div class="row">
                    <div class="col-md-12">
                        <p><?php echo $service_category->description; ?></p>
                    </div>
                </div>
            <?php } ?>
            <?php
            /* Services List */
            $args = array(
                'post_type' => array('service'),
                'tax_query' => array(
                    array(
                        'taxonomy' => 'service_type',
                        'field' => 'slug',
                        'terms' => $service_category->slug,
                    ),
                ),
                'post_status' => array('publish'),
                'nopaging' => false,
                'posts_per_page' => '2',
            );

            $services = new WP_Query($args);

            if ($services->have_posts()):
                ?>

                <div class="row">
                    <?php
                    while ($services->have_posts()) :
                        $services->the_post();
                        ?>
                        <!-- START Single CPT -->
                        <div class="col-md-6 main-col article-block">
                            <article id="service-<?php the_ID(); ?>" <?php post_class('cpt-single-item'); ?> >

                                <!-- CPT Featured Image -->
                                <?php if (has_post_thumbnail()) { ?>
                                    <div class="featured-image-box">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('service_featured', array('class' => 'featured-image')); ?>
                                        </a>
                                    </div>
                                <?php } ?>
                                <header>
                                    <div class="page-title">
                                        <h4> <a href="<?php the_permalink(); ?>"><?php the_custom_title(); ?> </a></h4>
                                    </div>
                                </header>                       

                                <!-- CPT Content -->
                                <div class="post-content post-excerpt-box">
                                    <?php
                                    the_excerpt();
                                    ?>
                                </div>
                                <a class="primary-color" href="<?php the_permalink(); ?>">
                                    <?php echo $button_label; ?>
                                </a>
                            </article>
                            <!-- END Single CPT -->
                        </div>
                    <?php endwhile; ?>

                    <!-- CPT Pagination -->
                    <?php
                    the_posts_pagination(array(
                        'prev_text' => '<span class="dashicons dashicons-arrow-left-alt"></span><span class="screen-reader-text">' . sprintf('Previous page') . '</span>',
                        'next_text' => '<span class="dashicons dashicons-arrow-right-alt"></span><span class="screen-reader-text">' . sprintf('Next page') . '</span>',
                        'before_page_number' => '<span class="meta-nav screen-reader-text">' . sprintf('Page') . ' </span>',
                    ));
                    ?>

                <?php else: ?>
                    <?php get_template_part('template-parts/content', 'none'); ?>
                <?php endif; ?>

                <?php
                /* Restore original Post Data */
                wp_reset_postdata();
                ?>

            </div>
        </div>
    </section>
    <?php
}
?>

<?php get_footer(); ?>