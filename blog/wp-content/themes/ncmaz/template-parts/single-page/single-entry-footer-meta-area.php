<div class="nc-SingleMetaAction2 flex space-x-2.5 items-center justify-between flex-wrap">
    <div class="flex space-x-2.5 items-center my-2 sm:my-0">
        <!-- VIEWS COUNT -->

        <?php if (defined('_NCMAZ_FRONTEND_VERSION')) : ?>
            <div class="ncmaz-button-like-post large relative">
                <?php echo do_shortcode('[favorite_button]'); ?>
            </div>
        <?php endif; ?>

        <!-- COMMENT COUNTS -->
        <a class="nc-SingleMetaAction2__comments group relative items-center min-w-[68px] rounded-full text-neutral-6000 bg-neutral-50 transition-colors dark:text-neutral-200 dark:bg-neutral-800 hover:bg-teal-50 dark:hover:bg-teal-100 hover:text-teal-600 dark:hover:text-teal-500 flex px-4 h-9 text-sm focus:outline-none" title="<?php echo esc_attr__('Comments', 'ncmaz'); ?>" href="<?php echo esc_url(get_permalink()); ?>#comments">
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
    </div>


    <!-- LIKE BUTTON -->
    <?php if (defined('_NCMAZ_FRONTEND_VERSION')) : ?>
        <div class="flex space-x-2 items-center my-2 sm:my-0">
            <!-- DROPDOWN SHARE -->
            <?php
            $data_SocialsShare_props = (object)[];
            $data_SocialsShare_props->href = get_permalink();
            $data_SocialsShare_props->className = "flex space-x-1 sm:space-x-2 ";
            $data_SocialsShare_props->size = 32;
            $data_SocialsShare_props->image = get_the_post_thumbnail_url(get_the_ID(), 'full');
            ?>
            <div data-is-react-component="SocialsShare" data-component-props="<?php echo esc_attr(json_encode($data_SocialsShare_props)); ?>"></div>
        </div>
    <?php endif; ?>
</div>