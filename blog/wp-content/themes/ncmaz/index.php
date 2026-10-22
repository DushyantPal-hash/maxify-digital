<?php
get_header();
?>

<main id="primary" class="nc-home">
	<!-- DEFAULT PAGE CONTENT -->
	<div class="container py-16 space-y-16">
		<?php if (have_posts()) :  ?>

			<!-- GRID POSTS -->
			<div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8 ">
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

			<!-- PAGINATION -->
			<?php if (have_posts()) :     ?>
				<div>
					<?php ncmaz_the_posts_navigation(); ?>
				</div>
			<?php endif; ?>
	</div>

</main>

<?php
get_footer();
