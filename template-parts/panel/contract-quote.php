<?php
/**
 * مرحله‌ی اعلام و تأیید مبلغ پروژه
 *
 * پیش از نمایش پرداخت مرحله‌ای فراخوانی می‌شود و بسته به وضعیت
 * قرارداد یکی از سه حالت را نشان می‌دهد:
 *   signed  → در انتظار اعلام مبلغ از سوی مجری
 *   quoted  → مبلغ اعلام شده و منتظر تأیید کارفرماست
 *   سایر    → قرارداد در وضعیتی است که پرداخت معنا ندارد
 *
 * @package Zarincode
 * @var array $zc_c داده‌ی قرارداد.
 */

defined( 'ABSPATH' ) || exit;

$zc_note    = (string) get_post_meta( $zc_c['id'], '_zc_ct_quote_note', true );
$zc_steps   = array(
	array( 'title' => __( 'امضای قرارداد', 'zarincode' ), 'done' => ! empty( $zc_c['signed_at'] ) ),
	array( 'title' => __( 'اعلام مبلغ پروژه', 'zarincode' ), 'done' => in_array( $zc_c['status'], array( 'quoted', 'approved', 'active', 'done' ), true ) ),
	array( 'title' => __( 'تأیید کارفرما', 'zarincode' ), 'done' => in_array( $zc_c['status'], array( 'approved', 'active', 'done' ), true ) ),
	array( 'title' => __( 'پرداخت مرحله‌ای', 'zarincode' ), 'done' => false ),
);
?>

<div class="zc-quote" data-zc-quote data-contract="<?php echo esc_attr( $zc_c['id'] ); ?>">

	<!-- نوار مراحل -->
	<ol class="zc-qsteps">
		<?php foreach ( $zc_steps as $zc_i => $zc_st ) : ?>
			<?php
			$zc_current = ! $zc_st['done'] && ( 0 === $zc_i || $zc_steps[ $zc_i - 1 ]['done'] );
			$zc_cls     = $zc_st['done'] ? 'is-done' : ( $zc_current ? 'is-current' : '' );
			?>
			<li class="zc-qstep <?php echo esc_attr( $zc_cls ); ?>">
				<span class="zc-qstep__n">
					<?php if ( $zc_st['done'] ) : ?>
						<?php zc_the_icon( 'check', 13 ); ?>
					<?php else : ?>
						<?php echo esc_html( zc_fa_num( $zc_i + 1 ) ); ?>
					<?php endif; ?>
				</span>
				<span class="zc-qstep__t"><?php echo esc_html( $zc_st['title'] ); ?></span>
			</li>
		<?php endforeach; ?>
	</ol>

	<?php if ( 'quoted' === $zc_c['status'] && $zc_c['amount'] > 0 ) : ?>

		<!-- مبلغ اعلام شده، منتظر تأیید -->
		<div class="zc-quote__card">
			<div class="zc-quote__head">
				<?php zc_the_icon( 'wallet', 20 ); ?>
				<h3><?php esc_html_e( 'مبلغ پروژه اعلام شد', 'zarincode' ); ?></h3>
			</div>

			<div class="zc-quote__amount">
				<span><?php esc_html_e( 'مبلغ کل پروژه', 'zarincode' ); ?></span>
				<strong><?php echo esc_html( zc_price_text( $zc_c['amount'] ) ); ?></strong>
			</div>

			<?php if ( $zc_note ) : ?>
				<div class="zc-quote__note">
					<strong><?php esc_html_e( 'توضیح مجری:', 'zarincode' ); ?></strong>
					<p><?php echo nl2br( esc_html( $zc_note ) ); ?></p>
				</div>
			<?php endif; ?>

			<!-- پیش‌نمایش مراحل پرداخت -->
			<?php $zc_defs = zc_contract_stage_defs( $zc_c['id'], $zc_c['tpl_id'] ); ?>

			<?php if ( $zc_defs ) : ?>
				<div class="zc-quote__plan">
					<strong class="zc-quote__plan-title">
						<?php zc_the_icon( 'grid', 15 ); ?>
						<?php esc_html_e( 'برنامه پرداخت پس از تأیید', 'zarincode' ); ?>
					</strong>

					<ul>
						<?php foreach ( $zc_defs as $zc_i => $zc_d ) : ?>
							<li>
								<span class="zc-quote__plan-n"><?php echo esc_html( zc_fa_num( $zc_i + 1 ) ); ?></span>
								<span class="zc-quote__plan-name"><?php echo esc_html( $zc_d['title'] ); ?></span>
								<span class="zc-quote__plan-pct"><?php echo esc_html( zc_fa_num( $zc_d['percent'] ) ); ?>٪</span>
								<span class="zc-quote__plan-amt">
									<?php echo esc_html( zc_price_text( round( $zc_c['amount'] * $zc_d['percent'] / 100 ) ) ); ?>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<label class="zc-ct__agree">
				<input type="checkbox" data-zc-quote-agree>
				<span><?php esc_html_e( 'مبلغ اعلام‌شده و برنامه پرداخت را بررسی کردم و می‌پذیرم.', 'zarincode' ); ?></span>
			</label>

			<div class="zc-quote__msg"></div>

			<div class="zc-quote__actions">
				<button type="button" class="zc-btn zc-btn--gold zc-btn--lg" data-zc-quote-approve disabled>
					<?php zc_the_icon( 'check', 17 ); ?>
					<?php esc_html_e( 'تأیید مبلغ و ادامه به پرداخت', 'zarincode' ); ?>
				</button>

				<button type="button" class="zc-btn zc-btn--ghost" data-zc-quote-dispute-open>
					<?php esc_html_e( 'اعتراض به مبلغ', 'zarincode' ); ?>
				</button>
			</div>

			<!-- فرم اعتراض -->
			<div class="zc-quote__dispute" data-zc-dispute-box hidden>
				<label class="zc-label"><?php esc_html_e( 'دلیل اعتراض خود را بنویسید', 'zarincode' ); ?></label>
				<textarea rows="3" data-zc-dispute-text
					placeholder="<?php esc_attr_e( 'مثلاً: برخی امکانات درخواستی در برآورد لحاظ نشده است…', 'zarincode' ); ?>"></textarea>

				<button type="button" class="zc-btn zc-btn--ghost zc-btn--sm" data-zc-quote-dispute>
					<?php esc_html_e( 'ارسال اعتراض', 'zarincode' ); ?>
				</button>
			</div>
		</div>

	<?php elseif ( in_array( $zc_c['status'], array( 'signed', 'pending' ), true ) ) : ?>

		<!-- در انتظار اعلام مبلغ -->
		<div class="zc-quote__wait">
			<?php zc_the_icon( 'clock', 30 ); ?>
			<h3><?php esc_html_e( 'در انتظار اعلام مبلغ پروژه', 'zarincode' ); ?></h3>

			<p>
				<?php esc_html_e( 'قرارداد شما امضا شده است. کارشناسان ما در حال بررسی مشخصات پروژه و برآورد مبلغ نهایی هستند. به‌محض تعیین مبلغ، از طریق پیامک و اعلان به شما اطلاع داده می‌شود.', 'zarincode' ); ?>
			</p>

			<p class="zc-quote__wait-note">
				<?php zc_the_icon( 'info', 14 ); ?>
				<?php esc_html_e( 'بخش پرداخت پس از اعلام مبلغ و تأیید شما فعال خواهد شد.', 'zarincode' ); ?>
			</p>

			<a class="zc-btn zc-btn--ghost zc-btn--sm"
				href="<?php echo esc_url( add_query_arg( array( 'tab' => 'contract-chat', 'contract' => $zc_c['id'] ), zc_panel_url() ) ); ?>">
				<?php zc_the_icon( 'chat', 15 ); ?>
				<?php esc_html_e( 'گفتگو با مجری', 'zarincode' ); ?>
			</a>
		</div>

	<?php elseif ( in_array( $zc_c['status'], array( 'terminating', 'terminated' ), true ) ) : ?>

		<div class="zc-quote__wait zc-quote__wait--warn">
			<?php zc_the_icon( 'alert', 30 ); ?>
			<h3>
				<?php
				echo 'terminated' === $zc_c['status']
					? esc_html__( 'این قرارداد فسخ شده است', 'zarincode' )
					: esc_html__( 'درخواست فسخ در حال بررسی است', 'zarincode' );
				?>
			</h3>

			<p>
				<?php
				echo 'terminated' === $zc_c['status']
					? esc_html__( 'قرارداد به‌صورت رسمی خاتمه یافته و امکان پرداخت جدید وجود ندارد.', 'zarincode' )
					: esc_html__( 'تا تعیین تکلیف درخواست فسخ، پرداخت جدید غیرفعال است.', 'zarincode' );
				?>
			</p>
		</div>

	<?php endif; ?>
</div>
