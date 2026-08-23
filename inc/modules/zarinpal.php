<?php
/**
 * ماژول درگاه پرداخت زرین‌پال
 * شامل: API مستقیم برای کیف پول + درگاه ووکامرس
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * ارسال درخواست پرداخت به زرین‌پال.
 *
 * @param float  $amount      مبلغ (تومان).
 * @param string $description توضیح.
 * @param string $callback    آدرس بازگشت.
 * @param array  $meta        اطلاعات تکمیلی (email, mobile).
 * @return array|WP_Error
 */
function zc_zarinpal_request( $amount, $description, $callback, $meta = array() ) {
	$merchant = trim( (string) ( $meta['merchant'] ?? zc_opt( 'zc_zarinpal_merchant', '' ) ) );

	if ( ! $merchant ) {
		return new WP_Error( 'zc_zp_config', __( 'مرچنت کد زرین‌پال تنظیم نشده است.', 'zarincode' ) );
	}

	$sandbox  = (bool) zc_opt( 'zc_zarinpal_sandbox', false );
	$currency = zc_opt( 'zc_zarinpal_currency', 'IRT' ); // IRT=تومان, IRR=ریال.
	$amount   = (int) round( $amount );

	$base = $sandbox ? 'https://sandbox.zarinpal.com' : 'https://payment.zarinpal.com';
	$url  = $base . '/pg/v4/payment/request.json';

	$user = wp_get_current_user();

	$body = array(
		'merchant_id'  => $merchant,
		'amount'       => 'IRR' === $currency ? $amount * 10 : $amount,
		'description'  => mb_substr( $description, 0, 250 ),
		'callback_url' => $callback,
		'metadata'     => array_filter(
			array(
				'email'  => $meta['email'] ?? ( $user->ID ? $user->user_email : '' ),
				'mobile' => $meta['mobile'] ?? ( $user->ID ? get_user_meta( $user->ID, 'zc_mobile', true ) : '' ),
			)
		),
	);

	$response = wp_remote_post(
		$url,
		array(
			'timeout' => 25,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
			),
			'body'    => wp_json_encode( $body ),
		)
	);

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'zc_zp_connection', __( 'ارتباط با درگاه پرداخت برقرار نشد.', 'zarincode' ) );
	}

	$result = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( empty( $result['data']['authority'] ) || 100 !== (int) ( $result['data']['code'] ?? 0 ) ) {
		$error = $result['errors']['message'] ?? __( 'خطا در ایجاد تراکنش.', 'zarincode' );
		zc_log( $result, 'Zarinpal-Request' );
		return new WP_Error( 'zc_zp_failed', $error );
	}

	$authority = $result['data']['authority'];

	// ذخیره موقت اطلاعات تراکنش.
	set_transient(
		'zc_zp_' . $authority,
		array(
			'amount'  => $amount,
			'user_id' => get_current_user_id(),
			'desc'    => $description,
			'time'    => time(),
		),
		2 * HOUR_IN_SECONDS
	);

	return array(
		'authority' => $authority,
		'url'       => $base . '/pg/StartPay/' . $authority,
	);
}

/**
 * تایید پرداخت زرین‌پال.
 *
 * @param string $authority کد authority.
 * @param float  $amount    مبلغ.
 * @param string $merchant  مرچنت اختصاصی درگاه ووکامرس.
 * @return array|WP_Error
 */
function zc_zarinpal_verify( $authority, $amount, $merchant = '' ) {
	$merchant = trim( (string) ( $merchant ?: zc_opt( 'zc_zarinpal_merchant', '' ) ) );
	$sandbox  = (bool) zc_opt( 'zc_zarinpal_sandbox', false );
	$currency = zc_opt( 'zc_zarinpal_currency', 'IRT' );
	$amount   = (int) round( $amount );

	$base = $sandbox ? 'https://sandbox.zarinpal.com' : 'https://payment.zarinpal.com';

	$response = wp_remote_post(
		$base . '/pg/v4/payment/verify.json',
		array(
			'timeout' => 25,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
			),
			'body'    => wp_json_encode(
				array(
					'merchant_id' => $merchant,
					'amount'      => 'IRR' === $currency ? $amount * 10 : $amount,
					'authority'   => $authority,
				)
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'zc_zp_connection', __( 'ارتباط با درگاه پرداخت برقرار نشد.', 'zarincode' ) );
	}

	$result = json_decode( wp_remote_retrieve_body( $response ), true );
	$code   = (int) ( $result['data']['code'] ?? 0 );

	// کد 100: موفق | کد 101: قبلاً تایید شده.
	if ( 100 !== $code && 101 !== $code ) {
		$error = $result['errors']['message'] ?? __( 'پرداخت تایید نشد.', 'zarincode' );
		zc_log( $result, 'Zarinpal-Verify' );
		return new WP_Error( 'zc_zp_verify_failed', $error );
	}

	return array(
		'ref_id'    => $result['data']['ref_id'] ?? '',
		'card_pan'  => $result['data']['card_pan'] ?? '',
		'fee'       => $result['data']['fee'] ?? 0,
		'code'      => $code,
	);
}

/**
 * ثبت درگاه زرین‌پال در ووکامرس.
 *
 * @param array $gateways درگاه‌ها.
 * @return array
 */
function zc_add_zarinpal_gateway( $gateways ) {
	/*
	 * اطمینان از اینکه کلاس درگاه پیش از ثبت در فیلتر تعریف شده است.
	 * ووکامرس ممکن است گیتوی‌ها را پیش از اجرای plugins_loaded (که
	 * zc_init_zarinpal_gateway روی آن است) لیست کند؛ در این صورت بدون
	 * تعریف کلاس، درگاه در فهرست فعال نمی‌شود.
	 */
	if ( function_exists( 'zc_init_zarinpal_gateway' ) ) {
		zc_init_zarinpal_gateway();
	}
	$gateways[] = 'ZC_Gateway_Zarinpal';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'zc_add_zarinpal_gateway', 10, 1 );

/**
 * تعریف کلاس درگاه ووکامرس.
 *
 * @return void
 */
function zc_init_zarinpal_gateway() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) || class_exists( 'ZC_Gateway_Zarinpal' ) ) {
		return;
	}

	/**
	 * کلاس درگاه زرین‌پال.
	 */
	class ZC_Gateway_Zarinpal extends WC_Payment_Gateway {

		/**
		 * سازنده.
		 */
		public function __construct() {
			$this->id                 = 'zc_zarinpal';
			$this->icon               = ZC_ASSETS . 'img/zarinpal.svg';
			$this->has_fields         = false;
			$this->method_title       = __( 'زرین‌پال (زرین کد)', 'zarincode' );
			$this->method_description = __( 'پرداخت امن آنلاین از طریق درگاه زرین‌پال با تمام کارت‌های عضو شتاب.', 'zarincode' );
			// بازپرداخت API زرین‌پال بدون دسترسی سرویس Refund تضمین‌شده نیست.
			$this->supports           = array( 'products' );

			$this->init_form_fields();
			$this->init_settings();

			$this->title       = $this->get_option( 'title', __( 'پرداخت آنلاین زرین‌پال', 'zarincode' ) );
			$this->description = $this->get_option( 'description', __( 'پرداخت امن با تمام کارت‌های بانکی عضو شتاب', 'zarincode' ) );

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_api_zc_zarinpal', array( $this, 'callback_handler' ) );
			add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
		}

		/**
		 * فیلدهای تنظیمات.
		 *
		 * @return void
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'     => array(
					'title'   => __( 'فعال‌سازی', 'zarincode' ),
					'type'    => 'checkbox',
					'label'   => __( 'فعال‌سازی درگاه زرین‌پال', 'zarincode' ),
					'default' => 'yes',
				),
				'title'       => array(
					'title'   => __( 'عنوان', 'zarincode' ),
					'type'    => 'text',
					'default' => __( 'پرداخت آنلاین زرین‌پال', 'zarincode' ),
				),
				'description' => array(
					'title'   => __( 'توضیحات', 'zarincode' ),
					'type'    => 'textarea',
					'default' => __( 'پرداخت امن با تمام کارت‌های بانکی عضو شتاب', 'zarincode' ),
				),
				'merchant'    => array(
					'title'       => __( 'مرچنت کد', 'zarincode' ),
					'type'        => 'text',
					'description' => __( 'کد ۳۶ کاراکتری دریافتی از پنل زرین‌پال. در صورت خالی بودن، از تنظیمات قالب خوانده می‌شود.', 'zarincode' ),
					'default'     => '',
				),
				'success_msg' => array(
					'title'   => __( 'پیام موفقیت', 'zarincode' ),
					'type'    => 'textarea',
					'default' => __( 'پرداخت شما با موفقیت انجام شد. کد پیگیری: {ref_id}', 'zarincode' ),
				),
				'failed_msg'  => array(
					'title'   => __( 'پیام ناموفق', 'zarincode' ),
					'type'    => 'textarea',
					'default' => __( 'پرداخت انجام نشد. در صورت کسر وجه، طی ۷۲ ساعت به حساب شما بازمی‌گردد.', 'zarincode' ),
				),
			);
		}

		/**
		 * پردازش پرداخت.
		 *
		 * @param int $order_id سفارش.
		 * @return array
		 */
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );

			$amount = (float) $order->get_total();

			// اعمال کیف پول در صورت انتخاب.
			$amount = zc_apply_wallet_to_order( $order, $amount );

			if ( $amount <= 0 ) {
				$order->payment_complete();
				$order->add_order_note( __( 'پرداخت کامل از طریق کیف پول انجام شد.', 'zarincode' ) );
				WC()->cart->empty_cart();
				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order ),
				);
			}

			$callback = add_query_arg( 'wc_order', $order_id, WC()->api_request_url( 'zc_zarinpal' ) );

			$result = zc_zarinpal_request(
				$amount,
				sprintf( /* translators: %s: order number */ __( 'پرداخت سفارش شماره %s', 'zarincode' ), $order->get_order_number() ),
				$callback,
				array(
					'email'    => $order->get_billing_email(),
					'mobile'   => $order->get_billing_phone(),
					'merchant' => trim( (string) $this->get_option( 'merchant', '' ) ),
				)
			);

			if ( is_wp_error( $result ) ) {
				zc_restore_order_wallet( $order, 'gateway_request_failed' );
				wc_add_notice( $result->get_error_message(), 'error' );
				return array( 'result' => 'failure' );
			}

			$order->update_meta_data( '_zc_zp_authority', $result['authority'] );
			$order->update_meta_data( '_zc_zp_amount', $amount );
			$order->update_meta_data( '_zc_zp_merchant', trim( (string) $this->get_option( 'merchant', '' ) ) );
			$order->save();

			return array(
				'result'   => 'success',
				'redirect' => $result['url'],
			);
		}

		/**
		 * صفحه رسید.
		 *
		 * @param int $order_id سفارش.
		 * @return void
		 */
		public function receipt_page( $order_id ) {
			echo '<p>' . esc_html__( 'در حال انتقال به درگاه پرداخت…', 'zarincode' ) . '</p>';
		}

		/**
		 * پردازش بازگشت از درگاه.
		 *
		 * @return void
		 */
		public function callback_handler() {
			$order_id  = isset( $_GET['wc_order'] ) ? absint( $_GET['wc_order'] ) : 0; // phpcs:ignore
			$authority = isset( $_GET['Authority'] ) ? sanitize_text_field( wp_unslash( $_GET['Authority'] ) ) : ''; // phpcs:ignore
			$status    = isset( $_GET['Status'] ) ? sanitize_text_field( wp_unslash( $_GET['Status'] ) ) : ''; // phpcs:ignore

			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				wp_safe_redirect( wc_get_page_permalink( 'shop' ) );
				exit;
			}

			if ( $order->is_paid() ) {
				wp_safe_redirect( $this->get_return_url( $order ) );
				exit;
			}

			$saved_authority = (string) $order->get_meta( '_zc_zp_authority', true );
			if ( ! $authority || ! $saved_authority || ! hash_equals( $saved_authority, $authority ) ) {
				wc_add_notice( __( 'شناسه تراکنش با سفارش مطابقت ندارد.', 'zarincode' ), 'error' );
				wp_safe_redirect( wc_get_checkout_url() );
				exit;
			}

			if ( 'OK' !== $status ) {
				$order->update_status( 'failed', __( 'پرداخت توسط کاربر لغو شد.', 'zarincode' ) );
				wc_add_notice( $this->get_option( 'failed_msg' ), 'error' );
				wp_safe_redirect( wc_get_checkout_url() );
				exit;
			}

			$amount = (float) $order->get_meta( '_zc_zp_amount' );
			$amount = $amount ? $amount : (float) $order->get_total();

			$verify = zc_zarinpal_verify( $authority, $amount, (string) $order->get_meta( '_zc_zp_merchant', true ) );

			if ( is_wp_error( $verify ) ) {
				$order->update_status( 'failed', $verify->get_error_message() );
				wc_add_notice( $verify->get_error_message(), 'error' );
				wp_safe_redirect( wc_get_checkout_url() );
				exit;
			}

			$order->payment_complete( $verify['ref_id'] );
			$order->update_meta_data( '_zc_zp_ref_id', $verify['ref_id'] );
			$order->update_meta_data( '_zc_zp_card', $verify['card_pan'] );
			$order->save();

			$order->add_order_note(
				sprintf(
					/* translators: 1: ref id 2: card */
					__( 'پرداخت موفق زرین‌پال. کد پیگیری: %1$s | کارت: %2$s', 'zarincode' ),
					$verify['ref_id'],
					$verify['card_pan']
				)
			);

			// ثبت در سیستم حسابداری.
			zc_add_transaction(
				array(
					'user_id'     => $order->get_user_id(),
					'amount'      => $amount,
					'type'        => 'income',
					'category'    => 'order',
					'description' => sprintf( /* translators: %s: order */ __( 'پرداخت سفارش #%s', 'zarincode' ), $order->get_order_number() ),
					'ref_id'      => $verify['ref_id'],
					'authority'   => $authority,
					'gateway'     => 'zarinpal',
					'status'      => 'completed',
				)
			);

			wc_add_notice( str_replace( '{ref_id}', $verify['ref_id'], $this->get_option( 'success_msg' ) ), 'success' );
			WC()->cart->empty_cart();

			wp_safe_redirect( $this->get_return_url( $order ) );
			exit;
		}
	}
}
add_action( 'plugins_loaded', 'zc_init_zarinpal_gateway', 11 );

/**
 * اعمال موجودی کیف پول روی سفارش.
 *
 * @param WC_Order $order  سفارش.
 * @param float    $amount مبلغ.
 * @return float مبلغ باقی‌مانده.
 */
function zc_apply_wallet_to_order( $order, $amount ) {
	if ( ! zc_opt( 'zc_wallet_enable', true ) || ! $order ) {
		return $amount;
	}

	// آغاز دوبارهٔ همان پرداخت نباید برداشت تازه ایجاد کند.
	$existing = (float) $order->get_meta( '_zc_wallet_paid', true );
	if ( $existing > 0 && ! $order->get_meta( '_zc_wallet_restored', true ) ) {
		return max( 0, $amount - $existing );
	}

	$use = isset( $_POST['zc_use_wallet'] ); // phpcs:ignore
	if ( ! $use && function_exists( 'WC' ) && WC()->session ) {
		$use = (bool) WC()->session->get( 'zc_use_wallet' );
	}
	if ( ! $use ) {
		return $amount;
	}

	$user_id = (int) $order->get_user_id();
	$balance = zc_wallet_balance( $user_id );
	if ( ! $user_id || $balance <= 0 ) {
		return $amount;
	}

	$deduct = min( $balance, (float) $amount );
	$tx     = zc_wallet_withdraw(
		$user_id,
		$deduct,
		sprintf( __( 'رزرو پرداخت سفارش #%s از کیف پول', 'zarincode' ), $order->get_order_number() ),
		'order',
		array( 'ref_id' => 'wallet-order-' . $order->get_id(), 'gateway' => 'wallet', 'status' => 'pending', 'meta' => array( 'order_id' => $order->get_id(), 'reservation' => true ) )
	);

	if ( is_wp_error( $tx ) || ! $tx ) {
		return $amount;
	}

	$order->update_meta_data( '_zc_wallet_paid', $deduct );
	$order->delete_meta_data( '_zc_wallet_restored' );
	$order->update_meta_data( '_zc_wallet_transaction_id', (int) $tx );
	$order->add_order_note( sprintf( __( 'مبلغ %s از کیف پول کاربر برای این سفارش رزرو شد.', 'zarincode' ), zc_fa_num( number_format( $deduct ) ) ) );
	$order->save();

	return max( 0, $amount - $deduct );
}

/**
 * قطعی‌کردن بخش کیف پول پس از پرداخت موفق سفارش.
 *
 * @param int $order_id سفارش.
 * @return void
 */
function zc_commit_order_wallet( $order_id ) {
	$order = function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : false;
	if ( $order && (float) $order->get_meta( '_zc_wallet_paid', true ) > 0 && ! $order->get_meta( '_zc_wallet_committed', true ) ) {
		global $wpdb;
		$amount = (float) $order->get_meta( '_zc_wallet_paid', true );
		$tx_id  = (int) $order->get_meta( '_zc_wallet_transaction_id', true );
		if ( $tx_id ) {
			$wpdb->update( $wpdb->prefix . 'zc_transactions', array( 'status' => 'completed', 'description' => sprintf( __( 'پرداخت قطعی سفارش #%s از کیف پول', 'zarincode' ), $order->get_order_number() ) ), array( 'id' => $tx_id ), array( '%s', '%s' ), array( '%d' ) ); // phpcs:ignore
		}
		zc_add_transaction(
			array(
				'user_id' => $order->get_user_id(), 'amount' => $amount, 'type' => 'income', 'category' => 'order',
				'description' => sprintf( __( 'درآمد بخش کیف پول سفارش #%s', 'zarincode' ), $order->get_order_number() ),
				'ref_id' => 'wallet-income-' . $order->get_id(), 'gateway' => 'wallet', 'status' => 'completed',
			)
		);
		$order->update_meta_data( '_zc_wallet_committed', '1' );
		$order->save();
	}
}
add_action( 'woocommerce_payment_complete', 'zc_commit_order_wallet', 5 );

/**
 * بازگرداندن رزرو کیف پول برای پرداخت ناموفق/لغوشده.
 *
 * @param int|WC_Order $order_or_id سفارش.
 * @param string       $reason      دلیل.
 * @return bool
 */
function zc_restore_order_wallet( $order_or_id, $reason = '' ) {
	$order = is_object( $order_or_id ) ? $order_or_id : ( function_exists( 'wc_get_order' ) ? wc_get_order( $order_or_id ) : false );
	if ( ! $order || $order->get_meta( '_zc_wallet_restored', true ) ) {
		return false;
	}

	$amount  = (float) $order->get_meta( '_zc_wallet_paid', true );
	$user_id = (int) $order->get_user_id();
	if ( $amount <= 0 || ! $user_id ) {
		return false;
	}

	global $wpdb;
	$original_tx = (int) $order->get_meta( '_zc_wallet_transaction_id', true );

	$tx = zc_wallet_deposit(
		$user_id,
		$amount,
		sprintf( __( 'بازگشت رزرو کیف پول سفارش #%s', 'zarincode' ), $order->get_order_number() ),
		'refund',
		array( 'ref_id' => 'wallet-restore-' . $order->get_id(), 'gateway' => 'wallet', 'meta' => array( 'reason' => $reason ) )
	);
	if ( ! $tx ) {
		return false;
	}
	if ( $original_tx ) {
		$wpdb->update( $wpdb->prefix . 'zc_transactions', array( 'status' => 'reversed' ), array( 'id' => $original_tx ), array( '%s' ), array( '%d' ) ); // phpcs:ignore
	}

	if ( $order->get_meta( '_zc_wallet_committed', true ) ) {
		zc_add_transaction( array( 'user_id' => $user_id, 'amount' => -$amount, 'type' => 'refund', 'category' => 'order', 'status' => 'completed', 'description' => sprintf( __( 'بازپرداخت سفارش #%s', 'zarincode' ), $order->get_order_number() ), 'ref_id' => 'wallet-refund-income-' . $order->get_id(), 'gateway' => 'wallet' ) );
	}
	$order->update_meta_data( '_zc_wallet_restored', '1' );
	$order->delete_meta_data( '_zc_wallet_committed' );
	$order->add_order_note( sprintf( __( 'مبلغ %s به کیف پول کاربر بازگردانده شد.', 'zarincode' ), number_format( $amount ) ) );
	$order->save();
	return true;
}

/** @param int $order_id سفارش. */
function zc_restore_failed_order_wallet( $order_id ) {
	zc_restore_order_wallet( $order_id, 'order_failed_or_cancelled' );
}
add_action( 'woocommerce_order_status_failed', 'zc_restore_failed_order_wallet', 5 );
add_action( 'woocommerce_order_status_cancelled', 'zc_restore_failed_order_wallet', 5 );
add_action( 'woocommerce_order_fully_refunded', static function ( $order_id ) {
	zc_restore_order_wallet( $order_id, 'order_fully_refunded' );
}, 10 );
