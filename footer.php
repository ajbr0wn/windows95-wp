		</div><!-- .win95-window-body -->

		<!-- Status bar -->
		<ul class="win95-status-bar">
			<li class="win95-status-bar__field" style="flex: 2">
				<?php
				if ( is_home() || is_front_page() ) {
					printf( __( '%s posts', 'win95' ), wp_count_posts()->publish );
				} elseif ( is_single() ) {
					printf( __( '%s words', 'win95' ), str_word_count( wp_strip_all_tags( get_the_content() ) ) );
				} else {
					echo __( 'Done', 'win95' );
				}
				?>
			</li>
			<li class="win95-status-bar__field"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></li>
		</ul>
	</div><!-- .main-window -->

	<?php
	// Social Links Window
	$social_links = win95_get_social_links();
	if ( ! empty( $social_links ) ) :
	?>
	<div class="win95-window social-window" id="social-window">
		<div class="win95-title-bar">
			<?php echo '<img class="win95-icon" src="' . esc_url( get_template_directory_uri() . '/assets/icons/home.png' ) . '" width="16" height="16" alt="" style="margin-right:3px;flex-shrink:0" draggable="false">'; ?>
			<span class="win95-title-bar-text">Social</span>
			<div class="win95-title-bar-controls">
				<button class="win95-btn-minimize" aria-label="Minimize"></button>
				<button class="win95-btn-close" aria-label="Close"></button>
			</div>
		</div>
		<div class="win95-window-body social-window__body">
			<?php foreach ( $social_links as $key => $link ) : ?>
				<a href="<?php echo esc_url( $link['url'] ); ?>" class="social-link" title="<?php echo esc_attr( $link['label'] ); ?>" target="_blank" rel="noopener noreferrer">
					<svg class="social-link__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
						<path d="<?php echo $link['icon']; ?>"/>
					</svg>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

</div><!-- .desktop -->

<!-- Taskbar -->
<div class="taskbar" id="taskbar">
	<!-- Start Button -->
	<button class="start-button" id="start-button" aria-expanded="false" aria-controls="start-menu">
		<img class="start-button__logo" src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/windows-logo.png' ); ?>" width="16" height="16" alt="" draggable="false"
			onerror="this.style.display='none';this.nextElementSibling.style.display='inline';"><svg class="start-button__logo" style="display:none" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="1" width="6" height="6" fill="#ff0000"/><rect x="9" y="1" width="6" height="6" fill="#00ff00"/><rect x="1" y="9" width="6" height="6" fill="#0000ff"/><rect x="9" y="9" width="6" height="6" fill="#ffff00"/></svg>
		<span>Start</span>
	</button>

	<!-- Start Menu -->
	<div class="start-menu" id="start-menu" role="navigation" aria-label="<?php esc_attr_e( 'Start Menu', 'win95' ); ?>">
		<div class="start-menu__sidebar">
			<span class="start-menu__sidebar-text"><span>Windows</span>95</span>
		</div>
		<div class="start-menu__content">
			<?php
			if ( has_nav_menu( 'start-menu' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'start-menu',
					'container'      => false,
					'menu_class'     => 'start-menu__items',
					'walker'         => new Win95_Start_Menu_Walker(),
					'depth'          => 1,
				) );
			} else {
				// Fallback menu with Win95-style icons
				echo '<ul class="start-menu__items">';
				echo '<li class="start-menu-item"><a href="' . esc_url( home_url( '/' ) ) . '" class="start-menu-link">' . win95_icon( 'programs' ) . '<span class="start-menu-label">' . __( 'Home', 'win95' ) . '</span></a></li>';
				echo '<li class="start-menu__separator"></li>';

				$pages = get_pages( array( 'number' => 8 ) );
				foreach ( $pages as $page ) {
					echo '<li class="start-menu-item"><a href="' . esc_url( get_permalink( $page->ID ) ) . '" class="start-menu-link">' . win95_icon( 'document' ) . '<span class="start-menu-label">' . esc_html( $page->post_title ) . '</span></a></li>';
				}

				echo '<li class="start-menu__separator"></li>';
				echo '<li class="start-menu-item"><a href="' . esc_url( home_url( '/?s=' ) ) . '" class="start-menu-link">' . win95_icon( 'find' ) . '<span class="start-menu-label">' . __( 'Find...', 'win95' ) . '</span></a></li>';

				if ( is_user_logged_in() ) {
					echo '<li class="start-menu-item"><a href="' . esc_url( admin_url() ) . '" class="start-menu-link">' . win95_icon( 'dashboard' ) . '<span class="start-menu-label">' . __( 'Dashboard', 'win95' ) . '</span></a></li>';
				}

				echo '<li class="start-menu__separator"></li>';
				echo '<li class="start-menu-item"><a href="' . esc_url( home_url( '/' ) ) . '" class="start-menu-link">' . win95_icon( 'shutdown' ) . '<span class="start-menu-label">' . __( 'Shut Down...', 'win95' ) . '</span></a></li>';
				echo '</ul>';
			}
			?>
		</div>
	</div>

	<!-- Quick Launch -->
	<div class="quick-launch">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php esc_attr_e( 'Home', 'win95' ); ?>">
			<img class="win95-icon" src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/ie.png' ); ?>" width="16" height="16" alt="" draggable="false">
		</a>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php esc_attr_e( 'My Computer', 'win95' ); ?>">
			<img class="win95-icon" src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/computer.png' ); ?>" width="16" height="16" alt="" draggable="false">
		</a>
		<?php if ( ! empty( $social_links ) ) : ?>
		<a href="#" id="social-quick-launch" title="<?php esc_attr_e( 'Social Links', 'win95' ); ?>">
			<img class="win95-icon" src="<?php echo esc_url( get_template_directory_uri() . '/assets/icons/home.png' ); ?>" width="16" height="16" alt="" draggable="false">
		</a>
		<?php endif; ?>
	</div>

	<!-- Taskbar window buttons -->
	<div class="taskbar-windows" id="taskbar-windows">
		<button class="taskbar-window-btn is-active">
			<?php
			if ( is_single() || is_page() ) {
				echo esc_html( wp_trim_words( get_the_title(), 5 ) );
			} else {
				echo esc_html( get_bloginfo( 'name' ) );
			}
			?>
		</button>
	</div>

	<!-- System Tray -->
	<div class="system-tray" id="system-tray">
		<span class="system-tray__clock" id="system-clock"></span>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
