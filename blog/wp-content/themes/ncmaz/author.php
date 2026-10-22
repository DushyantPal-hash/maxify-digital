<?php
get_header();
?>

<main id="primary" class="nc-PageArchive nc-PageArchive--author">
	<!-- MY PAGE CONTENT -->
	<?php if (defined('_NCMAZ_FRONTEND_VERSION')) : ?>
		<?php
		$userGraphql = graphql([
			'query' => '{
				user(id: "' . get_queried_object_id() . '", idType: DATABASE_ID) {
					id
					avatar {
					  url
					}
					name
					username
					userId
					url
					uri
					description
					ncUserMeta {
					  color
					  ncBio
					  youtubeUrl
					  facebookUrl
					  mediumUrl
					  githubUrl
					  vimeoUrl
					  twitterUrl
					  instagramUrl
					  linkedinUrl
					  pinterestUrl
					  twitchUrl
					  websiteUrl
					  buymeacoffeUrl
					  backgroundImage {
						sourceUrl
					  }
					  featuredImage {
						sourceUrl
						id
					  }
					}
				}
			}'
		]);
		// 
		$PageArchiveDateProps = (object)[];
		$PageArchiveDateProps->userData =  $userGraphql['data']['user'];
		// 
		if (get_current_user_id() !== get_queried_object_id()) {
			if (boolval(get_user_favorites_count($user_id = get_queried_object_id()))) {
				$PageArchiveDateProps->listIDFavorites = get_user_favorites($user_id = get_queried_object_id(), $site_id = null, $filters = null);
			} else {
				$PageArchiveDateProps->listIDFavorites  = (object)[];
			}
		}

		// 
		$PageArchiveDateProps->sectionCategoriesTrending = ncmazGetOptionForSectionTrendingArchivePage();
		?>
		<div data-is-react-component="PageArchiveAuthor" data-component-props="<?php echo esc_attr(json_encode($PageArchiveDateProps)); ?>"></div>
	<?php endif; ?>

	<!-- DEFAULT PAGE CONTENT -->
	<?php
	if (!defined('_NCMAZ_FRONTEND_VERSION')) {
		get_template_part('template-parts/archive-common');
	}
	?>
</main>

<?php
get_footer();
