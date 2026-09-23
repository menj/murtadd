<?php
/**
 * Header — opens the two-column shell (sidebar + main).
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#murtadd-content"><?php esc_html_e( 'Skip to content', 'murtadd' ); ?></a>
<div class="murtadd-shell">
	<?php get_sidebar(); ?>
	<main id="murtadd-content" class="murtadd-main">
