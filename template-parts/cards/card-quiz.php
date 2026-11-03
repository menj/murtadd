<?php
/**
 * Card: "Not sure where to start?" link to the Compelling Evidence persona
 * quiz. Home page and Start Here. Opens in the same tab. Renders nothing when
 * the switch on the Linked sites tab is off or the base URL is cleared.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

$murtadd_quiz = murtadd_quiz_url();
if ( '' === $murtadd_quiz ) {
	return;
}
?>
<aside class="murtadd-quiz-card">
	<h2 class="murtadd-quiz-card__title"><?php esc_html_e( 'Not sure where to start?', 'murtadd' ); ?></h2>
	<p class="murtadd-quiz-card__body"><?php esc_html_e( 'A few quick questions can point you to a reading path that fits where you are. There are no right answers, and your answers stay on your device.', 'murtadd' ); ?></p>
	<a class="murtadd-btn murtadd-quiz-card__btn" href="<?php echo esc_url( $murtadd_quiz ); ?>" aria-label="<?php esc_attr_e( 'Find your starting point: a short quiz on Compelling Evidence that suggests a reading path', 'murtadd' ); ?>"><?php esc_html_e( 'Find your starting point', 'murtadd' ); ?></a>
</aside>
