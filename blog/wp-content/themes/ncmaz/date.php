<?php
get_header();

?>

<main id="primary" class="nc-PageArchive nc-PageArchive--date">
	<!-- MY PAGE CONTENT -->
	<?php if (defined('_NCMAZ_FRONTEND_PREFIX')) : ?>
		<?php
		// 
		$PageArchiveDateProps = (object)[];
		$PageArchiveDateProps->day 		=  get_query_var('day');
		$PageArchiveDateProps->monthnum =  get_query_var('monthnum');
		$PageArchiveDateProps->year 	=  get_query_var('year');
		// 
		$PageArchiveDateProps->pageTitle 	=  get_the_archive_title();
		// 
		$PageArchiveDateProps->sectionCategoriesTrending = ncmazGetOptionForSectionTrendingArchivePage();

		?>
		<div data-is-react-component="PageArchiveDate" data-component-props="<?php echo esc_attr(json_encode($PageArchiveDateProps)); ?>"></div>
	<?php endif; ?>

	<!-- DEFAULT PAGE CONTENT -->
	<?php
	if (!defined('_NCMAZ_FRONTEND_PREFIX')) {
		get_template_part('template-parts/archive-common');
	}
	?>

</main>

<?php
get_footer();
