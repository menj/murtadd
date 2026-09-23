<?php
/**
 * Fatwa archive — the index. GET filters (school / era / topic), sortable,
 * enhanced by js/fatwa-filters.js; fully functional with JS disabled.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();

$murtadd_sel_school = isset( $_GET['school'] ) ? sanitize_key( wp_unslash( $_GET['school'] ) ) : '';
$murtadd_sel_era    = isset( $_GET['era'] ) ? sanitize_key( wp_unslash( $_GET['era'] ) ) : '';
$murtadd_sel_topic  = isset( $_GET['topic'] ) ? sanitize_key( wp_unslash( $_GET['topic'] ) ) : '';
?>

<header class="murtadd-archive-header">
	<h1><?php esc_html_e( 'Fatwa index', 'murtadd' ); ?></h1>
	<p class="murtadd-subtitle"><?php esc_html_e( 'Scholarly positions summarized in our own words, each linked to its primary source. A reference, not a verdict.', 'murtadd' ); ?></p>
</header>

<form class="murtadd-fatwa-filters" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'murtadd_fatwa' ) ); ?>">
	<label>
		<span><?php esc_html_e( 'School', 'murtadd' ); ?></span>
		<select name="school">
			<option value=""><?php esc_html_e( 'All schools', 'murtadd' ); ?></option>
			<?php
			$murtadd_schools = get_terms( array( 'taxonomy' => 'murtadd_school', 'hide_empty' => false ) );
			if ( ! is_wp_error( $murtadd_schools ) ) :
				foreach ( $murtadd_schools as $murtadd_school ) :
					?>
					<option value="<?php echo esc_attr( $murtadd_school->slug ); ?>" <?php selected( $murtadd_sel_school, $murtadd_school->slug ); ?>><?php echo esc_html( $murtadd_school->name ); ?></option>
				<?php endforeach; endif; ?>
		</select>
	</label>
	<label>
		<span><?php esc_html_e( 'Era', 'murtadd' ); ?></span>
		<select name="era">
			<option value=""><?php esc_html_e( 'All eras', 'murtadd' ); ?></option>
			<option value="classical" <?php selected( $murtadd_sel_era, 'classical' ); ?>><?php esc_html_e( 'Classical', 'murtadd' ); ?></option>
			<option value="modern" <?php selected( $murtadd_sel_era, 'modern' ); ?>><?php esc_html_e( 'Modern', 'murtadd' ); ?></option>
		</select>
	</label>
	<label>
		<span><?php esc_html_e( 'Topic', 'murtadd' ); ?></span>
		<select name="topic">
			<option value=""><?php esc_html_e( 'All topics', 'murtadd' ); ?></option>
			<?php
			$murtadd_topics = get_terms( array( 'taxonomy' => 'murtadd_topic', 'hide_empty' => false ) );
			if ( ! is_wp_error( $murtadd_topics ) ) :
				foreach ( $murtadd_topics as $murtadd_topic ) :
					?>
					<option value="<?php echo esc_attr( $murtadd_topic->slug ); ?>" <?php selected( $murtadd_sel_topic, $murtadd_topic->slug ); ?>><?php echo esc_html( $murtadd_topic->name ); ?></option>
				<?php endforeach; endif; ?>
		</select>
	</label>
	<button type="submit" class="murtadd-btn"><?php esc_html_e( 'Filter', 'murtadd' ); ?></button>
</form>

<?php if ( have_posts() ) : ?>
	<div id="murtadd-fatwa-list" class="murtadd-fatwa-table" role="table">
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
		<p class="murtadd-empty"><?php esc_html_e( 'No positions match these filters.', 'murtadd' ); ?></p>
		<div class="murtadd-suggest-routes">
			<a class="murtadd-btn murtadd-btn--outline" href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_fatwa' ) ); ?>"><?php esc_html_e( 'Clear filters', 'murtadd' ); ?></a>
		</div>
	</div>
<?php endif; ?>

<?php the_posts_pagination(); ?>

<?php get_footer(); ?>
