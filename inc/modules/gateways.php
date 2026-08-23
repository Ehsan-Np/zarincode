<?php
/**
 * درگاه‌های پرداخت اضافه زرین کد
 * ---------------------------------------------------------------------------
 * افزودن درگاه‌های ای‌دی‌پی (IdPay)، پی‌آی‌آر (Pay.ir) و پرداخت کارت‌به‌کارت
 * به ووکامرس، مطابق الگوی درگاه زرین‌پال.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

if ( ! zc_is_woo() ) {
	return;
}

/* ==========================================================================
   ثبت درگاه‌ها
   ========================================================================== */

/**
 * افزودن درگاه‌های جدید به لیست.
 *
 * @param array $gateways درگاه‌ها.
 * @return array
 */
function zc_add_extra_gateways( $gateways ) {
	zc_init_extra_gateways();
	$gateways[] = 'ZC_Gateway_Idpay';
	$gateways[] = 'ZC_Gateway_Payir';
	$gateways[] = 'ZC_Gateway_CCT';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'zc_add_extra_gateways', 10, 1 );

/**
 * تعریف کلاس‌های درگاه.
 *
 * @return void
 */
function zc_init_extra_gateways() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	/* ---------- درگاه ای‌دی‌پی ---------- */
	if ( ! class_exists( 'ZC_Gateway_Idpay' ) ) {
		class ZC_Gateway_Idpay extends WC_Payment_Gateway {
			public function __construct() {
				$this->id                 = 'zc_idpay';
				$this->has_fields         = false;
				$this->method_title       = __( 'ای‌دی‌پی (زرین کد)', 'zarincode' );
				$this->method_description = __( 'پرداخت امن آنلاین از طریق درگاه ای‌دی‌پی.', 'zarincode' );
				$this->supports           = array( 'products' );

				$this->init_form_fields();
				$this->init_settings();

				$this->title       = $this->get_option( 'title', __( 'پرداخت آنلاین ای‌دی‌پی', 'zarincode' ) );
				$this->description = $this->get_option( 'description', __( 'پرداخت امن با تمام کارت‌های بانکی عضو شتاب', 'zarincode' ) );

				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
add_action( 'woocommerce_api_zc_idpay', array( $this, 'callback_handler' ) );
					add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
				}

				public function is_available() {
					return (bool) zc_opt( 'zc_idpay_enable', false ) && parent::is_available();
				}

				public function init_form_fields() {
				$this->form_fields = array(
					'enabled'  => array( 'title' => __( 'فعال‌سازی', 'zarincode' ), 'type' => 'checkbox', 'label' => __( 'فعال‌سازی درگاه ای‌دی‌پی', 'zarincode' ), 'default' => 'no' ),
					'title'    => array( 'title' => __( 'عنوان', 'zarincode' ), 'type' => 'text', 'default' => __( 'پرداخت آنلاین ای‌دی‌پی', 'zarincode' ) ),
					'description' => array( 'title' => __( 'توضیحات', 'zarincode' ), 'type' => 'textarea', 'default' => __( 'پرداخت امن با تمام کارت‌های بانکی عضو شتاب', 'zarincode' ) ),
				);
			}

			public function process_payment( $order_id ) {
				$order = wc_get_order( $order_id );
				$amount = (float) $order->get_total();
				$amount = zc_apply_wallet_to_order( $order, $amount );

				if ( $amount <= 0 ) {
					$order->payment_complete();
					WC()->cart->empty_cart();
					return array( 'result' => 'success', 'redirect' => $this->get_return_url( $order ) );
				}

				$api = trim( (string) zc_opt( 'zc_idpay_api', '' ) );
				if ( ! $api ) {
					zc_restore_order_wallet( $order, 'idpay_not_configured' );
					wc_add_notice( __( 'درگاه ای‌دی‌پی پیکربندی نشده است.', 'zarincode' ), 'error' );
					return array( 'result' => 'failure' );
				}

				$sandbox  = (bool) zc_opt( 'zc_idpay_sandbox', false );
				$callback = add_query_arg( 'wc_order', $order_id, WC()->api_request_url( 'zc_idpay' ) );

				$response = wp_remote_post(
					'https://api.idpay.ir/v1.1/payment',
					array(
						'timeout' => 25,
						'headers' => array(
							'X-API-KEY' => $api,
							'Content-Type' => 'application/json',
							'X-SANDBOX' => $sandbox ? 'true' : 'false',
						),
						'body' => wp_json_encode(
							array(
								'order_id' => (string) $order_id,
								'amount'   => (int) round( $amount * 10 ), // ای‌دی‌پی به ریال.
								'name'     => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
								'phone'    => $order->get_billing_phone(),
								'mail'     => $order->get_billing_email(),
								'callback' => $callback,
							)
						),
					)
				);

				if ( is_wp_error( $response ) ) {
					zc_restore_order_wallet( $order, 'idpay_connection_failed' );
					wc_add_notice( __( 'خطا در اتصال به درگاه ای‌دی‌پی.', 'zarincode' ), 'error' );
					return array( 'result' => 'failure' );
				}

				$body = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( empty( $body['id'] ) ) {
					zc_restore_order_wallet( $order, 'idpay_request_rejected' );
					wc_add_notice( $body['error_message'] ?? __( 'خطا در پرداخت ای‌دی‌پی.', 'zarincode' ), 'error' );
					return array( 'result' => 'failure' );
				}

				$order->update_meta_data( '_zc_idpay_id', $body['id'] );
				$order->update_meta_data( '_zc_idpay_amount', $amount );
				$order->save();

				return array( 'result' => 'success', 'redirect' => $body['link'] );
			}

			public function receipt_page( $order_id ) {
				echo '<p>' . esc_html__( 'در حال انتقال به درگاه ای‌دی‌پی…', 'zarincode' ) . '</p>';
			}

			public function callback_handler() {
				$order_id = isset( $_GET['wc_order'] ) ? absint( $_GET['wc_order'] ) : 0; // phpcs:ignore
				$status   = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : ''; // phpcs:ignore
				$track_id = isset( $_POST['track_id'] ) ? sanitize_text_field( wp_unslash( $_POST['track_id'] ) ) : ''; // phpcs:ignore
				$id       = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : ''; // phpcs:ignore

				$order = wc_get_order( $order_id );
				if ( ! $order ) {
					wp_safe_redirect( wc_get_page_permalink( 'shop' ) );
					exit;
				}

					if ( $order->is_paid() ) {
						wp_safe_redirect( $this->get_return_url( $order ) );
						exit;
					}

					$saved_id = (string) $order->get_meta( '_zc_idpay_id', true );
					if ( ! $id || ! $saved_id || ! hash_equals( $saved_id, $id ) ) {
						wc_add_notice( __( 'شناسه تراکنش با سفارش مطابقت ندارد.', 'zarincode' ), 'error' );
						wp_safe_redirect( wc_get_checkout_url() );
						exit;
					}

					if ( 10 !== (int) $status ) {
					$order->update_status( 'failed', __( 'پرداخت توسط کاربر لغو شد.', 'zarincode' ) );
					wp_safe_redirect( wc_get_checkout_url() );
					exit;
				}

				// تأیید با ای‌دی‌پی.
				$api = trim( (string) zc_opt( 'zc_idpay_api', '' ) );
				$verify = wp_remote_post(
					'https://api.idpay.ir/v1.1/payment/verify',
					array(
						'timeout' => 25,
						'headers' => array(
							'X-API-KEY' => $api,
							'Content-Type' => 'application/json',
						),
						'body' => wp_json_encode(
							array(
								'id'       => $id,
								'order_id' => (string) $order_id,
							)
						),
					)
				);

				$vbody = json_decode( wp_remote_retrieve_body( $verify ), true );
					$expected_rial = (int) round( (float) $order->get_meta( '_zc_idpay_amount', true ) * 10 );
					if ( empty( $vbody['status'] ) || 100 !== (int) $vbody['status'] || ( isset( $vbody['amount'] ) && (int) $vbody['amount'] !== $expected_rial ) ) {
						$order->update_status( 'failed', __( 'تأیید یا مبلغ پرداخت ای‌دی‌پی نامعتبر بود.', 'zarincode' ) );
						wp_safe_redirect( wc_get_checkout_url() );
						exit;
					}

					$amount = (float) $order->get_meta( '_zc_idpay_amount' );
				$amount = $amount ? $amount : (float) $order->get_total();

				$order->payment_complete( $track_id );
				$order->update_meta_data( '_zc_idpay_track', $track_id );
				$order->save();
				$order->add_order_note( sprintf( __( 'پرداخت موفق ای‌دی‌پی. کد پیگیری: %s', 'zarincode' ), $track_id ) );

				zc_add_transaction(
					array(
						'user_id' => $order->get_user_id(),
						'amount'  => $amount,
						'type'    => 'income',
						'category'=> 'order',
						'description' => sprintf( __( 'پرداخت سفارش #%s', 'zarincode' ), $order->get_order_number() ),
						'ref_id'  => $track_id,
						'gateway' => 'idpay',
						'status'  => 'completed',
					)
				);

				WC()->cart->empty_cart();
				wc_add_notice( __( 'پرداخت با موفقیت انجام شد.', 'zarincode' ), 'success' );
				wp_safe_redirect( $this->get_return_url( $order ) );
				exit;
			}
		}
	}

	/* ---------- درگاه پی‌آی‌آر ---------- */
	if ( ! class_exists( 'ZC_Gateway_Payir' ) ) {
		class ZC_Gateway_Payir extends WC_Payment_Gateway {
			public function __construct() {
				$this->id                 = 'zc_payir';
				$this->has_fields         = false;
				$this->method_title       = __( 'پی‌آی‌آر (زرین کد)', 'zarincode' );
				$this->method_description = __( 'پرداخت امن آنلاین از طریق درگاه پی‌آی‌آر.', 'zarincode' );
				$this->supports           = array( 'products' );

				$this->init_form_fields();
				$this->init_settings();

				$this->title       = $this->get_option( 'title', __( 'پرداخت آنلاین پی‌آی‌آر', 'zarincode' ) );
				$this->description = $this->get_option( 'description', __( 'پرداخت امن با تمام کارت‌های بانکی عضو شتاب', 'zarincode' ) );

				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
add_action( 'woocommerce_api_zc_payir', array( $this, 'callback_handler' ) );
					add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
				}

				public function is_available() {
					return (bool) zc_opt( 'zc_payir_enable', false ) && parent::is_available();
				}

				public function init_form_fields() {
				$this->form_fields = array(
					'enabled'  => array( 'title' => __( 'فعال‌سازی', 'zarincode' ), 'type' => 'checkbox', 'label' => __( 'فعال‌سازی درگاه پی‌آی‌آر', 'zarincode' ), 'default' => 'no' ),
					'title'    => array( 'title' => __( 'عنوان', 'zarincode' ), 'type' => 'text', 'default' => __( 'پرداخت آنلاین پی‌آی‌آر', 'zarincode' ) ),
					'description' => array( 'title' => __( 'توضیحات', 'zarincode' ), 'type' => 'textarea', 'default' => __( 'پرداخت امن با تمام کارت‌های بانکی عضو شتاب', 'zarincode' ) ),
				);
			}

			public function process_payment( $order_id ) {
				$order = wc_get_order( $order_id );
				$amount = (float) $order->get_total();
				$amount = zc_apply_wallet_to_order( $order, $amount );

				if ( $amount <= 0 ) {
					$order->payment_complete();
					WC()->cart->empty_cart();
					return array( 'result' => 'success', 'redirect' => $this->get_return_url( $order ) );
				}

				$api = trim( (string) zc_opt( 'zc_payir_api', '' ) );
				if ( ! $api ) {
					zc_restore_order_wallet( $order, 'payir_not_configured' );
					wc_add_notice( __( 'درگاه پی‌آی‌آر پیکربندی نشده است.', 'zarincode' ), 'error' );
					return array( 'result' => 'failure' );
				}

				$callback = add_query_arg( 'wc_order', $order_id, WC()->api_request_url( 'zc_payir' ) );

				$response = wp_remote_post(
					'https://pay.ir/pg/v1/send',
					array(
						'timeout' => 25,
						'headers' => array( 'Content-Type' => 'application/json', 'Accept' => 'application/json' ),
						'body'    => wp_json_encode(
							array(
								'api'      => $api,
								'amount'   => (int) round( $amount ), // پی‌آی‌آر به تومان.
								'redirect' => $callback,
								'factorNumber' => (string) $order_id,
								'mobile'   => $order->get_billing_phone(),
								'description' => sprintf( __( 'پرداخت سفارش %s', 'zarincode' ), $order->get_order_number() ),
							)
						),
					)
				);

				if ( is_wp_error( $response ) ) {
					zc_restore_order_wallet( $order, 'payir_connection_failed' );
					wc_add_notice( __( 'خطا در اتصال به درگاه پی‌آی‌آر.', 'zarincode' ), 'error' );
					return array( 'result' => 'failure' );
				}

				$body = json_decode( wp_remote_retrieve_body( $response ), true );
				if ( empty( $body['token'] ) ) {
					zc_restore_order_wallet( $order, 'payir_request_rejected' );
					wc_add_notice( $body['errorMessage'] ?? __( 'خطا در پرداخت پی‌آی‌آر.', 'zarincode' ), 'error' );
					return array( 'result' => 'failure' );
				}

				$order->update_meta_data( '_zc_payir_token', $body['token'] );
				$order->update_meta_data( '_zc_payir_amount', $amount );
				$order->save();

				return array( 'result' => 'success', 'redirect' => 'https://pay.ir/pg/' . $body['token'] );
			}

			public function receipt_page( $order_id ) {
				echo '<p>' . esc_html__( 'در حال انتقال به درگاه پی‌آی‌آر…', 'zarincode' ) . '</p>';
			}

			public function callback_handler() {
				$order_id = isset( $_GET['wc_order'] ) ? absint( $_GET['wc_order'] ) : 0; // phpcs:ignore
				$status   = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore
				$trans_id = isset( $_GET['transId'] ) ? sanitize_text_field( wp_unslash( $_GET['transId'] ) ) : ''; // phpcs:ignore

				$order = wc_get_order( $order_id );
				if ( ! $order ) {
					wp_safe_redirect( wc_get_page_permalink( 'shop' ) );
					exit;
				}

				if ( $order->is_paid() ) {
					wp_safe_redirect( $this->get_return_url( $order ) );
					exit;
				}

				if ( '1' !== $status || ! $trans_id ) {
					$order->update_status( 'failed', __( 'پرداخت توسط کاربر لغو شد.', 'zarincode' ) );
					wp_safe_redirect( wc_get_checkout_url() );
					exit;
				}

				$api = trim( (string) zc_opt( 'zc_payir_api', '' ) );
				$verify = wp_remote_post(
					'https://pay.ir/pg/v1/verify',
					array(
						'timeout' => 25,
						'headers' => array( 'Content-Type' => 'application/json', 'Accept' => 'application/json' ),
						'body'    => wp_json_encode( array( 'api' => $api, 'token' => $order->get_meta( '_zc_payir_token' ) ) ),
					)
				);

					$vbody          = json_decode( wp_remote_retrieve_body( $verify ), true );
					$expected_amount = (int) round( (float) $order->get_meta( '_zc_payir_amount', true ) );
					if ( empty( $vbody['status'] ) || 1 !== (int) $vbody['status'] || ( isset( $vbody['amount'] ) && (int) $vbody['amount'] !== $expected_amount ) ) {
						$order->update_status( 'failed', __( 'تأیید یا مبلغ پرداخت پی‌آی‌آر نامعتبر بود.', 'zarincode' ) );
					wp_safe_redirect( wc_get_checkout_url() );
					exit;
				}

				$amount = (float) $order->get_meta( '_zc_payir_amount' );
				$amount = $amount ? $amount : (float) $order->get_total();

				$order->payment_complete( $trans_id );
				$order->update_meta_data( '_zc_payir_trans', $trans_id );
				$order->save();
				$order->add_order_note( sprintf( __( 'پرداخت موفق پی‌آی‌آر. کد پیگیری: %s', 'zarincode' ), $trans_id ) );

				zc_add_transaction(
					array(
						'user_id' => $order->get_user_id(),
						'amount'  => $amount,
						'type'    => 'income',
						'category'=> 'order',
						'description' => sprintf( __( 'پرداخت سفارش #%s', 'zarincode' ), $order->get_order_number() ),
						'ref_id'  => $trans_id,
						'gateway' => 'payir',
						'status'  => 'completed',
					)
				);

				WC()->cart->empty_cart();
				wc_add_notice( __( 'پرداخت با موفقیت انجام شد.', 'zarincode' ), 'success' );
				wp_safe_redirect( $this->get_return_url( $order ) );
				exit;
			}
		}
	}

	/* ---------- پرداخت کارت‌به‌کارت ---------- */
	if ( ! class_exists( 'ZC_Gateway_CCT' ) ) {
		class ZC_Gateway_CCT extends WC_Payment_Gateway {
			public function __construct() {
				$this->id                 = 'zc_cct';
				$this->has_fields         = true;
				$this->method_title       = __( 'کارت به کارت (زرین کد)', 'zarincode' );
				$this->method_description = __( 'پرداخت کارت‌به‌کارت؛ کاربر پس از واریز، کد پیگیری را ثبت می‌کند و پس از تأیید مدیر سفارش تکمیل می‌شود.', 'zarincode' );
				$this->supports           = array( 'products' );

				$this->init_form_fields();
				$this->init_settings();

				$this->title       = $this->get_option( 'title', __( 'پرداخت کارت به کارت', 'zarincode' ) );
				$this->description = $this->get_option( 'description', __( 'پرداخت از طریق کارت‌به‌کارت و تأیید دستی', 'zarincode' ) );

add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
				}

				public function is_available() {
					return (bool) zc_opt( 'zc_cct_enable', false ) && parent::is_available();
				}

				public function init_form_fields() {
					$this->form_fields = array(
						'enabled'  => array( 'title' => __( 'فعال‌سازی', 'zarincode' ), 'type' => 'checkbox', 'label' => __( 'فعال‌سازی پرداخت کارت‌به‌کارت', 'zarincode' ), 'default' => 'no' ),
					'title'    => array( 'title' => __( 'عنوان', 'zarincode' ), 'type' => 'text', 'default' => __( 'پرداخت کارت به کارت', 'zarincode' ) ),
					'description' => array( 'title' => __( 'توضیحات', 'zarincode' ), 'type' => 'textarea', 'default' => __( 'پرداخت از طریق کارت‌به‌کارت و تأیید دستی', 'zarincode' ) ),
				);
			}

			/**
			 * فیلدهای فرم پرداخت (شماره کارت + فیلد کد پیگیری).
			 */
			public function payment_fields() {
				if ( $this->description ) {
					echo wpautop( wp_kses_post( $this->description ) );
				}
				$card  = zc_opt( 'zc_cct_card_number', '' );
				$holder = zc_opt( 'zc_cct_card_holder', '' );
				$bank  = zc_opt( 'zc_cct_card_bank', '' );
				?>
				<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;margin-bottom:10px">
					<strong><?php esc_html_e( 'پرداخت به کارت:', 'zarincode' ); ?></strong>
					<div dir="ltr" style="text-align:left;font-weight:700;color:#0B2187;font-size:1.05rem;letter-spacing:1px;margin:6px 0"><?php echo esc_html( $card ); ?></div>
					<div style="font-size:.8rem;color:#475569"><?php echo esc_html( $holder ); ?> — <?php echo esc_html( $bank ); ?></div>
				</div>
				<p class="form-row form-row-wide">
					<label><?php esc_html_e( 'کد پیگیری / شماره تراکنش *', 'zarincode' ); ?></label>
					<input type="text" class="input-text" name="zc_cct_ref" placeholder="<?php esc_attr_e( 'کد ۱۶ رقمی پیگیری تراکنش', 'zarincode' ); ?>">
				</p>
				<?php
			}

			public function process_payment( $order_id ) {
				$order = wc_get_order( $order_id );
				$ref   = isset( $_POST['zc_cct_ref'] ) ? sanitize_text_field( wp_unslash( $_POST['zc_cct_ref'] ) ) : ''; // phpcs:ignore

				if ( ! $ref ) {
					wc_add_notice( __( 'لطفاً کد پیگیری تراکنش را وارد کنید.', 'zarincode' ), 'error' );
					return array( 'result' => 'failure' );
				}

				// وضعیت در انتظار پرداخت (on-hold) تا تأیید مدیر.
				$order->update_status( 'on-hold', sprintf( __( 'در انتظار تأیید پرداخت کارت‌به‌کارت. کد پیگیری: %s', 'zarincode' ), $ref ) );
				$order->update_meta_data( '_zc_cct_ref', $ref );
				$order->save();

				WC()->cart->empty_cart();

				// اعلان به مدیر.
				if ( function_exists( 'zc_notify_admins' ) ) {
					zc_notify_admins( sprintf( __( 'پرداخت کارت‌به‌کارت سفارش #%s در انتظار تأیید است. کد پیگیری: %s', 'zarincode' ), $order->get_order_number(), $ref ) );
				}

				return array( 'result' => 'success', 'redirect' => $this->get_return_url( $order ) );
			}
		}
	}
}
add_action( 'plugins_loaded', 'zc_init_extra_gateways', 11 );
