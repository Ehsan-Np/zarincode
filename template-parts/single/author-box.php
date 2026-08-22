<?php
/**
 * باکس نویسنده
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_author_id = get_the_author_meta( 'ID' );
$zc_bio       = get_the_author_meta( 'description' );
?>
<div class="zc-author-box" data-zc-anim="up">
	<div class="zc-avatar zc-avatar--lg"><?php echo get_avatar( $zc_author_id, 72 ); ?></div>
	<div class="zc-author-box__info">
		<h4><?php the_author(); ?></h4>
		<?php if ( $zc_bio ) : ?>
			<p><?php echo esc_html( $zc_bio ); ?></p>
		<?php endif; ?>
		<a href="<?php echo esc_url( get_author_posts_url( $zc_author_id ) ); ?>" class="zc-btn zc-btn--ghost zc-btn--sm">
			<?php zc_the_icon( 'grid', 15 ); ?><?php esc_html_e( 'همه نوشته‌های نویسنده', 'zarincode' ); ?>
		</a>
	</div>
</div>
