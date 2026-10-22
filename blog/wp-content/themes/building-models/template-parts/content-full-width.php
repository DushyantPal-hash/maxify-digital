<!-- START Page Content -->
<section class="section-box blog-section" role="main">
    <?php if (have_posts()): ?>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <!-- Blog Content -->
                    <?php get_template_part('template-parts/content', 'blog'); ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</section>