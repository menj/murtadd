<?php
/**
 * Doubt category archive — one of the four fixed triage categories.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="murtadd-archive-header">
	<?php murtadd_doubt_category_tabs(); ?>
	<h1><?php single_term_title(); ?></h1>
	<?php the_archive_description( '<p class="murtadd-subtitle">', '</p>' ); ?>
</header>

<div class="murtadd-article-list">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			$murtadd_response = get_post_meta( get_the_ID(), '_murtadd_short_response', true );
			?>
			<article <?php post_class( 'murtadd-list-row' ); ?>>
				<h2 class="murtadd-list-title"><a href="<?php the_permalink(); ?>">“<?php murtadd_the_doubt_statement(); ?>”</a></h2>
				<div class="murtadd-list-excerpt"><?php echo esc_html( wp_trim_words( $murtadd_response, 24 ) ); ?></div>
				<div class="murtadd-list-meta">
					<?php murtadd_term_chip( 'murtadd_topic', 'murtadd-chip-small' ); ?>
					<span><?php echo esc_html( murtadd_read_time( $murtadd_response ) ); ?></span>
				</div>
			</article>
		<?php endwhile; ?>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p class="murtadd-empty"><?php esc_html_e( 'No doubts in this category yet.', 'murtadd' ); ?></p>
	<?php endif; ?>
</div>
<?php get_footer(); ?>
