<?php

get_header();
?>

<main id="primary" class="nc-PageSearch">

	<?php if (defined('_NCMAZ_FRONTEND_VERSION')) : ?>
		<?php
		global $ncmaz_redux_demo;
		// 
		$PageSearchProps = (object)[];
		// 
		$PageSearchProps->searchQuery =  get_search_query();
		$PageSearchProps->pageTitle	=  get_the_archive_title();
		$PageSearchProps->listSuggestions =  $ncmaz_redux_demo['nc-search-page-settings--multi-text-suggestions'];
		$PageSearchProps->headerBackgroundImg =  $ncmaz_redux_demo['nc-search-page-settings--media-background'];
		// 
		$PageSearchProps->sectionCategoriesTrending = ncmazGetOptionForSectionTrendingArchivePage();

		?>
		<div data-is-react-component="PageSearch" data-component-props="<?php echo esc_attr(json_encode($PageSearchProps)); ?>"></div>
	<?php endif; ?>

	<!-- DEFAULT PAGE CONTENT -->
	<?php if (!defined('_NCMAZ_FRONTEND_VERSION')) : ?>
		<div class="container py-16 lg:py-28 space-y-16 lg:space-y-28">
			<?php if (have_posts()) : ?>
				<div>
					<!-- PAGE HEADER -->
					<header class="page-header">
						<h1 class="inline-block max-w-screen-2xl text-4xl font-semibold md:text-5xl">
							<?php
							/* translators: %s: search query. */
							printf(esc_html__('Search Results for: %s', 'ncmaz'), '<span>' . get_search_query() . '</span>'); 	?>
						</h1>
					</header>

					<!-- GRID POSTS -->
					<div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8 mt-14">
					<?php
					while (have_posts()) :
						the_post();
						get_template_part('template-parts/components/post-card', null, ['post' => $post]);
					endwhile;

				else :
					get_template_part('template-parts/content', 'none');
				endif; ?>
					</div>
				</div>

				<!-- PAGINATION -->
				<?php if (have_posts()) : ?>
					<div>
						<?php ncmaz_the_posts_navigation(); ?>
					</div>
				<?php endif; ?>
		</div>
	<?php endif; ?>

</main>

<?php
get_footer();
