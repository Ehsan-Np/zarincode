<?php
/**
 * Template Name: ورود و ثبت‌نام زرین کد
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

// کاربران وارد شده به پنل هدایت می‌شوند.
if ( is_user_logged_in() ) {
	wp_safe_redirect( zc_panel_url() );
	exit;
}

$zc_redirect = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : ''; // phpcs:ignore
$zc_method   = zc_opt( 'zc_login_method', 'both' );

get_header();
?>

<div class="zc-auth">
	<div class="zc-auth__box">

		<div class="zc-auth__side">
			<h2><?php echo esc_html( zc_opt( 'zc_login_title', 'به زرین کد خوش آمدید' ) ); ?></h2>
			<p><?php echo esc_html( zc_opt( 'zc_login_desc', 'با عضویت در زرین کد به بیش از ۲۰۰ دوره تخصصی برنامه‌نویسی، فروشگاه محصولات دیجیتال و پشتیبانی حرفه‌ای دسترسی پیدا می‌کنید.' ) ); ?></p>

			<div class="zc-auth__features">
				<?php
				$zc_features = array(
					array( 'video', __( 'دسترسی به دوره‌های ویدیویی', 'zarincode' ) ),
					array( 'certificate', __( 'گواهی معتبر پایان دوره', 'zarincode' ) ),
					array( 'headphone', __( 'پشتیبانی مستقیم مدرس', 'zarincode' ) ),
					array( 'wallet', __( 'کیف پول و تخفیف‌های ویژه', 'zarincode' ) ),
				);
				foreach ( $zc_features as $zc_f ) :
					?>
					<div class="zc-auth__feature">
						<span><?php zc_the_icon( $zc_f[0], 18 ); ?></span>
						<?php echo esc_html( $zc_f[1] ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="zc-auth__form">
			<div class="zc-auth__logo"><?php zc_site_logo(); ?></div>

			<?php if ( 'both' === $zc_method ) : ?>
			<div class="zc-auth__tabs">
				<button type="button" class="zc-auth__tab is-active" data-auth-tab="otp"><?php esc_html_e( 'ورود با پیامک', 'zarincode' ); ?></button>
				<button type="button" class="zc-auth__tab" data-auth-tab="password"><?php esc_html_e( 'رمز عبور', 'zarincode' ); ?></button>
			</div>
			<?php endif; ?>

			<!-- ورود با پیامک: مرحله ۱ -->
			<?php if ( in_array( $zc_method, array( 'both', 'otp' ), true ) ) : ?>
			<div class="zc-auth__pane" data-auth-pane="otp">

				<div class="zc-auth__step is-active" data-step="mobile">
					<h2 class="zc-auth__title"><?php esc_html_e( 'ورود / ثبت‌نام', 'zarincode' ); ?></h2>
					<p class="zc-auth__sub"><?php esc_html_e( 'شماره موبایل خود را وارد کنید تا کد تایید ارسال شود.', 'zarincode' ); ?></p>

					<form id="zc-otp-request">
						<div class="zc-field">
							<label class="zc-label"><?php esc_html_e( 'شماره موبایل', 'zarincode' ); ?></label>
							<div class="zc-input--icon">
								<?php zc_the_icon( 'phone', 18 ); ?>
								<input type="tel" name="mobile" id="zc-mobile" required inputmode="numeric"
									placeholder="09xxxxxxxxx" maxlength="11" autocomplete="tel" dir="ltr" style="text-align:left">
							</div>
						</div>

						<div class="zc-form-msg"></div>

						<button type="submit" class="zc-btn zc-btn--gold zc-btn--block zc-btn--lg">
							<?php esc_html_e( 'دریافت کد تایید', 'zarincode' ); ?>
							<?php zc_the_icon( 'arrow-left', 18 ); ?>
						</button>
					</form>

					<p class="zc-auth__footer">
						<?php esc_html_e( 'با ورود، ', 'zarincode' ); ?>
						<a href="<?php echo esc_url( zc_opt( 'zc_terms_link', '#' ) ); ?>"><?php esc_html_e( 'قوانین و مقررات', 'zarincode' ); ?></a>
						<?php esc_html_e( ' را می‌پذیرم.', 'zarincode' ); ?>
					</p>
				</div>

				<!-- مرحله ۲: کد تایید -->
				<div class="zc-auth__step" data-step="code">
					<button type="button" class="zc-auth__back" data-auth-back>
						<?php zc_the_icon( 'arrow-left', 15 ); ?><?php esc_html_e( 'تغییر شماره', 'zarincode' ); ?>
					</button>

					<h2 class="zc-auth__title"><?php esc_html_e( 'کد تایید را وارد کنید', 'zarincode' ); ?></h2>
					<p class="zc-auth__sub"><?php esc_html_e( 'کد ۵ رقمی ارسال شده به موبایل خود را وارد کنید.', 'zarincode' ); ?></p>

					<div class="zc-mobile-preview" id="zc-mobile-preview"></div>

					<form id="zc-otp-verify">
						<input type="hidden" name="mobile" id="zc-verify-mobile">
						<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $zc_redirect ); ?>">

						<div class="zc-otp-inputs" id="zc-otp-inputs">
							<?php for ( $zc_i = 0; $zc_i < 5; $zc_i++ ) : ?>
								<input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="<?php echo esc_attr( sprintf( __( 'رقم %d', 'zarincode' ), $zc_i + 1 ) ); ?>">
							<?php endfor; ?>
						</div>

						<input type="hidden" name="code" id="zc-otp-code">

						<div class="zc-otp-timer">
							<span id="zc-timer-text"><?php esc_html_e( 'ارسال مجدد کد تا', 'zarincode' ); ?> <strong id="zc-timer">۰۱:۰۰</strong></span>
							<button type="button" id="zc-resend" class="zc-btn zc-btn--ghost zc-btn--sm" style="display:none">
								<?php zc_the_icon( 'refresh', 15 ); ?><?php esc_html_e( 'ارسال مجدد کد', 'zarincode' ); ?>
							</button>
						</div>

						<div class="zc-form-msg"></div>

						<button type="submit" class="zc-btn zc-btn--gold zc-btn--block zc-btn--lg">
							<?php esc_html_e( 'ورود به حساب', 'zarincode' ); ?>
						</button>
					</form>
				</div>
			</div>
			<?php endif; ?>

			<!-- ورود با رمز عبور -->
			<?php if ( in_array( $zc_method, array( 'both', 'password' ), true ) ) : ?>
			<div class="zc-auth__pane" data-auth-pane="password" style="<?php echo 'both' === $zc_method ? 'display:none' : ''; ?>">

				<div class="zc-auth__step is-active" data-step="login">
					<h2 class="zc-auth__title"><?php esc_html_e( 'ورود به حساب', 'zarincode' ); ?></h2>
					<p class="zc-auth__sub"><?php esc_html_e( 'با نام کاربری، ایمیل یا موبایل وارد شوید.', 'zarincode' ); ?></p>

					<form data-zc-form="zc_login_password">
						<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $zc_redirect ); ?>">

						<div class="zc-field">
							<label class="zc-label"><?php esc_html_e( 'نام کاربری / ایمیل / موبایل', 'zarincode' ); ?></label>
							<div class="zc-input--icon">
								<?php zc_the_icon( 'user', 18 ); ?>
								<input type="text" name="username" required autocomplete="username">
							</div>
						</div>

						<div class="zc-field">
							<label class="zc-label"><?php esc_html_e( 'رمز عبور', 'zarincode' ); ?></label>
							<div class="zc-input--icon">
								<?php zc_the_icon( 'lock', 18 ); ?>
								<input type="password" name="password" required autocomplete="current-password">
							</div>
						</div>

						<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:16px">
							<label class="zc-check">
								<input type="checkbox" name="remember" value="1" checked>
								<span><?php esc_html_e( 'مرا به خاطر بسپار', 'zarincode' ); ?></span>
							</label>
							<button type="button" class="zc-auth__back" data-goto-step="forgot" style="margin:0">
								<?php esc_html_e( 'فراموشی رمز عبور؟', 'zarincode' ); ?>
							</button>
						</div>

						<div class="zc-form-msg"></div>

						<button type="submit" class="zc-btn zc-btn--gold zc-btn--block zc-btn--lg"><?php zc_the_icon( 'lock', 18 ); ?><?php esc_html_e( 'ورود', 'zarincode' ); ?></button>
					</form>

					<p class="zc-auth__footer">
						<?php esc_html_e( 'حساب کاربری ندارید؟', 'zarincode' ); ?>
						<button type="button" data-goto-step="register" style="color:var(--zc-gold-3);font-weight:700"><?php esc_html_e( 'ثبت‌نام کنید', 'zarincode' ); ?></button>
					</p>
				</div>

				<!-- ثبت‌نام -->
				<div class="zc-auth__step" data-step="register">
					<button type="button" class="zc-auth__back" data-goto-step="login">
						<?php zc_the_icon( 'arrow-left', 15 ); ?><?php esc_html_e( 'بازگشت به ورود', 'zarincode' ); ?>
					</button>

					<h2 class="zc-auth__title"><?php esc_html_e( 'ساخت حساب جدید', 'zarincode' ); ?></h2>
					<p class="zc-auth__sub"><?php esc_html_e( 'اطلاعات زیر را تکمیل کنید.', 'zarincode' ); ?></p>

					<form data-zc-form="zc_register">
						<div class="zc-field">
							<label class="zc-label"><?php esc_html_e( 'نام و نام خانوادگی', 'zarincode' ); ?></label>
							<input type="text" name="name" required>
						</div>
						<div class="zc-field">
							<label class="zc-label"><?php esc_html_e( 'شماره موبایل', 'zarincode' ); ?></label>
							<input type="tel" name="mobile" id="zc-reg-mobile" required maxlength="11" dir="ltr" style="text-align:left" placeholder="09xxxxxxxxx">
						</div>
						<div class="zc-field">
							<label class="zc-label"><?php esc_html_e( 'ایمیل (اختیاری)', 'zarincode' ); ?></label>
							<input type="email" name="email">
						</div>
						<div class="zc-field">
							<label class="zc-label"><?php esc_html_e( 'رمز عبور', 'zarincode' ); ?></label>
							<input type="password" name="password" required minlength="6" autocomplete="new-password">
						</div>

						<?php if ( zc_opt( 'zc_verify_mobile_on_register', true ) ) : ?>
						<div class="zc-field">
							<label class="zc-label"><?php esc_html_e( 'کد تایید موبایل', 'zarincode' ); ?></label>
							<div style="display:flex;gap:8px">
								<input type="text" name="code" inputmode="numeric" maxlength="5" style="flex:1" dir="ltr">
								<button type="button" class="zc-btn zc-btn--outline zc-btn--sm" data-send-code="#zc-reg-mobile" style="white-space:nowrap">
									<?php esc_html_e( 'ارسال کد', 'zarincode' ); ?>
								</button>
							</div>
						</div>
						<?php endif; ?>

						<div class="zc-form-msg"></div>

						<button type="submit" class="zc-btn zc-btn--gold zc-btn--block zc-btn--lg"><?php zc_the_icon( 'user', 18 ); ?><?php esc_html_e( 'ثبت‌نام', 'zarincode' ); ?></button>
					</form>
				</div>

				<!-- فراموشی رمز -->
				<div class="zc-auth__step" data-step="forgot">
					<button type="button" class="zc-auth__back" data-goto-step="login">
						<?php zc_the_icon( 'arrow-left', 15 ); ?><?php esc_html_e( 'بازگشت', 'zarincode' ); ?>
					</button>

					<h2 class="zc-auth__title"><?php esc_html_e( 'بازیابی رمز عبور', 'zarincode' ); ?></h2>
					<p class="zc-auth__sub"><?php esc_html_e( 'با دریافت کد پیامکی، رمز جدید تعیین کنید.', 'zarincode' ); ?></p>

					<form data-zc-form="zc_reset_password">
						<div class="zc-field">
							<label class="zc-label"><?php esc_html_e( 'شماره موبایل', 'zarincode' ); ?></label>
							<div style="display:flex;gap:8px">
								<input type="tel" name="mobile" id="zc-forgot-mobile" required maxlength="11" style="flex:1" dir="ltr">
								<button type="button" class="zc-btn zc-btn--outline zc-btn--sm" data-send-code="#zc-forgot-mobile" style="white-space:nowrap">
									<?php esc_html_e( 'ارسال کد', 'zarincode' ); ?>
								</button>
							</div>
						</div>
						<div class="zc-field">
							<label class="zc-label"><?php esc_html_e( 'کد تایید', 'zarincode' ); ?></label>
							<input type="text" name="code" required maxlength="5" dir="ltr">
						</div>
						<div class="zc-field">
							<label class="zc-label"><?php esc_html_e( 'رمز عبور جدید', 'zarincode' ); ?></label>
							<input type="password" name="password" required minlength="6">
						</div>

						<div class="zc-form-msg"></div>

						<button type="submit" class="zc-btn zc-btn--gold zc-btn--block zc-btn--lg"><?php esc_html_e( 'تغییر رمز و ورود', 'zarincode' ); ?></button>
					</form>
				</div>
			</div>
			<?php endif; ?>

		</div>
	</div>
</div>

<script>
(function(){
	'use strict';
	var $=function(s,c){return (c||document).querySelector(s);};
	var $$=function(s,c){return Array.prototype.slice.call((c||document).querySelectorAll(s));};
	var CFG=window.ZC||{};
	var fa=['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
	function toFa(n){return String(n).replace(/\d/g,function(d){return fa[d];});}
	function toEn(s){var o=s;fa.forEach(function(d,i){o=o.split(d).join(i);});return o;}

	/* تب‌های ورود */
	$$('[data-auth-tab]').forEach(function(tab){
		tab.addEventListener('click',function(){
			$$('[data-auth-tab]').forEach(function(t){t.classList.remove('is-active');});
			tab.classList.add('is-active');
			$$('[data-auth-pane]').forEach(function(p){
				p.style.display = p.dataset.authPane===tab.dataset.authTab ? '' : 'none';
			});
		});
	});

	/* رفتن بین مراحل */
	function gotoStep(pane,step){
		$$('.zc-auth__step',pane).forEach(function(s){s.classList.remove('is-active');});
		var target=$('[data-step="'+step+'"]',pane);
		if(target)target.classList.add('is-active');
	}
	$$('[data-goto-step]').forEach(function(btn){
		btn.addEventListener('click',function(){
			gotoStep(btn.closest('[data-auth-pane]'),btn.dataset.gotoStep);
		});
	});

	/* شمارنده ارسال مجدد */
	var timerInt=null;
	function startTimer(sec){
		clearInterval(timerInt);
		var el=$('#zc-timer'),txt=$('#zc-timer-text'),resend=$('#zc-resend');
		if(!el)return;
		if(resend)resend.style.display='none';
		if(txt)txt.style.display='';
		timerInt=setInterval(function(){
			sec--;
			var m=Math.floor(sec/60),s=sec%60;
			el.textContent=toFa((m<10?'0':'')+m+':'+(s<10?'0':'')+s);
			if(sec<=0){
				clearInterval(timerInt);
				if(txt)txt.style.display='none';
				if(resend)resend.style.display='inline-flex';
			}
		},1000);
	}

	/* درخواست کد */
	var reqForm=$('#zc-otp-request');
	if(reqForm){
		reqForm.addEventListener('submit',function(e){
			e.preventDefault();
			var input=$('#zc-mobile');
			var mobile=toEn(input.value.trim());
			var btn=reqForm.querySelector('[type=submit]');
			var msg=$('.zc-form-msg',reqForm);

			if(!/^09\d{9}$/.test(mobile)){
				msg.innerHTML='<div class="zc-alert zc-alert--error">شماره موبایل معتبر نیست.</div>';
				return;
			}

			btn.classList.add('is-loading');btn.disabled=true;
			msg.innerHTML='';

			window.zcAjax('zc_send_otp',{mobile:mobile}).then(function(res){
				btn.classList.remove('is-loading');btn.disabled=false;
				if(res.success){
					$('#zc-verify-mobile').value=res.data.mobile||mobile;
					$('#zc-mobile-preview').textContent=toFa(res.data.mobile||mobile);
					gotoStep(reqForm.closest('[data-auth-pane]'),'code');
					startTimer(res.data.expire||60);
					var first=$('#zc-otp-inputs input');
					if(first)setTimeout(function(){first.focus();},300);
					window.zcToast(res.data.message,'success');
				}else{
					msg.innerHTML='<div class="zc-alert zc-alert--error">'+res.data.message+'</div>';
				}
			});
		});
	}

	/* ارسال مجدد */
	var resendBtn=$('#zc-resend');
	if(resendBtn){
		resendBtn.addEventListener('click',function(){
			var mobile=$('#zc-verify-mobile').value;
			resendBtn.classList.add('is-loading');
			window.zcAjax('zc_send_otp',{mobile:mobile}).then(function(res){
				resendBtn.classList.remove('is-loading');
				window.zcToast(res.data.message,res.success?'success':'error');
				if(res.success)startTimer(res.data.expire||60);
			});
		});
	}

	/* اینپوت‌های OTP */
	var otpInputs=$$('#zc-otp-inputs input');
	otpInputs.forEach(function(input,idx){
		input.addEventListener('input',function(){
			input.value=toEn(input.value).replace(/\D/g,'');
			if(input.value && idx<otpInputs.length-1)otpInputs[idx+1].focus();
			updateCode();
		});
		input.addEventListener('keydown',function(e){
			if(e.key==='Backspace' && !input.value && idx>0)otpInputs[idx-1].focus();
		});
		input.addEventListener('paste',function(e){
			e.preventDefault();
			var data=toEn((e.clipboardData||window.clipboardData).getData('text')).replace(/\D/g,'');
			data.split('').forEach(function(ch,i){if(otpInputs[i])otpInputs[i].value=ch;});
			updateCode();
			var last=Math.min(data.length,otpInputs.length)-1;
			if(otpInputs[last])otpInputs[last].focus();
		});
	});
	function updateCode(){
		var code=otpInputs.map(function(i){return i.value;}).join('');
		var hidden=$('#zc-otp-code');
		if(hidden)hidden.value=code;
		if(code.length===otpInputs.length){
			var form=$('#zc-otp-verify');
			if(form)form.dispatchEvent(new Event('submit',{cancelable:true,bubbles:true}));
		}
	}

	/* تایید کد */
	var verifyForm=$('#zc-otp-verify');
	if(verifyForm){
		verifyForm.addEventListener('submit',function(e){
			e.preventDefault();
			var btn=verifyForm.querySelector('[type=submit]');
			var msg=$('.zc-form-msg',verifyForm);
			btn.classList.add('is-loading');btn.disabled=true;

			window.zcAjax('zc_login_otp',{
				mobile:$('#zc-verify-mobile').value,
				code:$('#zc-otp-code').value,
				redirect_to:verifyForm.querySelector('[name=redirect_to]').value
			}).then(function(res){
				btn.classList.remove('is-loading');btn.disabled=false;
				if(res.success){
					msg.innerHTML='<div class="zc-alert zc-alert--success">'+res.data.message+'</div>';
					setTimeout(function(){location.href=res.data.redirect;},700);
				}else{
					msg.innerHTML='<div class="zc-alert zc-alert--error">'+res.data.message+'</div>';
					otpInputs.forEach(function(i){i.value='';});
					if(otpInputs[0])otpInputs[0].focus();
				}
			});
		});
	}

	/* دکمه‌های ارسال کد در ثبت‌نام و بازیابی */
	$$('[data-send-code]').forEach(function(btn){
		btn.addEventListener('click',function(){
			var input=$(btn.dataset.sendCode);
			if(!input)return;
			var mobile=toEn(input.value.trim());
			if(!/^09\d{9}$/.test(mobile)){window.zcToast('شماره موبایل معتبر نیست.','error');return;}
			btn.classList.add('is-loading');btn.disabled=true;
			window.zcAjax('zc_send_otp',{mobile:mobile}).then(function(res){
				btn.classList.remove('is-loading');
				window.zcToast(res.data.message,res.success?'success':'error');
				if(res.success){
					var sec=res.data.expire||60;
					var t=setInterval(function(){
						sec--;btn.textContent=toFa(sec)+' ثانیه';
						if(sec<=0){clearInterval(t);btn.textContent='ارسال مجدد';btn.disabled=false;}
					},1000);
				}else{btn.disabled=false;}
			});
		});
	});

	/* نرمال‌سازی ورودی موبایل */
	$$('input[type=tel]').forEach(function(input){
		input.addEventListener('input',function(){
			input.value=toEn(input.value).replace(/[^\d]/g,'');
		});
	});
})();
</script>

<?php
get_footer();
