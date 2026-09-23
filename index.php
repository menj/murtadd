<?php
/**
 * Fallback template.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="murtadd-archive-header">
	<?php if ( is_archive() ) : ?>
		<?php the_archive_title( '<h1>', '</h1>' ); ?>
	<?php else : ?>
		<h1><?php bloginfo( 'name' ); ?></h1>
	<?php endif; ?>
</header>

<div class="murtadd-article-list">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/rows/row', 'list-entry' );
		endwhile;
		?>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p class="murtadd-empty"><?php esc_html_e( 'Nothing found.', 'murtadd' ); ?></p>
	<?php endif; ?>
</div>
<?php get_footer(); ?>
