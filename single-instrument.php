<?php
/**
 * Single instrument.
 *
 * @package Gami_Surgical
 */

get_header();

if ( ! gami_elementor_location( 'single' ) ) :
	while ( have_posts() ) :
		the_post();
		$gami_specs = gami_instrument_specs();
		$gami_terms = get_the_terms( get_the_ID(), 'instrument_category' );
		?>
		<main id="primary" class="site-main container instrument">
			<?php gami_breadcrumbs(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'instrument__grid' ); ?>>
				<div class="instrument__media">
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="instrument__photo"><?php the_post_thumbnail( 'large' ); ?></figure>
					<?php else : ?>
						<div class="instrument__photo instrument__photo--empty"><?php echo gami_placeholder_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<?php endif; ?>
				</div>

				<div class="instrument__summary">
					<?php if ( isset( $gami_specs['catalogue_no'] ) ) : ?>
						<p class="instrument__catno"><?php echo esc_html( $gami_specs['catalogue_no']['value'] ); ?></p>
					<?php endif; ?>
					<?php the_title( '<h1 class="instrument__title">', '</h1>' ); ?>
					<?php if ( has_excerpt() ) : ?>
						<p class="instrument__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>

					<?php if ( $gami_specs ) : ?>
						<table class="spec-table">
							<caption class="screen-reader-text"><?php esc_html_e( 'Specifications', 'gami-surgical' ); ?></caption>
							<tbody>
								<?php foreach ( $gami_specs as $gami_spec ) : ?>
									<tr>
										<th scope="row"><?php echo esc_html( $gami_spec['label'] ); ?></th>
										<td><?php echo esc_html( $gami_spec['value'] ); ?></td>
									</tr>
								<?php endforeach; ?>
								<?php if ( $gami_terms && ! is_wp_error( $gami_terms ) ) : ?>
									<tr>
										<th scope="row"><?php esc_html_e( 'Category', 'gami-surgical' ); ?></th>
										<td><?php the_terms( get_the_ID(), 'instrument_category', '', ', ' ); ?></td>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					<?php endif; ?>

					<p class="instrument__actions">
						<a class="button button--primary" href="#quote"><?php esc_html_e( 'Request a quote', 'gami-surgical' ); ?></a>
						<?php
						$gami_wa = gami_whatsapp_url( sprintf( /* translators: %s instrument */ __( 'Quote request: %s', 'gami-surgical' ), get_the_title() . ( isset( $gami_specs['catalogue_no'] ) ? ' (' . $gami_specs['catalogue_no']['value'] . ')' : '' ) ) );
						if ( $gami_wa ) :
							?>
							<a class="button button--ghost" href="<?php echo esc_url( $gami_wa ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Ask on WhatsApp', 'gami-surgical' ); ?></a>
						<?php endif; ?>
					</p>
				</div>
			</article>

			<?php if ( trim( get_the_content() ) ) : ?>
				<section class="instrument__details entry-content">
					<h2><?php esc_html_e( 'Details', 'gami-surgical' ); ?></h2>
					<?php the_content(); ?>
				</section>
			<?php endif; ?>

			<section class="instrument__quote">
				<?php echo gami_quote_form( array( 'instrument_id' => get_the_ID() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</section>

			<?php
			if ( $gami_terms && ! is_wp_error( $gami_terms ) ) :
				$gami_related = new WP_Query(
					array(
						'post_type'      => 'instrument',
						'posts_per_page' => 4,
						'post__not_in'   => array( get_the_ID() ),
						'no_found_rows'  => true,
						'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery
							array(
								'taxonomy' => 'instrument_category',
								'field'    => 'term_id',
								'terms'    => wp_list_pluck( $gami_terms, 'term_id' ),
							),
						),
					)
				);
				if ( $gami_related->have_posts() ) :
					?>
					<section class="instrument__related">
						<h2 class="section__title"><?php esc_html_e( 'Related instruments', 'gami-surgical' ); ?></h2>
						<ul class="instrument-grid" style="--cols:4">
							<?php
							while ( $gami_related->have_posts() ) :
								$gami_related->the_post();
								get_template_part( 'template-parts/content', 'instrument-card' );
							endwhile;
							wp_reset_postdata();
							?>
						</ul>
					</section>
					<?php
				endif;
			endif;
			?>
		</main>
		<?php
	endwhile;
endif;

get_footer();
