<?php

/**
 * Template Name: NCMAZ Custom Page
 * Template Post Type: page
 * @package WordPress
 */

get_header();
?>
<main class="ncmaz-myCustomPage pt-10 pb-16 md:py-16 relative overflow-hidden">
    <div class="nc-BgGlassmorphism absolute inset-x-0 top-0 min-h-0 pl-10 py-32 flex flex-col overflow-hidden z-0" data-nc-id="BgGlassmorphism">
        <span class="bg-[#ef233c] w-80 h-80 rounded-full mix-blend-multiply filter blur-3xl opacity-20 lg:w-96 lg:h-9w-96"></span>
        <span class="bg-[#04868b] w-80 h-80 ml-10 -mt-10 rounded-full mix-blend-multiply filter blur-3xl opacity-20 lg:w-96 lg:h-9w-96 nc-animation-delay-2000"></span>
    </div>
    <div class="relative">
        <?php
        if (have_posts()) :
            the_post();

            the_content();
        endif;
        ?>
    </div>
</main>
<?php
get_footer();
