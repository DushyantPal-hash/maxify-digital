<!-- START Page Content -->
<section class="section-box blog-section" role="main">
    <?php if (have_posts()): ?>
        <div class="container">
            <div class="row">
                <!-- Left Sidebar -->
                <aside class="col-md-4">
                    <div class="sidebar blog_sidebar left_sidebar">
                        <?php dynamic_sidebar('sidebar-1'); ?>
                    </div>
                </aside>

                <div class="col-md-8">
                    <!-- Blog Content -->
                    <?php get_template_part('template-parts/content', 'blog'); ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</section>