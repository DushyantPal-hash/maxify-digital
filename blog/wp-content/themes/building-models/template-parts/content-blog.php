<?php $blog_layout = ot_get_option('blog_layout_radio'); ?>
<div class="cpt-listing <?php echo ($blog_layout == 'right-sidebar' || $blog_layout == 'left-sidebar') ? 'has-sidebar' : ''; ?>">

    <?php while (have_posts()): the_post(); ?>
        <div class="row">

            <!-- Featured Image -->
            <?php if (has_post_thumbnail()) { ?>
                <div class="col-md-4">
                    <div class="blog-thumb" role="img">
                        <?php the_post_thumbnail('page_featured', array('class' => 'featured-image')); ?>
                    </div>
                </div>
            <?php } ?>

            <div class="cpt-content article-block <?php echo (has_post_thumbnail()) ? 'col-md-8' : 'col-md-12'; ?>">

                <!-- START Single CPT -->
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
                    <header>
                        <h2 class="main-title">
                            <a href="<?php the_permalink(); ?>" >
                                <?php the_title(); ?>
                            </a>
                        </h2>
                    </header>
                    <!-- CPT Metadata -->
                    <?php if ('off' != ot_get_option('show_post_meta')): ?>
                        <?php if (!is_page(get_the_ID())): ?>
                            <?php get_template_part('template-parts/post', 'meta'); ?>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- CPT Content -->
                    <p class="post-content main-content-box">
                        <?php
                        $content = get_the_content();
                        echo wp_trim_words($content, '50');
                        ?>
                    </p>
                    <div class="">
                        <a class="button" href="<?php the_permalink(); ?>" >
                            <?php _e('Read More', OC_TEXT_DOMAIN); ?>
                        </a>
                    </div>
                </article>
            </div>
        </div>
        <!-- END Single CPT -->
    <?php endwhile; ?>
</div>

<!-- CPT Pagination -->
<div class="row">
    <div class="col-md-12">
        <!-- CPT Pagination -->
        <?php
        the_posts_pagination(array(
            'mid_size' => '5',
            'prev_text' => '<span class="dashicons dashicons-arrow-left-alt"></span>',
            'next_text' => '<span class="dashicons dashicons-arrow-right-alt"></span>',
                //'before_page_number' => '<span class="meta-nav screen-reader-text">' . __('Page', 'twentyseventeen') . ' </span>',
        ));
        ?>
    </div>
</div>