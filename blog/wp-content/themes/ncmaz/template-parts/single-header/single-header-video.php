<header class="entry-header container entry-header--style-gallery relative py-14 lg:py-20 flex flex-col lg:flex-row lg:items-center">


    <!-- ======================= -->
    <div class="absolute inset-y-0 transform translate-x-1/2 right-1/2 w-screen lg:translate-x-0 lg:w-[calc(100vw/2)] bg-neutral-900 dark:bg-black/60 lg:rounded-r-[40px]"></div>

    <!-- ======================= -->
    <div class="pb-10 lg:pb-0 lg:pr-10 relative">
        <?php get_template_part('template-parts/single-header/single-header-content-2', null, ['has_excerpt' => 0]);  ?>
    </div>

    <?php if (defined('_NCMAZ_FRONTEND_VERSION')) : ?>
        <div class="relative lg:w-8/12 flex-shrink-0">
            <?php
            $HeaderSingleVideoProps = (object)[];
            $HeaderSingleVideoProps->videoUrl = get_field('video_url');
            $HeaderSingleVideoProps->featuredImage = get_the_post_thumbnail_url(get_the_ID(), 'full');
            ?>
            <div data-is-react-component="HeaderSingleVideo" data-component-props="<?php echo esc_attr(json_encode($HeaderSingleVideoProps)); ?>"></div>
        </div>
    <?php endif; ?>



</header><!-- .entry-header -->