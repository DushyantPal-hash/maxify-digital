<?php
$userImgUrl = getAuthorImgUrlById($post->post_author);

?>

<div class="nc-SingleHeader space-y-5">

    <!-- CATEGORIES -->
    <div class="nc-CategoryBadgeList flex flex-wrap">
        <?php echo get_the_category_list(esc_html__(' ', 'ncmaz')); ?>
    </div>

    <!-- TITLE -->
    <?php
    if (is_singular()) :
        the_title('<h1 class="entry-title text-neutral-900 font-semibold text-3xl md:text-4xl md:!leading-[120%] lg:text-5xl dark:text-neutral-100 max-w-4xl ">', '</h1>');
    else :
        the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');
    endif;  ?>

    <!-- // ========== SHOW EXCERPT ========== -->
    <?php
    if (has_excerpt() && $args['has_excerpt']) {
        echo '<div class="text-base text-neutral-500 md:text-lg dark:text-neutral-400 pb-1">';
        the_excerpt();
        echo '</div>';
    }
    ?>

    <!-- DIVIDER BORDER -->
    <div class="w-full border-b border-neutral-100 dark:border-neutral-800"></div>

    <!-- META -->
    <?php if ('post' === get_post_type()) : ?>
        <div class="flex flex-col lg:flex-row justify-between lg:items-end space-y-5 lg:space-y-0 lg:space-x-5">
            <div class="nc-PostMeta2 nc-PostMeta2-2 flex items-center text-neutral-700 text-left dark:text-neutral-200 text-sm leading-none flex-shrink-0" data-nc-id="PostMeta2">
                <a class="flex items-center space-x-2" href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>">
                    <div class="wil-avatar relative flex-shrink-0 inline-flex items-center justify-center overflow-hidden text-neutral-100 uppercase font-semibold  rounded-full shadow-inner h-10 w-10 sm:h-11 sm:w-11 text-xl ring-1 ring-white dark:ring-neutral-900">
                        <img class="absolute inset-0 w-full h-full object-cover" src="<?php echo esc_url($userImgUrl); ?>" alt="<?php echo esc_attr(get_the_author()); ?>">
                    </div>
                </a>
                <div class="ml-3">
                    <div class="flex items-center">
                        <a class="block font-semibold" href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>">
                            <?php echo esc_html(get_the_author()) ?>
                        </a>
                    </div>
                    <div class="text-xs mt-[6px]">
                        <span class="text-neutral-700 dark:text-neutral-300">
                            <?php echo get_the_date(); ?>
                        </span>

                        <?php if (defined('_NCMAZ_FRONTEND_VERSION')) : ?>
                            <span class="mx-2 font-semibold">·</span>
                            <span class="text-neutral-700 dark:text-neutral-300">
                                <?php echo do_shortcode('[rt_reading_time postfix="min read" postfix_singular="min read"]'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php get_template_part('template-parts/single-header/single-header-meta-area', null, ['hideCommentOnMobile' => true]); ?>
        </div>


    <?php endif; ?>
</div>