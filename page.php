<?php
/**
 * Generic page.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'murtadd-page' ); ?>>
		<header class="murtadd-entry-header">
			<h1><?php the_title(); ?></h1>
		</header>
		<div class="murtadd-page-body">
			<?php the_content(); ?>
		</div>
	</article>

	<?php
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}
	?>
	<?php
endwhile;

get_footer();
