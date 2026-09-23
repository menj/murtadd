<?php
/**
 * Rebuttal archive.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="murtadd-archive-header">
	<h1><?php esc_html_e( 'Rebuttals', 'murtadd' ); ?></h1>
	<p class="murtadd-subtitle"><?php esc_html_e( 'Circulated claims, stated fairly — then answered with sources you can check yourself.', 'murtadd' ); ?></p>
</header>

<div class="murtadd-article-list">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			$murtadd_claim = get_post_meta( get_the_ID(), '_murtadd_claim', true );
			?>
			<article <?php post_class( 'murtadd-list-row' ); ?>>
				<h2 class="murtadd-list-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<div class="murtadd-list-excerpt"><?php echo esc_html( wp_trim_words( $murtadd_claim, 24 ) ); ?></div>
				<div class="murtadd-list-meta">
					<?php murtadd_term_chip( 'murtadd_topic', 'murtadd-chip-small' ); ?>
				</div>
			</article>
		<?php endwhile; ?>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p class="murtadd-empty"><?php esc_html_e( 'No rebuttals published yet.', 'murtadd' ); ?></p>
	<?php endif; ?>
</div>
<?php get_footer(); ?>
