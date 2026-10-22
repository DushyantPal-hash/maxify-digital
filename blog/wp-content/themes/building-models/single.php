<?php get_header(); ?>
<!-- Blog Layout -->
<?php $post_layout = ot_get_option('single_post_layout_radio'); ?>
<?php if (have_posts()) the_post(); ?>

<!-- START Page Content -->
<section class="post-details" role="main">

    <!-- START Single CPT -->
    <article id="page-<?php the_ID(); ?>" <?php post_class(); ?> role="article">
        <div class="container">
            <div class="row">

                <?php if ($post_layout == 'right-sidebar' || $post_layout == 'left-sidebar'): ?>

                    <?php if ($post_layout == 'left-sidebar'): ?>

                        <!-- Left Sidebar -->
                        <aside class="col-md-4 sidebar blog_sidebar left_sidebar" role="complementary">
                            <?php dynamic_sidebar('sidebar-1'); ?>
                        </aside>

                    <?php endif; ?>

                    <div class="col-md-8">
                    <?php else: ?>
                        <div class="col-md-12">

                        <?php endif; ?>

                        <!-- Post Content -->
                        <?php get_template_part('template-parts/content', 'single'); ?>

                    </div>

                    <?php if ($post_layout == 'right-sidebar'): ?>

                        <!-- Right Sidebar -->
                        <aside class="col-md-4 sidebar blog_sidebar" role="complementary">
                            <?php dynamic_sidebar('sidebar-1'); ?>
                        </aside>

                    <?php endif; ?>
                </div>
            </div>
    </article>
    <!-- END Single CPT -->
</section>


<!-- END Page Content -->

<?php get_footer(); ?>