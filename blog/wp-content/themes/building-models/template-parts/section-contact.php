<!-- START Contact Content -->
<?php if (get_post_meta(get_the_ID(), 'contact_form_switch', true) != 'off'): ?>
    <section class="contact-section featured-container" role="main">
        <article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
            <div class="container">
                <div class="row">
                    <!-- Contact Featured Banner -->
                    <?php
                    $image_id = get_post_meta(get_the_ID(), 'contact_featured_banner', true);
                    $image_url = wp_get_attachment_image_src($image_id, 'service_featured');
                    if (!empty($image_url)) {
                        $banner_img_url = $image_url[0];
                        $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                        ?>
                        <div class="col-md-5 no-padding featured-image-box">
                            <img src="<?php echo $banner_img_url; ?>" alt="<?php echo $image_alt; ?>"  />
                        </div>
                        <?php
                    }
                    ?>

                    <!-- Contact Form Description -->
                    <div class="contact-form-container <?php echo (!empty($image_url)) ? 'col-md-7' : 'col-md-12'; ?>">
                        <div class="post-content text-center" role="main">
                            <div class="contact-form-content-box">
                                <div class="post-title">
                                    <strong><?php echo get_post_meta(get_the_ID(), 'contact_form_title', true); ?></strong>

                                    <?php
                                    $icon_id = get_post_meta(get_the_ID(), 'contact_form_icon', true);
                                    $icon_url = wp_get_attachment_image_src($icon_id, 'icon-thumb');
                                    if (!empty($icon_url)) {
                                        $icon_img_url = $icon_url[0];
                                        $icon_alt = get_post_meta($icon_id, '_wp_attachment_image_alt', true);
                                        ?>
                                        <img class="icon-thumb" src="<?php echo $icon_img_url; ?>" alt="<?php echo $icon_alt; ?>"  />
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="main-content-box"> <?php echo get_post_meta(get_the_ID(), 'contact_form_description', true); ?> </div>
                            </div>
                            <?php
                            // Include contact form section
                            get_template_part('template-parts/contact', 'form');
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    </section>
<?php endif; ?>
<!-- END Single CPT -->