<?php get_header(); ?>

<?php
$page_layout = get_post_meta(get_the_id(), 'page_sidebar', true);
?>
<?php if (have_posts()) the_post(); ?>

<!-- START Page Content -->
<section class="page-content" role="main">

    <!-- START Single CPT -->
    <article id="page-<?php the_ID(); ?>" <?php post_class(); ?> role="article">
        <div class="container">
            <div class="row">
                <?php if ($page_layout == 'right-sidebar' || $page_layout == 'left-sidebar'): ?>

                    <?php if ($page_layout == 'left-sidebar'): ?>

                        <!-- Left Sidebar -->
                        <aside class="col-md-4 sidebar blog_sidebar left_sidebar" role="complementary">
                            <?php dynamic_sidebar('sidebar-1'); ?>
                        </aside>

                    <?php endif; ?>

                    <div class="col-md-8">
                    <?php else: ?>
                        <div class="col-md-12">

                        <?php endif; ?>
                        <!-- Page Content -->
                        <div class="post-content-box" role="main">
                            <?php
                            $title_switch = get_post_meta($id, 'custom_page_title_switch', true);
                            if ($title_switch != 'off') {
                                ?>
                                <h1 class="main-title">
                                    <?php
                                    the_custom_title();
                                    ?>
                                </h1>
                            <?php } ?>
                                <?php if (has_post_thumbnail()) { ?>
                                <div class="featured-image-box">
                                <?php the_post_thumbnail('content_large'); ?>
                                </div>
                            <?php } ?>
                            <div class="main-content-box"> <?php the_content(); ?> </div>
<?php edit_post_link('edit', '<p>', '</p>'); ?>
                        </div>
                    </div>

<?php if ($page_layout == 'right-sidebar'): ?>

                        <!-- Right Sidebar -->
                        <aside class="col-md-4 sidebar blog_sidebar" role="complementary">
    <?php dynamic_sidebar('sidebar-1'); ?>
                        </aside>

<?php endif; ?>

                </div>
            </div>
    </article>
    <!-- END -->
</section>

<!-- START generic page section -->
<?php get_template_part('template-parts/section', 'generic'); ?>
<!-- END generic page section  -->

<!-- END Page Content -->
<?php get_footer(); ?>