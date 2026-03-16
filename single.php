<?php
/**
 * Single post template.
 *
 * @package Win95
 */

get_header();

if ( have_posts() ) : while ( have_posts() ) : the_post();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="post-meta">
		<span><?php echo get_the_date(); ?></span>
		<span><?php the_author(); ?></span>
		<?php
		$categories = get_the_category();
		$cats = array_filter( $categories, function( $cat ) { return $cat->slug !== 'uncategorized'; } );
		if ( ! empty( $cats ) ) :
		?>
			<span><?php echo implode( ', ', array_map( function( $cat ) {
				return '<a href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . esc_html( $cat->name ) . '</a>';
			}, $cats ) ); ?></span>
		<?php endif; ?>
		<?php if ( has_tag() ) : ?>
			<span><?php the_tags( '', ', ' ); ?></span>
		<?php endif; ?>
	</div>

	<?php if ( has_post_thumbnail() ) : ?>
		<div style="margin-bottom: 12px;">
			<?php the_post_thumbnail( 'large' ); ?>
		</div>
	<?php endif; ?>

	<div class="single-content">
		<?php the_content(); ?>
	</div>

	<?php
	wp_link_pages( array(
		'before' => '<div class="pagination">',
		'after'  => '</div>',
	) );
	?>
</article>

<hr class="win95-separator">

<nav class="post-navigation">
	<div>
		<?php previous_post_link( '&laquo; %link' ); ?>
	</div>
	<div>
		<?php next_post_link( '%link &raquo;' ); ?>
	</div>
</nav>

<?php
if ( comments_open() || get_comments_number() ) :
	comments_template();
endif;

endwhile; endif;

get_footer();
