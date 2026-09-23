<?php
/**
 * Doubts index.
 *
 * A table, deliberately.
 *
 * The site this one answers led with a table of names: people who had left,
 * each row a link, each row a story. It was the emotional engine of that page,
 * and everything else on it was scaffolding around the table.
 *
 * This is the reversal. Same furniture, same invitation to click through, same
 * accumulating weight of row after row. Where theirs listed people who walked
 * out, this lists questions that were met. Every row is the doubt in the
 * reader's own voice, so that a person scrolling it finds their own sentence
 * already written down and already answered.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();

$murtadd_total = (int) wp_count_posts( 'murtadd_doubt' )->publish;
?>
<header class="murtadd-archive-header">
	<h1><?php esc_html_e( 'The doubts', 'murtadd' ); ?></h1>
	<p class="murtadd-subtitle">
		<?php esc_html_e( 'Questions people actually carry, in the words they actually use. Each one has a short answer you can read in a few minutes, and sources you can check yourself. Find yours below.', 'murtadd' ); ?>
	</p>
</header>

<?php murtadd_doubt_category_tabs(); ?>

<?php if ( have_posts() ) : ?>
	<div class="murtadd-doubts-table" role="table">
		<div class="murtadd-doubts-head" role="row">
			<span role="columnheader"><?php esc_html_e( 'The doubt', 'murtadd' ); ?></span>
			<span role="columnheader"><?php esc_html_e( 'Where it starts', 'murtadd' ); ?></span>
			<span role="columnheader"><?php esc_html_e( 'Answer', 'murtadd' ); ?></span>
			<span role="columnheader"><span class="screen-reader-text"><?php esc_html_e( 'Read', 'murtadd' ); ?></span></span>
		</div>

		<?php
		while ( have_posts() ) :
			the_post();
			$murtadd_cats = get_the_terms( get_the_ID(), 'murtadd_doubt_category' );
			?>
			<a class="murtadd-doubts-row" role="row" href="<?php the_permalink(); ?>">
				<span class="murtadd-doubt-cell" role="cell">
					&ldquo;<?php murtadd_the_doubt_statement(); ?>&rdquo;
				</span>
				<span class="murtadd-doubt-cat" role="cell">
					<?php if ( $murtadd_cats && ! is_wp_error( $murtadd_cats ) ) : ?>
						<?php echo esc_html( $murtadd_cats[0]->name ); ?>
					<?php endif; ?>
				</span>
				<span class="murtadd-doubt-time" role="cell">
					<?php murtadd_the_reading_time(); ?>
				</span>
				<span class="murtadd-doubt-arrow" role="cell" aria-hidden="true">&rarr;</span>
			</a>
			<?php
		endwhile;
		?>
	</div>

	<p class="murtadd-doubts-count">
		<?php
		printf(
			/* translators: %d: number of doubts answered */
			esc_html( _n( '%d doubt answered so far.', '%d doubts answered so far.', $murtadd_total, 'murtadd' ) ),
			(int) $murtadd_total
		);
		?>
		<?php esc_html_e( 'If yours is not here, it is not because it cannot be answered.', 'murtadd' ); ?>
	</p>

	<?php the_posts_pagination(); ?>
<?php else : ?>
	<p class="murtadd-empty"><?php esc_html_e( 'No doubts filed yet.', 'murtadd' ); ?></p>
<?php endif; ?>

<?php get_footer(); ?>
