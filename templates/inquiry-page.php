<?php
/**
 * Inquiry page: the visitor's inquiry list and the inquiry form.
 * Anything written in the page editor shows above the list.
 *
 * @package Gami_Surgical
 */

get_header();
?>
<main id="primary" class="site-main site-main--inquiry">
	<div class="container">
		<?php gami_breadcrumbs(); ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<header class="inquiry-head">
				<h1 class="inquiry-head__title"><?php the_title(); ?></h1>
				<?php if ( trim( get_the_content() ) ) : ?>
					<div class="inquiry-head__content entry-content"><?php the_content(); ?></div>
				<?php endif; ?>
			</header>
			<?php
		endwhile;
		gami_inquiry_app();
		?>
	</div>
</main>
<?php
get_footer();
