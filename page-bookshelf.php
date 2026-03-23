<?php
/**
 * Template Name: Bookshelf
 *
 * Reading tracker page -shows books and papers on shelves, grouped by year.
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
					<!-- The shelf -->
					<div class="bookshelf-shelf">
						<div class="bookshelf-shelf__books">
							<?php foreach ( $items as $item ) :
								if ( $active_tab === 'papers' ) {
									$item_author = get_post_meta( $item->ID, '_win95_paper_authors', true );
									$item_venue  = get_post_meta( $item->ID, '_win95_paper_venue', true );
									$item_url    = get_post_meta( $item->ID, '_win95_paper_url', true );
									$spine_color = get_post_meta( $item->ID, '_win95_paper_color', true ) ?: '#800000';
									$item_rating = '';
								} else {
									$item_author = get_post_meta( $item->ID, '_win95_book_author', true );
									$item_venue  = '';
									$item_url    = get_post_meta( $item->ID, '_win95_book_url', true );
									$spine_color = get_post_meta( $item->ID, '_win95_book_color', true ) ?: '#000080';
									$item_rating = get_post_meta( $item->ID, '_win95_book_rating', true );
								}
								$has_cover   = has_post_thumbnail( $item->ID );
								$cover_url   = $has_cover ? get_the_post_thumbnail_url( $item->ID, 'medium' ) : '';
								$notes       = apply_filters( 'the_content', $item->post_content );
							?>
								<button class="bookshelf-book"
									style="--spine-color: <?php echo esc_attr( $spine_color ); ?>"
									data-title="<?php echo esc_attr( $item->post_title ); ?>"
									data-author="<?php echo esc_attr( $item_author ); ?>"
									data-year="<?php echo esc_attr( $year ); ?>"
									data-rating="<?php echo esc_attr( $item_rating ); ?>"
									data-venue="<?php echo esc_attr( $item_venue ); ?>"
									data-url="<?php echo esc_attr( $item_url ); ?>"
									data-cover="<?php echo esc_attr( $cover_url ); ?>"
									data-notes="<?php echo esc_attr( wp_strip_all_tags( $item->post_content ) ); ?>"
									data-type="<?php echo esc_attr( $active_tab === 'papers' ? 'paper' : 'book' ); ?>"
									onclick="win95OpenBookProperties(this)"
									title="<?php echo esc_attr( $item->post_title ); ?>">
									<span class="bookshelf-book__spine">
										<span class="bookshelf-book__spine-title"><?php echo esc_html( wp_trim_words( $item->post_title, 6, '...' ) ); ?></span>
									</span>
									<?php if ( $has_cover ) : ?>
										<span class="bookshelf-book__cover" style="background-image: url(<?php echo esc_url( $cover_url ); ?>)"></span>
									<?php endif; ?>
								</button>
							<?php endforeach; ?>
						</div>
						<!-- Shelf plank -->
						<div class="bookshelf-shelf__plank"></div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<!-- Properties Dialog (hidden, cloned by JS) -->
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
function win95OpenBookProperties(el) {
	var dialog = document.getElementById('bookshelf-properties');
	var title = el.getAttribute('data-title') || '';
	var author = el.getAttribute('data-author') || '';
	var year = el.getAttribute('data-year') || '';
	var rating = el.getAttribute('data-rating') || '';
	var venue = el.getAttribute('data-venue') || '';
	var url = el.getAttribute('data-url') || '';
	var cover = el.getAttribute('data-cover') || '';
	var notes = el.getAttribute('data-notes') || '';
	var type = el.getAttribute('data-type') || 'book';

	document.getElementById('props-title').textContent = title + ' - Properties';
	document.getElementById('props-item-title').textContent = title;
	document.getElementById('props-author').textContent = author;
	document.getElementById('props-year').textContent = year;

	// Author label
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
