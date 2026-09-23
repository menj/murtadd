<?php
/**
 * Search results — mixed content types, styled as the theme's standard list.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();

$murtadd_query = get_search_query();
?>
<header class="murtadd-archive-header">
	<h1>
		<?php
		/* translators: %s: search query */
		printf( esc_html__( 'Search: %s', 'murtadd' ), '<span class="murtadd-search-term">' . esc_html( $murtadd_query ) . '</span>' );
		?>
	</h1>
	<p class="murtadd-subtitle">
		<?php
		global $wp_query;
		$murtadd_found = (int) $wp_query->found_posts;
		/* translators: %d: number of results */
		printf( esc_html( _n( '%d result across doubts, rebuttals, fatwa entries, and the blog.', '%d results across doubts, rebuttals, fatwa entries, and the blog.', $murtadd_found, 'murtadd' ) ), $murtadd_found );
		?>
	</p>
	<div class="murtadd-search-inline"><?php get_search_form(); ?></div>
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
		<div class="murtadd-no-results">
			<p class="murtadd-empty"><?php esc_html_e( 'Nothing matched that search. Try a broader term, or start from one of these.', 'murtadd' ); ?></p>
			<div class="murtadd-suggest-routes">
				<a class="murtadd-btn murtadd-btn--outline" href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_doubt' ) ); ?>"><?php esc_html_e( 'Browse doubts', 'murtadd' ); ?></a>
				<a class="murtadd-btn murtadd-btn--outline" href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_rebuttal' ) ); ?>"><?php esc_html_e( 'Browse rebuttals', 'murtadd' ); ?></a>
				<a class="murtadd-btn murtadd-btn--outline" href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_fatwa' ) ); ?>"><?php esc_html_e( 'Fatwa index', 'murtadd' ); ?></a>
			</div>
		</div>
	<?php endif; ?>
</div>
<?php get_footer(); ?>
