<?php
$userImgUrl = getAuthorImgUrlById($args['post']->post_author);
$postID = $args['post']->ID;
is_sticky($postID)
?>

<div class="nc-Card11 relative flex flex-col group [ nc-box-has-hover ] [ nc-dark-box-bg-has-hover ] h-full">

    <!-- FEATURED IMAGE -->
    <?php if (has_post_thumbnail($postID)) : ?>
        <div class="block flex-shrink-0 relative w-full rounded-t-xl overflow-hidden aspect-w-4 aspect-h-3">
            <div>
                <div class="nc-PostFeaturedMedia relative w-full h-full" data-nc-id="PostFeaturedMedia">
                    <div class="nc-NcImage absolute inset-0 " data-nc-id="NcImage">
                        <?php echo get_the_post_thumbnail($postID, 'post-thumbnail', [
                            'class' => 'object-cover w-full h-full m-0 '
                        ]); ?>
                    </div>
                    <div class="absolute inset-0"></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- LINk -->
    <a class="absolute inset-0" href="<?php echo esc_url(get_permalink($postID)); ?>"></a>

    <!-- CATS -->
    <span class="<?php echo esc_attr(has_post_thumbnail($postID) ? "absolute top-3 inset-x-3" : "block p-4 pb-0"); ?>">
        <div class="nc-CategoryBadgeList flex flex-wrap" data-nc-id="CategoryBadgeList">
            <?php echo get_the_category_list(null, null, $postID); ?>
        </div>
    </span>

    <!-- CONTENT -->
    <div class="p-4 flex flex-col flex-grow">
        <div class="nc-PostCardMeta inline-flex items-center flex-nowrap text-neutral-800 dark:text-neutral-200 text-xs leading-none" data-nc-id="PostCardMeta">

            <!-- AUTHOR -->
            <a class="flex items-center space-x-2 truncate" href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>">
                <div class="wil-avatar relative flex-shrink-0 inline-flex items-center justify-center overflow-hidden text-neutral-100 uppercase font-semibold shadow-inner rounded-full h-7 w-7 text-sm ring-1 ring-white dark:ring-neutral-900">
                    <img class="absolute inset-0 w-full h-full object-cover" src="<?php echo esc_url($userImgUrl); ?>" alt="<?php echo esc_attr(get_the_author()); ?>">
                </div>
                <span class="block text-neutral-700 hover:text-black dark:text-neutral-300 dark:hover:text-white font-medium truncate py-1">
                    <?php echo esc_html(get_the_author()); ?>
                </span>
            </a>
            <span class="text-neutral-500 dark:text-neutral-400 mx-[6px] font-medium">·</span>
            <!-- DATE -->
            <span class="text-neutral-500 dark:text-neutral-400 font-normal flex-shrink-0">
                <?php echo esc_html(get_the_date()); ?>
            </span>
        </div>

        <!-- TITLE -->
        <?php the_title(sprintf('<h3 class="nc-card-title block text-base font-semibold text-neutral-900 dark:text-neutral-100 my-3"><a href="%s">', esc_url(get_permalink())), is_sticky($postID) ? '<i class="ml-1 las la-thumbtack"></i></a></h3>' : '</a></h3>'); ?>


        <div class="flex items-end justify-between ">
            <div class="nc-PostCardLikeAndComment flex items-center space-x-2 relative" data-nc-id="PostCardLikeAndComment">

                <a class="nc-PostCardCommentBtn relative items-center min-w-[68px] rounded-full text-neutral-6000 bg-neutral-50 transition-colors dark:text-neutral-200 dark:bg-neutral-800 hover:bg-teal-50 dark:hover:bg-teal-100 hover:text-teal-600 dark:hover:text-teal-500 hidden sm:flex  px-3 h-8 text-xs focus:outline-none" title="<?php echo esc_attr__('Comments', 'ncmaz'); ?>" data-nc-id="PostCardCommentBtn" href="<?php echo esc_url(get_permalink($postID)); ?>">
                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4.75 6.75C4.75 5.64543 5.64543 4.75 6.75 4.75H17.25C18.3546 4.75 19.25 5.64543 19.25 6.75V14.25C19.25 15.3546 18.3546 16.25 17.25 16.25H14.625L12 19.25L9.375 16.25H6.75C5.64543 16.25 4.75 15.3546 4.75 14.25V6.75Z"></path>
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M9.5 11C9.5 11.2761 9.27614 11.5 9 11.5C8.72386 11.5 8.5 11.2761 8.5 11C8.5 10.7239 8.72386 10.5 9 10.5C9.27614 10.5 9.5 10.7239 9.5 11Z"></path>
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M12.5 11C12.5 11.2761 12.2761 11.5 12 11.5C11.7239 11.5 11.5 11.2761 11.5 11C11.5 10.7239 11.7239 10.5 12 10.5C12.2761 10.5 12.5 10.7239 12.5 11Z"></path>
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M15.5 11C15.5 11.2761 15.2761 11.5 15 11.5C14.7239 11.5 14.5 11.2761 14.5 11C14.5 10.7239 14.7239 10.5 15 10.5C15.2761 10.5 15.5 10.7239 15.5 11Z"></path>
                    </svg>
                    <span class="ml-1 text-neutral-900 dark:text-neutral-200">
                        <?php echo esc_html($args['post']->comment_count); ?>
                    </span>
                </a>
            </div>

        </div>
    </div>
</div>