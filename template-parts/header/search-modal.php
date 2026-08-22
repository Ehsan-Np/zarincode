<?php
/**
 * مودال جستجوی ای‌جکس
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="zc-search" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'جستجو در سایت', 'zarincode' ); ?>">
	<div class="zc-search__box">
		<form class="zc-search__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php zc_the_icon( 'search', 22 ); ?>
			<input type="search" name="s" class="zc-search__input zc-input"
				placeholder="<?php esc_attr_e( 'دنبال چه چیزی می‌گردید؟ (دوره، محصول، مقاله…)', 'zarincode' ); ?>"
				autocomplete="off" aria-label="<?php esc_attr_e( 'عبارت جستجو', 'zarincode' ); ?>">
			<button type="button" class="zc-search__close" data-zc-close="search" aria-label="<?php esc_attr_e( 'بستن', 'zarincode' ); ?>">
				<?php zc_the_icon( 'close', 20 ); ?>
			</button>
		</form>

		<div class="zc-search__filters">
			<?php
			$zc_filters = array(
				'all'       => __( 'همه', 'zarincode' ),
				'course'    => __( 'دوره‌ها', 'zarincode' ),
				'product'   => __( 'محصولات', 'zarincode' ),
				'post'      => __( 'مقالات', 'zarincode' ),
				'tutorial'  => __( 'آموزش‌ها', 'zarincode' ),
			);
			foreach ( $zc_filters as $zc_key => $zc_label ) :
				?>
				<button type="button" class="zc-search__filter<?php echo 'all' === $zc_key ? ' is-active' : ''; ?>" data-type="<?php echo esc_attr( $zc_key ); ?>">
					<?php echo esc_html( $zc_label ); ?>
				</button>
			<?php endforeach; ?>
		</div>

		<div class="zc-search__results">
			<?php
			$zc_popular = zc_opt( 'zc_popular_searches', 'php, لاراول, ری‌اکت, وردپرس, پایتون' );
			$zc_tags    = array_filter( array_map( 'trim', explode( ',', $zc_popular ) ) );
			?>
			<div class="zc-search__state">
				<p style="margin-bottom:12px"><?php esc_html_e( 'جستجوهای پرطرفدار:', 'zarincode' ); ?></p>
				<div class="zc-tagcloud" style="justify-content:center">
					<?php foreach ( $zc_tags as $zc_tag ) : ?>
						<a href="<?php echo esc_url( home_url( '/?s=' . rawurlencode( $zc_tag ) ) ); ?>"><?php echo esc_html( $zc_tag ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		<div class="zc-search__footer">
			<span style="font-size:.78rem;color:var(--zc-muted)">
				<?php esc_html_e( 'برای بستن کلید ESC را بزنید', 'zarincode' ); ?>
			</span>
		</div>
	</div>
</div>
