<?php
/**
 * افزودن به سبد خرید برای محصول ساده — نسخه‌ی زرین کد
 * ---------------------------------------------------------------------------
 * اگر محصول از قبل در سبد خرید باشد، به‌جای فرم افزودن، دکمه‌ی «مشاهده سبد خرید»
 * نمایش داده می‌شود تا کاربر بداند محصول را در سبد دارد.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product ) {
	return;
}

$product_id = $product->get_id();

// اگر محصول در سبد خرید هست، دکمه‌ی «مشاهده سبد» را نشان بده.
if ( function_exists( 'zc_cart_has_product' ) && zc_cart_has_product( $product_id ) ) :
	?>
	<div class="zc-pdp-buy__incart">
		<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="zc-btn zc-btn--block zc-btn--gold">
			<?php zc_the_icon( 'cart', 18 ); ?>
			<span><?php esc_html_e( 'مشاهده سبد خرید', 'zarincode' ); ?></span>
		</a>
		<p class="zc-pdp-buy__incart-note"><?php esc_html_e( 'این محصول در سبد خرید شما موجود است.', 'zarincode' ); ?></p>
	</div>
	<?php
	return;
endif;

// اگر محصول قابل خرید نیست (ناموجود) بگذارید ووکامرس خودش مدیریت کند.
if ( ! $product->is_purchasable() ) {
	woocommerce_template_loop_add_to_cart();
	return;
}

do_action( 'woocommerce_before_add_to_cart_form' );
?>
<form class="cart zc-pdp-cartform" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

	<?php
	do_action( 'woocommerce_before_add_to_cart_quantity' );

	woocommerce_quantity_input(
		array(
			'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
			'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
			'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // phpcs:ignore
		),
		$product
	);

	do_action( 'woocommerce_after_add_to_cart_quantity' );
	?>

	<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>"
		class="single_add_to_cart_button zc-btn zc-btn--gold zc-btn--block alt">
		<?php zc_the_icon( 'cart', 18 ); ?>
		<span><?php echo esc_html( $product->single_add_to_cart_text() ); ?></span>
	</button>

	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
</form>
<?php
do_action( 'woocommerce_after_add_to_cart_form' );
