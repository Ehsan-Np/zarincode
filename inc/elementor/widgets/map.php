<?php
/**
 * ویجت نقشه‌ی زرین کد (سبک و سریع)
 * ---------------------------------------------------------------------------
 * ویجت اختصاصی نقشه با پشتیبانی از سه ارائه‌دهنده:
 *   - OpenStreetMap  (بدون کلید، از طریق iframe رسمی)
 *   - نشان Neshan    (کیت Leaflet سبک، نیاز به کلید API از پلتفرم نشان)
 *   - بلد Balad      (کد گنجاندن iframe رسمی که از بالد کپی می‌شود)
 *   - iframe دلخواه   (هر نقشه/آدرس دلخواه)
 *
 * برای هر موقعیت، امکان نمایش «پین» با لوگو، عنوان، توضیح و آدرس فراهم است.
 * بهینه‌سازی: SDK نشان فقط در صورت نیاز و هنگام نزدیک شدن به ویوپورت بارگذاری
 * می‌شود؛ iframe ها هم lazy هستند. هیچ درخواست خارجی برای OSM و iframe‌ی دلخواه
 * وجود ندارد.
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * ویجت نقشه.
 */
class ZC_Widget_map extends ZC_Widget_Base {

	/** @return string */
	public function get_name() { return 'zc_map'; }
	/** @return string */
	public function get_title() { return __( 'زرین کد | نقشه (نشان/بلد/OSM)', 'zarincode' ); }
	/** @return string */
	public function get_icon() { return 'eicon-google-maps'; }
	/** @return array */
	public function get_keywords() {
		return array( 'map', 'نقشه', 'neshan', 'نشان', 'balad', 'بلد', 'openstreetmap', 'osm' );
	}

	/**
	 * کنترل‌ها.
	 *
	 * @return void
	 */
	protected function register_controls() {

		/* ---------- تنظیمات نقشه ---------- */
		$this->start_controls_section(
			'map_section',
			array( 'label' => __( 'ارائه‌دهنده و موقعیت', 'zarincode' ) )
		);

		$this->add_control(
			'provider',
			array(
				'label'   => __( 'ارائه‌دهنده‌ی نقشه', 'zarincode' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'osm',
				'options' => array(
					'osm'    => __( 'OpenStreetMap (بدون کلید)', 'zarincode' ),
					'neshan' => __( 'نشان (Neshan — کلید API)', 'zarincode' ),
					'balad'  => __( 'بلد (Balad — کد iframe)', 'zarincode' ),
					'custom' => __( 'iframe دلخواه', 'zarincode' ),
				),
			)
		);

		$this->add_control(
			'lat',
			array(
				'label'   => __( 'عرض جغرافیایی (Latitude)', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '35.7575',
				'condition' => array( 'provider' => array( 'osm', 'neshan' ) ),
			)
		);

		$this->add_control(
			'lng',
			array(
				'label'   => __( 'طول جغرافیایی (Longitude)', 'zarincode' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '51.4100',
				'condition' => array( 'provider' => array( 'osm', 'neshan' ) ),
			)
		);

		$this->add_control(
			'zoom',
			array(
				'label'   => __( 'بزرگ‌نمایی', 'zarincode' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => array( 'size' => 15 ),
				'range'   => array( 'px' => array( 'min' => 3, 'max' => 19 ) ),
				'condition' => array( 'provider' => array( 'osm', 'neshan' ) ),
			)
		);

		$this->add_control(
			'height',
			array(
				'label'   => __( 'ارتفاع نقشه', 'zarincode' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => array( 'size' => 380 ),
				'range'   => array( 'px' => array( 'min' => 180, 'max' => 700 ) ),
				'selectors' => array( '{{WRAPPER}} .zc-map__frame, {{WRAPPER}} .zc-map__canvas' => 'height:{{SIZE}}px' ),
			)
		);

		$this->add_control(
			'neshan_key',
			array(
				'label'       => __( 'کلید API نشان', 'zarincode' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'اگر خالی باشد از تنظیمات قالب استفاده می‌شود.', 'zarincode' ),
				'description' => __( 'کلید را از پنل توسعه‌دهندگان نشان (platform.neshan.org) دریافت کنید.', 'zarincode' ),
				'condition'   => array( 'provider' => 'neshan' ),
			)
		);

		$this->add_control(
			'neshan_maptype',
			array(
				'label'     => __( 'استایل نقشه‌ی نشان', 'zarincode' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'dreamy',
				'options'   => array(
					'dreamy'         => __( 'دریمی (روشن)', 'zarincode' ),
					'standard-night' => __( 'شب', 'zarincode' ),
				),
				'condition' => array( 'provider' => 'neshan' ),
			)
		);

		$this->add_control(
			'balad_iframe',
			array(
				'label'       => __( 'کد گنجاندن بلد', 'zarincode' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 6,
				'description' => __( 'در بلد، کسب‌وکار را پیدا کنید ← اشتراک‌گذاری ← گنجاندن نقشه ← کپی کد، و اینجا بچسبانید.', 'zarincode' ),
				'condition'   => array( 'provider' => 'balad' ),
			)
		);

		$this->add_control(
			'custom_iframe',
			array(
				'label'       => __( 'آدرس یا کد iframe دلخواه', 'zarincode' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 6,
				'description' => __( 'می‌توانید نشانی مستقیم (https://…) یا تگ کامل <iframe> وارد کنید.', 'zarincode' ),
				'condition'   => array( 'provider' => 'custom' ),
			)
		);

		$this->end_controls_section();

		/* ---------- پین / کارت موقعیت ---------- */
		$this->start_controls_section(
			'pin_section',
			array( 'label' => __( 'پین موقعیت (لوگو و توضیحات)', 'zarincode' ) )
		);

		$this->add_control(
			'pin_enable',
			array(
				'label'        => __( 'نمایش پین روی نقشه', 'zarincode' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'pin_logo',
			array(
				'label' => __( 'لوگوی پین (تصویر)', 'zarincode' ),
				'type'  => Controls_Manager::MEDIA,
				'description' => __( 'تصویر لوگو یا آیکن برای پین؛ اگر خالی باشد از آیکن پیش‌فرض استفاده می‌شود.', 'zarincode' ),
			)
		);

		$this->add_control(
			'pin_title',
			array(
				'label'       => __( 'عنوان موقعیت', 'zarincode' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'زرین کد', 'zarincode' ),
				'placeholder' => __( 'مثلاً: زرین کد', 'zarincode' ),
			)
		);

		$this->add_control(
			'pin_desc',
			array(
				'label' => __( 'توضیحات موقعیت', 'zarincode' ),
				'type'  => Controls_Manager::TEXTAREA,
				'rows'  => 3,
				'placeholder' => __( 'مثلاً: دفتر مرکزی و مرکز آموزش', 'zarincode' ),
			)
		);

		$this->add_control(
			'pin_address',
			array(
				'label' => __( 'آدرس', 'zarincode' ),
				'type'  => Controls_Manager::TEXTAREA,
				'rows'  => 2,
				'placeholder' => __( 'آدرس کامل', 'zarincode' ),
			)
		);

		$this->add_control(
			'pin_btn_text',
			array(
				'label'       => __( 'متن دکمه (اختیاری)', 'zarincode' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'مسیریابی', 'zarincode' ),
			)
		);

		$this->add_control(
			'pin_btn_url',
			array(
				'label'       => __( 'لینک دکمه', 'zarincode' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://',
			)
		);

		$this->add_control(
			'pin_show_popup',
			array(
				'label'        => __( 'باز بودن پاپ‌آپ پین هنگام لود', 'zarincode' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => array( 'provider' => array( 'osm', 'neshan' ) ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * ساخت کلید API نشان (کلید ویجت یا سراسری).
	 *
	 * @param array $s تنظیمات.
	 * @return string
	 */
	private function neshan_key( $s ) {
		$key = trim( (string) ( $s['neshan_key'] ?? '' ) );
		if ( ! $key ) {
			$key = trim( (string) zc_opt( 'zc_neshan_api_key', '' ) );
		}
		return $key;
	}

	/**
	 * رندر.
	 *
	 * @return void
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$prov  = $s['provider'] ?? 'osm';
		$lat   = (float) ( $s['lat'] ?? 35.7575 );
		$lng   = (float) ( $s['lng'] ?? 51.41 );
		$zoom  = (int) ( $s['zoom']['size'] ?? 15 );
		$pin   = ( 'yes' === ( $s['pin_enable'] ?? 'yes' ) );
		$title = trim( (string) ( $s['pin_title'] ?? '' ) );

		$logo_url = '';
		if ( ! empty( $s['pin_logo']['url'] ) ) {
			$logo_url = $s['pin_logo']['url'];
		}
		$desc    = (string) ( $s['pin_desc'] ?? '' );
		$address = (string) ( $s['pin_address'] ?? '' );
		$btn_txt = (string) ( $s['pin_btn_text'] ?? '' );
		$btn_url = (string) ( ( $s['pin_btn_url']['url'] ?? '' ) );

		$classes = array( 'zc-map', 'zc-map--' . $prov );
		if ( $pin && $title ) {
			$classes[] = 'has-pin';
		}
		?>
		<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-zc-anim="up">
			<div class="zc-map__viewport">
				<?php
				if ( 'osm' === $prov ) {
					$this->render_osm( $lat, $lng, $zoom, $pin, $s );
				} elseif ( 'neshan' === $prov ) {
					$this->render_neshan( $lat, $lng, $zoom, $pin, $s );
				} elseif ( 'balad' === $prov ) {
					$this->render_iframe_code( (string) ( $s['balad_iframe'] ?? '' ) );
				} elseif ( 'custom' === $prov ) {
					$this->render_iframe_code( (string) ( $s['custom_iframe'] ?? '' ) );
				}
				?>

				<?php if ( $pin && $title ) : ?>
				<div class="zc-map__card">
					<?php if ( $logo_url ) : ?>
						<div class="zc-map__card-logo"><img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" decoding="async"></div>
					<?php endif; ?>
					<div class="zc-map__card-body">
						<h3 class="zc-map__card-title"><?php echo esc_html( $title ); ?></h3>
						<?php if ( $desc ) : ?><p class="zc-map__card-desc"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
						<?php if ( $address ) : ?>
							<p class="zc-map__card-address"><?php echo esc_html( $address ); ?></p>
						<?php endif; ?>
						<?php if ( $btn_url ) : ?>
							<a class="zc-btn zc-btn--gold zc-btn--sm" href="<?php echo esc_url( $btn_url ); ?>" target="_blank" rel="noopener nofollow"><?php echo esc_html( $btn_txt ); ?></a>
						<?php endif; ?>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( 'neshan' === $prov ) : ?>
			<script><?php echo $this->neshan_script(); // phpcs:ignore ?></script>
		<?php endif; ?>
		<?php
	}

	/**
	 * رندر OpenStreetMap (iframe رسمی + پین).
	 *
	 * @param float $lat عرض.
	 * @param float $lng طول.
	 * @param int   $zoom بزرگنمایی.
	 * @param bool  $pin  پین.
	 * @param array $s    تنظیمات.
	 * @return void
	 */
	private function render_osm( $lat, $lng, $zoom, $pin, $s ) {
		$d    = 0.0045;
		$bbox = ( $lng - $d ) . '%2C' . ( $lat - $d / 2 ) . '%2C' . ( $lng + $d ) . '%2C' . ( $lat + $d / 2 );
		$src  = 'https://www.openstreetmap.org/export/embed.html?bbox=' . $bbox . '&layer=mapnik&marker=' . $lat . '%2C' . $lng;

		if ( 'yes' === ( $s['pin_show_popup'] ?? 'yes' ) && $pin ) {
			$src .= '&note=' . rawurlencode( (string) ( $s['pin_title'] ?? '' ) );
		}
		?>
		<iframe class="zc-map__frame" src="<?php echo esc_url( $src ); ?>" loading="lazy"
			title="<?php esc_attr_e( 'نقشه OpenStreetMap', 'zarincode' ); ?>"></iframe>
		<?php
	}

	/**
	 * رندر نشان (Neshan Leaflet) — سبک و lazy.
	 *
	 * @param float $lat عرض.
	 * @param float $lng طول.
	 * @param int   $zoom بزرگنمایی.
	 * @param bool  $pin  پین.
	 * @param array $s    تنظیمات.
	 * @return void
	 */
	private function render_neshan( $lat, $lng, $zoom, $pin, $s ) {
		$key = $this->neshan_key( $s );

		if ( ! $key ) {
			echo '<div class="zc-map__note">' . esc_html__( 'برای نقشه‌ی نشان، کلید API را در تنظیمات قالب یا این ویجت وارد کنید.', 'zarincode' ) . '</div>';
			return;
		}

		$maptype = (string) ( $s['neshan_maptype'] ?? 'dreamy' );
		$logo    = (string) ( $s['pin_logo']['url'] ?? '' );
		$title   = esc_attr( (string) ( $s['pin_title'] ?? '' ) );
		$desc    = esc_attr( (string) ( $s['pin_desc'] ?? '' ) );
		$popup   = 'yes' === ( $s['pin_show_popup'] ?? 'yes' ) ? '1' : '0';
		$uniq    = 'zc-neshan-' . wp_unique_id();
		?>
		<div class="zc-map__canvas" id="<?php echo esc_attr( $uniq ); ?>" data-key="<?php echo esc_attr( $key ); ?>"
			data-lat="<?php echo esc_attr( $lat ); ?>" data-lng="<?php echo esc_attr( $lng ); ?>"
			data-zoom="<?php echo esc_attr( $zoom ); ?>" data-maptype="<?php echo esc_attr( $maptype ); ?>"
			data-pin="<?php echo $pin ? '1' : '0'; ?>" data-logo="<?php echo esc_attr( $logo ); ?>"
			data-title="<?php echo esc_attr( $title ); ?>" data-desc="<?php echo esc_attr( $desc ); ?>"
			data-popup="<?php echo esc_attr( $popup ); ?>"></div>
		<?php
	}

	/**
	 * رندر iframe کد (بلد / دلخواه) با پاک‌سازی امن.
	 *
	 * @param string $code کد.
	 * @return void
	 */
	private function render_iframe_code( $code ) {
		$code = trim( (string) $code );

		if ( '' === $code ) {
			echo '<div class="zc-map__note">' . esc_html__( 'کد iframe وارد نشده است.', 'zarincode' ) . '</div>';
			return;
		}

		// اگر فقط آدرس باشد، داخل iframe قرار می‌گیرد.
		if ( 0 === strpos( $code, 'http' ) ) {
			$code = '<iframe src="' . esc_url( $code ) . '" loading="lazy" title="' . esc_attr__( 'نقشه', 'zarincode' ) . '"></iframe>';
		}

		echo '<div class="zc-map__frame">';
		echo function_exists( 'zc_kses_badge' ) ? zc_kses_badge( $code ) : wp_kses_post( $code ); // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</div>';
	}

	/**
	 * اسکریپت اختصاصی رندر نشان (تزریق lazy SDK).
	 *
	 * @return string
	 */
	private function neshan_script() {
		ob_start();
		?>
		(function () {
			'use strict';
			var seen = 0;
			function loadSDK(cb) {
				var c = document.createElement('link');
				c.rel = 'stylesheet';
				c.href = 'https://static.neshan.org/sdk/leaflet/v1.9.4/neshan-sdk/v1.0.8/index.css';
				document.head.appendChild(c);
				var s = document.createElement('script');
				s.src = 'https://static.neshan.org/sdk/leaflet/v1.9.4/neshan-sdk/v1.0.8/index.js';
				s.onload = function () { cb && cb(); };
				s.onerror = function () { cb && cb(true); };
				document.head.appendChild(s);
			}
			function initMap(el) {
				if (el.getAttribute('data-init')) return;
				el.setAttribute('data-init', '1');
				loadSDK(function (err) {
					if (err || typeof L === 'undefined' || !L.Map) return;
					var key = el.getAttribute('data-key');
					var lat = parseFloat(el.getAttribute('data-lat'));
					var lng = parseFloat(el.getAttribute('data-lng'));
					var zoom = parseInt(el.getAttribute('data-zoom'), 10) || 15;
					var maptype = el.getAttribute('data-maptype') || 'dreamy';
					var map;
					try {
						map = new L.Map(el.id, { key: key, maptype: maptype, center: [lat, lng], zoom: zoom });
					} catch (e) { return; }
					if (el.getAttribute('data-pin') !== '1') return;
					var title = el.getAttribute('data-title') || '';
					var desc = el.getAttribute('data-desc') || '';
					var logo = el.getAttribute('data-logo') || '';
					var popup = el.getAttribute('data-popup') === '1';
					var html = '';
					if (logo) html += '<div class="zc-neshan-marker"><img src="' + logo + '" alt=""></div>';
					var body = '<div class="zc-neshan-popup">';
					if (title) body += '<strong>' + title + '</strong>';
					if (desc) body += '<span>' + desc + '</span>';
					body += '</div>';
					html += body;
					var icon = logo ? L.divIcon({ className: 'zc-neshan-icon', html: html, iconSize: [40, 40], iconAnchor: [20, 40] }) : null;
					var marker = L.marker([lat, lng], icon ? { icon: icon } : {}).addTo(map);
					if (popup) marker.bindPopup(body).openPopup();
				});
			}
			function scan() {
				var els = document.querySelectorAll('.zc-map[data-zc-anim] .zc-map__canvas[data-key]');
				for (var i = 0; i < els.length; i++) {
					var el = els[i];
					if (el.getAttribute('data-init')) continue;
					var r = el.getBoundingClientRect();
					if (r.top < (window.innerHeight + 200) && r.bottom > -200) { initMap(el); }
				}
			}
			if ('IntersectionObserver' in window) {
				var io = new IntersectionObserver(function (entries) {
					entries.forEach(function (e) { if (e.isIntersecting) { initMap(e.target); io.unobserve(e.target); } });
				}, { rootMargin: '300px' });
				document.querySelectorAll('.zc-map__canvas[data-key]').forEach(function (el) { io.observe(el); });
			} else {
				scan();
				window.addEventListener('scroll', function () { seen++; if (seen % 3 === 0) scan(); }, { passive: true });
			}
		})();
		<?php
		return ob_get_clean();
	}

	/**
	 * خروجی نهایی با اسکریپت نشان (فقط برای نشان).
	 *
	 * @return void
	 */
	protected function content_template() {
		parent::content_template();
	}
}
