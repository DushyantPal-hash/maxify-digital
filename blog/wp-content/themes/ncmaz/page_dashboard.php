<?php

/**
 * Template Name: NCMAZ Dashboard Page
 * Template Post Type: page
 * @package WordPress
 */

get_header();
?>
<main class="ncmaz-myDashboardPage relative">

    <div class="relative">
        <div class="nc-HeadBackgroundCommon absolute h-[400px] max-h-full top-0 left-0 right-0 w-full bg-primary-100 dark:bg-neutral-800 bg-opacity-25 dark:bg-opacity-40" data-nc-id="HeadBackgroundCommon"></div>
        <div class="container relative pt-10 pb-16 lg:pt-14 lg:pb-24">
            <div class="mx-auto">
                <?php the_title('<h1 class="ncmazPageTitle flex items-center text-3xl leading-[115%] md:text-5xl md:leading-[115%] font-semibold text-neutral-900 dark:text-neutral-100 justify-center">', '</h1>') ?>
                <div class="ncmaz-myDashboardPage__content  mt-2">
                    <?php
                    if (have_posts()) :
                        the_post();

                        the_content();
                    endif;
                    ?>
                </div>
            </div>


        </div>


    </div>
</main>
<?php
get_footer();
