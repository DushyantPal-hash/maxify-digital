<?php


class WPDocs_Walker_Nav_Menu extends Walker_Nav_Menu
{

	function start_lvl(&$output, $depth = 0, $args = array())
	{
		$indent = ($depth > 0  ? str_repeat("\t", $depth) : ''); // code indent
		$display_depth = ($depth + 1); // because it counts the first submenu as 0
		$classes = array(
			'sub-menu',
			($display_depth % 2  ? 'menu-odd' : 'menu-even'),
			($display_depth >= 2 ? 'sub-sub-menu' : ''),
			'menu-depth-' . $display_depth
		);
		$class_names = implode(' ', $classes);

		$output .= "\n" . $indent . '<div class="sub-menu-wrap"><ul class="' . $class_names . '">' . "\n";
	}
	function end_lvl(&$output, $depth = 0, $args = null)
	{
		if (isset($args->item_spacing) && 'discard' === $args->item_spacing) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}
		$indent  = str_repeat($t, $depth);
		$output .= "$indent</ul></div>{$n}";
	}

	function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
	{

		global $wp_query;
		$indent = ($depth > 0 ? str_repeat("\t", $depth) : ''); // code indent

		$depth_classes = array(
			($depth == 0 ? 'main-menu-item' : 'sub-menu-item'),
			($depth >= 2 ? 'sub-sub-menu-item' : ''),
			($depth % 2 ? 'menu-item-odd' : 'menu-item-even'),
			'menu-item-depth-' . $depth
		);
		$depth_class_names = esc_attr(implode(' ', $depth_classes));

		// Passed classes.
		$classes = empty($item->classes) ? array() : (array) $item->classes;
		$class_names = esc_attr(implode(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item)));

		// PLEASE USE STRING INSTED BOOLEAN
		$is_mega_menu = 'false';
		if (defined('_NCMAZ_FRONTEND_VERSION')) {
			$is_mega_menu = boolval(get_field('is_mega_menu', $item)) ? 'true' : 'false';
		}

		// USE GRAPHQL get data megamenu for menu-item
		// DEMO DEFAULT VALUE
		$graphql = [
			'data'	=> null
		];
		if ($is_mega_menu === 'true') {
			$graphql = graphql([
				'query' => ' {
					menuItem(id: ' . $item->ID . ', idType: DATABASE_ID) {
						id
						label
						title
						menuItemId
						ncmazMenuCustomFields {
						  fieldGroupName
						  isMegaMenu
						  numberOfPosts
						  showTabFilter
						  taxonomies {
							name
							slug
							link
							categoryId
						  }
						}
					  }
				}'
			]);
		}
		// Build HTML.
		$output .= $indent . '<li data-graphql="' . htmlspecialchars(json_encode($graphql['data']), ENT_QUOTES, 'UTF-8') . '"  data-item-id="' . $item->ID . '"  data-is-megamenu="' . $is_mega_menu . '"  class="' . $depth_class_names . ' ' . $class_names . '">';

		// Link attributes.
		$attributes  = !empty($item->attr_title) ? ' title="'  . esc_attr($item->attr_title) . '"' : '';
		$attributes .= !empty($item->target)     ? ' target="' . esc_attr($item->target) . '"' : '';
		$attributes .= !empty($item->xfn)        ? ' rel="'    . esc_attr($item->xfn) . '"' : '';
		$attributes .= !empty($item->url)        ? ' href="'   . esc_attr($item->url) . '"' : '';
		$attributes .= ' class="menu-link ' . ($depth > 0 ? 'sub-menu-link' : 'main-menu-link') . '"';

		// Build HTML output and pass through the proper filter.
		$item_output = sprintf(
			'%1$s<a%2$s>%3$s%4$s%5$s</a>%6$s',
			$args->before,
			$attributes,
			$args->link_before,
			apply_filters('the_title', $item->title, $item->ID),
			$args->link_after,
			$args->after
		);
		$output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
	}
}
?>


<?php if (has_nav_menu('primary')) : ?>
	<nav id="site-navigation" class="hidden xl:block relative primary-navigation site-header-nav-main flex-grow" aria-label="<?php esc_attr_e('Primary menu', 'ncmaz'); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location'  => 'primary',
				'menu_class'      => 'menu-wrapper',
				'container_class' => 'primary-menu-container w-full',
				'items_wrap'      => '<ul id="primary-menu-list" class="nc-Navigation flex lg:flex-wrap lg:items-center lg:space-x-1 %2$s">%3$s</ul>',
				'fallback_cb'     => false,
				'walker' => new WPDocs_Walker_Nav_Menu()
			)
		);
		?>
	</nav>
<?php endif; ?>