<?php /* Display featured section content if enabled */ ?>
<?php if (get_post_meta(get_the_ID(), 'home_featured_switch', true) != 'off'): ?>
    <section class="section featured-container white-bg text-center">
        <div class="container">
            <div class="about-section">
                <div class="row">
                    <div class="col-md-4 featured-image-box img-circle">
                        <?php
                        $image_id = get_post_meta(get_the_ID(), 'featured_section_banner', true);
                        $image_url = wp_get_attachment_image_src($image_id, 'thumbs');
                        if (!empty($image_url)) {
                            $banner_img_url = $image_url[0];
                            $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                            ?>
                            <img src="<?php echo $banner_img_url; ?>" alt="<?php echo $image_alt; ?>"  />
                            <?php
                        }
                        ?>
                    </div>
                    <div class="col-md-8">
                        <div class="row  text-left featured-box">
                            <div class="col-md-12">

                                <h2>
                                    <?php
                                    $icon_id = get_post_meta(get_the_ID(), 'featured_title_icon', true);
                                    $icon_url = wp_get_attachment_image_src($icon_id, 'icon-thumb');
                                    if (!empty($icon_url)) {
                                        $icon_img_url = $icon_url[0];
                                        $icon_alt = get_post_meta($icon_id, '_wp_attachment_image_alt', true);
                                        ?>
                                        <img class="icon-thumb" src="<?php echo $icon_img_url; ?>" alt="<?php echo $icon_alt; ?>"  />
                                        <?php
                                    }
                                    ?>
                                    <?php echo get_post_meta(get_the_ID(), 'featured_section_title', true); ?>
                                </h2>
                                <div class="main-content-box"> 
                                    <?php echo get_post_meta(get_the_ID(), 'featured_section_banner_description', true); ?>
                                </div>
                                <div>
                                    <?php if (strlen(get_post_meta(get_the_ID(), 'featured_button_title', true))): ?>
                                        <?php
                                        $featured_button_link = get_post_meta(get_the_ID(), 'featured_button_link', true);
                                        if ('#' == $featured_button_link || '' == $featured_button_link) {
                                            $featured_button_link = get_permalink(get_page_by_path('about-me'));
                                        }
                                        ?>
                                        <a href="<?php echo $featured_button_link; ?>" class="button">
                                            <?php echo get_post_meta(get_the_ID(), 'featured_button_title', true); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php

 endif;