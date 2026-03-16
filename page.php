<?php
/**
 * Page template.
 *
 * @package Win95
 */

get_header();

if ( have_posts() ) : while ( have_posts() ) : the_post();
?>

<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="single-content">
		<?php the_content(); ?>
	</div>
</article>

<?php
if ( comments_open() || get_comments_number() ) :
	comments_template();
endif;

endwhile; endif;

get_footer();
