<?php

// $hideCommentOnMobile = isset($args['hideCommentOnMobile']) ? boolval($args['hideCommentOnMobile']) : false;
$hideCommentOnMobile = false;

?>

<div class="single-header-meta-area nc-SingleMetaAction2 flex flex-wrap flex-row space-x-2 sm:space-x-2.5 space-y-0.5 sm:space-y-0 items-center ">
    <!-- VIEWS COUNT -->
    <?php if (defined('_NCMAZ_FRONTEND_VERSION')) : ?>
        <div class="nc-SingleMetaAction2__views relative sm:min-w-[68px] rounded-full text-neutral-6000 bg-neutral-50 dark:text-neutral-200 dark:bg-neutral-800 flex items-center justify-center mt-0.5 sm:mt-0 px-2 h-7 sm:h-8 text-xs sm:text-sm focus:outline-none" title="<?php echo esc_attr__('Views', 'ncmaz'); ?>">
            <svg width="24" height="24" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M19.25 12C19.25 13 17.5 18.25 12 18.25C6.5 18.25 4.75 13 4.75 12C4.75 11 6.5 5.75 12 5.75C17.5 5.75 19.25 11 19.25 12Z"></path>
                <circle cx="12" cy="12" r="2.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2"></circle>
            </svg>
            <span class="ml-1 text-neutral-900 dark:text-neutral-200">
                <?php echo esc_html(get_field('views_count') ?? '1'); ?>
            </span>
        </div>
    <?php endif; ?>

    <!-- COMMENT COUNTS -->
    <a class="nc-SingleMetaAction2__comments group relative sm:min-w-[68px] rounded-full text-neutral-6000 bg-neutral-50 transition-colors dark:text-neutral-200 dark:bg-neutral-800 hover:bg-teal-50 dark:hover:bg-teal-100 hover:text-teal-600 dark:hover:text-teal-500 items-center justify-center px-2 h-7 sm:h-8 text-xs sm:text-sm focus:outline-none <?php echo ($hideCommentOnMobile ? "hidden sm:flex" : "flex") ?>" title="<?php echo esc_attr__('Comments', 'ncmaz'); ?>" href="<?php echo esc_url(get_permalink()); ?>#comments">
        <svg width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4.75 6.75C4.75 5.64543 5.64543 4.75 6.75 4.75H17.25C18.3546 4.75 19.25 5.64543 19.25 6.75V14.25C19.25 15.3546 18.3546 16.25 17.25 16.25H14.625L12 19.25L9.375 16.25H6.75C5.64543 16.25 4.75 15.3546 4.75 14.25V6.75Z"></path>
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M9.5 11C9.5 11.2761 9.27614 11.5 9 11.5C8.72386 11.5 8.5 11.2761 8.5 11C8.5 10.7239 8.72386 10.5 9 10.5C9.27614 10.5 9.5 10.7239 9.5 11Z"></path>
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M12.5 11C12.5 11.2761 12.2761 11.5 12 11.5C11.7239 11.5 11.5 11.2761 11.5 11C11.5 10.7239 11.7239 10.5 12 10.5C12.2761 10.5 12.5 10.7239 12.5 11Z"></path>
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M15.5 11C15.5 11.2761 15.2761 11.5 15 11.5C14.7239 11.5 14.5 11.2761 14.5 11C14.5 10.7239 14.7239 10.5 15 10.5C15.2761 10.5 15.5 10.7239 15.5 11Z"></path>
        </svg>
        <span class="ml-1 text-neutral-900 dark:text-neutral-200 group-hover:text-teal-600">
            <?php echo esc_html(get_comments_number()); ?>
        </span>
    </a>

    <!-- DIVIDER -->
    <?php if (defined('_NCMAZ_FRONTEND_VERSION')) : ?>
        <div class="sm:px-1">
            <div class="border-l border-neutral-200 dark:border-neutral-700 h-6"></div>
        </div>
    <?php endif; ?>

    <!-- LIKE BUTTON -->
    <?php if (defined('_NCMAZ_FRONTEND_VERSION')) : ?>
        <div class="ncmaz-button-like-post relative text-xs sm:text-sm">
            <?php echo do_shortcode('[favorite_button]'); ?>
        </div>
    <?php endif; ?>

    <!-- DROPDOWN SHARE -->
    <?php if (defined('_NCMAZ_FRONTEND_VERSION')) : ?>
        <?php
        $data_PostCardDropdownShare_props = (object)[];
        $data_PostCardDropdownShare_props->panelMenusClass = "w-52 right-0 top-0 origin-bottom-right";
        $data_PostCardDropdownShare_props->href = get_permalink();
        $data_PostCardDropdownShare_props->image = get_the_post_thumbnail_url(get_the_ID(), 'full');
        ?>
        <div class="flex" data-is-react-component="PostCardDropdownShare" data-component-props="<?php echo esc_attr(json_encode($data_PostCardDropdownShare_props)); ?>"></div>
    <?php endif; ?>

    <!-- DROPDOWN MORE ACTION -->
    <?php if (defined('_NCMAZ_FRONTEND_VERSION')) : ?>
        <?php if (is_user_logged_in() && !empty(get_edit_post_link())) : ?>
            <?php
            $PostMoreActionDropdown_data = (object)[];
            $PostMoreActionDropdown_data->panelMenusClass = "w-52 right-0 top-0 origin-bottom-right";
            $PostMoreActionDropdown_data->href = esc_url(get_permalink());
            $PostMoreActionDropdown_data->postDataBaseId = $post->ID;
            $PostMoreActionDropdown_data->isReloadAfterDelete = true;

            ?>
            <div data-is-react-component="PostMoreActionDropdown" data-component-props="<?php echo esc_attr(json_encode($PostMoreActionDropdown_data)); ?>"></div>
        <?php endif; ?>
    <?php endif; ?>

</div>