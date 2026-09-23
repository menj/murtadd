<?php
/**
 * Rail — doubt and rebuttal singles.
 *
 * The stacked right-hand column of framed boxes is the most recognisable
 * feature of the site this one answers. It is quoted here as a silhouette:
 * same position, same rhythm of stacked panels. The execution is current —
 * hairline cards, tracked labels, sticky positioning, no filled header bars
 * and no period ornament.
 *
 * The scripture box is the point of the whole gesture. The original site
 * placed the same verse in the same position and read it as a licence to
 * leave. Here it sits in the same furniture and answers the other way.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

$murtadd_display    = get_option( 'murtadd_content_display', array() );
$murtadd_show_verse = ! isset( $murtadd_display['show_scripture'] ) || $murtadd_display['show_scripture'];
$murtadd_verse      = isset( $murtadd_display['scripture_text'] ) ? $murtadd_display['scripture_text'] : __( 'Let there be no compulsion in religion.', 'murtadd' );
$murtadd_verse_ref  = isset( $murtadd_display['scripture_ref'] ) ? $murtadd_display['scripture_ref'] : __( 'Qur’an 2:256', 'murtadd' );
?>
<aside class="murtadd-rail" aria-label="<?php esc_attr_e( 'Related', 'murtadd' ); ?>">

	<?php if ( $murtadd_show_verse && $murtadd_verse ) : ?>
		<div class="murtadd-rail-card murtadd-rail-card--verse">
			<blockquote class="murtadd-verse">
				<p><?php echo esc_html( $murtadd_verse ); ?></p>
				<cite><?php echo esc_html( $murtadd_verse_ref ); ?></cite>
			</blockquote>
		</div>
	<?php endif; ?>

	<?php
	$murtadd_start = get_page_by_path( 'start-here' );
	if ( $murtadd_start ) :
		?>
		<div class="murtadd-rail-card">
			<h2 class="murtadd-rail-label"><?php esc_html_e( 'New here', 'murtadd' ); ?></h2>
			<p class="murtadd-rail-text"><?php esc_html_e( 'Three routes in, depending on where you actually are.', 'murtadd' ); ?></p>
			<a class="murtadd-rail-link" href="<?php echo esc_url( get_permalink( $murtadd_start ) ); ?>"><?php esc_html_e( 'Start here', 'murtadd' ); ?></a>
		</div>
	<?php endif; ?>

	<?php
	$murtadd_recent = get_posts(
		array(
			'post_type'      => array( 'murtadd_doubt', 'murtadd_rebuttal' ),
			'posts_per_page' => 4,
			'post__not_in'   => array( get_the_ID() ),
			'no_found_rows'  => true,
		)
	);
	if ( $murtadd_recent ) :
		?>
		<div class="murtadd-rail-card">
			<h2 class="murtadd-rail-label"><?php esc_html_e( 'Recently added', 'murtadd' ); ?></h2>
			<ul class="murtadd-rail-list">
				<?php foreach ( $murtadd_recent as $murtadd_item ) : ?>
					<li>
						<a href="<?php echo esc_url( get_permalink( $murtadd_item ) ); ?>">
							<span class="murtadd-rail-type"><?php echo esc_html( murtadd_type_label( $murtadd_item->post_type ) ); ?></span>
							<?php echo esc_html( get_the_title( $murtadd_item ) ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

</aside>
