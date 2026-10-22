 <div class="container py-16 space-y-16">
     <?php if (have_posts()) :  ?>
         <div>
             <!-- PAGE HEADER -->
             <header class="page-header ">
                 <?php
                    the_archive_title('<h3 class="inline-block max-w-screen-2xl text-4xl font-semibold md:text-5xl">', '</h3>');
                    the_archive_description('<div class="archive-description mt-5">', '</div>');
                    ?>
             </header>

             <!-- GRID POSTS -->
             <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8 mt-14">
             <?php
                while (have_posts()) :
                    the_post();
                    get_template_part('template-parts/components/post-card', null, ['post' => $post]);
                endwhile;

            else :
                get_template_part('template-parts/content', 'none');
            endif;
                ?>
             </div>
         </div>
         <!-- PAGINATION -->
         <?php if (have_posts()) :     ?>
             <div>
                 <?php ncmaz_the_posts_navigation(); ?>
             </div>
         <?php endif; ?>
 </div>