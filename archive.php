<?php
/**
 * Archive template.
 *
 * @package Win95
 */

get_header();
?>

<div class="archive-header" style="margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #c0c0c0;">
	<h1 style="font-size: 14px; font-weight: bold; margin: 0;">
		<?php the_archive_title(); ?>
	</h1>
	<?php the_archive_description( '<div style="font-size: 11px; color: #808080; margin-top: 4px;">', '</div>' ); ?>
</div>

<?php if ( have_posts() ) : ?>
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
		<?php the_posts_pagination( array(
			'mid_size'  => 2,
			'prev_text' => '&laquo; Back',
			'next_text' => 'Next &raquo;',
		) ); ?>
	</div>
<?php else : ?>
	<p><?php _e( 'No posts found.', 'win95' ); ?></p>
<?php endif; ?>

<?php
get_footer();
