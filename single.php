<?php
/**
 * Single blog post.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'murtadd-blog-post' ); ?>>
		<header class="murtadd-entry-header">
			<a class="murtadd-back" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">← <?php esc_html_e( 'Back to blog', 'murtadd' ); ?></a>
			<div class="murtadd-meta-row">
				<span><?php echo esc_html( get_the_date() ); ?></span>
				<span>·</span>
				<span><?php echo esc_html( murtadd_read_time( get_the_content() ) ); ?></span>
			</div>
			<h1><?php the_title(); ?></h1>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="murtadd-featured"><?php the_post_thumbnail( 'large' ); ?></figure>
		<?php endif; ?>

		<div class="murtadd-blog-body murtadd-dropcap">
			<?php the_content(); ?>
		</div>

		<?php
		$murtadd_topics = get_the_terms( get_the_ID(), 'murtadd_topic' );
		if ( $murtadd_topics && ! is_wp_error( $murtadd_topics ) ) :
			?>
			<div class="murtadd-chips">
				<?php foreach ( $murtadd_topics as $murtadd_topic ) : ?>
					<a class="murtadd-chip" href="<?php echo esc_url( get_term_link( $murtadd_topic ) ); ?>"><?php echo esc_html( $murtadd_topic->name ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</article>

	<?php
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}
	?>
	<?php
endwhile;

get_footer();
