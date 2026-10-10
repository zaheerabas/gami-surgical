<?php
/**
 * Post in blog listings.
 *
 * Variants (pass array( 'variant' => … )):
 *   card    – image on top (default)
 *   feature – full-width card with image beside the text
 *   lead    – large story in the Latest section
 *   compact – small image beside the title
 *   row     – image beside title and excerpt
 *   index   – dated line in "All articles", no image
 *
 * @package Gami_Surgical
 */

$gami_variant = isset( $args['variant'] ) ? $args['variant'] : 'card';
$gami_cat     = gami_primary_category();
$gami_large   = in_array( $gami_variant, array( 'feature', 'lead' ), true );
$gami_size    = $gami_large ? 'gami-wide' : 'gami-card-wide';
$gami_heading = 'h3';
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card post-card--' . $gami_variant ); ?>>
	<?php if ( 'index' === $gami_variant ) : ?>
		<time class="post-card__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
			<span class="post-card__day"><?php echo esc_html( get_the_date( 'd' ) ); ?></span>
			<span class="post-card__month"><?php echo esc_html( get_the_date( 'M Y' ) ); ?></span>
		</time>
	<?php else : ?>
		<a class="post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( $gami_size, array( 'loading' => $gami_large ? 'eager' : 'lazy', 'alt' => '' ) ); ?>
			<?php else : ?>
				<span class="post-card__placeholder"><span><?php echo esc_html( $gami_cat ? $gami_cat->name : get_bloginfo( 'name' ) ); ?></span></span>
			<?php endif; ?>
		</a>
	<?php endif; ?>

	<div class="post-card__body">
		<?php if ( $gami_cat ) : ?>
			<a class="post-card__cat" href="<?php echo esc_url( get_category_link( $gami_cat ) ); ?>"><?php echo esc_html( $gami_cat->name ); ?></a>
		<?php endif; ?>
		<?php the_title( '<' . $gami_heading . ' class="post-card__title"><a href="' . esc_url( get_permalink() ) . '">', '</a></' . $gami_heading . '>' ); ?>
		<?php if ( 'compact' !== $gami_variant ) : ?>
			<p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), $gami_large ? 40 : 24 ) ); ?></p>
		<?php endif; ?>
		<p class="post-card__meta">
			<?php if ( 'index' !== $gami_variant ) : ?>
				<?php gami_posted_on(); ?>
			<?php endif; ?>
			<span><?php echo esc_html( gami_reading_time_label() ); ?></span>
		</p>
		<?php if ( 'lead' === $gami_variant || 'index' === $gami_variant ) : ?>
			<a class="post-card__more" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1"><?php esc_html_e( 'Read article', 'gami-surgical' ); ?> <span>→</span></a>
		<?php endif; ?>
	</div>
</article>
