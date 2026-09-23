<?php
/**
 * Generic archive — date archives and any archive without a more specific template.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="murtadd-archive-header">
	<?php the_archive_title( '<h1>', '</h1>' ); ?>
	<?php the_archive_description( '<p class="murtadd-subtitle">', '</p>' ); ?>
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
		<p class="murtadd-empty"><?php esc_html_e( 'Nothing filed here yet.', 'murtadd' ); ?></p>
	<?php endif; ?>
</div>
<?php get_footer(); ?>
