<?php
/**
 * صفحه مدیریت گفتگوهای آنلاین در پیشخوان
 *
 * فهرست جلسه‌های گفتگو در ستون راست و پنجره‌ی پاسخ در ستون چپ.
 * مدیر می‌تواند پاسخ بدهد، گفتگو را ببندد یا حذف کند.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * ثبت زیرمنوی گفتگوها.
 *
 * @return void
 */
function zc_register_chats_page() {
	$unread = zc_unread_chat_count();
	$label  = __( 'گفتگوهای آنلاین', 'zarincode' );

	if ( $unread ) {
		$label .= sprintf(
			' <span class="awaiting-mod"><span class="pending-count">%s</span></span>',
			esc_html( zc_fa_num( $unread ) )
		);
	}

	if ( current_user_can( 'manage_options' ) ) {
		add_submenu_page(
			'zarincode',
			__( 'گفتگوهای آنلاین', 'zarincode' ),
			$label,
			'zc_answer_ticket',
			'zarincode-chats',
			'zc_admin_chats_page'
		);
	} else {
		add_menu_page(
			__( 'گفتگوهای آنلاین', 'zarincode' ),
			$label,
			'zc_answer_ticket',
			'zarincode-chats',
			'zc_admin_chats_page',
			'dashicons-format-chat',
			26
		);
	}
}
add_action( 'admin_menu', 'zc_register_chats_page', 20 );

/**
 * خروجی صفحه مدیریت گفتگوها.
 *
 * @return void
 */
function zc_admin_chats_page() {
	if ( ! function_exists( 'zc_can_support' ) || ! zc_can_support() ) {
		wp_die( esc_html__( 'دسترسی غیرمجاز.', 'zarincode' ) );
	}
	$filter   = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$active   = isset( $_GET['session'] ) ? sanitize_text_field( wp_unslash( $_GET['session'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$sessions = zc_get_chat_sessions(
		array(
			'status' => $filter,
			'search' => $search,
			'limit'  => 50,
		)
	);

	// اگر جلسه‌ای انتخاب نشده، اولین مورد باز می‌شود.
	if ( ! $active && $sessions ) {
		$active = $sessions[0]->session_id;
	}

	$messages = $active ? zc_get_chat_messages( $active ) : array();

	if ( $active ) {
		zc_mark_chat_read( $active );
	}

	$base = admin_url( 'admin.php?page=zarincode-chats' );
	?>
	<div class="wrap zc-admin-wrap zc-chats">
		<?php zc_admin_notice_anchor(); ?>
		<h1 class="wp-heading-inline"><?php esc_html_e( 'گفتگوهای آنلاین', 'zarincode' ); ?></h1>

		<ul class="subsubsub">
			<?php
			$filters = array(
				'all'    => __( 'همه', 'zarincode' ),
				'unread' => __( 'خوانده‌نشده', 'zarincode' ),
				'open'   => __( 'باز', 'zarincode' ),
				'closed' => __( 'بسته‌شده', 'zarincode' ),
			);

			$last = array_key_last( $filters );

			foreach ( $filters as $key => $label ) :
				?>
				<li>
					<a href="<?php echo esc_url( add_query_arg( 'status', $key, $base ) ); ?>"
						class="<?php echo $filter === $key ? 'current' : ''; ?>">
						<?php echo esc_html( $label ); ?>
					</a>
					<?php echo $key !== $last ? ' | ' : ''; ?>
				</li>
			<?php endforeach; ?>
		</ul>

		<form method="get" class="zc-chats__search">
			<input type="hidden" name="page" value="zarincode-chats" />
			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>"
				placeholder="<?php esc_attr_e( 'جستجو در پیام‌ها…', 'zarincode' ); ?>" />
			<button type="submit" class="button"><?php esc_html_e( 'جستجو', 'zarincode' ); ?></button>
		</form>

		<div class="zc-chats__layout">

			<!-- ستون فهرست گفتگوها -->
			<aside class="zc-chats__list">
				<?php if ( ! $sessions ) : ?>
					<p class="zc-chats__empty"><?php esc_html_e( 'هنوز گفتگویی ثبت نشده است.', 'zarincode' ); ?></p>
				<?php else : ?>
					<?php
					foreach ( $sessions as $session ) :
						$user      = $session->user_id ? get_userdata( $session->user_id ) : null;
						$name      = $user ? $user->display_name : __( 'کاربر مهمان', 'zarincode' );
						$is_active = ( $active === $session->session_id );
						?>
						<a href="<?php echo esc_url( add_query_arg( array( 'session' => $session->session_id, 'status' => $filter ), $base ) ); ?>"
							class="zc-chats__item<?php echo $is_active ? ' is-active' : ''; ?><?php echo $session->unread ? ' is-unread' : ''; ?>">

							<span class="zc-chats__avatar"><?php echo esc_html( mb_substr( $name, 0, 1 ) ); ?></span>

							<span class="zc-chats__info">
								<strong>
									<?php echo esc_html( $name ); ?>
									<?php if ( $session->unread ) : ?>
										<em class="zc-chats__badge"><?php echo esc_html( zc_fa_num( $session->unread ) ); ?></em>
									<?php endif; ?>
								</strong>
								<span class="zc-chats__preview"><?php echo esc_html( wp_trim_words( $session->last_message, 8 ) ); ?></span>
								<span class="zc-chats__time">
									<?php echo esc_html( zc_fa_num( mysql2date( 'j F — H:i', $session->last_time ) ) ); ?>
								</span>
							</span>
						</a>
					<?php endforeach; ?>
				<?php endif; ?>
			</aside>

			<!-- ستون گفتگو -->
			<section class="zc-chats__panel">
				<?php if ( ! $active ) : ?>
					<div class="zc-chats__placeholder">
						<?php esc_html_e( 'برای مشاهده، یک گفتگو را از فهرست انتخاب کنید.', 'zarincode' ); ?>
					</div>
				<?php else : ?>
					<div class="zc-chats__head">
						<strong><?php esc_html_e( 'جلسه:', 'zarincode' ); ?> <code><?php echo esc_html( substr( $active, 0, 14 ) ); ?></code></strong>

						<span class="zc-chats__actions">
							<button type="button" class="button zc-chat-close" data-session="<?php echo esc_attr( $active ); ?>">
								<?php esc_html_e( 'بستن گفتگو', 'zarincode' ); ?>
							</button>
							<button type="button" class="button zc-chat-reopen" data-session="<?php echo esc_attr( $active ); ?>">
								<?php esc_html_e( 'بازکردن', 'zarincode' ); ?>
							</button>
							<?php if ( current_user_can( 'manage_options' ) ) : ?>
								<button type="button" class="button button-link-delete zc-chat-delete" data-session="<?php echo esc_attr( $active ); ?>">
									<?php esc_html_e( 'حذف', 'zarincode' ); ?>
								</button>
							<?php endif; ?>
						</span>
					</div>

					<div class="zc-chats__box" id="zc-chat-box" data-session="<?php echo esc_attr( $active ); ?>">
						<?php foreach ( $messages as $msg ) : ?>
							<div class="zc-chats__msg zc-chats__msg--<?php echo esc_attr( $msg->sender ); ?>">
								<div class="zc-chats__bubble"><?php echo wp_kses_post( nl2br( esc_html( $msg->message ) ) ); ?></div>
								<span class="zc-chats__meta">
									<?php
									$senders = array(
										'user'  => __( 'کاربر', 'zarincode' ),
										'admin' => __( 'پشتیبان', 'zarincode' ),
										'bot'   => __( 'ربات', 'zarincode' ),
									);
									echo esc_html( $senders[ $msg->sender ] ?? $msg->sender );
									?>
									— <?php echo esc_html( zc_fa_num( mysql2date( 'H:i', $msg->created_at ) ) ); ?>
								</span>
							</div>
						<?php endforeach; ?>
					</div>

					<form class="zc-chats__reply" id="zc-chat-reply">
						<textarea id="zc-chat-message" rows="3"
							placeholder="<?php esc_attr_e( 'پاسخ خود را بنویسید… (Ctrl+Enter برای ارسال)', 'zarincode' ); ?>"></textarea>

						<button type="submit" class="button button-primary button-large">
							<?php esc_html_e( 'ارسال پاسخ', 'zarincode' ); ?>
						</button>
					</form>
				<?php endif; ?>
			</section>
		</div>
	</div>

	<script>
		jQuery( function ( $ ) {
			var nonce = '<?php echo esc_js( wp_create_nonce( 'zc_admin_nonce' ) ); ?>';
			var box   = $( '#zc-chat-box' );

			function scrollBottom() {
				if ( box.length ) { box.scrollTop( box[ 0 ].scrollHeight ); }
			}

			scrollBottom();

			// ارسال پاسخ
			$( '#zc-chat-reply' ).on( 'submit', function ( e ) {
				e.preventDefault();

				var msg = $( '#zc-chat-message' ).val().trim();
				if ( ! msg ) { return; }

				var btn = $( this ).find( 'button' ).prop( 'disabled', true );

				$.post( ajaxurl, {
					action: 'zc_chat_admin_reply',
					nonce: nonce,
					session_id: box.data( 'session' ),
					message: msg
				} ).done( function ( res ) {
					if ( res.success ) {
						box.append(
							'<div class="zc-chats__msg zc-chats__msg--admin">' +
							'<div class="zc-chats__bubble">' + $( '<div>' ).text( msg ).html().replace( /\n/g, '<br>' ) + '</div>' +
							'<span class="zc-chats__meta"><?php echo esc_js( __( 'پشتیبان', 'zarincode' ) ); ?> — ' + res.data.time + '</span></div>'
						);
						$( '#zc-chat-message' ).val( '' );
						scrollBottom();
					} else {
						alert( res.data.message || 'خطا' );
					}
				} ).always( function () {
					btn.prop( 'disabled', false );
				} );
			} );

			// ارسال با Ctrl+Enter
			$( '#zc-chat-message' ).on( 'keydown', function ( e ) {
				if ( e.ctrlKey && e.key === 'Enter' ) { $( '#zc-chat-reply' ).submit(); }
			} );

			// تغییر وضعیت
			function setStatus( session, status ) {
				$.post( ajaxurl, {
					action: 'zc_chat_set_status', nonce: nonce, session_id: session, status: status
				} ).done( function ( res ) { alert( res.data.message || '' ); } );
			}

			$( '.zc-chat-close' ).on( 'click', function () { setStatus( $( this ).data( 'session' ), 'closed' ); } );
			$( '.zc-chat-reopen' ).on( 'click', function () { setStatus( $( this ).data( 'session' ), 'open' ); } );

			$( '.zc-chat-delete' ).on( 'click', function () {
				if ( ! confirm( '<?php echo esc_js( __( 'این گفتگو برای همیشه حذف شود؟', 'zarincode' ) ); ?>' ) ) { return; }

				$.post( ajaxurl, {
					action: 'zc_chat_delete', nonce: nonce, session_id: $( this ).data( 'session' )
				} ).done( function () { location.href = '<?php echo esc_js( $base ); ?>'; } );
			} );

			// دریافت زنده‌ی پیام‌های تازه هر ۲۰ ثانیه
			if ( box.length ) {
				setInterval( function () {
					$.post( ajaxurl, {
						action: 'zc_chat_admin_fetch', nonce: nonce, session_id: box.data( 'session' )
					} ).done( function ( res ) {
						if ( ! res.success ) { return; }

						var current = box.find( '.zc-chats__msg' ).length;

						if ( res.data.messages.length > current ) {
							box.empty();

							res.data.messages.forEach( function ( m ) {
								box.append(
									'<div class="zc-chats__msg zc-chats__msg--' + m.sender + '">' +
									'<div class="zc-chats__bubble">' + m.message + '</div>' +
									'<span class="zc-chats__meta">' + m.time + '</span></div>'
								);
							} );

							scrollBottom();
						}
					} );
				}, 20000 );
			}
		} );
	</script>
	<?php
}
