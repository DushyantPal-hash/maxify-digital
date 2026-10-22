<?php /* Display Section Banner content if enabled */ ?>
<?php if (get_post_meta(get_the_ID(), 'section_banner_content_switch', true) != 'off'): ?>
    <section class="page-content banner banner-box <?php echo get_post_meta(get_the_ID(), 'banner_height', true); ?> " role="banner">
        <div class="container">
            <div class="row">
                <!-- Featured Image -->
                <?php if (has_post_thumbnail()) { ?>
                    <div class="col-lg-8 col-md-12 flex-column">
                        <div class="banner-box-image" role="img">
                            <?php the_post_thumbnail('content_large', array('class' => 'featured-image')); ?>
                            <div class="service-cat-text">
                                <?php
                                global $post;
                                $terms = wp_get_post_terms($post->ID, 'service_type');
                                echo $terms[0]->name;
                                ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="col-lg-4 col-md-12 flex-column">
                    <div class="banner-content-box">
                        <div class="banner-caption">
                            <div class="title-container">
                                <h1 class="main-title"> <?php the_title(); ?> </h1>
                            </div>

                            <div class="banner-description">
                                <?php echo get_post_meta(get_the_ID(), 'banner_description', true); ?>
                            </div>
                        </div>
                        <?php if (strlen(get_post_meta(get_the_ID(), 'banner_button_label', true))): ?>
                            <div class="col-lg-12 col-sm-6 banner-button no-padding">
                                <?php
                                $banner_button_link = get_post_meta(get_the_ID(), 'banner_button_link', true);
                                if ('#' == $banner_button_link || '' == $banner_button_link) {
                                    $banner_button_link = get_permalink(get_page_by_path('contact'));
                                }
                                ?>
                                <a href="<?php echo $banner_button_link; ?>" class="button"><?php echo get_post_meta(get_the_ID(), 'banner_button_label', true); ?></a>
                            </div>
                        <?php endif; ?>

                    </div>  
                </div>
            </div>
        </div>
    </section>
    <?php

 endif;