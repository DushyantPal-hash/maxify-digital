<section class="section-box" role="main">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-12">
                <div class="section-title">
                    <header class="page-header">
                        <h1 class="main-title">
                            <?php
                            if (is_home() && !is_front_page()) {
                                $blog_page_id = get_option('page_for_posts');
                                wp_title();
                            } else if (is_search()) {
                                echo sprintf(__('Search: %s', OC_TEXT_DOMAIN), get_search_query());
                            } else {
                                the_archive_title();
                            }
                            ?>
                        </h1>
                    </header><!-- .page-header -->
                </div>
                <?php if (strlen(get_the_archive_description())): ?>
                    <div class="section-content">
                        <?php the_archive_description('<div class="taxonomy-description">', '</div>'); ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</section>