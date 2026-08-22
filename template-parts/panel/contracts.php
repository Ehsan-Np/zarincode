<?php
/**
 * تب قراردادهای من
 *
 * سه حالت دارد: نمایش یک قرارداد، ساخت قرارداد تازه، و فهرست کلی.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

$zc_view     = isset( $_GET['contract'] ) ? absint( $_GET['contract'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
$zc_new      = isset( $_GET['new'] ) ? absint( $_GET['new'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
$zc_statuses = zc_contract_statuses();

/* ==========================================================================
   نمایش یک قرارداد
   ========================================================================== */
if ( $zc_view && zc_can_view_contract( $zc_view ) ) :
	$zc_c = zc_contract_data( $zc_view );
	?>
	<div class="zc-panel__head">
		<h2 class="zc-panel__title">
			<?php zc_the_icon( 'file', 20 ); ?>
			<?php esc_html_e( 'قرارداد', 'zarincode' ); ?>
			<code class="zc-ct__num" dir="ltr"><?php echo esc_html( $zc_c['number'] ); ?></code>
		</h2>

		<a class="zc-btn zc-btn--ghost zc-btn--sm" href="<?php echo esc_url( add_query_arg( 'tab', 'contracts', zc_panel_url() ) ); ?>">
			<?php esc_html_e( 'بازگشت به فهرست', 'zarincode' ); ?>
		</a>
	</div>

	<div class="zc-panel__box zc-ct-view">
		<div class="zc-panel__box-head">
			<span class="zc-ct__state" style="--s:<?php echo esc_attr( $zc_c['status_color'] ); ?>">
				<?php echo esc_html( $zc_c['status_label'] ); ?>
			</span>

			<div class="zc-ct__acts">
				<?php if ( 'pending' !== $zc_c['status'] ) : ?>
					<a class="zc-btn zc-btn--gold zc-btn--sm" target="_blank" rel="noopener"
						href="<?php echo esc_url( zc_contract_download_url( $zc_c['id'] ) ); ?>">
						<?php zc_the_icon( 'download', 15 ); ?>
						<?php esc_html_e( 'دریافت نسخه‌ی قرارداد', 'zarincode' ); ?>
					</a>
				<?php endif; ?>

				<a class="zc-btn zc-btn--ghost zc-btn--sm"
					href="<?php echo esc_url( add_query_arg( array( 'tab' => 'contract-chat', 'contract' => $zc_c['id'] ), zc_panel_url() ) ); ?>">
					<?php zc_the_icon( 'chat', 15 ); ?>
					<?php esc_html_e( 'گفتگو درباره‌ی این قرارداد', 'zarincode' ); ?>
				</a>
			</div>
		</div>

		<div class="zc-panel__box-body">

			<?php if ( $zc_c['progress'] > 0 ) : ?>
				<div class="zc-ct__prog">
					<div class="zc-ct__prog-top">
						<span><?php esc_html_e( 'پیشرفت پروژه', 'zarincode' ); ?></span>
						<strong><?php echo esc_html( zc_fa_num( $zc_c['progress'] ) ); ?>٪</strong>
					</div>
					<div class="zc-ct__prog-bar">
						<span style="width:<?php echo esc_attr( $zc_c['progress'] ); ?>%"></span>
					</div>
				</div>
			<?php endif; ?>

			<div class="zc-ct__paper">
				<?php echo wp_kses_post( $zc_c['body'] ); ?>

				<?php if ( $zc_c['signature'] ) : ?>
					<div class="zc-ct__signed">
						<div class="zc-ct__sig-box">
							<span class="zc-ct__sig-cap"><?php esc_html_e( 'امضای مشتری', 'zarincode' ); ?></span>
							<img src="<?php echo esc_attr( $zc_c['signature'] ); ?>" alt="<?php esc_attr_e( 'امضا', 'zarincode' ); ?>">
						</div>

						<div class="zc-ct__sig-meta">
							<p>
								<?php esc_html_e( 'تاریخ امضا:', 'zarincode' ); ?>
								<strong><?php echo esc_html( zc_fa_num( zc_jalali_date( 'j F Y — H:i', $zc_c['signed_at'] ) ) ); ?></strong>
							</p>
							<p>
								<?php esc_html_e( 'کد رهگیری:', 'zarincode' ); ?>
								<code dir="ltr"><?php echo esc_html( $zc_c['hash'] ); ?></code>
							</p>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( 'pending' === $zc_c['status'] ) : ?>
				<?php require ZC_DIR . 'template-parts/panel/contract-sign.php'; ?>
			<?php else : ?>
				<?php require ZC_DIR . 'template-parts/panel/contract-payments.php'; ?>
				<?php require ZC_DIR . 'template-parts/panel/contract-terminate.php'; ?>
			<?php endif; ?>
		</div>
	</div>

	<?php
	/* ==========================================================================
	   ساخت قرارداد تازه
	   ========================================================================== */
elseif ( $zc_new ) :
	$zc_tpl = get_post( $zc_new );

	if ( ! $zc_tpl || 'zc_contract_tpl' !== $zc_tpl->post_type ) :
		?>
		<div class="zc-empty"><?php esc_html_e( 'الگوی قرارداد یافت نشد.', 'zarincode' ); ?></div>
		<?php
	else :
		$zc_fields = zc_contract_fields( $zc_new );
		?>
		<div class="zc-panel__head">
			<h2 class="zc-panel__title">
				<?php zc_the_icon( 'edit', 20 ); ?>
				<?php echo esc_html( $zc_tpl->post_title ); ?>
			</h2>

			<a class="zc-btn zc-btn--ghost zc-btn--sm" href="<?php echo esc_url( add_query_arg( 'tab', 'contracts', zc_panel_url() ) ); ?>">
				<?php esc_html_e( 'انصراف', 'zarincode' ); ?>
			</a>
		</div>

		<div class="zc-ct-steps" data-zc-ct-steps>
			<div class="zc-ct-step is-active" data-step="1">
				<span class="zc-ct-step__n">۱</span>
				<span class="zc-ct-step__t"><?php esc_html_e( 'تکمیل اطلاعات', 'zarincode' ); ?></span>
			</div>
			<div class="zc-ct-step" data-step="2">
				<span class="zc-ct-step__n">۲</span>
				<span class="zc-ct-step__t"><?php esc_html_e( 'مطالعه‌ی قرارداد', 'zarincode' ); ?></span>
			</div>
			<div class="zc-ct-step" data-step="3">
				<span class="zc-ct-step__n">۳</span>
				<span class="zc-ct-step__t"><?php esc_html_e( 'تأیید و امضا', 'zarincode' ); ?></span>
			</div>
		</div>

		<div class="zc-panel__box" data-zc-ct-wizard data-tpl="<?php echo esc_attr( $zc_new ); ?>">

			<!-- گام یک -->
			<div class="zc-panel__box-body zc-ct-pane is-active" data-pane="1">
				<form class="zc-ct-form" data-zc-ct-form>
					<div class="zc-ct-grid">
						<?php foreach ( $zc_fields as $zc_k => $zc_f ) : ?>
							<div class="zc-field <?php echo in_array( $zc_f['type'], array( 'textarea', 'image', 'document' ), true ) ? 'zc-field--full' : ''; ?>">
								<label class="zc-label" for="zc-cf-<?php echo esc_attr( $zc_k ); ?>">
									<?php echo esc_html( $zc_f['label'] ); ?>
									<?php if ( ! empty( $zc_f['required'] ) ) : ?>
										<span class="req">*</span>
									<?php endif; ?>
								</label>

								<?php if ( 'textarea' === $zc_f['type'] ) : ?>
									<textarea id="zc-cf-<?php echo esc_attr( $zc_k ); ?>" name="f_<?php echo esc_attr( $zc_k ); ?>" rows="3"
										<?php echo ! empty( $zc_f['required'] ) ? 'required' : ''; ?>></textarea>

								<?php elseif ( 'select' === $zc_f['type'] ) : ?>
									<select id="zc-cf-<?php echo esc_attr( $zc_k ); ?>" name="f_<?php echo esc_attr( $zc_k ); ?>">
										<?php foreach ( $zc_f['options'] as $zc_o ) : ?>
											<option value="<?php echo esc_attr( $zc_o ); ?>"><?php echo esc_html( $zc_o ); ?></option>
										<?php endforeach; ?>
									</select>

								<?php elseif ( in_array( $zc_f['type'], array( 'image', 'document' ), true ) ) : ?>
									<div class="zc-dropzone" data-zc-dropzone
										data-max="<?php echo esc_attr( zc_max_upload_size() ); ?>"
										data-max-label="<?php echo esc_attr( zc_max_upload_label() ); ?>">

										<input type="file" class="zc-dropzone__input"
											id="zc-cf-<?php echo esc_attr( $zc_k ); ?>"
											name="f_<?php echo esc_attr( $zc_k ); ?>"
											accept="<?php echo 'image' === $zc_f['type'] ? 'image/jpeg,image/png,image/webp' : '.pdf,.doc,.docx'; ?>"
											<?php echo ! empty( $zc_f['required'] ) ? 'required' : ''; ?>>

										<label for="zc-cf-<?php echo esc_attr( $zc_k ); ?>" class="zc-dropzone__label">
											<?php zc_the_icon( 'image' === $zc_f['type'] ? 'image' : 'file', 22 ); ?>
											<strong><?php esc_html_e( 'انتخاب فایل یا کشیدن به این کادر', 'zarincode' ); ?></strong>
											<em>
												<?php
												echo 'image' === $zc_f['type']
													? esc_html__( 'JPG یا PNG', 'zarincode' )
													: esc_html__( 'PDF، DOC یا DOCX', 'zarincode' );
												?>
												— <?php echo esc_html( zc_max_upload_label() ); ?> <?php esc_html_e( 'مگابایت', 'zarincode' ); ?>
											</em>
										</label>

										<div class="zc-dropzone__preview" hidden></div>
									</div>

								<?php elseif ( 'date' === $zc_f['type'] ) : ?>
									<input type="text" class="zc-date" id="zc-cf-<?php echo esc_attr( $zc_k ); ?>"
										name="f_<?php echo esc_attr( $zc_k ); ?>" placeholder="۱۴۰۴/۰۱/۰۱"
										<?php echo ! empty( $zc_f['required'] ) ? 'required' : ''; ?>>

								<?php else : ?>
									<input type="<?php echo esc_attr( $zc_f['type'] ); ?>"
										id="zc-cf-<?php echo esc_attr( $zc_k ); ?>"
										name="f_<?php echo esc_attr( $zc_k ); ?>"
										<?php echo ! empty( $zc_f['pattern'] ) ? 'pattern="' . esc_attr( $zc_f['pattern'] ) . '"' : ''; ?>
										<?php echo ! empty( $zc_f['required'] ) ? 'required' : ''; ?>>
								<?php endif; ?>

								<?php if ( ! empty( $zc_f['help'] ) ) : ?>
									<span class="zc-help"><?php echo esc_html( $zc_f['help'] ); ?></span>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>

					<div class="zc-form-msg"></div>

					<button type="submit" class="zc-btn zc-btn--gold zc-btn--lg">
						<?php esc_html_e( 'ساخت پیش‌نویس قرارداد', 'zarincode' ); ?>
						<?php zc_the_icon( 'arrow-left', 16 ); ?>
					</button>
				</form>
			</div>

			<!-- گام دو -->
			<div class="zc-panel__box-body zc-ct-pane" data-pane="2">
				<div class="zc-ct__paper" data-zc-ct-preview></div>

				<label class="zc-ct__agree">
					<input type="checkbox" data-zc-ct-agree>
					<span><?php esc_html_e( 'متن قرارداد را کامل خواندم و با همه‌ی مفاد آن موافقم.', 'zarincode' ); ?></span>
				</label>

				<div class="zc-ct__nav">
					<button type="button" class="zc-btn zc-btn--ghost" data-zc-ct-back>
						<?php esc_html_e( 'بازگشت و ویرایش', 'zarincode' ); ?>
					</button>

					<button type="button" class="zc-btn zc-btn--gold" data-zc-ct-next disabled>
						<?php esc_html_e( 'ادامه و دریافت کد تأیید', 'zarincode' ); ?>
					</button>
				</div>
			</div>

			<!-- گام سه -->
			<div class="zc-panel__box-body zc-ct-pane" data-pane="3">
				<?php require ZC_DIR . 'template-parts/panel/contract-sign.php'; ?>
			</div>
		</div>

		<?php
	endif;

	/* ==========================================================================
	   فهرست قراردادها
	   ========================================================================== */
else :
	$zc_list = zc_user_contracts();

	$zc_tpls = get_posts(
		array(
			'post_type'      => 'zc_contract_tpl',
			'posts_per_page' => 20,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'relation' => 'OR',
				array(
					'key'   => '_zc_ct_public',
					'value' => '1',
				),
				array(
					'key'     => '_zc_ct_public',
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);
	?>
	<div class="zc-panel__head">
		<h2 class="zc-panel__title">
			<?php zc_the_icon( 'file', 20 ); ?>
			<?php esc_html_e( 'قراردادهای من', 'zarincode' ); ?>
		</h2>
	</div>

	<?php if ( $zc_tpls ) : ?>
		<div class="zc-panel__box">
			<div class="zc-panel__box-head">
				<h3><?php esc_html_e( 'ثبت قرارداد جدید', 'zarincode' ); ?></h3>
			</div>

			<div class="zc-panel__box-body">
				<div class="zc-ct-tpls">
					<?php foreach ( $zc_tpls as $zc_t ) : ?>
						<?php $zc_amount = (float) get_post_meta( $zc_t->ID, '_zc_ct_amount', true ); ?>

						<a class="zc-ct-tpl" href="<?php echo esc_url( add_query_arg( array( 'tab' => 'contracts', 'new' => $zc_t->ID ), zc_panel_url() ) ); ?>">
							<span class="zc-ct-tpl__ic"><?php zc_the_icon( 'file', 20 ); ?></span>

							<span class="zc-ct-tpl__body">
								<strong><?php echo esc_html( $zc_t->post_title ); ?></strong>
								<em><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $zc_t->post_content ), 14 ) ); ?></em>

								<?php if ( $zc_amount > 0 ) : ?>
									<span class="zc-ct-tpl__price"><?php echo esc_html( zc_price_text( $zc_amount ) ); ?></span>
								<?php endif; ?>
							</span>

							<span class="zc-ct-tpl__go"><?php zc_the_icon( 'arrow-left', 16 ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="zc-panel__box">
		<div class="zc-panel__box-head">
			<h3><?php esc_html_e( 'قراردادهای ثبت‌شده', 'zarincode' ); ?></h3>
		</div>

		<div class="zc-panel__box-body">
			<?php if ( ! $zc_list ) : ?>
				<div class="zc-empty">
					<?php zc_the_icon( 'file', 34 ); ?>
					<p><?php esc_html_e( 'هنوز قراردادی ثبت نکرده‌اید.', 'zarincode' ); ?></p>
				</div>
			<?php else : ?>
				<div class="zc-ct-list">
					<?php foreach ( $zc_list as $zc_c ) : ?>
						<a class="zc-ct-row" href="<?php echo esc_url( add_query_arg( array( 'tab' => 'contracts', 'contract' => $zc_c['id'] ), zc_panel_url() ) ); ?>">
							<span class="zc-ct-row__main">
								<strong><?php echo esc_html( $zc_c['title'] ); ?></strong>
								<code dir="ltr"><?php echo esc_html( $zc_c['number'] ); ?></code>
							</span>

							<span class="zc-ct-row__meta">
								<?php if ( $zc_c['progress'] > 0 ) : ?>
									<span class="zc-ct-row__prog">
										<span style="width:<?php echo esc_attr( $zc_c['progress'] ); ?>%"></span>
									</span>
								<?php endif; ?>

								<time><?php echo esc_html( zc_fa_num( zc_jalali_date( 'j F Y', $zc_c['created'] ) ) ); ?></time>

								<span class="zc-ct__state" style="--s:<?php echo esc_attr( $zc_c['status_color'] ); ?>">
									<?php echo esc_html( $zc_c['status_label'] ); ?>
								</span>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
