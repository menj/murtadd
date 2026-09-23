<?php
/**
 * Blog index (posts page) — rows with right rail.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="murtadd-blog-layout">
	<div class="murtadd-blog-main">
		<header class="murtadd-archive-header">
			<h1><?php esc_html_e( 'Blog', 'murtadd' ); ?></h1>
		</header>
		<div class="murtadd-article-list">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<article <?php post_class( 'murtadd-list-row murtadd-blog-row' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<a class="murtadd-blog-thumb" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium' ); ?></a>
						<?php endif; ?>
						<div>
							<h2 class="murtadd-list-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<div class="murtadd-list-excerpt"><?php the_excerpt(); ?></div>
							<div class="murtadd-list-meta"><span><?php echo esc_html( get_the_date() ); ?></span></div>
						</div>
					</article>
				<?php endwhile; ?>
				<?php the_posts_pagination(); ?>
			<?php else : ?>
				<p class="murtadd-empty"><?php esc_html_e( 'No posts yet.', 'murtadd' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<aside class="murtadd-blog-rail">
		<div class="murtadd-ayah-box">
			<p class="murtadd-ayah" lang="ar" dir="rtl">الْحَقُّ مِن رَّبِّكَ فَلَا تَكُونَنَّ مِنَ الْمُمْتَرِينَ</p>
			<p class="murtadd-ayah-tr"><?php esc_html_e( '“The truth is from your Lord — so never be among the doubters.” — Qur’an 2:147', 'murtadd' ); ?></p>
		</div>
		<div class="murtadd-rail-recent">
			<h2 class="murtadd-kicker"><?php esc_html_e( 'Recently added', 'murtadd' ); ?></h2>
			<ul>
				<?php
				$murtadd_recent = get_posts( array( 'post_type' => array( 'murtadd_doubt', 'murtadd_rebuttal' ), 'numberposts' => 3 ) );
				foreach ( $murtadd_recent as $murtadd_r ) :
					?>
					<li><a href="<?php echo esc_url( get_permalink( $murtadd_r ) ); ?>"><?php echo esc_html( get_the_title( $murtadd_r ) ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</aside>
</div>
<?php get_footer(); ?>
