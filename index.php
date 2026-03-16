<?php
/**
 * The main template file.
 *
 * @package Win95
 */

get_header();
?>

<?php if ( have_posts() ) : ?>

	<div class="post-list">
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-item' ); ?>>
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="post-item__thumbnail">
						<a href="<?php the_permalink(); ?>">
							<?php the_post_thumbnail( 'thumbnail' ); ?>
						</a>
					</div>
				<?php endif; ?>

				<h2 class="post-item__title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>

				<div class="post-item__meta">
					<?php echo get_the_date(); ?> | <?php the_author(); ?>
					<?php
					$categories = get_the_category();
					$cats = array_filter( $categories, function( $cat ) { return $cat->slug !== 'uncategorized'; } );
					if ( ! empty( $cats ) ) :
					?>
						| <?php echo implode( ', ', array_map( function( $cat ) {
							return '<a href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . esc_html( $cat->name ) . '</a>';
						}, $cats ) ); ?>
					<?php endif; ?>
					| <?php comments_number( '0 comments', '1 comment', '% comments' ); ?>
				</div>

				<div class="post-item__excerpt">
					<?php the_excerpt(); ?>
				</div>

				<div style="clear:both"></div>
			</article>
		<?php endwhile; ?>
	</div>

	<div class="pagination">
		<?php
		the_posts_pagination( array(
			'mid_size'  => 2,
			'prev_text' => '&laquo; Back',
			'next_text' => 'Next &raquo;',
		) );
		?>
	</div>

<?php else : ?>

	<div class="no-results">
		<p><?php _e( 'No posts found. Check back later!', 'win95' ); ?></p>
	</div>

<?php endif; ?>

<?php
get_footer();
