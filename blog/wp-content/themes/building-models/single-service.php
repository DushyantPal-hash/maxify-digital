<?php
get_header();
?>

<?php if (have_posts()) the_post(); ?>

<!-- START banner container -->
<?php get_template_part('template-parts/section', 'banner-content'); ?>
<!-- END banner container -->

<!-- START Page Content -->
<section class="service-details service-content-section" role="main">
    <!-- START Single CPT -->
    <article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
        <div class="container">
            <div class="row">
                <!-- Content -->
                <div class="col-md-10">
                    <div class="post-content">
                        <div class="main-content-box"> <?php the_content(); ?> </div>
                        <?php // @todo comment remove // edit_post_link('edit', '<p>', '</p>'); ?>
                    </div>
                </div>
            </div>
        </div>
    </article>
    <!-- END Single CPT -->
</section>

<!-- END Page Content -->
<?php if (get_post_meta(get_the_ID(), 'info_blocks_switch', true) != 'off'): ?>
    <section class="steps-block white-bg">
        <div class="container">
            <?php
            $info_blocks = get_post_meta(get_the_ID(), 'info_blocks_list', true);
            if (!empty($info_blocks)):
                ?>
                <div class="row">
                    <?php foreach ($info_blocks as $info_block): ?>
                        <div class="col-md-9">
                            <div class="block-title">
                                <h4 class="primary-color">
                                    <?php echo $info_block['title']; ?>
                                </h4>
                            </div>
                            <div class="block-content">
                                <?php echo $info_block['block_content']; ?>
                            </div>
                            <div class="block-img">
                                <?php
                                $image_id = $info_block['featured_section_banner'];
                                get_image_from_block_list($image_id);
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?php get_footer(); ?>