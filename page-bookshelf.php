<?php
/**
 * Template Name: Bookshelf
 *
 * Reading tracker page: books and papers on shelves, grouped by year.
 *
 * @package Win95
 */

get_header();

// Determine active tab
$active_tab = isset( $_GET['tab'] ) && $_GET['tab'] === 'papers' ? 'papers' : 'books';
$post_type  = $active_tab === 'papers' ? 'win95_paper' : 'win95_book';
$by_year    = win95_get_reading_by_year( $post_type );

// Count totals
$total_books  = wp_count_posts( 'win95_book' )->publish;
$total_papers = wp_count_posts( 'win95_paper' )->publish;
?>

<div class="bookshelf-page">
	<!-- Tabs -->
	<div class="win95-tabs bookshelf-tabs">
		<a href="<?php echo esc_url( add_query_arg( 'tab', 'books' ) ); ?>"
		   class="win95-tab <?php echo $active_tab === 'books' ? 'is-active' : ''; ?>">
			<?php echo win95_icon( 'document', 16 ); ?>
			<?php printf( __( 'Books (%d)', 'win95' ), $total_books ); ?>
		</a>
		<a href="<?php echo esc_url( add_query_arg( 'tab', 'papers' ) ); ?>"
		   class="win95-tab <?php echo $active_tab === 'papers' ? 'is-active' : ''; ?>">
			<?php echo win95_icon( 'document', 16 ); ?>
			<?php printf( __( 'Papers (%d)', 'win95' ), $total_papers ); ?>
		</a>
	</div>

	<?php if ( empty( $by_year ) ) : ?>
		<div class="bookshelf-empty">
			<p><?php _e( 'No items yet. Add some from the WordPress admin!', 'win95' ); ?></p>
		</div>
	<?php else : ?>
		<?php foreach ( $by_year as $year => $items ) : ?>
			<!-- Year folder -->
			<div class="bookshelf-year" id="year-<?php echo esc_attr( $year ); ?>">
				<button class="bookshelf-year__header" onclick="this.parentElement.classList.toggle('is-collapsed')" aria-expanded="true">
					<?php echo win95_icon( 'folder-32', 16 ); ?>
					<span class="bookshelf-year__label"><?php echo esc_html( $year ); ?></span>
					<span class="bookshelf-year__count">(<?php echo count( $items ); ?>)</span>
					<span class="bookshelf-year__arrow">&#9660;</span>
				</button>

				<div class="bookshelf-year__content">
				<?php if ( $active_tab === 'papers' ) : ?>
					<!-- Cork bulletin board for papers -->
					<div class="corkboard">
						<div class="corkboard__surface">
							<?php
							foreach ( $items as $idx => $item ) :
								$item_author = get_post_meta( $item->ID, '_win95_paper_authors', true );
								$item_venue  = get_post_meta( $item->ID, '_win95_paper_venue', true );
								$item_url    = get_post_meta( $item->ID, '_win95_paper_url', true );
								$item_pages  = get_post_meta( $item->ID, '_win95_paper_pages', true );
								$cover_url   = has_post_thumbnail( $item->ID ) ? get_the_post_thumbnail_url( $item->ID, 'medium' ) : '';

								// Deterministic random rotation between -3 and 3 degrees
								$rotation = ( ( crc32( 'r' . $item->ID ) % 700 ) - 350 ) / 100;
								// Pin color from spine color
								$pin_color = get_post_meta( $item->ID, '_win95_paper_color', true ) ?: '#800000';
								// Deterministic pin horizontal offset (40-60% so it's roughly centered)
								$pin_offset = 40 + ( abs( crc32( 'o' . $item->ID ) ) % 21 );
							?>
								<button class="corkboard__card"
									style="--card-rotation: <?php echo $rotation; ?>deg; --pin-color: <?php echo esc_attr( $pin_color ); ?>; --pin-offset: <?php echo $pin_offset; ?>%"
									data-title="<?php echo esc_attr( $item->post_title ); ?>"
									data-author="<?php echo esc_attr( $item_author ); ?>"
									data-year="<?php echo esc_attr( $year ); ?>"
									data-rating=""
									data-pages="<?php echo esc_attr( $item_pages ); ?>"
									data-venue="<?php echo esc_attr( $item_venue ); ?>"
									data-url="<?php echo esc_attr( $item_url ); ?>"
									data-cover="<?php echo esc_attr( $cover_url ); ?>"
									data-notes="<?php echo esc_attr( wp_strip_all_tags( $item->post_content ) ); ?>"
									data-type="paper"
									onclick="win95OpenBookProperties(this)"
									title="<?php echo esc_attr( $item->post_title ); ?>">
									<span class="corkboard__pin"></span>
									<span class="corkboard__card-title"><?php echo esc_html( $item->post_title ); ?></span>
									<?php if ( $item_author ) : ?>
										<span class="corkboard__card-authors"><?php echo esc_html( $item_author ); ?></span>
									<?php endif; ?>
									<?php if ( $item_venue ) : ?>
										<span class="corkboard__card-venue"><?php echo esc_html( $item_venue ); ?></span>
									<?php endif; ?>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
				<?php else : ?>
					<!-- Bookshelf for books -->
					<div class="bookshelf-shelves" data-bookshelf>
						<?php
						// Pre-compute series styling: books in the same series
						// share a consistent height and font
						$series_styles = array();
						$font_families = array(
							"Georgia, 'Times New Roman', serif",
							"'Palatino Linotype', Palatino, serif",
							"Arial, Helvetica, sans-serif",
							"'Book Antiqua', Palatino, serif",
							"Garamond, 'Times New Roman', serif",
							"Tahoma, Geneva, sans-serif",
							"'Trebuchet MS', sans-serif",
							"Verdana, Geneva, sans-serif",
						);
						$weight_opts = array( 'normal', 'bold', 'normal', 'bold', 'normal' );

						foreach ( $items as $item ) :
							$item_author = get_post_meta( $item->ID, '_win95_book_author', true );
							$item_url    = get_post_meta( $item->ID, '_win95_book_url', true );
							$spine_color = get_post_meta( $item->ID, '_win95_book_color', true ) ?: '#000080';
							$item_rating = get_post_meta( $item->ID, '_win95_book_rating', true );
							$item_pages  = get_post_meta( $item->ID, '_win95_book_pages', true );
							$item_series = get_post_meta( $item->ID, '_win95_book_series', true );
							$has_cover   = has_post_thumbnail( $item->ID );
							$cover_url   = $has_cover ? get_the_post_thumbnail_url( $item->ID, 'medium' ) : '';

							// Page count for thickness
							$pages = intval( $item_pages );
							if ( $pages <= 0 ) $pages = 250;
							$pages = max( 30, min( 1500, $pages ) );

							// Thickness: correlates with page count + jitter
							$thickness = round( 12 + ( ( $pages - 30 ) / 1470 ) * 52 );
							$thick_jitter = ( ( crc32( 't' . $item->ID ) % 6 ) - 3 );
							$thickness = max( 10, min( 64, $thickness + $thick_jitter ) );

							// Series: use series name as seed for height/font so they match
							$style_seed = ! empty( $item_series ) ? $item_series : 'id-' . $item->ID;

							if ( ! empty( $item_series ) && isset( $series_styles[ $item_series ] ) ) {
								// Reuse series style
								$height         = $series_styles[ $item_series ]['height'];
								$book_font      = $series_styles[ $item_series ]['font'];
								$text_transform = $series_styles[ $item_series ]['transform'];
								$font_weight    = $series_styles[ $item_series ]['weight'];
							} else {
								// Compute height from seed
								$h_hash = crc32( 'h' . $style_seed );
								$height_variation = ( ( $h_hash % 20 ) - 10 );
								$base_height = 95 + ( ( $pages - 30 ) / 1470 ) * 55;
								$height = round( max( 90, min( 165, $base_height + $height_variation ) ) );

								// Font from seed
								$font_idx = abs( crc32( 'f' . $style_seed ) ) % count( $font_families );
								$book_font = $font_families[ $font_idx ];
								$text_transform = ( abs( crc32( 'u' . $style_seed ) ) % 4 === 0 ) ? 'uppercase' : 'none';
								$font_weight = $weight_opts[ abs( crc32( 'w' . $style_seed ) ) % count( $weight_opts ) ];

								if ( ! empty( $item_series ) ) {
									$series_styles[ $item_series ] = array(
										'height'    => $height,
										'font'      => $book_font,
										'transform' => $text_transform,
										'weight'    => $font_weight,
									);
								}
							}

							// Spine display title
							$spine_title_meta = get_post_meta( $item->ID, '_win95_book_spine_title', true );
							$spine_display = ! empty( $spine_title_meta ) ? $spine_title_meta : $item->post_title;

							// Font size scales with thickness
							$font_size = max( 8, min( 12, round( $thickness * 0.32 ) ) );

							// Text color from spine luminance
							$hex = ltrim( $spine_color, '#' );
							$r = hexdec( substr( $hex, 0, 2 ) );
							$g = hexdec( substr( $hex, 2, 2 ) );
							$b = hexdec( substr( $hex, 4, 2 ) );
							$luminance = ( 0.299 * $r + 0.587 * $g + 0.114 * $b ) / 255;
							$text_color = $luminance > 0.55 ? '#000000' : '#ffffff';
							$text_shadow_color = $luminance > 0.55 ? '255,255,255' : '0,0,0';

							// Complementary color via HSL hue rotation with forced saturation
							$rf = $r / 255; $gf = $g / 255; $bf = $b / 255;
							$cmax = max( $rf, $gf, $bf );
							$cmin = min( $rf, $gf, $bf );
							$delta = $cmax - $cmin;
							if ( $delta == 0 ) {
								$h = 0;
							} elseif ( $cmax == $rf ) {
								$h = fmod( ( $gf - $bf ) / $delta, 6 );
							} elseif ( $cmax == $gf ) {
								$h = ( $bf - $rf ) / $delta + 2;
							} else {
								$h = ( $rf - $gf ) / $delta + 4;
							}
							$h = fmod( $h / 6 + 0.5, 1.0 ); // rotate 180 degrees
							// Convert back to RGB with forced saturation=0.85, lightness=0.6
							$cs = 0.85; $cl = 0.6;
							$c2 = ( 1 - abs( 2 * $cl - 1 ) ) * $cs;
							$x2 = $c2 * ( 1 - abs( fmod( $h * 6, 2 ) - 1 ) );
							$m2 = $cl - $c2 / 2;
							$h6 = $h * 6;
							if ( $h6 < 1 )      { $cr = $c2; $cg = $x2; $cb = 0; }
							elseif ( $h6 < 2 )  { $cr = $x2; $cg = $c2; $cb = 0; }
							elseif ( $h6 < 3 )  { $cr = 0; $cg = $c2; $cb = $x2; }
							elseif ( $h6 < 4 )  { $cr = 0; $cg = $x2; $cb = $c2; }
							elseif ( $h6 < 5 )  { $cr = $x2; $cg = 0; $cb = $c2; }
							else                 { $cr = $c2; $cg = 0; $cb = $x2; }
							$comp_rgb = round( ( $cr + $m2 ) * 255 ) . ',' . round( ( $cg + $m2 ) * 255 ) . ',' . round( ( $cb + $m2 ) * 255 );

							// Dither size scales with thickness
							$dither_size = max( 4, round( $thickness * 0.22 ) );
							// Dither extends further up on taller books
							$dither_extent = min( 65, round( 45 + ( $height - 90 ) * 0.25 ) );
						?>
							<button class="bookshelf-book"
								style="--spine-color: <?php echo esc_attr( $spine_color ); ?>; --spine-comp-rgb: <?php echo esc_attr( $comp_rgb ); ?>; --book-height: <?php echo $height; ?>px; --book-thickness: <?php echo $thickness; ?>px; --spine-font: <?php echo esc_attr( $book_font ); ?>; --spine-font-size: <?php echo $font_size; ?>px; --spine-text-transform: <?php echo $text_transform; ?>; --spine-font-weight: <?php echo $font_weight; ?>; --spine-text-color: <?php echo $text_color; ?>; --spine-text-shadow-rgb: <?php echo $text_shadow_color; ?>; --dither-size: <?php echo $dither_size; ?>px; --dither-extent: <?php echo $dither_extent; ?>%"
								data-title="<?php echo esc_attr( $item->post_title ); ?>"
								data-author="<?php echo esc_attr( $item_author ); ?>"
								data-year="<?php echo esc_attr( $year ); ?>"
								data-rating="<?php echo esc_attr( $item_rating ); ?>"
								data-pages="<?php echo esc_attr( $item_pages ); ?>"
								data-venue=""
								data-url="<?php echo esc_attr( $item_url ); ?>"
								data-cover="<?php echo esc_attr( $cover_url ); ?>"
								data-notes="<?php echo esc_attr( wp_strip_all_tags( $item->post_content ) ); ?>"
								data-type="book"
								onclick="win95OpenBookProperties(this)"
								title="<?php echo esc_attr( $item->post_title ); ?>">
								<span class="bookshelf-book__side-left"></span>
								<span class="bookshelf-book__spine">
									<span class="bookshelf-book__spine-title"><?php echo esc_html( $spine_display ); ?></span>
								</span>
								<span class="bookshelf-book__side-right"></span>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<!-- Properties Dialog -->
<div class="win95-window bookshelf-properties" id="bookshelf-properties" style="display:none">
	<div class="win95-title-bar">
		<?php echo win95_icon( 'document', 16 ); ?>
		<span class="win95-title-bar-text" id="props-title">Properties</span>
		<div class="win95-title-bar-controls">
			<button class="win95-btn-close" aria-label="<?php esc_attr_e( 'Close', 'win95' ); ?>" onclick="document.getElementById('bookshelf-properties').style.display='none'"></button>
		</div>
	</div>
	<div class="win95-window-body bookshelf-properties__body">
		<div class="bookshelf-properties__layout">
			<div class="bookshelf-properties__cover" id="props-cover"></div>
			<div class="bookshelf-properties__info">
				<div class="bookshelf-properties__row">
					<span class="bookshelf-properties__label"><?php _e( 'Title:', 'win95' ); ?></span>
					<span class="bookshelf-properties__value" id="props-item-title"></span>
				</div>
				<div class="bookshelf-properties__row">
					<span class="bookshelf-properties__label" id="props-author-label"><?php _e( 'Author:', 'win95' ); ?></span>
					<span class="bookshelf-properties__value" id="props-author"></span>
				</div>
				<div class="bookshelf-properties__row" id="props-venue-row" style="display:none">
					<span class="bookshelf-properties__label"><?php _e( 'Venue:', 'win95' ); ?></span>
					<span class="bookshelf-properties__value" id="props-venue"></span>
				</div>
				<div class="bookshelf-properties__row">
					<span class="bookshelf-properties__label"><?php _e( 'Year Read:', 'win95' ); ?></span>
					<span class="bookshelf-properties__value" id="props-year"></span>
				</div>
				<div class="bookshelf-properties__row" id="props-pages-row" style="display:none">
					<span class="bookshelf-properties__label"><?php _e( 'Pages:', 'win95' ); ?></span>
					<span class="bookshelf-properties__value" id="props-pages"></span>
				</div>
				<div class="bookshelf-properties__row" id="props-rating-row">
					<span class="bookshelf-properties__label"><?php _e( 'Rating:', 'win95' ); ?></span>
					<span class="bookshelf-properties__value" id="props-rating"></span>
				</div>
				<hr class="win95-separator">
				<div class="bookshelf-properties__row" id="props-notes-row" style="display:none">
					<span class="bookshelf-properties__label"><?php _e( 'Notes:', 'win95' ); ?></span>
					<span class="bookshelf-properties__value" id="props-notes"></span>
				</div>
			</div>
		</div>
		<div class="bookshelf-properties__buttons">
			<a href="#" class="win95-btn" id="props-link" target="_blank" rel="noopener noreferrer" style="display:none"><?php _e( 'Open Link', 'win95' ); ?></a>
			<button class="win95-btn win95-btn--default" onclick="document.getElementById('bookshelf-properties').style.display='none'"><?php _e( 'OK', 'win95' ); ?></button>
		</div>
	</div>
</div>

<script>
/* Build shelves: group books into rows, each row gets its own plank */
(function() {
	var shelfIdx = 0;
	function buildShelves() {
		shelfIdx = 0;
		document.querySelectorAll('[data-bookshelf]').forEach(function(container) {
			var books = Array.from(container.querySelectorAll('.bookshelf-book'));
			if (!books.length) return;

			// Move all books back to the container, remove old shelves
			books.forEach(function(b) { container.appendChild(b); });
			var existingShelves = container.querySelectorAll('.bookshelf-shelf');
			existingShelves.forEach(function(s) { s.remove(); });

			// Measure available width (account for shelf padding: 12px left + 20px right)
			var containerWidth = container.offsetWidth - 32;
			var currentRowWidth = 0;
			var currentShelf = null;
			var currentBooksDiv = null;

			function randomStars(seed) {
				/* Simple seeded PRNG so same shelf index = same stars across rebuilds */
				var s = seed;
				function rand() { s = (s * 16807 + 0) % 2147483647; return (s & 0x7fffffff) / 0x7fffffff; }
				var colors = [
					'255,150,150','150,150,255','150,255,180','255,200,140',
					'180,150,255','140,255,255','255,150,200','200,150,255',
					'255,170,170','150,255,200','255,255,150','150,220,255','255,150,255'
				];
				var layers = [];
				var count = 10 + Math.floor(rand() * 6);
				for (var i = 0; i < count; i++) {
					var size = 1.5 + rand() * 0.5;
					var x = (3 + rand() * 94).toFixed(1);
					var y = (5 + rand() * 75).toFixed(1);
					var c = colors[Math.floor(rand() * colors.length)];
					var a = (0.65 + rand() * 0.25).toFixed(2);
					layers.push('radial-gradient(' + size.toFixed(1) + 'px ' + size.toFixed(1) + 'px at ' + x + '% ' + y + '%, rgba(' + c + ',' + a + '), transparent)');
				}
				return layers.join(',\n');
			}

			function newShelf() {
				currentShelf = document.createElement('div');
				currentShelf.className = 'bookshelf-shelf';
				currentBooksDiv = document.createElement('div');
				currentBooksDiv.className = 'bookshelf-shelf__books';
				/* Unique star background per shelf */
				var base = 'linear-gradient(180deg, #0a0a10 0%, #0e0e16 40%, #0a0a10 calc(100% - 24px), #dfdfdf calc(100% - 24px), #c0c0c0 calc(100% - 10px), #a0a0a0 100%)';
				currentBooksDiv.style.background = randomStars(shelfIdx * 7919 + 1) + ',\n' + base;
				shelfIdx++;
				var plank = document.createElement('div');
				plank.className = 'bookshelf-shelf__plank';
				currentShelf.appendChild(currentBooksDiv);
				currentShelf.appendChild(plank);
				container.appendChild(currentShelf);
				currentRowWidth = 0;
			}

			newShelf();

			books.forEach(function(book) {
				// Read the CSS variable directly for accurate width
				var thickness = parseInt(book.style.getPropertyValue('--book-thickness')) || 32;

				if (currentRowWidth + thickness > containerWidth && currentRowWidth > 0) {
					newShelf();
				}

				currentBooksDiv.appendChild(book);
				currentRowWidth += thickness;
			});
		});
	}

	// Build on load (with retry to handle AJAX-spawned windows that
	// aren't fully sized yet on first render)
	function initBuild() {
		buildShelves();
		// Rebuild shortly after in case window was still being positioned
		setTimeout(buildShelves, 150);
		setTimeout(buildShelves, 500);
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initBuild);
	} else {
		initBuild();
	}

	// Rebuild on resize (debounced) - use ResizeObserver to catch
	// Win95 window resizing, not just browser window resizing
	var resizeTimer;
	function debouncedBuild() {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(buildShelves, 200);
	}
	window.addEventListener('resize', debouncedBuild);
	if (typeof ResizeObserver !== 'undefined') {
		var ro = new ResizeObserver(debouncedBuild);
		document.querySelectorAll('[data-bookshelf]').forEach(function(c) { ro.observe(c); });
	}
})();

/* Properties dialog */
function win95OpenBookProperties(el) {
	var dialog = document.getElementById('bookshelf-properties');
	var title = el.getAttribute('data-title') || '';
	var author = el.getAttribute('data-author') || '';
	var year = el.getAttribute('data-year') || '';
	var rating = el.getAttribute('data-rating') || '';
	var pages = el.getAttribute('data-pages') || '';
	var venue = el.getAttribute('data-venue') || '';
	var url = el.getAttribute('data-url') || '';
	var cover = el.getAttribute('data-cover') || '';
	var notes = el.getAttribute('data-notes') || '';
	var type = el.getAttribute('data-type') || 'book';

	document.getElementById('props-title').textContent = title + ' - Properties';
	document.getElementById('props-item-title').textContent = title;
	document.getElementById('props-author').textContent = author;
	document.getElementById('props-year').textContent = year;

	document.getElementById('props-author-label').textContent = type === 'paper' ? 'Authors:' : 'Author:';

	// Cover
	var coverEl = document.getElementById('props-cover');
	if (cover) {
		coverEl.innerHTML = '<img src="' + cover + '" alt="">';
		coverEl.style.display = '';
	} else {
		coverEl.innerHTML = '';
		coverEl.style.display = 'none';
	}

	// Pages
	var pagesRow = document.getElementById('props-pages-row');
	if (pages) {
		document.getElementById('props-pages').textContent = pages;
		pagesRow.style.display = '';
	} else {
		pagesRow.style.display = 'none';
	}

	// Rating (stars)
	var ratingRow = document.getElementById('props-rating-row');
	if (rating && type === 'book') {
		var stars = '';
		for (var i = 0; i < 5; i++) {
			stars += i < parseInt(rating) ? '\u2605' : '\u2606';
		}
		document.getElementById('props-rating').textContent = stars;
		ratingRow.style.display = '';
	} else {
		ratingRow.style.display = 'none';
	}

	// Venue (papers only)
	var venueRow = document.getElementById('props-venue-row');
	if (venue && type === 'paper') {
		document.getElementById('props-venue').textContent = venue;
		venueRow.style.display = '';
	} else {
		venueRow.style.display = 'none';
	}

	// Notes
	var notesRow = document.getElementById('props-notes-row');
	if (notes) {
		document.getElementById('props-notes').textContent = notes;
		notesRow.style.display = '';
	} else {
		notesRow.style.display = 'none';
	}

	// Link
	var linkBtn = document.getElementById('props-link');
	if (url) {
		linkBtn.href = url;
		linkBtn.style.display = '';
	} else {
		linkBtn.style.display = 'none';
	}

	dialog.style.display = 'flex';
	dialog.style.flexDirection = 'column';
}
</script>

<?php get_footer(); ?>
