<?php
/**
 * School term archive — the fatwa index, scoped to one school.
 * Reuses the index table so /school/hanafi/ matches /fatwa/ exactly.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="murtadd-archive-header">
	<a class="murtadd-back" href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_fatwa' ) ); ?>">← <?php esc_html_e( 'Full fatwa index', 'murtadd' ); ?></a>
	<h1><?php single_term_title(); ?></h1>
	<p class="murtadd-subtitle"><?php esc_html_e( 'Positions from this school, summarized in our own words, each linked to its primary source.', 'murtadd' ); ?></p>
	<?php the_archive_description( '<p class="murtadd-subtitle">', '</p>' ); ?>
</header>

<?php if ( have_posts() ) : ?>
	<div class="murtadd-fatwa-table" role="table">
		<div class="murtadd-fatwa-head" role="row">
			<span role="columnheader"><?php esc_html_e( 'Scholar / body', 'murtadd' ); ?></span>
			<span role="columnheader"><?php esc_html_e( 'School', 'murtadd' ); ?></span>
			<span role="columnheader"><?php esc_html_e( 'Era', 'murtadd' ); ?></span>
			<span role="columnheader"><?php esc_html_e( 'Position', 'murtadd' ); ?></span>
			<span role="columnheader"><span class="screen-reader-text"><?php esc_html_e( 'Link', 'murtadd' ); ?></span></span>
		</div>
		<?php while ( have_posts() ) : the_post(); ?>
			<?php get_template_part( 'template-parts/rows/row', 'fatwa-entry' ); ?>
		<?php endwhile; ?>
	</div>
<?php else : ?>
	<div class="murtadd-no-results">
		<p class="murtadd-empty"><?php esc_html_e( 'No positions filed under this school yet.', 'murtadd' ); ?></p>
	</div>
<?php endif; ?>

<?php the_posts_pagination(); ?>
<?php get_footer(); ?>
