<?php
/**
 * Search results template.
 *
 * @package Win95
 */

get_header();
?>

<div style="margin-bottom: 12px;">
	<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<input type="text" class="win95-text-input search-field" style="flex:1" placeholder="<?php esc_attr_e( 'Search...', 'win95' ); ?>" value="<?php echo get_search_query(); ?>" name="s">
		<button type="submit" class="win95-btn"><?php _e( 'Find Now', 'win95' ); ?></button>
	</form>
</div>

<?php if ( have_posts() ) : ?>
	<p style="font-size: 11px; color: #808080; margin-bottom: 8px;">
		<?php printf( __( 'Found %d results for "%s"', 'win95' ), $wp_query->found_posts, get_search_query() ); ?>
	</p>

	<div class="post-list">
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-item' ); ?>>
				<h2 class="post-item__title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
				<div class="post-item__meta">
					<?php echo get_the_date(); ?> | <?php the_author(); ?>
				</div>
				<div class="post-item__excerpt">
					<?php the_excerpt(); ?>
				</div>
			</article>
		<?php endwhile; ?>
	</div>

	<div class="pagination">
		<?php the_posts_pagination(); ?>
	</div>
<?php else : ?>
	<p><?php _e( 'No results found. Try a different search.', 'win95' ); ?></p>
<?php endif; ?>

<?php
get_footer();
