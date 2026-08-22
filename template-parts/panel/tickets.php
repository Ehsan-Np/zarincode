<?php
/**
 * تب تیکت‌های پشتیبانی
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_ticket_id = isset( $_GET['ticket'] ) ? absint( $_GET['ticket'] ) : 0; // phpcs:ignore
$zc_statuses  = zc_ticket_statuses();
$zc_prios     = zc_ticket_priorities();
$zc_depts     = zc_ticket_departments();

if ( $zc_ticket_id ) :
	$zc_ticket = get_post( $zc_ticket_id );

	if ( ! $zc_ticket || (int) $zc_ticket->post_author !== get_current_user_id() ) {
		echo '<div class="zc-alert zc-alert--error">' . esc_html__( 'تیکت یافت نشد.', 'zarincode' ) . '</div>';
		return;
	}

	$zc_status = get_post_meta( $zc_ticket_id, '_zc_status', true );
	$zc_prio   = get_post_meta( $zc_ticket_id, '_zc_priority', true );
	$zc_dept   = get_post_meta( $zc_ticket_id, '_zc_department', true );
	?>

	<a href="<?php echo esc_url( zc_panel_url( 'tickets' ) ); ?>" class="zc-btn zc-btn--ghost zc-btn--sm" style="margin-bottom:18px">
		<?php zc_the_icon( 'arrow-left', 16 ); ?><?php esc_html_e( 'بازگشت به لیست تیکت‌ها', 'zarincode' ); ?>
	</a>

	<div class="zc-panel__box">
		<div class="zc-panel__box-head" style="flex-wrap:wrap;gap:10px">
			<h3 style="flex:1;min-width:200px"><?php zc_the_icon( 'ticket', 19 ); ?><?php echo esc_html( $zc_ticket->post_title ); ?></h3>
			<div style="display:flex;gap:7px;flex-wrap:wrap">
				<span class="zc-badge zc-badge--<?php echo esc_attr( $zc_statuses[ $zc_status ]['color'] ?? 'blue' ); ?>"><?php echo esc_html( $zc_statuses[ $zc_status ]['label'] ?? $zc_status ); ?></span>
				<span class="zc-badge zc-badge--<?php echo esc_attr( $zc_prios[ $zc_prio ]['color'] ?? 'blue' ); ?>"><?php echo esc_html( $zc_prios[ $zc_prio ]['label'] ?? $zc_prio ); ?></span>
				<span class="zc-badge zc-badge--dark"><?php echo esc_html( zc_ticket_department_label( $zc_dept ) ); ?></span>
			</div>
		</div>

		<div class="zc-panel__box-body">
			<div class="zc-ticket-thread" id="zc-ticket-thread">
				<div class="zc-ticket-msg">
					<div class="zc-ticket-msg__avatar"><?php echo get_avatar( $zc_ticket->post_author, 44 ); ?></div>
					<div class="zc-ticket-msg__body">
						<div class="zc-ticket-msg__head">
							<strong><?php echo esc_html( get_the_author_meta( 'display_name', $zc_ticket->post_author ) ); ?></strong>
							<time><?php echo esc_html( zc_human_time( $zc_ticket->post_date ) ); ?></time>
						</div>
						<div class="zc-ticket-msg__text"><?php echo wpautop( wp_kses_post( $zc_ticket->post_content ) ); ?></div>
						<?php
						$zc_attach = get_post_meta( $zc_ticket_id, '_zc_attachment', true );
						if ( $zc_attach ) :
							?>
							<a href="<?php echo esc_url( wp_get_attachment_url( $zc_attach ) ); ?>" class="zc-btn zc-btn--ghost zc-btn--sm" target="_blank" rel="noopener">
								<?php zc_the_icon( 'download', 15 ); ?><?php esc_html_e( 'دانلود پیوست', 'zarincode' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>

				<?php
				$zc_replies = get_comments(
					array(
						'post_id' => $zc_ticket_id,
						'order'   => 'ASC',
						'status'  => 'approve',
					)
				);
				foreach ( $zc_replies as $zc_reply ) {
					echo zc_render_ticket_reply( $zc_reply ); // phpcs:ignore
				}
				?>
			</div>

			<?php if ( 'closed' !== $zc_status ) : ?>
				<form data-zc-form="zc_reply_ticket" class="zc-ticket-reply" style="margin-top:22px">
					<input type="hidden" name="ticket_id" value="<?php echo esc_attr( $zc_ticket_id ); ?>">
					<div class="zc-field">
						<label class="zc-label"><?php esc_html_e( 'پاسخ شما', 'zarincode' ); ?></label>
						<textarea name="message" rows="4" required placeholder="<?php esc_attr_e( 'پاسخ خود را بنویسید…', 'zarincode' ); ?>"></textarea>
					</div>
					<div class="zc-form-msg"></div>
					<div style="display:flex;gap:10px;flex-wrap:wrap">
						<button type="submit" class="zc-btn zc-btn--gold"><?php zc_the_icon( 'send', 17 ); ?><?php esc_html_e( 'ارسال پاسخ', 'zarincode' ); ?></button>
						<button type="button" class="zc-btn zc-btn--outline" data-zc-close-ticket="<?php echo esc_attr( $zc_ticket_id ); ?>"><?php zc_the_icon( 'check', 16 ); ?><?php esc_html_e( 'بستن تیکت', 'zarincode' ); ?></button>
					</div>
				</form>
			<?php else : ?>
				<div class="zc-alert zc-alert--info" style="margin-top:20px"><?php zc_the_icon( 'info', 18 ); ?><span><?php esc_html_e( 'این تیکت بسته شده است. برای پیگیری، تیکت جدید ثبت کنید.', 'zarincode' ); ?></span></div>
			<?php endif; ?>
		</div>
	</div>

<?php else : ?>

	<div class="zc-ticket-layout">
		<div class="zc-panel__box" data-zc-anim="up">
			<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'ticket', 19 ); ?><?php esc_html_e( 'تیکت‌های من', 'zarincode' ); ?></h3></div>
			<div class="zc-panel__box-body">
				<?php
				$zc_tickets = zc_get_user_tickets();
				if ( $zc_tickets->have_posts() ) :
					?>
					<div class="zc-ticket-list">
						<?php
						while ( $zc_tickets->have_posts() ) :
							$zc_tickets->the_post();
							$zc_st = get_post_meta( get_the_ID(), '_zc_status', true );
							?>
							<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'tickets', 'ticket' => get_the_ID() ), zc_panel_url() ) ); ?>" class="zc-ticket-item">
								<span class="zc-ticket-item__icon"><?php zc_the_icon( 'ticket', 20 ); ?></span>
								<span class="zc-ticket-item__info">
									<strong><?php the_title(); ?></strong>
									<small>
										<?php echo esc_html( zc_human_time( get_post_meta( get_the_ID(), '_zc_last_reply', true ) ?: get_the_date( 'c' ) ) ); ?>
										· <?php echo esc_html( zc_fa_num( get_comments_number() ) ); ?> <?php esc_html_e( 'پاسخ', 'zarincode' ); ?>
									</small>
								</span>
								<span class="zc-badge zc-badge--<?php echo esc_attr( $zc_statuses[ $zc_st ]['color'] ?? 'blue' ); ?>">
									<?php echo esc_html( $zc_statuses[ $zc_st ]['label'] ?? $zc_st ); ?>
								</span>
							</a>
							<?php
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				<?php else : ?>
					<div class="zc-empty" style="padding:34px 10px">
						<div class="zc-empty__icon" style="width:64px;height:64px"><?php zc_the_icon( 'ticket', 28 ); ?></div>
						<p><?php esc_html_e( 'تیکتی ثبت نکرده‌اید.', 'zarincode' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="zc-panel__box" data-zc-anim="up" data-zc-delay="90">
			<div class="zc-panel__box-head"><h3><?php zc_the_icon( 'edit', 19 ); ?><?php esc_html_e( 'ثبت تیکت جدید', 'zarincode' ); ?></h3></div>
			<div class="zc-panel__box-body">
				<form class="zc-tform" data-zc-form="zc_create_ticket" enctype="multipart/form-data">

					<!-- ردیف افقی: موضوع، دپارتمان، اولویت -->
					<div class="zc-tform__row">
						<div class="zc-field zc-tform__col--wide">
							<label class="zc-label" for="zc-t-subject">
								<?php esc_html_e( 'موضوع', 'zarincode' ); ?> <span class="req">*</span>
							</label>
							<input type="text" id="zc-t-subject" name="subject" required
								placeholder="<?php esc_attr_e( 'موضوع را کوتاه و روشن بنویسید', 'zarincode' ); ?>">
						</div>

						<div class="zc-field">
							<label class="zc-label" for="zc-t-dept"><?php esc_html_e( 'دپارتمان', 'zarincode' ); ?></label>
							<select id="zc-t-dept" name="department">
								<?php foreach ( $zc_depts as $zc_k => $zc_v ) : ?>
									<option value="<?php echo esc_attr( $zc_k ); ?>"><?php echo esc_html( $zc_v ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="zc-field">
							<label class="zc-label" for="zc-t-prio"><?php esc_html_e( 'اولویت', 'zarincode' ); ?></label>
							<select id="zc-t-prio" name="priority">
								<?php foreach ( $zc_prios as $zc_k => $zc_v ) : ?>
									<option value="<?php echo esc_attr( $zc_k ); ?>" <?php selected( 'normal', $zc_k ); ?>>
										<?php echo esc_html( $zc_v['label'] ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<!-- ویرایشگر متنی -->
					<div class="zc-field">
						<label class="zc-label"><?php esc_html_e( 'متن پیام', 'zarincode' ); ?> <span class="req">*</span></label>
						<?php zc_wysiwyg_editor( 'message', '', array( 'height' => 220 ) ); ?>
					</div>

					<!-- پیوست‌ها -->
					<div class="zc-field">
						<label class="zc-label"><?php esc_html_e( 'فایل پیوست', 'zarincode' ); ?></label>

						<div class="zc-dropzone" data-zc-dropzone data-max="<?php echo esc_attr( zc_max_upload_size() ); ?>" data-max-label="<?php echo esc_attr( zc_max_upload_label() ); ?>">
							<input type="file" name="attachment" id="zc-t-file" class="zc-dropzone__input"
								accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.zip,.rar,.txt,.doc,.docx">

							<label for="zc-t-file" class="zc-dropzone__label">
								<?php zc_the_icon( 'download', 22 ); ?>
								<strong><?php esc_html_e( 'فایل را بکشید یا کلیک کنید', 'zarincode' ); ?></strong>
								<em><?php
									printf(
										/* translators: %s: حداکثر حجم به مگابایت */
										esc_html__( 'تصویر، PDF، ZIP — حداکثر %s مگابایت', 'zarincode' ),
										esc_html( zc_max_upload_label() )
									);
								?></em>
							</label>

							<div class="zc-dropzone__preview" hidden></div>
						</div>
					</div>

					<div class="zc-form-msg"></div>

					<div class="zc-tform__actions">
						<button type="submit" class="zc-btn zc-btn--gold zc-btn--lg">
							<?php zc_the_icon( 'send', 17 ); ?>
							<span><?php esc_html_e( 'ارسال تیکت', 'zarincode' ); ?></span>
						</button>

						<p class="zc-tform__note">
							<?php zc_the_icon( 'clock', 15 ); ?>
							<?php esc_html_e( 'میانگین زمان پاسخ‌گویی: کمتر از ۶ ساعت کاری', 'zarincode' ); ?>
						</p>
					</div>
				</form>
			</div>
		</div>
	</div>

<?php endif; ?>
