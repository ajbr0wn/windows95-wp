<?php
/**
 * 404 template - Blue Screen of Death!
 *
 * @package Win95
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body>

<div class="bsod" id="bsod">
	<div class="bsod__content">
		<div class="bsod__title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></div>
		<div class="bsod__text">
An error has occurred. To continue:

Press CTRL+ALT+DEL to restart your computer. If you do this, you will lose any unsaved information in all open applications.

Error: 0E : 016F : BFF9B3D4

The page you requested could not be found. It may have been moved, deleted, or perhaps it never existed in the first place.

*  Press any key to return to the <a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color: #ffffff;">home page</a>.
*  Press CTRL+ALT+DEL to try again.
		</div>
		<div class="bsod__prompt">
			Press any key to continue <span class="bsod__cursor">_</span>
		</div>
	</div>
</div>

<script>
// Any key press or click returns to home
document.addEventListener('keydown', function() {
	window.location.href = '<?php echo esc_url( home_url( '/' ) ); ?>';
});
document.addEventListener('click', function(e) {
	if (e.target.tagName !== 'A') {
		window.location.href = '<?php echo esc_url( home_url( '/' ) ); ?>';
	}
});
</script>

<?php wp_footer(); ?>
</body>
</html>
