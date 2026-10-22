<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package ncmaz
 */

global $ncmaz_redux_demo;

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
 <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
    
	<?php wp_head(); ?>
</head>

<body data-type="ncmaz-core/body" <?php body_class('bg-white text-base dark:bg-neutral-900 text-neutral-900 dark:text-neutral-200'); ?>>
	<?php wp_body_open(); ?>

	<!-- HEADER NOTIFY MESSAGE -->
	<?php if (!empty($ncmaz_redux_demo)) : ?>
		<?php if (!empty($ncmaz_redux_demo['nc-header-settings--general--fixed-notify'])) : ?>
			<div class="header-notice-message relative py-4 px-10 bg-neutral-800 text-neutral-100 hidden justify-center overflow-hidden">
				<div class="text-sm text-center">
					<?php echo wp_kses($ncmaz_redux_demo['nc-header-settings--general--fixed-notify'], array(
						'a' => array(
							'href' => array(),
							'title' => array()
						),
						'br' => array(),
						'em' => array(),
						'strong' => array(),
					)); ?>
				</div>
				<span class="header-notice-message__close absolute right-2 top-1/2 -translate-y-1/2 flex items-center justify-center p-2 cursor-pointer opacity-80 hover:opacity-100">
					<i class="las la-times"></i>
				</span>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<!-- HEADER -->
	<?php get_template_part('template-parts/header/site-header'); ?>
	<!-- FOR REACT RENDER -->
	<div id="root"></div>
	<div id="site-header"></div>