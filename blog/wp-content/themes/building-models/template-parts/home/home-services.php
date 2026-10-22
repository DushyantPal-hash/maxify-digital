<?php /* Display services section content if enabled */ ?>
<?php if (get_post_meta(get_the_ID(), 'home_services_switch', true) != 'off'): ?>
    <section class="section home-services white-bg text-center">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <?php $service_section_title = get_post_meta(get_the_ID(), 'services_section_title', true); ?>
                    <?php if (!empty($service_section_title)) { ?>
                        <div class="section-title">
                            <h2>
                                <?php echo $service_section_title; ?>
                            </h2>
                        </div>
                    <?php } ?>

                    <?php $service_section_content = get_post_meta(get_the_ID(), 'services_section_description', true); ?>
                    <?php if (!empty($service_section_content)) { ?>
                        <div class="section-content">
                            <?php echo nl2br($service_section_content); ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php if (get_post_meta(get_the_ID(), 'home_services_posts_switch', true) != 'off'): ?>

                <?php
                /* Display selected services or all services */
                $services_ids = get_post_meta(get_the_ID(), 'home_services_ids', true);
                $args = array(
                    'post_type' => array('service'),
                    'post_status' => array('publish'),
                    'posts_per_page' => '8',
                );
                if (is_array($services_ids) && !empty($services_ids)) {
                    $args['post__in'] = $services_ids;
                }

                $services = new WP_Query($args);

                if (!empty($services_ids) && $services->have_posts()):
                    ?>
                    <div class="section-columns service-columns">
                        <div class="row  justify-center text-left">
                            <?php
                            while ($services->have_posts()) :
                                $services->the_post();
                                ?>
                                <div class="col-lg-4 col-md-4 col-sm-6 service-col ">
                                    <div class="featured-block" >
                                        <a title="<?php the_title(); ?>" href="<?php the_permalink(); ?>">
                                            <img src="<?php the_post_thumbnail_url('home_services') ?>" alt="<?php the_title(); ?>"/>
                                        </a>
                                    </div>
                                    <div class="title-container">
                                        <?php
                                        $icon_id = get_post_meta(get_the_ID(), 'content_title_icon', true);
                                        $icon_url = wp_get_attachment_image_src($icon_id, 'icon-thumb');
                                        if (!empty($icon_url)) {
                                            $icon_img_url = $icon_url[0];
                                            $icon_alt = get_post_meta($icon_id, '_wp_attachment_image_alt', true);
                                            ?>
                                            <img class="icon-thumb" src="<?php echo $icon_img_url; ?>" alt="<?php echo $icon_alt; ?>"  />
                                        <?php }
                                        ?>
                                        <h2 class="main-title">
                                            <a title="<?php the_title(); ?>" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </h2>
                                    </div>
                                    <div>
                                        <?php the_excerpt(); ?>
                                    </div>
                                </div>

                            <?php endwhile; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php
                    /* Restore original Post Data */
                    wp_reset_postdata();
                    ?>
                <?php endif; ?>
        </div>
    </section>
    <?php
 endif;