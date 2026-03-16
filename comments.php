<?php
/**
 * Comments template.
 *
 * @package Win95
 */

if ( post_password_required() ) {
	return;
}
?>

<div class="comments-window win95-window is-active" id="comments">
	<div class="win95-title-bar">
		<span class="win95-title-bar-text">
			<?php
			printf(
				_n( '%s Comment', '%s Comments', get_comments_number(), 'win95' ),
				number_format_i18n( get_comments_number() )
			);
			?>
		</span>
		<div class="win95-title-bar-controls">
			<button class="win95-btn-minimize" aria-label="Minimize"></button>
			<button class="win95-btn-maximize" aria-label="Maximize"></button>
			<button class="win95-btn-close" aria-label="Close"></button>
		</div>
	</div>
	<div class="win95-window-body">
		<?php if ( have_comments() ) : ?>
			<ol class="comment-list">
				<?php
				wp_list_comments( array(
					'style'      => 'ol',
					'short_ping' => true,
					'callback'   => 'win95_comment',
				) );
				?>
			</ol>

			<?php the_comments_navigation(); ?>
		<?php endif; ?>

		<?php if ( comments_open() ) : ?>
			<hr class="win95-separator">
			<?php
			comment_form( array(
				'title_reply'          => __( 'Leave a Comment', 'win95' ),
				'title_reply_before'   => '<h3 style="font-size: 12px; font-weight: bold; margin: 0 0 8px;">',
				'title_reply_after'    => '</h3>',
				'comment_notes_before' => '',
				'class_form'           => 'comment-form',
				'class_submit'         => 'win95-btn win95-btn--default',
				'label_submit'         => __( 'Submit', 'win95' ),
			) );
			?>
		<?php endif; ?>
	</div>
</div>

<?php
/**
 * Custom comment callback.
 */
function win95_comment( $comment, $args, $depth ) {
	$tag = ( $args['style'] === 'div' ) ? 'div' : 'li';
	?>
	<<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( 'comment-item' ); ?>>
		<div class="comment-author">
			<?php comment_author_link(); ?>
		</div>
		<div class="comment-date">
			<?php comment_date(); ?> at <?php comment_time(); ?>
		</div>
		<?php if ( $comment->comment_approved == '0' ) : ?>
			<em style="font-size: 10px;"><?php _e( 'Your comment is awaiting moderation.', 'win95' ); ?></em>
		<?php endif; ?>
		<div class="comment-body">
			<?php comment_text(); ?>
		</div>
		<?php
		comment_reply_link( array_merge( $args, array(
			'depth'     => $depth,
			'max_depth' => $args['max_depth'],
			'before'    => '<div style="margin-top: 4px;">',
			'after'     => '</div>',
		) ) );
		?>
	<?php
}
