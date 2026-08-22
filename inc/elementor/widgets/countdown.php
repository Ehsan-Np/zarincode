<?php
/**
 * ویجت شمارش معکوس تخفیف
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * ویجت شمارش معکوس.
 */
class ZC_Widget_countdown extends ZC_Widget_Base {

	/** @return string */
	public function get_name() { return 'zc_countdown'; }
	/** @return string */
	public function get_title() { return __( 'زرین کد | شمارش معکوس تخفیف', 'zarincode' ); }
	/** @return string */
	public function get_icon() { return 'eicon-countdown'; }

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'zarincode' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'جشنواره تخفیف زرین کد', 'zarincode' ) ) );
		$this->add_control( 'sub', array( 'label' => __( 'زیرعنوان', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'تا ۷۰٪ تخفیف روی تمام دوره‌ها', 'zarincode' ) ) );
		$this->add_control( 'date', array( 'label' => __( 'تاریخ پایان', 'zarincode' ), 'type' => Controls_Manager::DATE_TIME, 'default' => gmdate( 'Y-m-d H:i', strtotime( '+7 days' ) ) ) );
		$this->add_control( 'btn_text', array( 'label' => __( 'متن دکمه', 'zarincode' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'مشاهده تخفیف‌ها', 'zarincode' ) ) );
		$this->add_control( 'btn_link', array( 'label' => __( 'لینک', 'zarincode' ), 'type' => Controls_Manager::URL ) );
		$this->end_controls_section();
	}

	/**
	 * رندر.
	 *
	 * @return void
	 */
	protected function render() {
		$s  = $this->get_settings_for_display();
		$ts = strtotime( $s['date'] );
		?>
		<div class="zc-cd" data-zc-anim="zoom">
			<h2 style="color:#fff;position:relative"><?php echo esc_html( $s['title'] ); ?></h2>
			<p style="color:rgba(255,255,255,.72);position:relative"><?php echo esc_html( $s['sub'] ); ?></p>

			<div class="zc-cd__timer" data-zc-timer="<?php echo esc_attr( $ts ); ?>">
				<?php
				$units = array(
					'days'    => __( 'روز', 'zarincode' ),
					'hours'   => __( 'ساعت', 'zarincode' ),
					'minutes' => __( 'دقیقه', 'zarincode' ),
					'seconds' => __( 'ثانیه', 'zarincode' ),
				);
				foreach ( $units as $key => $label ) :
					?>
					<div class="zc-cd__unit">
						<span class="zc-cd__num" data-unit="<?php echo esc_attr( $key ); ?>">۰۰</span>
						<span class="zc-cd__lbl"><?php echo esc_html( $label ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>

			<?php if ( ! empty( $s['btn_text'] ) ) : ?>
				<a href="<?php echo esc_url( $s['btn_link']['url'] ?? '#' ); ?>" class="zc-btn zc-btn--gold zc-btn--lg" style="position:relative">
					<?php echo esc_html( $s['btn_text'] ); ?><?php zc_the_icon( 'arrow-left', 18 ); ?>
				</a>
			<?php endif; ?>
		</div>
		<script>
		(function(){
			var box=document.querySelector('[data-zc-timer="<?php echo esc_js( $ts ); ?>"]');
			if(!box)return;
			var end=<?php echo (int) $ts; ?>*1000;
			var fa=['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
			function f(n){n=n<10?'0'+n:''+n;return n.replace(/\d/g,function(d){return fa[d];});}
			function tick(){
				var diff=end-Date.now();
				if(diff<0)diff=0;
				var s=Math.floor(diff/1000),d=Math.floor(s/86400),h=Math.floor(s%86400/3600),m=Math.floor(s%3600/60),ss=s%60;
				var q=function(u){return box.querySelector('[data-unit="'+u+'"]');};
				if(q('days'))q('days').textContent=f(d);
				if(q('hours'))q('hours').textContent=f(h);
				if(q('minutes'))q('minutes').textContent=f(m);
				if(q('seconds'))q('seconds').textContent=f(ss);
			}
			tick();setInterval(tick,1000);
		})();
		</script>
		<?php
	}
}
