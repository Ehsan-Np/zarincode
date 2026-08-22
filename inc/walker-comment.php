<?php
/**
 * واکر دیدگاه‌ها
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

/**
 * کلاس واکر دیدگاه.
 */
class ZC_Comment_Walker extends Walker_Comment {

	/**
	 * نوع درخت.
	 *
	 * @var string
	 */
	public $tree_type = 'comment';

	/**
	 * شروع سطح.
	 *
	 * @param string $output خروجی.
	 * @param int    $depth  عمق.
	 * @param array  $args   آرگومان.
	 * @return void
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= '<ul class="children">' . "\n";
	}

	/**
	 * پایان سطح.
	 *
	 * @param string $output خروجی.
	 * @param int    $depth  عمق.
	 * @param array  $args   آرگومان.
	 * @return void
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		$output .= '</ul>' . "\n";
	}

	/**
	 * شروع المان.
	 *
	 * @param string     $output  خروجی.
	 * @param WP_Comment $comment دیدگاه.
	 * @param int        $depth   عمق.
	 * @param array      $args    آرگومان.
	 * @param int        $id      شناسه.
	 * @return void
	 */
	public function start_el( &$output, $comment, $depth = 0, $args = array(), $id = 0 ) {
		$GLOBALS['comment'] = $comment; // phpcs:ignore
		$is_author          = get_post_field( 'post_author', $comment->comment_post_ID ) == $comment->user_id; // phpcs:ignore

		ob_start();
		?>
		<li id="comment-<?php comment_ID(); ?>" <?php comment_class( 'zc-comment' ); ?>>
			<div class="zc-avatar zc-avatar--md"><?php echo get_avatar( $comment, 48 ); ?></div>
			<div class="zc-comment__body">
				<div class="zc-comment__head">
					<span class="zc-comment__author"><?php echo esc_html( get_comment_author() ); ?></span>
					<?php if ( $is_author ) : ?>
						<span class="zc-badge zc-badge--gold"><?php esc_html_e( 'نویسنده', 'zarincode' ); ?></span>
					<?php endif; ?>
					<span class="zc-comment__date"><?php echo esc_html( zc_human_time( get_comment_date( 'c' ) ) ); ?></span>
				</div>

				<?php if ( '0' === $comment->comment_approved ) : ?>
					<div class="zc-alert zc-alert--warning" style="padding:8px 12px;font-size:.8rem">
						<?php esc_html_e( 'دیدگاه شما در انتظار تایید است.', 'zarincode' ); ?>
					</div>
				<?php endif; ?>

				<div class="zc-comment__text"><?php comment_text(); ?></div>

				<div class="zc-comment__actions">
					<?php
					comment_reply_link(
						array_merge(
							$args,
							array(
								'depth'      => $depth,
								'max_depth'  => $args['max_depth'],
								'reply_text' => zc_icon( 'send', 14 ) . ' ' . __( 'پاسخ', 'zarincode' ),
							)
						),
						$comment
					);
					?>
				</div>
			</div>
		<?php
		$output .= ob_get_clean();
	}

	/**
	 * پایان المان.
	 *
	 * @param string     $output  خروجی.
	 * @param WP_Comment $comment دیدگاه.
	 * @param int        $depth   عمق.
	 * @param array      $args    آرگومان.
	 * @return void
	 */
	public function end_el( &$output, $comment, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}
}
