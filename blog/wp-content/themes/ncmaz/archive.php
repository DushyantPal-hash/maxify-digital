<?php
get_header();
?>

<main id="primary" class="nc-PageArchive overflow-hidden">
	<!-- MY PAGE CONTENT -->
	<?php if (defined('_NCMAZ_FRONTEND_PREFIX')) : ?>
		<?php
		$termNodeCommon =  'id
		count
		databaseId
		description
		link
		name
		slug
		termTaxonomyId
		ncTaxonomyMeta {
		  color
		  featuredImage {
			sourceUrl
		  }
		}';

		$graphql = graphql([
			'query' => ' { 
				termNode(id: ' . get_queried_object_id() . ', idType: DATABASE_ID) {
					... on Category {' . $termNodeCommon . '}
					... on Tag {' . $termNodeCommon . '}
					... on PostFormat {' . $termNodeCommon . '}
			  }
			}'
		]);

		$PageArchiveProps = (object)[];
		$PageArchiveProps->termId = get_queried_object_id();
		$PageArchiveProps->termData = $graphql['data']['termNode'];
		$PageArchiveProps->featuredImage = get_field('featured_image', 'category_' . get_queried_object_id());
		$PageArchiveProps->isCategory = is_category();
		$PageArchiveProps->isTag = is_tag();
		$PageArchiveProps->isFormatVideo = false;
		$PageArchiveProps->isFormatAudio = false;
		// 
		$PageArchiveProps->sectionCategoriesTrending = ncmazGetOptionForSectionTrendingArchivePage();

		?>
		<div data-is-react-component="PageArchive" data-component-props="<?php echo esc_attr(json_encode($PageArchiveProps)); ?>"></div>
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
