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
							if ( $active_tab === 'papers' ) {
								$item_author = get_post_meta( $item->ID, '_win95_paper_authors', true );
								$item_venue  = get_post_meta( $item->ID, '_win95_paper_venue', true );
								$item_url    = get_post_meta( $item->ID, '_win95_paper_url', true );
								$spine_color = get_post_meta( $item->ID, '_win95_paper_color', true ) ?: '#800000';
								$item_rating = '';
								$item_pages  = get_post_meta( $item->ID, '_win95_paper_pages', true );
								$item_series = '';
							} else {
								$item_author = get_post_meta( $item->ID, '_win95_book_author', true );
								$item_venue  = '';
								$item_url    = get_post_meta( $item->ID, '_win95_book_url', true );
								$spine_color = get_post_meta( $item->ID, '_win95_book_color', true ) ?: '#000080';
								$item_rating = get_post_meta( $item->ID, '_win95_book_rating', true );
								$item_pages  = get_post_meta( $item->ID, '_win95_book_pages', true );
								$item_series = get_post_meta( $item->ID, '_win95_book_series', true );
							}
							$has_cover   = has_post_thumbnail( $item->ID );
							$cover_url   = $has_cover ? get_the_post_thumbnail_url( $item->ID, 'medium' ) : '';

							// Page count for thickness
							$pages = intval( $item_pages );
							if ( $pages <= 0 ) $pages = 250;
							$pages = max( 30, min( 1500, $pages ) );

							// Thickness: correlates with page count + jitter
							$thickness = round( 18 + ( ( $pages - 30 ) / 1470 ) * 34 );
							$thick_jitter = ( ( crc32( 't' . $item->ID ) % 8 ) - 4 );
							$thickness = max( 16, min( 54, $thickness + $thick_jitter ) );

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
								$height_variation = ( ( $h_hash % 40 ) - 20 );
								$base_height = 85 + ( ( $pages - 30 ) / 1470 ) * 35;
								$height = round( max( 70, min( 140, $base_height + $height_variation ) ) );

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
							$spine_title_meta = ( $active_tab === 'papers' )
								? get_post_meta( $item->ID, '_win95_paper_spine_title', true )
								: get_post_meta( $item->ID, '_win95_book_spine_title', true );
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
						?>
							<button class="bookshelf-book"
								style="--spine-color: <?php echo esc_attr( $spine_color ); ?>; --book-height: <?php echo $height; ?>px; --book-thickness: <?php echo $thickness; ?>px; --spine-font: <?php echo esc_attr( $book_font ); ?>; --spine-font-size: <?php echo $font_size; ?>px; --spine-text-transform: <?php echo $text_transform; ?>; --spine-font-weight: <?php echo $font_weight; ?>; --spine-text-color: <?php echo $text_color; ?>"
								data-title="<?php echo esc_attr( $item->post_title ); ?>"
								data-author="<?php echo esc_attr( $item_author ); ?>"
								data-year="<?php echo esc_attr( $year ); ?>"
								data-rating="<?php echo esc_attr( $item_rating ); ?>"
								data-pages="<?php echo esc_attr( $item_pages ); ?>"
								data-venue="<?php echo esc_attr( $item_venue ); ?>"
								data-url="<?php echo esc_attr( $item_url ); ?>"
								data-cover="<?php echo esc_attr( $cover_url ); ?>"
								data-notes="<?php echo esc_attr( wp_strip_all_tags( $item->post_content ) ); ?>"
								data-type="<?php echo esc_attr( $active_tab === 'papers' ? 'paper' : 'book' ); ?>"
								onclick="win95OpenBookProperties(this)"
								title="<?php echo esc_attr( $item->post_title ); ?>">
								<span class="bookshelf-book__spine">
									<span class="bookshelf-book__spine-title"><?php echo esc_html( $spine_display ); ?></span>
								</span>
							</button>
						<?php endforeach; ?>
					</div>
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
	function buildShelves() {
		document.querySelectorAll('[data-bookshelf]').forEach(function(container) {
			var books = Array.from(container.querySelectorAll('.bookshelf-book'));
			if (!books.length) return;

			// Clear existing shelf wrappers (for resize rebuilds)
			var existingShelves = container.querySelectorAll('.bookshelf-shelf');
			existingShelves.forEach(function(s) {
				var booksInside = s.querySelectorAll('.bookshelf-book');
				booksInside.forEach(function(b) { container.appendChild(b); });
				s.remove();
			});

			var containerWidth = container.offsetWidth - 16; // padding
			var currentRowWidth = 0;
			var currentShelf = null;
			var currentBooksDiv = null;

			function newShelf() {
				currentShelf = document.createElement('div');
				currentShelf.className = 'bookshelf-shelf';
				currentBooksDiv = document.createElement('div');
				currentBooksDiv.className = 'bookshelf-shelf__books';
				var plank = document.createElement('div');
				plank.className = 'bookshelf-shelf__plank';
				currentShelf.appendChild(currentBooksDiv);
				currentShelf.appendChild(plank);
				container.appendChild(currentShelf);
				currentRowWidth = 0;
			}

			newShelf();

			books.forEach(function(book) {
				var bookWidth = book.offsetWidth || parseInt(getComputedStyle(book).getPropertyValue('--book-thickness')) || 32;
				bookWidth += 3; // gap

				if (currentRowWidth + bookWidth > containerWidth && currentRowWidth > 0) {
					newShelf();
				}

				currentBooksDiv.appendChild(book);
				currentRowWidth += bookWidth;
			});
		});
	}

	// Build on load
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', buildShelves);
	} else {
		buildShelves();
	}

	// Rebuild on resize (debounced)
	var resizeTimer;
	window.addEventListener('resize', function() {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(buildShelves, 200);
	});
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
