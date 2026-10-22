<!-- START Page Content -->
<section class="section-box blog-section" role="main">
    <?php if (have_posts()): ?>
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <!-- Blog Content -->
                    <?php get_template_part('template-parts/content', 'blog'); ?>
                </div>
                <!-- Right Sidebar -->
                <aside class="col-md-4">
                    <div class="sidebar blog_sidebar right_sidebar">
                        <?php dynamic_sidebar('sidebar-1'); ?>
                    </div>
                </aside>
            </div>
        </div>
    <?php else: ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</section>