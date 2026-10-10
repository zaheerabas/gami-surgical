<?php
/**
 * Article (single post) extras: contents list, sidebar, author box,
 * share links, quote box and related articles.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prepare article HTML: give h2/h3 ids for the contents list and anchor links,
 * wrap tables so they scroll on phones, and mark links to other sites.
 *
 * @param string $html Rendered content.
 * @return array( 'html' => string, 'toc' => array of array( id, text, level ) ).
 */
function gami_prepare_article( $html ) {
	$toc  = array();
	$used = array();

	$html = preg_replace_callback(
		'/<h([23])([^>]*)>(.*?)<\/h\1>/is',
		function ( $m ) use ( &$toc, &$used ) {
			$text = trim( wp_strip_all_tags( $m[3] ) );
			if ( '' === $text ) {
				return $m[0];
			}
			$attrs = $m[2];
			if ( preg_match( '/\sid\s*=\s*(["\'])(.*?)\1/i', $attrs, $idm ) ) {
				$id = $idm[2];
			} else {
				$base = sanitize_title( $text );
				$base = $base ? $base : 'section';
				$id   = $base;
				$n    = 2;
				while ( isset( $used[ $id ] ) ) {
					$id = $base . '-' . $n++;
				}
				$attrs .= ' id="' . esc_attr( $id ) . '"';
			}
			$used[ $id ] = true;
			$toc[]       = array( $id, $text, (int) $m[1] );
			$anchor      = sprintf(
				'<a class="heading-anchor" href="#%1$s" aria-label="%2$s">#</a>',
				esc_attr( $id ),
				/* translators: %s: heading */
				esc_attr( sprintf( __( 'Link to “%s”', 'gami-surgical' ), $text ) )
			);
			return '<h' . $m[1] . $attrs . '>' . $m[3] . $anchor . '</h' . $m[1] . '>';
		},
		$html
	);

	$html = preg_replace( '/<table\b(.*?)<\/table>/is', '<div class="table-wrap" tabindex="0"><table$1</table></div>', $html );

	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	$html = preg_replace_callback(
		'/<a\s([^>]*href=(["\'])(https?:\/\/[^"\']+)\2[^>]*)>/i',
		function ( $m ) use ( $host ) {
			$link_host = wp_parse_url( $m[3], PHP_URL_HOST );
			if ( ! $link_host || $link_host === $host || false !== strpos( $m[1], 'class=' ) ) {
				return $m[0];
			}
			$extra = ' class="is-external"';
			if ( false === stripos( $m[1], 'target=' ) ) {
				$extra .= ' target="_blank" rel="noopener"';
			}
			return '<a ' . $m[1] . $extra . '>';
		},
		$html
	);

	return array(
		'html' => $html,
		'toc'  => $toc,
	);
}

/**
 * "On this page" list. Shown when the article has at least two h2 headings.
 *
 * @param array  $toc     From gami_prepare_article().
 * @param string $context "inline" (collapsible, above the text on phones) or "sidebar".
 */
function gami_article_toc( $toc, $context = 'sidebar' ) {
	$h2 = array_filter(
		$toc,
		function ( $item ) {
			return 2 === $item[2];
		}
	);
	if ( ! gami_option( 'article_toc' ) || count( $h2 ) < 2 ) {
		return;
	}

	$list = '<ol class="toc__list">';
	foreach ( $h2 as $item ) {
		// The list is numbered already, so drop "1." / "2)" from numbered headings.
		$text  = preg_replace( '/^\d+\s*[.)]\s+/', '', $item[1] );
		$list .= sprintf( '<li><a href="#%1$s">%2$s</a></li>', esc_attr( $item[0] ), esc_html( $text ) );
	}
	$list .= '</ol>';

	if ( 'inline' === $context ) {
		printf(
			'<details class="toc toc--inline"><summary>%1$s <span>%2$s</span></summary>%3$s</details>',
			esc_html__( 'On this page', 'gami-surgical' ),
			/* translators: %d: number of sections */
			esc_html( sprintf( _n( '%d section', '%d sections', count( $h2 ), 'gami-surgical' ), count( $h2 ) ) ),
			$list // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	} else {
		printf(
			'<nav class="side-box toc toc--side" aria-label="%1$s"><p class="side-box__title">%1$s</p>%2$s</nav>',
			esc_attr__( 'On this page', 'gami-surgical' ),
			$list // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}
}

/**
 * Sidebar beside articles: contents, About box, topics, latest articles, then
 * any widgets placed in the Blog sidebar.
 */
function gami_article_sidebar( $toc ) {
	?>
	<aside id="secondary" class="layout-aside article-aside" aria-label="<?php esc_attr_e( 'Article sidebar', 'gami-surgical' ); ?>">
		<?php // Contents and About stay in view while reading; the rest follows after them. ?>
		<div class="article-aside__track">
		<div class="article-aside__sticky">
		<?php gami_article_toc( $toc, 'sidebar' ); ?>

		<?php if ( gami_option( 'article_about' ) ) : ?>
			<section class="side-box side-box--about">
				<p class="side-box__title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
				<p><?php echo esc_html( gami_option( 'article_about_text' ) ); ?></p>
				<p class="side-box__actions">
					<a class="button button--primary" href="<?php echo esc_url( gami_quote_url() ); ?>"><?php esc_html_e( 'Request a quote', 'gami-surgical' ); ?></a>
					<?php $gami_wa = gami_whatsapp_url( sprintf( /* translators: %s: article title */ __( 'Hello, I read “%s” and have a question.', 'gami-surgical' ), get_the_title() ) ); ?>
					<?php if ( $gami_wa ) : ?>
						<a class="side-box__link" href="<?php echo esc_url( $gami_wa ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Ask on WhatsApp', 'gami-surgical' ); ?></a>
					<?php endif; ?>
				</p>
			</section>
		<?php endif; ?>
		</div>
		</div>

		<div class="article-aside__more">
		<?php
		$gami_cats = gami_blog_categories();
		if ( $gami_cats ) :
			$gami_current = gami_primary_category();
			?>
			<section class="side-box">
				<p class="side-box__title"><?php esc_html_e( 'Topics', 'gami-surgical' ); ?></p>
				<ul class="side-topics">
					<?php foreach ( $gami_cats as $gami_cat ) : ?>
						<li>
							<a href="<?php echo esc_url( get_category_link( $gami_cat ) ); ?>"<?php echo $gami_current && $gami_current->term_id === $gami_cat->term_id ? ' aria-current="true"' : ''; ?>>
								<?php echo esc_html( $gami_cat->name ); ?>
								<span><?php echo esc_html( number_format_i18n( $gami_cat->count ) ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

		<?php
		$gami_latest = new WP_Query(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => 4,
				'post__not_in'        => array( get_the_ID() ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);
		if ( $gami_latest->have_posts() ) :
			?>
			<section class="side-box">
				<p class="side-box__title"><?php esc_html_e( 'Latest articles', 'gami-surgical' ); ?></p>
				<ul class="side-posts">
					<?php
					while ( $gami_latest->have_posts() ) :
						$gami_latest->the_post();
						?>
						<li>
							<a href="<?php the_permalink(); ?>">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'thumbnail', array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
								<?php else : ?>
									<span class="side-posts__ph" aria-hidden="true"></span>
								<?php endif; ?>
								<span>
									<span class="side-posts__title"><?php the_title(); ?></span>
									<span class="side-posts__meta"><?php echo esc_html( get_the_date() ); ?></span>
								</span>
							</a>
						</li>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</ul>
			</section>
		<?php endif; ?>

		<?php if ( is_active_sidebar( 'sidebar-blog' ) ) : ?>
			<div class="article-aside__widgets"><?php dynamic_sidebar( 'sidebar-blog' ); ?></div>
		<?php endif; ?>
		</div>
	</aside>
	<?php
}

/**
 * About the author, under the article.
 */
function gami_author_box() {
	if ( ! gami_option( 'article_author_box' ) ) {
		return;
	}
	$author_id = (int) get_the_author_meta( 'ID' );
	$bio       = get_the_author_meta( 'description', $author_id );
	?>
	<section class="author-box">
		<?php echo get_avatar( $author_id, 72, '', '', array( 'class' => 'author-box__avatar' ) ); ?>
		<div>
			<p class="author-box__label"><?php esc_html_e( 'Written by', 'gami-surgical' ); ?></p>
			<p class="author-box__name"><?php echo esc_html( get_the_author() ); ?></p>
			<p class="author-box__bio">
				<?php
				echo $bio ? esc_html( $bio ) : esc_html(
					sprintf(
						/* translators: %s: site name */
						__( 'Part of the %s team in Sialkot, writing about how instruments are made, chosen and cared for.', 'gami-surgical' ),
						get_bloginfo( 'name' )
					)
				);
				?>
			</p>
		</div>
	</section>
	<?php
}

/**
 * Up to three posts from the same category, else the latest.
 */
function gami_related_posts( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$cat     = gami_primary_category( $post_id );
	$args    = array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'post__not_in'        => array( $post_id ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	if ( $cat ) {
		$args['cat'] = $cat->term_id;
	}
	$related = new WP_Query( $args );
	if ( ! $related->have_posts() && $cat ) {
		unset( $args['cat'] );
		$related = new WP_Query( $args );
	}
	if ( ! $related->have_posts() ) {
		return;
	}
	?>
	<section class="related-posts" aria-labelledby="related-title">
		<div class="container">
			<?php gami_blog_section_head( __( 'Keep reading', 'gami-surgical' ), gami_blog_url(), __( 'All articles', 'gami-surgical' ), 'related-title' ); ?>
			<div class="blog-grid blog-grid--3">
				<?php gami_blog_cards( $related, 'card' ); ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Quote box under each article.
 */
function gami_article_cta() {
	?>
	<aside class="article-cta">
		<div>
			<p class="article-cta__title"><?php esc_html_e( 'Need instruments made to this standard?', 'gami-surgical' ); ?></p>
			<p class="article-cta__text"><?php esc_html_e( 'Gami Surgical manufactures surgical, dental and beauty instruments in Sialkot and ships worldwide. Send us your list for a quote.', 'gami-surgical' ); ?></p>
		</div>
		<a class="button button--primary" href="<?php echo esc_url( gami_quote_url() ); ?>"><?php esc_html_e( 'Request a quote', 'gami-surgical' ); ?></a>
	</aside>
	<?php
}

/**
 * Share links (no third-party scripts), plus copy link.
 */
function gami_share_links() {
	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( get_the_title() );
	$links = array(
		'LinkedIn' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url,
		'Facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $url,
		'X'        => 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title,
		'WhatsApp' => 'https://wa.me/?text=' . $title . '%20' . $url,
		'Email'    => 'mailto:?subject=' . $title . '&body=' . $url,
	);
	echo '<div class="share-links"><span class="share-links__label">' . esc_html__( 'Share this article', 'gami-surgical' ) . '</span><ul>';
	foreach ( $links as $name => $href ) {
		printf(
			'<li><a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a></li>',
			esc_url( $href, array( 'http', 'https', 'mailto' ) ),
			esc_html( $name )
		);
	}
	printf(
		'<li><button type="button" class="share-links__copy" data-copy="%1$s" data-done="%3$s">%2$s</button></li>',
		esc_url( get_permalink() ),
		esc_html__( 'Copy link', 'gami-surgical' ),
		esc_attr__( 'Copied', 'gami-surgical' )
	);
	echo '</ul></div>';
}
