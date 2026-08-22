<?php
/**
 * قالب دیدگاه‌ها
 *
 * @package Zarincode
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="zc-comments">

	<?php if ( have_comments() ) : ?>
		<h3 class="zc-comments__title">
			<?php zc_the_icon( 'chat', 20 ); ?>
			<?php
			printf(
				/* translators: %s: comments count */
				esc_html__( '%s دیدگاه', 'zarincode' ),
				esc_html( zc_fa_num( get_comments_number() ) )
			);
			?>
		</h3>

		<ol class="zc-comments__list">
			<?php
			wp_list_comments(
				array(
					'walker'      => new ZC_Comment_Walker(),
					'style'       => 'ol',
					'avatar_size' => 48,
				)
			);
			?>
		</ol>

		<?php
		the_comments_pagination(
			array(
				'prev_text' => zc_icon( 'chevron', 18 ),
				'next_text' => zc_icon( 'chevron', 18 ),
				'class'     => 'zc-pagination',
			)
		);
		?>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply'          => '<span style="display:flex;align-items:center;gap:9px">' . zc_icon( 'edit', 20 ) . esc_html__( 'دیدگاه خود را بنویسید', 'zarincode' ) . '</span>',
			'title_reply_to'       => esc_html__( 'پاسخ به %s', 'zarincode' ),
			'cancel_reply_link'    => esc_html__( 'لغو پاسخ', 'zarincode' ),
			'label_submit'         => esc_html__( 'ثبت دیدگاه', 'zarincode' ),
			'class_submit'         => 'zc-btn zc-btn--gold',
			'class_form'           => 'zc-comment-form',
			'comment_notes_before' => '<p class="zc-help">' . esc_html__( 'نشانی ایمیل شما منتشر نخواهد شد. بخش‌های موردنیاز علامت‌گذاری شده‌اند.', 'zarincode' ) . '</p>',
			'comment_field'        => '<div class="zc-field"><label class="zc-label" for="comment">' . esc_html__( 'دیدگاه شما', 'zarincode' ) . ' <span class="req">*</span></label><textarea id="comment" name="comment" rows="5" required placeholder="' . esc_attr__( 'نظر، سوال یا تجربه خود را بنویسید…', 'zarincode' ) . '"></textarea></div>',
			'fields'               => array(
				'author' => '<div class="zc-row"><div class="zc-col zc-field"><label class="zc-label" for="author">' . esc_html__( 'نام', 'zarincode' ) . ' <span class="req">*</span></label><input id="author" name="author" type="text" required></div>',
				'email'  => '<div class="zc-col zc-field"><label class="zc-label" for="email">' . esc_html__( 'ایمیل', 'zarincode' ) . ' <span class="req">*</span></label><input id="email" name="email" type="email" required></div></div>',
				'url'    => '',
			),
		)
	);
	?>
</div>
