<?php
/**
 * کارت مدرس
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_id       = get_the_ID();
$zc_role     = get_post_meta( $zc_id, '_zc_teacher_role', true );
$zc_courses  = (int) get_post_meta( $zc_id, '_zc_teacher_courses', true );
$zc_students = (int) get_post_meta( $zc_id, '_zc_teacher_students', true );
?>
<article <?php post_class( 'zc-card' ); ?> style="text-align:center;padding:24px 18px" data-zc-anim="up">
	<a href="<?php the_permalink(); ?>" class="zc-avatar zc-avatar--xl" style="margin:0 auto 14px;display:block">
		<?php echo zc_thumbnail( $zc_id, 'zc-avatar' ); // phpcs:ignore ?>
	</a>
	<h3 class="zc-card__title" style="margin-bottom:4px"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
	<?php if ( $zc_role ) : ?>
		<p style="font-size:.82rem;color:var(--zc-gold-3);font-weight:600;margin-bottom:10px"><?php echo esc_html( $zc_role ); ?></p>
	<?php endif; ?>
	<div class="zc-card__meta" style="justify-content:center">
		<span><?php zc_the_icon( 'video', 15 ); ?><?php echo esc_html( zc_fa_num( $zc_courses ) ); ?> <?php esc_html_e( 'دوره', 'zarincode' ); ?></span>
		<span><?php zc_the_icon( 'users', 15 ); ?><?php echo esc_html( zc_fa_num( number_format( $zc_students ) ) ); ?></span>
	</div>
</article>
