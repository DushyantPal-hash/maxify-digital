<header class="entry-header container entry-header--style-gallery">
    <div class="max-w-screen-md mx-auto">
        <?php get_template_part('template-parts/single-header/single-header-content', null, ['has_excerpt' => 0]); ?>
    </div>

    <?php if (defined('_NCMAZ_FRONTEND_VERSION')) : ?>
        <?php
        $singleHeaderGalleryProps = (object)[];
        $singleHeaderGalleryProps->photos = [
            get_field('image_1'),
            get_field('image_2'),
            get_field('image_3'),
            get_field('image_4'),
            get_field('image_5'),
        ];
        ?>
        <div data-is-react-component="HeaderSingleGallery" data-component-props="<?php echo esc_attr(json_encode($singleHeaderGalleryProps)); ?>"></div>
    <?php endif; ?>
</header><!-- .entry-header -->