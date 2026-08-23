<?php
/**
 * سیستم تیکتینگ حرفه‌ای
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * وضعیت‌های تیکت.
 *
 * @return array
 */
function zc_ticket_statuses() {
	return array(
		'open'        => array( 'label' => __( 'باز', 'zarincode' ), 'color' => 'blue' ),
		'answered'    => array( 'label' => __( 'پاسخ داده شده', 'zarincode' ), 'color' => 'green' ),
		'pending'     => array( 'label' => __( 'در انتظار پاسخ شما', 'zarincode' ), 'color' => 'orange' ),
		'in_progress' => array( 'label' => __( 'در حال بررسی', 'zarincode' ), 'color' => 'gold' ),
		'closed'      => array( 'label' => __( 'بسته شده', 'zarincode' ), 'color' => 'dark' ),
	);
}

/**
 * اولویت‌های تیکت.
 *
 * @return array
 */
function zc_ticket_priorities() {
	return array(
		'low'    => array( 'label' => __( 'کم', 'zarincode' ), 'color' => 'green' ),
		'normal' => array( 'label' => __( 'متوسط', 'zarincode' ), 'color' => 'blue' ),
		'high'   => array( 'label' => __( 'زیاد', 'zarincode' ), 'color' => 'orange' ),
		'urgent' => array( 'label' => __( 'بحرانی', 'zarincode' ), 'color' => 'red' ),
	);
}

/**
 * دپارتمان‌های پشتیبانی.
 *
 * @return array
 */
/**
 * برچسب خوانای یک دپارتمان.
 *
 * اگر مدیر فهرست دپارتمان‌ها را عوض کند، تیکت‌های قدیمی کلید قبلی را
 * نگه می‌دارند؛ این تابع ابتدا فهرست جاری و سپس کلیدهای پیش‌فرض را
 * بررسی می‌کند تا هیچ‌وقت کلید خام به کاربر نشان داده نشود.
 *
 * @param string $key کلید دپارتمان.
 * @return string برچسب.
 */
function zc_ticket_department_label( $key ) {
	if ( ! $key ) {
		return __( 'عمومی', 'zarincode' );
	}

	$list = zc_ticket_departments();

	if ( isset( $list[ $key ] ) ) {
		return $list[ $key ];
	}

	$builtin = array(
		'technical' => __( 'پشتیبانی فنی', 'zarincode' ),
		'course'    => __( 'پشتیبانی دوره‌ها', 'zarincode' ),
		'financial' => __( 'مالی و پرداخت', 'zarincode' ),
		'sales'     => __( 'فروش و مشاوره', 'zarincode' ),
		'other'     => __( 'سایر موارد', 'zarincode' ),
	);

	return $builtin[ $key ] ?? $key;
}

function zc_ticket_departments() {
	$custom = zc_opt( 'zc_ticket_departments', '' );

	if ( $custom ) {
		$lines = array_filter( array_map( 'trim', explode( "\n", $custom ) ) );
		$list  = array();
		$i     = 0;

		foreach ( $lines as $line ) {
			$i++;

			// نگارش «کلید | عنوان» برای تعیین دستی شناسه.
			if ( false !== strpos( $line, '|' ) ) {
				$parts = array_map( 'trim', explode( '|', $line, 2 ) );
				$key   = sanitize_key( $parts[0] );
				$label = $parts[1];
			} else {
				$key   = '';
				$label = $line;
			}

			/*
			 * sanitize_title روی متن فارسی رشته‌ی درصدی می‌سازد که با
			 * کلیدهای ذخیره‌شده در متای تیکت‌ها همخوانی ندارد؛ پس برای
			 * خطوط بدون کلید، شناسه‌ی پایدارِ عددی می‌سازیم.
			 */
			if ( ! $key || preg_match( '/^%|%/', $key ) ) {
				$key = 'dept-' . $i;
			}

			$list[ $key ] = $label;
		}

		if ( $list ) {
			return $list;
		}
	}

	return array(
		'technical' => __( 'پشتیبانی فنی', 'zarincode' ),
		'course'    => __( 'پشتیبانی دوره‌ها', 'zarincode' ),
		'financial' => __( 'مالی و پرداخت', 'zarincode' ),
		'sales'     => __( 'فروش و مشاوره', 'zarincode' ),
		'other'     => __( 'سایر موارد', 'zarincode' ),
	);
}

/**
 * ایجاد تیکت جدید (ای‌جکس).
 *
 * @return void
 */
function zc_ajax_create_ticket() {
	zc_check_ajax();

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'ابتدا وارد حساب کاربری شوید.', 'zarincode' ) ) );
	}

	if ( ! zc_opt( 'zc_ticket_enable', true ) ) {
		wp_send_json_error( array( 'message' => __( 'سیستم تیکتینگ غیرفعال است.', 'zarincode' ) ) );
	}

	$user_id    = get_current_user_id();
	$subject    = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	// خروجی ویرایشگر با فهرست مجاز محدودتر پاک می‌شود.
	$message    = isset( $_POST['message'] ) ? zc_kses_editor( wp_unslash( $_POST['message'] ) ) : ''; // phpcs:ignore
	$department = isset( $_POST['department'] ) ? sanitize_text_field( wp_unslash( $_POST['department'] ) ) : 'technical';
	$priority   = isset( $_POST['priority'] ) ? sanitize_text_field( wp_unslash( $_POST['priority'] ) ) : 'normal';
	$related    = isset( $_POST['related'] ) ? absint( $_POST['related'] ) : 0;

	/*
	 * ویرایشگر برای ناحیه‌ی خالی ممکن است <br> یا <p></p> بفرستد؛
	 * پس «خالی بودن» را روی متن بدون تگ می‌سنجیم نه روی HTML.
	 */
	if ( ! $subject || '' === trim( wp_strip_all_tags( $message ) ) ) {
		wp_send_json_error( array( 'message' => __( 'عنوان و متن پیام الزامی است.', 'zarincode' ) ) );
	}

	// جلوگیری از اسپم.
	$lock = 'zc_ticket_lock_' . $user_id;
	if ( get_transient( $lock ) ) {
		wp_send_json_error( array( 'message' => __( 'لطفاً کمی صبر کنید و سپس تیکت جدید ثبت کنید.', 'zarincode' ) ) );
	}

	$ticket_id = wp_insert_post(
		array(
			'post_type'    => 'zc_ticket',
			'post_title'   => $subject,
			'post_content' => $message,
			'post_status'  => 'publish',
			'post_author'  => $user_id,
		)
	);

	if ( is_wp_error( $ticket_id ) ) {
		wp_send_json_error( array( 'message' => __( 'خطا در ثبت تیکت.', 'zarincode' ) ) );
	}

	update_post_meta( $ticket_id, '_zc_status', 'open' );
	update_post_meta( $ticket_id, '_zc_department', $department );
	update_post_meta( $ticket_id, '_zc_priority', $priority );
	update_post_meta( $ticket_id, '_zc_last_reply', current_time( 'mysql' ) );
	if ( $related ) {
		update_post_meta( $ticket_id, '_zc_related_post', $related );
	}

	// آپلود فایل پیوست.
	if ( ! empty( $_FILES['attachment']['name'] ) ) { // phpcs:ignore
		$attach_id = zc_handle_ticket_upload( 'attachment', $ticket_id );
		if ( $attach_id && ! is_wp_error( $attach_id ) ) {
			update_post_meta( $ticket_id, '_zc_attachment', $attach_id );
		}
	}

	set_transient( $lock, 1, 60 );

	// اطلاع‌رسانی به مدیر.
	zc_notify_admin_new_ticket( $ticket_id );

	do_action( 'zc_ticket_created', $ticket_id, $user_id );

	wp_send_json_success(
		array(
			'message'  => __( 'تیکت شما با موفقیت ثبت شد. به زودی پاسخ داده می‌شود.', 'zarincode' ),
			'redirect' => add_query_arg( array( 'tab' => 'tickets', 'ticket' => $ticket_id ), zc_panel_url() ),
		)
	);
}
add_action( 'wp_ajax_zc_create_ticket', 'zc_ajax_create_ticket' );

/**
 * پاسخ به تیکت.
 *
 * @return void
 */
function zc_ajax_reply_ticket() {
	zc_check_ajax();

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'ابتدا وارد شوید.', 'zarincode' ) ) );
	}

	$ticket_id = isset( $_POST['ticket_id'] ) ? absint( $_POST['ticket_id'] ) : 0;
	$message   = isset( $_POST['message'] ) ? zc_kses_editor( wp_unslash( $_POST['message'] ) ) : ''; // phpcs:ignore
	$user_id   = get_current_user_id();

	$ticket = get_post( $ticket_id );

	if ( ! $ticket || 'zc_ticket' !== $ticket->post_type ) {
		wp_send_json_error( array( 'message' => __( 'تیکت یافت نشد.', 'zarincode' ) ) );
	}

	// بررسی مالکیت.
	if ( (int) $ticket->post_author !== $user_id && ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	if ( 'closed' === get_post_meta( $ticket_id, '_zc_status', true ) ) {
		wp_send_json_error( array( 'message' => __( 'این تیکت بسته شده است.', 'zarincode' ) ) );
	}

	if ( '' === trim( wp_strip_all_tags( $message ) ) ) {
		wp_send_json_error( array( 'message' => __( 'متن پاسخ را وارد کنید.', 'zarincode' ) ) );
	}

	$is_staff = current_user_can( 'edit_posts' );

	$comment_id = wp_insert_comment(
		array(
			'comment_post_ID'  => $ticket_id,
			'comment_content'  => $message,
			'user_id'          => $user_id,
			'comment_author'   => wp_get_current_user()->display_name,
			'comment_approved' => 1,
			'comment_type'     => 'zc_ticket_reply',
		)
	);

	if ( ! $comment_id ) {
		wp_send_json_error( array( 'message' => __( 'خطا در ثبت پاسخ.', 'zarincode' ) ) );
	}

	add_comment_meta( $comment_id, '_zc_is_staff', $is_staff ? 1 : 0 );

	update_post_meta( $ticket_id, '_zc_status', $is_staff ? 'answered' : 'pending' );
	update_post_meta( $ticket_id, '_zc_last_reply', current_time( 'mysql' ) );

	// پیامک اطلاع‌رسانی به کاربر.
	if ( $is_staff && zc_opt( 'zc_sms_ticket_notify', true ) ) {
		zc_notify_user_sms(
			(int) $ticket->post_author,
			sprintf(
				/* translators: %s: ticket subject */
				__( 'تیکت شما با موضوع «%s» پاسخ داده شد. زرین کد', 'zarincode' ),
				mb_substr( $ticket->post_title, 0, 30 )
			)
		);
	}

	do_action( 'zc_ticket_replied', $ticket_id, $comment_id, $is_staff );

	wp_send_json_success(
		array(
			'message' => __( 'پاسخ شما ثبت شد.', 'zarincode' ),
			'html'    => zc_render_ticket_reply( get_comment( $comment_id ) ),
			'reload'  => false,
		)
	);
}
add_action( 'wp_ajax_zc_reply_ticket', 'zc_ajax_reply_ticket' );

/**
 * بستن تیکت.
 *
 * @return void
 */
function zc_ajax_close_ticket() {
	zc_check_ajax();

	$ticket_id = isset( $_POST['ticket_id'] ) ? absint( $_POST['ticket_id'] ) : 0;
	$ticket    = get_post( $ticket_id );

	if ( ! $ticket || ( (int) $ticket->post_author !== get_current_user_id() && ! current_user_can( 'edit_posts' ) ) ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	update_post_meta( $ticket_id, '_zc_status', 'closed' );

	wp_send_json_success( array( 'message' => __( 'تیکت بسته شد.', 'zarincode' ), 'reload' => true ) );
}
add_action( 'wp_ajax_zc_close_ticket', 'zc_ajax_close_ticket' );

/**
 * رندر یک پاسخ تیکت.
 *
 * @param WP_Comment $comment دیدگاه.
 * @return string
 */
function zc_render_ticket_reply( $comment ) {
	$is_staff = (bool) get_comment_meta( $comment->comment_ID, '_zc_is_staff', true );

	ob_start();
	?>
	<div class="zc-ticket-msg<?php echo $is_staff ? ' zc-ticket-msg--staff' : ''; ?>">
		<div class="zc-ticket-msg__avatar">
			<?php echo get_avatar( $comment->user_id, 44 ); ?>
		</div>
		<div class="zc-ticket-msg__body">
			<div class="zc-ticket-msg__head">
				<strong><?php echo esc_html( $comment->comment_author ); ?></strong>
				<?php if ( $is_staff ) : ?>
					<span class="zc-badge zc-badge--gold"><?php esc_html_e( 'پشتیبانی', 'zarincode' ); ?></span>
				<?php endif; ?>
				<time><?php echo esc_html( zc_human_time( $comment->comment_date ) ); ?></time>
			</div>
			<div class="zc-ticket-msg__text"><?php echo wpautop( wp_kses_post( $comment->comment_content ) ); ?></div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * دریافت تیکت‌های کاربر.
 *
 * @param int   $user_id کاربر.
 * @param array $args    آرگومان.
 * @return WP_Query
 */
function zc_get_user_tickets( $user_id = 0, $args = array() ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	$defaults = array(
		'post_type'      => 'zc_ticket',
		'author'         => $user_id,
		'posts_per_page' => 20,
		'post_status'    => 'publish',
		'orderby'        => 'modified',
		'order'          => 'DESC',
	);

	return new WP_Query( wp_parse_args( $args, $defaults ) );
}

/**
 * آپلود فایل پیوست تیکت.
 *
 * @param string $field فیلد.
 * @param int    $post_id پست.
 * @return int|WP_Error
 */
function zc_handle_ticket_upload( $field, $post_id ) {
	if ( ! zc_opt( 'zc_ticket_attach', true ) ) {
		return new WP_Error( 'attachments_disabled', __( 'پیوست فایل غیرفعال است.', 'zarincode' ) );
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$allowed = array( 'jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip', 'rar', 'txt', 'doc', 'docx' );
	$file    = $_FILES[ $field ] ?? null; // phpcs:ignore

	if ( ! $file ) {
		return new WP_Error( 'no_file', __( 'فایلی انتخاب نشده است.', 'zarincode' ) );
	}

	$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
	if ( ! in_array( $ext, $allowed, true ) ) {
		return new WP_Error( 'bad_type', __( 'فرمت فایل مجاز نیست.', 'zarincode' ) );
	}

	$max = (int) zc_opt( 'zc_ticket_max_size', 5 ) * MB_IN_BYTES;
	if ( $file['size'] > $max ) {
		return new WP_Error( 'too_big', __( 'حجم فایل بیش از حد مجاز است.', 'zarincode' ) );
	}

	return media_handle_upload( $field, $post_id );
}

/**
 * اطلاع‌رسانی تیکت جدید به مدیر.
 *
 * @param int $ticket_id تیکت.
 * @return void
 */
function zc_notify_admin_new_ticket( $ticket_id ) {
	$admin_email = zc_opt( 'zc_ticket_admin_email', get_option( 'admin_email' ) );
	$ticket      = get_post( $ticket_id );

	wp_mail(
		$admin_email,
		sprintf( /* translators: %s: subject */ __( 'تیکت جدید: %s', 'zarincode' ), $ticket->post_title ),
		sprintf(
			/* translators: 1: content 2: link */
			__( "تیکت جدیدی در سایت ثبت شد.\n\nمتن: %1\$s\n\nمشاهده: %2\$s", 'zarincode' ),
			wp_strip_all_tags( $ticket->post_content ),
			admin_url( 'post.php?post=' . $ticket_id . '&action=edit' )
		)
	);

	// اطلاع‌رسانی در تلگرام و بله به همه‌ی مدیران تعریف‌شده.
	if ( zc_opt( 'zc_ticket_notify_admin', true ) && function_exists( 'zc_notify_admins' ) ) {
		$priorities = zc_ticket_priorities();
		$priority   = get_post_meta( $ticket_id, '_zc_priority', true );
		$dept       = get_post_meta( $ticket_id, '_zc_department', true );
		$author     = get_userdata( (int) $ticket->post_author );

		zc_notify_admins(
			sprintf(
				/* translators: 1: عنوان 2: کاربر 3: دپارتمان 4: اولویت 5: متن */
				__( "🎫 <b>تیکت پشتیبانی تازه</b>\n\n📌 %1\$s\n👤 %2\$s\n🏷 %3\$s | ⚡ %4\$s\n\n«%5\$s»", 'zarincode' ),
				esc_html( $ticket->post_title ),
				esc_html( $author ? $author->display_name : __( 'ناشناس', 'zarincode' ) ),
				esc_html( zc_ticket_department_label( $dept ) ),
				esc_html( $priorities[ $priority ]['label'] ?? '—' ),
				esc_html( wp_trim_words( wp_strip_all_tags( $ticket->post_content ), 35 ) )
			),
			array(
				array(
					'text' => __( 'پاسخ به تیکت', 'zarincode' ),
					'url'  => admin_url( 'post.php?post=' . $ticket_id . '&action=edit' ),
				),
			)
		);
	}
}

/**
 * ستون‌های لیست تیکت در پیشخوان.
 *
 * @param array $cols ستون‌ها.
 * @return array
 */
function zc_ticket_columns( $cols ) {
	unset( $cols['date'] );
	$cols['zc_status']     = __( 'وضعیت', 'zarincode' );
	$cols['zc_priority']   = __( 'اولویت', 'zarincode' );
	$cols['zc_department'] = __( 'دپارتمان', 'zarincode' );
	$cols['zc_replies']    = __( 'پاسخ‌ها', 'zarincode' );
	$cols['zc_last']       = __( 'آخرین بروزرسانی', 'zarincode' );
	return $cols;
}
add_filter( 'manage_zc_ticket_posts_columns', 'zc_ticket_columns' );

/**
 * محتوای ستون‌ها.
 *
 * @param string $col ستون.
 * @param int    $id  شناسه.
 * @return void
 */
function zc_ticket_column_content( $col, $id ) {
	switch ( $col ) {
		case 'zc_status':
			$statuses = zc_ticket_statuses();
			$status   = get_post_meta( $id, '_zc_status', true );
			echo esc_html( $statuses[ $status ]['label'] ?? $status );
			break;
		case 'zc_priority':
			$p = zc_ticket_priorities();
			$v = get_post_meta( $id, '_zc_priority', true );
			echo esc_html( $p[ $v ]['label'] ?? $v );
			break;
		case 'zc_department':
			$v = get_post_meta( $id, '_zc_department', true );
			echo esc_html( zc_ticket_department_label( $v ) );
			break;
		case 'zc_replies':
			echo esc_html( zc_fa_num( get_comments_number( $id ) ) );
			break;
		case 'zc_last':
			echo esc_html( zc_human_time( get_post_meta( $id, '_zc_last_reply', true ) ) );
			break;
	}
}
add_action( 'manage_zc_ticket_posts_custom_column', 'zc_ticket_column_content', 10, 2 );

/* ==========================================================================
   امکانات حرفه‌ای تیکتینگ: رضایت‌سنجی، SLA، بازگشایی و آمار
   ========================================================================== */

/**
 * زمان پاسخ‌گویی تعهدشده (SLA) بر اساس اولویت — بر حسب ساعت.
 *
 * @return array
 */
function zc_ticket_sla_hours() {
	return apply_filters(
		'zc_ticket_sla_hours',
		array(
			'urgent' => (int) zc_opt( 'zc_sla_urgent', 3 ),
			'high'   => (int) zc_opt( 'zc_sla_high', 8 ),
			'normal' => (int) zc_opt( 'zc_sla_normal', 24 ),
			'low'    => (int) zc_opt( 'zc_sla_low', 48 ),
		)
	);
}

/**
 * محاسبه‌ی مهلت پاسخ یک تیکت.
 *
 * @param int $ticket_id شناسه تیکت.
 * @return array آرایه‌ای شامل مهلت، باقی‌مانده و وضعیت تأخیر.
 */
function zc_ticket_sla( $ticket_id ) {
	$priority = get_post_meta( $ticket_id, '_zc_priority', true );
	$priority = $priority ? $priority : 'normal';
	$hours    = zc_ticket_sla_hours();
	$limit    = $hours[ $priority ] ?? 24;

	$created  = (int) get_post_time( 'U', true, $ticket_id );
	$deadline = $created + ( $limit * HOUR_IN_SECONDS );
	$now      = time();
	$status   = get_post_meta( $ticket_id, '_zc_status', true );

	// تیکت‌های پاسخ‌داده‌شده یا بسته، دیگر مشمول تأخیر نیستند.
	$settled = in_array( $status, array( 'answered', 'closed' ), true );

	return array(
		'hours'     => $limit,
		'deadline'  => $deadline,
		'remaining' => $deadline - $now,
		'overdue'   => ( ! $settled && $now > $deadline ),
		'settled'   => $settled,
	);
}

/**
 * ثبت امتیاز رضایت کاربر از پاسخ پشتیبانی (آجاکس).
 *
 * @return void
 */
function zc_ajax_rate_ticket() {
	zc_check_ajax();

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'ابتدا وارد حساب خود شوید.', 'zarincode' ) ) );
	}

	$ticket_id = isset( $_POST['ticket_id'] ) ? absint( $_POST['ticket_id'] ) : 0;
	$rating    = isset( $_POST['rating'] ) ? absint( $_POST['rating'] ) : 0;
	$comment   = sanitize_textarea_field( wp_unslash( $_POST['comment'] ?? '' ) );
	$ticket    = $ticket_id ? get_post( $ticket_id ) : null;

	if ( ! $ticket || 'zc_ticket' !== $ticket->post_type ) {
		wp_send_json_error( array( 'message' => __( 'تیکت یافت نشد.', 'zarincode' ) ) );
	}

	if ( (int) $ticket->post_author !== get_current_user_id() ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	if ( $rating < 1 || $rating > 5 ) {
		wp_send_json_error( array( 'message' => __( 'امتیاز باید بین ۱ تا ۵ باشد.', 'zarincode' ) ) );
	}

	update_post_meta( $ticket_id, '_zc_ticket_rating', $rating );
	update_post_meta( $ticket_id, '_zc_ticket_rating_comment', $comment );
	update_post_meta( $ticket_id, '_zc_ticket_rated_at', time() );

	do_action( 'zc_ticket_rated', $ticket_id, $rating, $comment );

	wp_send_json_success( array( 'message' => __( 'از بازخورد شما سپاسگزاریم!', 'zarincode' ) ) );
}
add_action( 'wp_ajax_zc_rate_ticket', 'zc_ajax_rate_ticket' );

/**
 * بازگشایی تیکت بسته‌شده توسط کاربر (آجاکس).
 *
 * @return void
 */
function zc_ajax_reopen_ticket() {
	zc_check_ajax();

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'ابتدا وارد حساب خود شوید.', 'zarincode' ) ) );
	}

	$ticket_id = isset( $_POST['ticket_id'] ) ? absint( $_POST['ticket_id'] ) : 0;
	$ticket    = $ticket_id ? get_post( $ticket_id ) : null;

	if ( ! $ticket || 'zc_ticket' !== $ticket->post_type ) {
		wp_send_json_error( array( 'message' => __( 'تیکت یافت نشد.', 'zarincode' ) ) );
	}

	if ( (int) $ticket->post_author !== get_current_user_id() ) {
		wp_send_json_error( array( 'message' => __( 'دسترسی غیرمجاز.', 'zarincode' ) ) );
	}

	update_post_meta( $ticket_id, '_zc_status', 'open' );
	update_post_meta( $ticket_id, '_zc_reopened_at', time() );

	do_action( 'zc_ticket_reopened', $ticket_id );

	wp_send_json_success(
		array(
			'message' => __( 'تیکت دوباره باز شد. کارشناسان ما به‌زودی پاسخ می‌دهند.', 'zarincode' ),
			'reload'  => true,
		)
	);
}
add_action( 'wp_ajax_zc_reopen_ticket', 'zc_ajax_reopen_ticket' );

/**
 * آمار تیکت‌های یک کاربر.
 *
 * @param int $user_id شناسه کاربر.
 * @return array
 */
function zc_user_ticket_stats( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	$tickets = get_posts(
		array(
			'post_type'      => 'zc_ticket',
			'author'         => $user_id,
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		)
	);

	$stats = array(
		'total'    => count( $tickets ),
		'open'     => 0,
		'answered' => 0,
		'closed'   => 0,
	);

	foreach ( $tickets as $tid ) {
		$status = get_post_meta( $tid, '_zc_status', true );

		if ( 'closed' === $status ) {
			$stats['closed']++;
		} elseif ( 'answered' === $status ) {
			$stats['answered']++;
		} else {
			$stats['open']++;
		}
	}

	return $stats;
}

/**
 * میانگین زمان پاسخ‌گویی پشتیبانی (بر حسب ساعت) برای نمایش در پنل.
 *
 * @return float
 */
function zc_support_avg_response() {
	$cached = get_transient( 'zc_support_avg_response' );

	if ( false !== $cached ) {
		return (float) $cached;
	}

	$tickets = get_posts(
		array(
			'post_type'      => 'zc_ticket',
			'posts_per_page' => 60,
			'post_status'    => 'any',
			'fields'         => 'ids',
		)
	);

	$sum   = 0;
	$count = 0;

	foreach ( $tickets as $tid ) {
		$first = get_post_meta( $tid, '_zc_first_reply_at', true );

		if ( ! $first ) {
			continue;
		}

		$created = (int) get_post_time( 'U', true, $tid );
		$diff    = (int) $first - $created;

		if ( $diff > 0 ) {
			$sum += $diff;
			$count++;
		}
	}

	$avg = $count ? round( ( $sum / $count ) / HOUR_IN_SECONDS, 1 ) : 0;

	set_transient( 'zc_support_avg_response', $avg, HOUR_IN_SECONDS );

	return $avg;
}

/**
 * ثبت زمان اولین پاسخ کارشناس برای محاسبه‌ی SLA.
 *
 * @param int $ticket_id شناسه تیکت.
 * @param int $user_id   شناسه پاسخ‌دهنده.
 * @return void
 */
function zc_track_first_reply( $ticket_id, $user_id = 0 ) {
	// فقط پاسخ کارشناس (نه خود کاربر) ملاک است.
	$author = (int) get_post_field( 'post_author', $ticket_id );

	if ( $user_id && $user_id === $author ) {
		return;
	}

	if ( ! get_post_meta( $ticket_id, '_zc_first_reply_at', true ) ) {
		update_post_meta( $ticket_id, '_zc_first_reply_at', time() );
		delete_transient( 'zc_support_avg_response' );
	}
}
add_action( 'zc_ticket_replied', 'zc_track_first_reply', 10, 2 );
