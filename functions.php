<?php
/**
 * Windows 95 Theme functions and definitions.
 *
 * @package Win95
 */

if ( ! defined( 'WIN95_VERSION' ) ) {
	define( 'WIN95_VERSION', '1.0.0' );
}

/**
 * Theme setup.
 */
function win95_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );
	add_theme_support( 'custom-logo', array(
		'height'      => 32,
		'width'       => 32,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'custom-background', array(
		'default-color' => '008080',
	) );

	register_nav_menus( array(
		'start-menu'  => __( 'Start Menu', 'win95' ),
		'quick-launch' => __( 'Quick Launch Bar', 'win95' ),
	) );

	set_post_thumbnail_size( 300, 200, true );
}
add_action( 'after_setup_theme', 'win95_setup' );

/**
 * Enqueue scripts and styles.
 */
function win95_scripts() {
	wp_enqueue_style( 'win95-fonts', get_template_directory_uri() . '/assets/css/fonts.css', array(), WIN95_VERSION );
	wp_enqueue_style( 'win95-ui', get_template_directory_uri() . '/assets/css/win95.css', array(), WIN95_VERSION );
	wp_enqueue_style( 'win95-theme', get_template_directory_uri() . '/assets/css/theme.css', array( 'win95-ui' ), WIN95_VERSION );
	wp_enqueue_script( 'win95-js', get_template_directory_uri() . '/assets/js/win95.js', array(), WIN95_VERSION, true );

	wp_localize_script( 'win95-js', 'win95Data', array(
		'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		'themeUrl' => get_template_directory_uri(),
		'homeUrl'  => home_url( '/' ),
		'siteName' => get_bloginfo( 'name' ),
	) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'win95_scripts' );

/**
 * Register widget areas.
 */
function win95_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Desktop Widgets', 'win95' ),
		'id'            => 'desktop-widgets',
		'description'   => __( 'Widgets displayed as windows on the desktop.', 'win95' ),
		'before_widget' => '<div id="%1$s" class="win95-window desktop-widget %2$s"><div class="win95-title-bar"><span class="win95-title-bar-text">',
		'after_widget'  => '</div></div></div>',
		'before_title'  => '',
		'after_title'   => '</span><div class="win95-title-bar-controls"><button class="win95-btn-minimize" aria-label="Minimize"></button><button class="win95-btn-maximize" aria-label="Maximize"></button><button class="win95-btn-close" aria-label="Close"></button></div></div><div class="win95-window-body">',
	) );
}
add_action( 'widgets_init', 'win95_widgets_init' );

/**
 * Social Links Customizer settings.
 */
function win95_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'win95_social', array(
		'title'    => __( 'Social Links', 'win95' ),
		'priority' => 35,
	) );

	// Blog folder label
	$wp_customize->add_setting( 'win95_blog_label', array(
		'default'           => __( 'My Blog', 'win95' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'win95_blog_label', array(
		'label'   => __( 'Blog Folder Label', 'win95' ),
		'description' => __( 'The name shown on the desktop folder icon that links to your blog.', 'win95' ),
		'section' => 'win95_social',
		'type'    => 'text',
	) );

	$social_networks = array(
		'twitter'    => 'Twitter / X',
		'discord'    => 'Discord',
		'github'     => 'GitHub',
		'youtube'    => 'YouTube',
		'twitch'     => 'Twitch',
		'instagram'  => 'Instagram',
		'facebook'   => 'Facebook',
		'linkedin'   => 'LinkedIn',
		'mastodon'   => 'Mastodon',
		'reddit'     => 'Reddit',
		'tiktok'     => 'TikTok',
		'bluesky'    => 'Bluesky',
		'email'      => 'Email',
		'rss'        => 'RSS',
		'website'    => 'Website',
	);

	foreach ( $social_networks as $key => $label ) {
		$wp_customize->add_setting( 'win95_social_' . $key, array(
			'default'           => '',
			'sanitize_callback' => ( $key === 'email' ) ? 'sanitize_email' : 'esc_url_raw',
		) );
		$wp_customize->add_control( 'win95_social_' . $key, array(
			'label'   => $label,
			'section' => 'win95_social',
			'type'    => 'url',
		) );
	}
}
add_action( 'customize_register', 'win95_customize_register' );

/**
 * Get active social links.
 */
function win95_get_social_links() {
	$networks = array(
		'twitter'   => array( 'label' => 'Twitter / X',  'icon' => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z' ),
		'discord'   => array( 'label' => 'Discord',      'icon' => 'M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03z' ),
		'github'    => array( 'label' => 'GitHub',       'icon' => 'M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12' ),
		'youtube'   => array( 'label' => 'YouTube',      'icon' => 'M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z' ),
		'twitch'    => array( 'label' => 'Twitch',       'icon' => 'M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714z' ),
		'instagram' => array( 'label' => 'Instagram',    'icon' => 'M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 1 0 0-12.324zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405a1.441 1.441 0 1 1-2.88 0 1.441 1.441 0 0 1 2.88 0z' ),
		'facebook'  => array( 'label' => 'Facebook',     'icon' => 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z' ),
		'linkedin'  => array( 'label' => 'LinkedIn',     'icon' => 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z' ),
		'mastodon'  => array( 'label' => 'Mastodon',     'icon' => 'M23.268 5.313c-.35-2.578-2.617-4.61-5.304-5.004C17.51.242 15.792 0 11.813 0h-.03c-3.98 0-4.835.242-5.288.309C3.882.692 1.496 2.518.917 5.127.64 6.412.614 7.837.661 9.143c.065 1.862.079 3.723.236 5.58.12 1.332.346 2.65.672 3.942.599 2.45 3.025 4.494 5.387 5.325 2.454.87 5.106 1.012 7.641.456.312-.067.618-.156.918-.26a19.39 19.39 0 0 0 1.985-.83l.043-.023-.094-2.067s-1.838.581-3.895.51c-2.042-.072-4.2-.227-4.532-2.83a5.172 5.172 0 0 1-.048-.745s1.938.473 4.393.585c1.502.069 2.909-.088 4.338-.266 2.758-.345 5.161-2.144 5.467-3.788.481-2.582.44-6.308.44-6.308l-.005-.31zm-3.398 6.46h-2.555V6.99c0-1.115-.468-1.682-1.403-1.682-1.034 0-1.551.672-1.551 2v2.89h-2.54v-2.89c0-1.328-.517-2-1.551-2-.935 0-1.403.567-1.403 1.682v4.783H6.312c0-1.282-.029-2.342-.088-3.18.059-.878.326-1.558.8-2.042.475-.483 1.094-.726 1.855-.726 1.082 0 1.902.416 2.449 1.248l.53.888.527-.888c.547-.832 1.367-1.248 2.449-1.248.761 0 1.38.243 1.855.726.475.484.741 1.164.8 2.042-.058.838-.088 1.898-.088 3.18z' ),
		'reddit'    => array( 'label' => 'Reddit',       'icon' => 'M12 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 0 1-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 0 1 .042.52c0 2.694-3.13 4.87-7.004 4.87-3.874 0-7.004-2.176-7.004-4.87 0-.183.015-.366.043-.534A1.748 1.748 0 0 1 4.028 12c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 0 1 .14-.197.35.35 0 0 1 .238-.042l2.906.617a1.214 1.214 0 0 1 1.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 0-.688-.561-1.249-1.249-1.249zm5.5 0c-.687 0-1.248.561-1.248 1.25 0 .687.561 1.248 1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.562-1.249-1.25-1.249zm-5.466 3.99a.327.327 0 0 0-.231.094.33.33 0 0 0 0 .463c.842.842 2.484.913 2.961.913.477 0 2.105-.056 2.961-.913a.361.361 0 0 0 .029-.463.33.33 0 0 0-.464 0c-.547.533-1.684.73-2.512.73-.828 0-1.979-.196-2.512-.73a.326.326 0 0 0-.232-.095z' ),
		'tiktok'    => array( 'label' => 'TikTok',       'icon' => 'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z' ),
		'bluesky'   => array( 'label' => 'Bluesky',      'icon' => 'M12 10.8c-1.087-2.114-4.046-6.053-6.798-7.995C2.566.944 1.561 1.266.902 1.565.139 1.908 0 3.08 0 3.768c0 .69.378 5.65.624 6.479.785 2.627 3.657 3.493 6.264 3.252-4.593.646-8.626 2.222-3.544 7.849 5.599 5.509 7.564-1.378 8.656-4.974.164-.538.224-.79.224-.579 0-.21.06.041.224.58 1.092 3.595 3.057 10.482 8.656 4.973 5.347-5.89.663-7.16-3.544-7.849 2.607.241 5.48-.625 6.264-3.252.246-.828.624-5.789.624-6.478 0-.69-.139-1.861-.902-2.206-.659-.298-1.664-.62-4.3 1.24C16.046 4.748 13.087 8.687 12 10.8z' ),
		'email'     => array( 'label' => 'Email',        'icon' => 'M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z' ),
		'rss'       => array( 'label' => 'RSS',          'icon' => 'M19.199 24C19.199 13.467 10.533 4.8 0 4.8V0c13.165 0 24 10.835 24 24h-4.801zM3.291 17.415c1.814 0 3.293 1.479 3.293 3.295 0 1.813-1.485 3.29-3.301 3.29C1.47 24 0 22.526 0 20.71s1.475-3.294 3.291-3.295zM15.909 24h-4.665c0-6.169-5.075-11.245-11.244-11.245V8.09c8.727 0 15.909 7.184 15.909 15.91z' ),
		'website'   => array( 'label' => 'Website',      'icon' => 'M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm1 16.057v-3.057h2.994c-.059 1.143-.212 2.24-.456 3.279-.823-.12-1.674-.188-2.538-.222zm1.957 2.162c-.499 1.33-1.159 2.497-1.957 3.456v-3.62c.666.028 1.319.081 1.957.164zm-1.957-7.219v-3h3.328c-.046.462-.11.917-.193 1.363-.838-.128-1.698-.201-2.579-.236l-.556-.127zm4.265-3h3.977c-.395 1.08-.973 2.074-1.693 2.948-.251-.381-.521-.748-.809-1.098-.566-.684-1.075-1.273-1.475-1.85zm-5.265 0h-3v3h-.556c-.881.035-1.741.108-2.579.236a23.378 23.378 0 0 1-.193-1.363h3.328v-1.873h3v-3zm-8.265 0h-3.977c.395-1.08.973-2.074 1.693-2.948.251.381.521.748.809 1.098.566.684 1.075 1.273 1.475 1.85z' ),
	);

	$links = array();
	foreach ( $networks as $key => $data ) {
		$url = get_theme_mod( 'win95_social_' . $key, '' );
		if ( ! empty( $url ) ) {
			$links[ $key ] = array(
				'url'   => ( $key === 'email' ) ? 'mailto:' . $url : $url,
				'label' => $data['label'],
				'icon'  => $data['icon'],
			);
		}
	}

	return $links;
}

/**
 * Custom Start Menu walker.
 */
class Win95_Start_Menu_Walker extends Walker_Nav_Menu {
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="start-menu-item ' . esc_attr( $class_names ) . '"' : ' class="start-menu-item"';

		$output .= '<li' . $class_names . '>';

		$atts = array(
			'title'  => ! empty( $item->attr_title ) ? $item->attr_title : '',
			'target' => ! empty( $item->target ) ? $item->target : '',
			'rel'    => ! empty( $item->xfn ) ? $item->xfn : '',
			'href'   => ! empty( $item->url ) ? $item->url : '',
			'class'  => 'start-menu-link',
		);

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$attributes .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
			}
		}

		$item_output = $args->before ?? '';
		$item_output .= '<a' . $attributes . '>';
		$item_output .= '<span class="start-menu-icon"></span>';
		$item_output .= '<span class="start-menu-label">' . apply_filters( 'the_title', $item->title, $item->ID ) . '</span>';
		$item_output .= '</a>';
		$item_output .= $args->after ?? '';

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}

/**
 * Register Reading custom post types (Books & Papers).
 */
function win95_register_reading_post_types() {
	// Books
	register_post_type( 'win95_book', array(
		'labels' => array(
			'name'               => __( 'Books', 'win95' ),
			'singular_name'      => __( 'Book', 'win95' ),
			'add_new'            => __( 'Add New Book', 'win95' ),
			'add_new_item'       => __( 'Add New Book', 'win95' ),
			'edit_item'          => __( 'Edit Book', 'win95' ),
			'new_item'           => __( 'New Book', 'win95' ),
			'view_item'          => __( 'View Book', 'win95' ),
			'search_items'       => __( 'Search Books', 'win95' ),
			'not_found'          => __( 'No books found', 'win95' ),
			'not_found_in_trash' => __( 'No books found in Trash', 'win95' ),
			'menu_name'          => __( 'Books', 'win95' ),
		),
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => true,
		'menu_icon'    => 'dashicons-book',
		'supports'     => array( 'title', 'editor', 'thumbnail' ),
		'has_archive'  => false,
		'rewrite'      => false,
	) );

	// Papers
	register_post_type( 'win95_paper', array(
		'labels' => array(
			'name'               => __( 'Papers', 'win95' ),
			'singular_name'      => __( 'Paper', 'win95' ),
			'add_new'            => __( 'Add New Paper', 'win95' ),
			'add_new_item'       => __( 'Add New Paper', 'win95' ),
			'edit_item'          => __( 'Edit Paper', 'win95' ),
			'new_item'           => __( 'New Paper', 'win95' ),
			'view_item'          => __( 'View Paper', 'win95' ),
			'search_items'       => __( 'Search Papers', 'win95' ),
			'not_found'          => __( 'No papers found', 'win95' ),
			'not_found_in_trash' => __( 'No papers found in Trash', 'win95' ),
			'menu_name'          => __( 'Papers', 'win95' ),
		),
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => true,
		'menu_icon'    => 'dashicons-media-document',
		'supports'     => array( 'title', 'editor', 'thumbnail' ),
		'has_archive'  => false,
		'rewrite'      => false,
	) );
}
add_action( 'init', 'win95_register_reading_post_types' );

/**
 * Register meta boxes for Books and Papers.
 */
function win95_reading_meta_boxes() {
	add_meta_box(
		'win95_book_details',
		__( 'Book Details', 'win95' ),
		'win95_book_meta_box_cb',
		'win95_book',
		'normal',
		'high'
	);
	add_meta_box(
		'win95_paper_details',
		__( 'Paper Details', 'win95' ),
		'win95_paper_meta_box_cb',
		'win95_paper',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'win95_reading_meta_boxes' );

function win95_book_meta_box_cb( $post ) {
	wp_nonce_field( 'win95_book_meta', 'win95_book_meta_nonce' );
	$author   = get_post_meta( $post->ID, '_win95_book_author', true );
	$year     = get_post_meta( $post->ID, '_win95_book_year', true );
	$rating   = get_post_meta( $post->ID, '_win95_book_rating', true );
	$pages    = get_post_meta( $post->ID, '_win95_book_pages', true );
	$spine    = get_post_meta( $post->ID, '_win95_book_spine_title', true );
	$series   = get_post_meta( $post->ID, '_win95_book_series', true );
	$color    = get_post_meta( $post->ID, '_win95_book_color', true );
	$url      = get_post_meta( $post->ID, '_win95_book_url', true );
	?>
	<p>
		<label for="win95_book_author"><strong><?php _e( 'Author', 'win95' ); ?></strong></label><br>
		<input type="text" id="win95_book_author" name="win95_book_author" value="<?php echo esc_attr( $author ); ?>" style="width:100%">
	</p>
	<p>
		<label for="win95_book_spine_title"><strong><?php _e( 'Spine Title (optional)', 'win95' ); ?></strong></label><br>
		<input type="text" id="win95_book_spine_title" name="win95_book_spine_title" value="<?php echo esc_attr( $spine ); ?>" style="width:100%">
		<span class="description"><?php _e( 'Short/abbreviated title for the book spine. Leave blank to use the full title.', 'win95' ); ?></span>
	</p>
	<p>
		<label for="win95_book_series"><strong><?php _e( 'Series (optional)', 'win95' ); ?></strong></label><br>
		<input type="text" id="win95_book_series" name="win95_book_series" value="<?php echo esc_attr( $series ); ?>" style="width:100%">
		<span class="description"><?php _e( 'Books in the same series share height and font on the shelf.', 'win95' ); ?></span>
	</p>
	<p>
		<label for="win95_book_year"><strong><?php _e( 'Year Read', 'win95' ); ?></strong></label><br>
		<input type="number" id="win95_book_year" name="win95_book_year" value="<?php echo esc_attr( $year ); ?>" min="1900" max="2099" style="width:100px">
	</p>
	<p>
		<label for="win95_book_rating"><strong><?php _e( 'Rating (1-5)', 'win95' ); ?></strong></label><br>
		<input type="number" id="win95_book_rating" name="win95_book_rating" value="<?php echo esc_attr( $rating ); ?>" min="1" max="5" style="width:60px">
	</p>
	<p>
		<label for="win95_book_pages"><strong><?php _e( 'Page Count', 'win95' ); ?></strong></label><br>
		<input type="number" id="win95_book_pages" name="win95_book_pages" value="<?php echo esc_attr( $pages ); ?>" min="1" max="9999" style="width:100px">
		<span class="description"><?php _e( 'Controls the height and thickness of the book on the shelf.', 'win95' ); ?></span>
	</p>
	<p>
		<label for="win95_book_color"><strong><?php _e( 'Spine Color', 'win95' ); ?></strong></label><br>
		<input type="color" id="win95_book_color" name="win95_book_color" value="<?php echo esc_attr( $color ?: '#000080' ); ?>">
		<span class="description"><?php _e( 'Color of the book spine on the shelf.', 'win95' ); ?></span>
	</p>
	<p>
		<label for="win95_book_url"><strong><?php _e( 'Link URL (optional)', 'win95' ); ?></strong></label><br>
		<input type="url" id="win95_book_url" name="win95_book_url" value="<?php echo esc_attr( $url ); ?>" style="width:100%" placeholder="https://...">
		<span class="description"><?php _e( 'External link (e.g. Goodreads, publisher page).', 'win95' ); ?></span>
	</p>
	<?php
}

function win95_paper_meta_box_cb( $post ) {
	wp_nonce_field( 'win95_paper_meta', 'win95_paper_meta_nonce' );
	$authors  = get_post_meta( $post->ID, '_win95_paper_authors', true );
	$year     = get_post_meta( $post->ID, '_win95_paper_year', true );
	$venue    = get_post_meta( $post->ID, '_win95_paper_venue', true );
	$pages    = get_post_meta( $post->ID, '_win95_paper_pages', true );
	$spine    = get_post_meta( $post->ID, '_win95_paper_spine_title', true );
	$url      = get_post_meta( $post->ID, '_win95_paper_url', true );
	$color    = get_post_meta( $post->ID, '_win95_paper_color', true );
	?>
	<p>
		<label for="win95_paper_authors"><strong><?php _e( 'Authors', 'win95' ); ?></strong></label><br>
		<input type="text" id="win95_paper_authors" name="win95_paper_authors" value="<?php echo esc_attr( $authors ); ?>" style="width:100%">
	</p>
	<p>
		<label for="win95_paper_spine_title"><strong><?php _e( 'Spine Title (optional)', 'win95' ); ?></strong></label><br>
		<input type="text" id="win95_paper_spine_title" name="win95_paper_spine_title" value="<?php echo esc_attr( $spine ); ?>" style="width:100%">
		<span class="description"><?php _e( 'Short title for the spine. Leave blank to use the full title.', 'win95' ); ?></span>
	</p>
	<p>
		<label for="win95_paper_year"><strong><?php _e( 'Year Read', 'win95' ); ?></strong></label><br>
		<input type="number" id="win95_paper_year" name="win95_paper_year" value="<?php echo esc_attr( $year ); ?>" min="1900" max="2099" style="width:100px">
	</p>
	<p>
		<label for="win95_paper_venue"><strong><?php _e( 'Venue / Journal', 'win95' ); ?></strong></label><br>
		<input type="text" id="win95_paper_venue" name="win95_paper_venue" value="<?php echo esc_attr( $venue ); ?>" style="width:100%">
	</p>
	<p>
		<label for="win95_paper_pages"><strong><?php _e( 'Page Count', 'win95' ); ?></strong></label><br>
		<input type="number" id="win95_paper_pages" name="win95_paper_pages" value="<?php echo esc_attr( $pages ); ?>" min="1" max="9999" style="width:100px">
	</p>
	<p>
		<label for="win95_paper_url"><strong><?php _e( 'Link URL (optional)', 'win95' ); ?></strong></label><br>
		<input type="url" id="win95_paper_url" name="win95_paper_url" value="<?php echo esc_attr( $url ); ?>" style="width:100%" placeholder="https://arxiv.org/...">
	</p>
	<p>
		<label for="win95_paper_color"><strong><?php _e( 'Spine Color', 'win95' ); ?></strong></label><br>
		<input type="color" id="win95_paper_color" name="win95_paper_color" value="<?php echo esc_attr( $color ?: '#800000' ); ?>">
	</p>
	<?php
}

/**
 * Save reading meta.
 */
function win95_save_reading_meta( $post_id ) {
	// Books
	if ( isset( $_POST['win95_book_meta_nonce'] ) && wp_verify_nonce( $_POST['win95_book_meta_nonce'], 'win95_book_meta' ) ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		$fields = array( 'win95_book_author', 'win95_book_year', 'win95_book_rating', 'win95_book_pages', 'win95_book_spine_title', 'win95_book_series', 'win95_book_color', 'win95_book_url' );
		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta( $post_id, '_' . $field, sanitize_text_field( $_POST[ $field ] ) );
			}
		}
	}

	// Papers
	if ( isset( $_POST['win95_paper_meta_nonce'] ) && wp_verify_nonce( $_POST['win95_paper_meta_nonce'], 'win95_paper_meta' ) ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		$fields = array( 'win95_paper_authors', 'win95_paper_year', 'win95_paper_venue', 'win95_paper_pages', 'win95_paper_spine_title', 'win95_paper_url', 'win95_paper_color' );
		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta( $post_id, '_' . $field, sanitize_text_field( $_POST[ $field ] ) );
			}
		}
	}
}
add_action( 'save_post', 'win95_save_reading_meta' );

/**
 * Get reading items grouped by year.
 */
function win95_get_reading_by_year( $post_type = 'win95_book' ) {
	$meta_key = ( $post_type === 'win95_paper' ) ? '_win95_paper_year' : '_win95_book_year';

	$items = get_posts( array(
		'post_type'      => $post_type,
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'meta_key'       => $meta_key,
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
	) );

	$by_year = array();
	foreach ( $items as $item ) {
		$year = get_post_meta( $item->ID, $meta_key, true );
		if ( ! $year ) $year = __( 'Unknown', 'win95' );
		$by_year[ $year ][] = $item;
	}

	krsort( $by_year );
	return $by_year;
}

/**
 * Customize excerpt length.
 */
function win95_excerpt_length( $length ) {
	return 30;
}
add_filter( 'excerpt_length', 'win95_excerpt_length' );

/**
 * Customize excerpt "more" string.
 */
function win95_excerpt_more( $more ) {
	return '...';
}
add_filter( 'excerpt_more', 'win95_excerpt_more' );

/**
 * Add body classes.
 */
function win95_body_classes( $classes ) {
	$classes[] = 'win95-desktop';

	if ( is_singular() ) {
		$classes[] = 'win95-single-window';
	}

	return $classes;
}
add_filter( 'body_class', 'win95_body_classes' );

/**
 * Output the SVG icon sprite in the footer so symbols are available.
 */
function win95_icon_sprite() {
	$sprite = get_template_directory() . '/assets/icons.svg';
	if ( file_exists( $sprite ) ) {
		echo '<div style="display:none">';
		include $sprite;
		echo '</div>';
	}
}
add_action( 'wp_footer', 'win95_icon_sprite', 5 );

/**
 * Return an icon element -raster PNG primary with SVG fallback.
 */
function win95_icon( $name, $size = 16 ) {
	$png_url = get_template_directory_uri() . '/assets/icons/' . $name . '.png';
	$png_path = get_template_directory() . '/assets/icons/' . $name . '.png';

	if ( file_exists( $png_path ) ) {
		// Raster icon with SVG fallback via onerror
		return sprintf(
			'<img class="win95-icon" src="%1$s" width="%2$d" height="%2$d" alt="" aria-hidden="true" ' .
			'onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'inline\'" draggable="false">' .
			'<svg class="win95-icon" width="%2$d" height="%2$d" aria-hidden="true" style="display:none"><use href="#icon-%3$s"/></svg>',
			esc_url( $png_url ),
			intval( $size ),
			esc_attr( $name )
		);
	}

	// SVG-only fallback
	return sprintf(
		'<svg class="win95-icon" width="%2$d" height="%2$d" aria-hidden="true"><use href="#icon-%1$s"/></svg>',
		esc_attr( $name ),
		intval( $size )
	);
}
