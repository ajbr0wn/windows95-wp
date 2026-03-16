<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="desktop" id="desktop">
	<!-- Desktop Icons Grid -->
	<div class="desktop-icons-grid">
		<!-- Static system icons -->
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="desktop-icon" title="<?php esc_attr_e( 'My Computer', 'win95' ); ?>">
			<img class="desktop-icon__image" src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/computer-32.png' ); ?>" width="32" height="32" alt="" draggable="false">
			<span class="desktop-icon__label"><?php _e( 'My Computer', 'win95' ); ?></span>
		</a>
		<?php
		// Link to the posts page if set, otherwise homepage
		$blog_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
		$blog_label = get_theme_mod( 'win95_blog_label', __( 'My Blog', 'win95' ) );
		?>
		<a href="<?php echo esc_url( $blog_url ); ?>" class="desktop-icon" title="<?php echo esc_attr( $blog_label ); ?>">
			<img class="desktop-icon__image" src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/folder-32.png' ); ?>" width="32" height="32" alt="" draggable="false">
			<span class="desktop-icon__label"><?php echo esc_html( $blog_label ); ?></span>
		</a>

		<?php
		// Desktop icons for published pages (not posts)
		$desktop_pages = get_pages( array( 'number' => 6, 'sort_column' => 'menu_order' ) );
		if ( ! empty( $desktop_pages ) ) :
			foreach ( $desktop_pages as $dpage ) :
		?>
			<a href="<?php echo esc_url( get_permalink( $dpage->ID ) ); ?>" class="desktop-icon" title="<?php echo esc_attr( $dpage->post_title ); ?>">
				<img class="desktop-icon__image" src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/document-32.png' ); ?>" width="32" height="32" alt="" draggable="false">
				<span class="desktop-icon__label"><?php echo wp_trim_words( $dpage->post_title, 4 ); ?></span>
			</a>
		<?php
			endforeach;
		endif;
		?>
	</div>

	<?php
	// Determine window title
	if ( is_home() || is_front_page() ) {
		$window_title = get_bloginfo( 'name' ) . ' - Internet Explorer';
	} elseif ( is_single() || is_page() ) {
		$window_title = get_the_title() . ' - Notepad';
	} elseif ( is_archive() ) {
		$window_title = get_the_archive_title() . ' - Explorer';
	} elseif ( is_search() ) {
		$window_title = __( 'Search Results', 'win95' ) . ' - Find';
	} else {
		$window_title = get_bloginfo( 'name' );
	}
	?>

	<!-- Main Window -->
	<div class="win95-window main-window is-active" id="main-window">
		<div class="win95-title-bar" id="main-title-bar">
			<?php
			// Show appropriate icon in title bar
			if ( is_single() || is_page() ) {
				$title_icon = 'document';
			} else {
				$title_icon = 'ie';
			}
			echo '<img class="win95-icon" src="' . esc_url( get_template_directory_uri() . '/assets/icons/' . $title_icon . '.png' ) . '" width="16" height="16" alt="" style="margin-right:3px;flex-shrink:0" draggable="false">';
			?>
			<span class="win95-title-bar-text"><?php echo esc_html( $window_title ); ?></span>
			<div class="win95-title-bar-controls">
				<button class="win95-btn-minimize" aria-label="<?php esc_attr_e( 'Minimize', 'win95' ); ?>"></button>
				<button class="win95-btn-maximize" aria-label="<?php esc_attr_e( 'Maximize', 'win95' ); ?>"></button>
				<button class="win95-btn-close" aria-label="<?php esc_attr_e( 'Close', 'win95' ); ?>" onclick="window.location='<?php echo esc_url( home_url( '/' ) ); ?>'"></button>
			</div>
		</div>

		<!-- Menu bar -->
		<ul class="win95-menu-bar">
			<li>
				<button>File</button>
				<ul class="win95-dropdown" role="menu">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
					<li class="win95-dropdown-separator"></li>
					<?php
					wp_list_pages( array(
						'title_li' => '',
						'depth'    => 1,
					) );
					?>
					<li class="win95-dropdown-separator"></li>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Exit</a></li>
				</ul>
			</li>
			<li>
				<button>Edit</button>
				<ul class="win95-dropdown" role="menu">
					<li><a href="#" onclick="document.execCommand('copy');return false;">Copy</a></li>
					<li><a href="#" onclick="document.execCommand('selectAll');return false;">Select All</a></li>
				</ul>
			</li>
			<li>
				<button>View</button>
				<ul class="win95-dropdown" role="menu">
					<li><a href="#" onclick="document.querySelector('.main-window .win95-window-body').style.fontSize='11px';return false;">Text Size: Small</a></li>
					<li><a href="#" onclick="document.querySelector('.main-window .win95-window-body').style.fontSize='13px';return false;">Text Size: Medium</a></li>
					<li><a href="#" onclick="document.querySelector('.main-window .win95-window-body').style.fontSize='16px';return false;">Text Size: Large</a></li>
					<li class="win95-dropdown-separator"></li>
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php _e( 'Refresh', 'win95' ); ?></a></li>
				</ul>
			</li>
			<li>
				<button>Help</button>
				<ul class="win95-dropdown" role="menu">
					<li><a href="<?php echo esc_url( home_url( '/about' ) ); ?>">About</a></li>
				</ul>
			</li>
		</ul>

		<?php if ( is_home() || is_front_page() || is_archive() || is_search() ) : ?>
		<!-- Address bar for list views -->
		<div class="win95-address-bar">
			<span class="win95-address-bar__label">Address</span>
			<input type="text" class="win95-text-input win95-address-bar__input" value="" readonly>
			<script>document.querySelector('.win95-address-bar__input').value = window.location.href;</script>
		</div>
		<?php endif; ?>

		<div class="win95-window-body">
