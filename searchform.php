<?php
/**
 * فرم جستجو
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;
?>
<form role="search" method="get" class="zc-searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<div style="display:flex;gap:8px">
		<div class="zc-input--icon" style="flex:1">
			<?php zc_the_icon( 'search', 18 ); ?>
			<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>"
				placeholder="<?php esc_attr_e( 'جستجو…', 'zarincode' ); ?>"
				aria-label="<?php esc_attr_e( 'جستجو', 'zarincode' ); ?>">
		</div>
		<button type="submit" class="zc-btn zc-btn--gold" aria-label="<?php esc_attr_e( 'جستجو', 'zarincode' ); ?>">
			<?php zc_the_icon( 'search', 18 ); ?>
		</button>
	</div>
</form>
