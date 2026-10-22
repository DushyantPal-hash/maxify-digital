<header class="entry-header entry-header--style-3 relative pt-16 z-10 md:py-20 lg:py-28 bg-neutral-900 dark:bg-black">
    <div class="dark container relative z-10">
        <div class="max-w-screen-md">
            <?php get_template_part('template-parts/single-header/single-header-content', null, ['has_excerpt' => 0]); ?>
        </div>
    </div>

    <div class="mt-8 md:mt-0 md:absolute md:top-0 md:right-0 md:bottom-0 md:w-1/2 lg:w-2/5 2xl:w-1/3">
        <div class="hidden md:block absolute top-0 left-0 bottom-0 w-1/5 from-neutral-900 dark:from-black bg-gradient-to-r"></div>

        <?php
        if (!post_password_required() && !is_attachment() && has_post_thumbnail()) {
            the_post_thumbnail('post-thumbnail', [
                'class' => 'block w-full h-full object-cover'
            ]);
        }
        ?>
    </div>


</header><!-- .entry-header -->