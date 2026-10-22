<?php
$authorId = $post->post_author;
$userImgUrl = getAuthorImgUrlById($authorId);
?>
<?php if (!empty(get_the_author_meta('description'))) : ?>
    <div class="max-w-screen-md mx-auto mt-10">
        <div class="nc-SingleAuthor flex">

            <a class="flex space-x-2" href="<?php echo esc_url(get_author_posts_url($authorId)); ?>">
                <div class="wil-avatar relative flex-shrink-0 inline-flex items-center justify-center overflow-hidden text-neutral-100 uppercase font-semibold shadow-inner rounded-2xl h-12 w-12 text-lg sm:text-xl sm:h-24 sm:w-24  ring-1 ring-white dark:ring-neutral-900">
                    <img class="absolute inset-0 w-full h-full object-cover" src="<?php echo esc_url($userImgUrl); ?>" alt="<?php echo esc_attr(get_the_author()); ?>">
                </div>
            </a>

            <div class="flex flex-col ml-3 max-w-lg sm:ml-5"><span class="text-xs text-neutral-400 uppercase tracking-wider">
                    <?php echo esc_html__('WRITTEN BY', 'ncmaz'); ?>
                </span>
                <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-200">
                    <a href="<?php echo esc_url(get_author_posts_url($authorId)); ?>">
                        <?php echo get_the_author(); ?>
                    </a>
                </h2>
                <span class="text-sm text-neutral-500 dark:text-neutral-300">
                    <?php echo nl2br(get_the_author_meta('description')); ?>
                </span>
            </div>
        </div>
    </div>
<?php endif; ?>