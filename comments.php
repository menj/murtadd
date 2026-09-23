<?php
/**
 * Comments — blog posts and pages.
 * Threaded, paginated, with the theme's own form markup.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

// Do not load for password-protected posts until the password is supplied.
if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="murtadd-comments">

	<?php if ( have_comments() ) : ?>
		<h2 class="murtadd-kicker murtadd-kicker--accent">
			<?php
			$murtadd_count = (int) get_comments_number();
			/* translators: %d: comment count */
			printf( esc_html( _n( '%d response', '%d responses', $murtadd_count, 'murtadd' ) ), $murtadd_count );
			?>
		</h2>

		<ol class="murtadd-comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 40,
					'callback'    => 'murtadd_render_comment',
				)
			);
			?>
		</ol>

		<?php the_comments_pagination( array( 'screen_reader_text' => __( 'Comments navigation', 'murtadd' ) ) ); ?>

		<?php if ( ! comments_open() ) : ?>
			<p class="murtadd-comments-closed"><?php esc_html_e( 'Comments are closed on this post.', 'murtadd' ); ?></p>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_form'           => 'murtadd-comment-form',
			'class_submit'         => 'murtadd-btn',
			'title_reply'          => __( 'Leave a response', 'murtadd' ),
			'title_reply_to'       => __( 'Reply to %s', 'murtadd' ),
			'cancel_reply_link'    => __( 'Cancel reply', 'murtadd' ),
			'label_submit'         => __( 'Post response', 'murtadd' ),
			'title_reply_before'   => '<h2 id="reply-title" class="murtadd-kicker murtadd-kicker--accent">',
			'title_reply_after'    => '</h2>',
			'comment_notes_before' => '<p class="murtadd-comment-notes">' . esc_html__( 'Responses are moderated before they appear. Address the argument, not the person. Your email address is never published.', 'murtadd' ) . '</p>',
			'comment_field'        => sprintf(
				'<p class="comment-form-comment"><label for="comment">%1$s</label><textarea id="comment" name="comment" rows="6" required></textarea></p>',
				esc_html__( 'Your response', 'murtadd' )
			),
		)
	);
	?>
</section>
