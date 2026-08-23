<?php
/**
 * کلاس درس تمام‌صفحه.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_course_id = (int) get_query_var( 'zc_learn' );
if ( ! $zc_course_id && isset( $_GET['zc_learn'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$zc_course_id = absint( wp_unslash( $_GET['zc_learn'] ) ); // phpcs:ignore
}
$zc_lesson_key = isset( $_GET['lesson'] ) ? sanitize_text_field( wp_unslash( $_GET['lesson'] ) ) : (string) get_query_var( 'zc_lesson' ); // phpcs:ignore
$zc_lessons    = zc_flatten_lessons( $zc_course_id );
if ( ! $zc_lesson_key && $zc_lessons ) {
	$zc_lesson_key = $zc_lessons[0]['key'];
}
$zc_current = $zc_lesson_key ? zc_find_lesson( $zc_course_id, $zc_lesson_key ) : null;
$zc_user    = wp_get_current_user();
$zc_embed   = $zc_current ? zc_video_embed_data( $zc_current['video'] ?? '' ) : array( 'type' => 'none', 'src' => '' );
$zc_progress = zc_get_course_progress( $zc_user->ID, $zc_course_id );
$zc_prev     = '';
$zc_next     = '';
foreach ( $zc_lessons as $i => $item ) {
	if ( $item['key'] === $zc_lesson_key ) {
		$zc_prev = $i > 0 ? $zc_lessons[ $i - 1 ]['key'] : '';
		$zc_next = isset( $zc_lessons[ $i + 1 ] ) ? $zc_lessons[ $i + 1 ]['key'] : '';
		break;
	}
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( get_the_title( $zc_course_id ) ); ?></title>
	<?php wp_head(); ?>
	<link rel="stylesheet" href="<?php echo esc_url( ZC_ASSETS . 'css/classroom.css' ); ?>?ver=<?php echo esc_attr( ZC_VERSION ); ?>">
</head>
<body class="zc-classroom-body">
<div class="zc-class" data-course="<?php echo (int) $zc_course_id; ?>" data-lesson="<?php echo esc_attr( $zc_lesson_key ); ?>" data-threshold="<?php echo (int) zc_opt( 'zc_lesson_complete_percent', 80 ); ?>">
	<aside class="zc-class__side">
		<a class="zc-class__brand" href="<?php echo esc_url( get_permalink( $zc_course_id ) ); ?>"><?php echo esc_html( get_the_title( $zc_course_id ) ); ?></a>
		<div class="zc-class__prog"><i style="width:<?php echo (int) $zc_progress; ?>%"></i><span><?php echo esc_html( zc_fa_num( $zc_progress ) ); ?>٪</span></div>
		<nav class="zc-class__nav">
			<?php foreach ( $zc_lessons as $item ) : ?>
				<?php
				$locked = ! zc_lesson_is_unlocked( $zc_user->ID, $zc_course_id, $item ) && empty( $item['free'] );
				$done   = zc_is_lesson_completed( $zc_user->ID, $zc_course_id, $item['key'] );
				?>
				<?php if ( $locked ) : ?>
					<span class="zc-class__item is-locked"><?php echo esc_html( $item['title'] ?? '' ); ?></span>
				<?php else : ?>
					<a class="zc-class__item<?php echo $item['key'] === $zc_lesson_key ? ' is-active' : ''; ?><?php echo $done ? ' is-done' : ''; ?>"
						href="<?php echo esc_url( zc_classroom_url( $zc_course_id, $item['key'] ) ); ?>">
						<?php echo esc_html( $item['title'] ?? '' ); ?>
					</a>
				<?php endif; ?>
			<?php endforeach; ?>
		</nav>
	</aside>
	<main class="zc-class__main">
		<header class="zc-class__bar">
			<h1><?php echo esc_html( $zc_current['title'] ?? get_the_title( $zc_course_id ) ); ?></h1>
			<div class="zc-class__actions">
				<?php if ( $zc_prev ) : ?><a class="zc-btn zc-btn--ghost zc-btn--sm" href="<?php echo esc_url( zc_classroom_url( $zc_course_id, $zc_prev ) ); ?>"><?php esc_html_e( 'جلسه قبل', 'zarincode' ); ?></a><?php endif; ?>
				<?php if ( $zc_next ) : ?><a class="zc-btn zc-btn--gold zc-btn--sm" href="<?php echo esc_url( zc_classroom_url( $zc_course_id, $zc_next ) ); ?>"><?php esc_html_e( 'جلسه بعد', 'zarincode' ); ?></a><?php endif; ?>
				<a class="zc-btn zc-btn--ghost zc-btn--sm" href="<?php echo esc_url( zc_panel_url( 'courses' ) ); ?>"><?php esc_html_e( 'پنل', 'zarincode' ); ?></a>
			</div>
		</header>
		<div class="zc-class__stage">
			<?php if ( 'file' === $zc_embed['type'] ) : ?>
				<video class="zc-class__video" controls controlsList="nodownload" data-zc-player>
					<source src="<?php echo esc_url( $zc_embed['src'] ); ?>">
				</video>
			<?php elseif ( 'iframe' === $zc_embed['type'] ) : ?>
				<iframe class="zc-class__frame" src="<?php echo esc_url( $zc_embed['src'] ); ?>" allowfullscreen allow="accelerometer; autoplay; encrypted-media; picture-in-picture" data-zc-iframe></iframe>
			<?php else : ?>
				<div class="zc-class__empty"><?php esc_html_e( 'ویدیویی برای این جلسه ثبت نشده است.', 'zarincode' ); ?></div>
			<?php endif; ?>
			<div class="zc-class__mark" aria-hidden="true"><?php echo esc_html( $zc_user->display_name . ' · ' . $zc_user->user_email ); ?></div>
		</div>
		<?php if ( ! empty( $zc_current['content'] ) ) : ?>
			<div class="zc-class__notes"><?php echo wp_kses_post( wpautop( $zc_current['content'] ) ); ?></div>
		<?php endif; ?>
	</main>
</div>
<?php wp_footer(); ?>
<script src="<?php echo esc_url( ZC_ASSETS . 'js/player.js' ); ?>?ver=<?php echo esc_attr( ZC_VERSION ); ?>"></script>
</body>
</html>
