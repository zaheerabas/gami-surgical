<?php
/**
 * Posts → Upload Articles: turn article .html files into blog posts.
 *
 * An article file is a normal HTML page. The theme reads:
 *   <title> or the first <h1>            → post title (the <h1> is not repeated in the body)
 *   <meta name="description">            → excerpt
 *   <meta name="slug">                   → URL slug (defaults to the title)
 *   <meta name="category">               → category, created if missing
 *   <meta name="tags">                   → comma-separated tags
 *   <body>                               → post content
 * Uploading a file whose slug already exists updates that post instead of
 * adding a copy. Posts can then be edited in the block editor or Elementor.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gami_article_upload_menu() {
	add_posts_page(
		__( 'Upload Articles', 'gami-surgical' ),
		__( 'Upload Articles', 'gami-surgical' ),
		'edit_posts',
		'gami-upload-articles',
		'gami_article_upload_page'
	);
}
add_action( 'admin_menu', 'gami_article_upload_menu' );

/**
 * Parse an article file.
 *
 * @param string $html File contents.
 * @return array|WP_Error title, content, excerpt, slug, category, tags.
 */
function gami_parse_article( $html ) {
	$html = (string) $html;

	$meta = array();
	if ( preg_match_all( '/<meta\s[^>]*>/i', $html, $tags ) ) {
		foreach ( $tags[0] as $tag ) {
			if ( preg_match( '/\bname\s*=\s*(["\'])(.*?)\1/i', $tag, $name ) && preg_match( '/\bcontent\s*=\s*(["\'])(.*?)\1/is', $tag, $content ) ) {
				$meta[ strtolower( trim( $name[2] ) ) ] = html_entity_decode( trim( $content[2] ), ENT_QUOTES, 'UTF-8' );
			}
		}
	}

	$body = preg_match( '/<body[^>]*>(.*)<\/body>/is', $html, $m ) ? $m[1] : preg_replace( '/<head[^>]*>.*?<\/head>/is', '', $html );
	$body = preg_replace( '/<(script|style)[^>]*>.*?<\/\1>/is', '', $body );

	$title = '';
	if ( preg_match( '/<h1[^>]*>(.*?)<\/h1>/is', $body, $h1 ) ) {
		$title = $h1[1];
		$body  = preg_replace( '/<h1[^>]*>.*?<\/h1>/is', '', $body, 1 );
	} elseif ( preg_match( '/<title[^>]*>(.*?)<\/title>/is', $html, $t ) ) {
		$title = $t[1];
	}
	$title = trim( html_entity_decode( wp_strip_all_tags( $title ), ENT_QUOTES, 'UTF-8' ) );

	// Only the <article> element when the file wraps the text in one.
	if ( preg_match( '/<article[^>]*>(.*)<\/article>/is', $body, $a ) ) {
		$body = $a[1];
	}
	$content = trim( wp_kses_post( $body ) );

	if ( '' === $title || '' === wp_strip_all_tags( $content ) ) {
		return new WP_Error( 'gami_article_empty', __( 'No title or no text found.', 'gami-surgical' ) );
	}

	return array(
		'title'    => $title,
		'content'  => $content,
		'excerpt'  => sanitize_textarea_field( $meta['description'] ?? '' ),
		'slug'     => sanitize_title( ( $meta['slug'] ?? '' ) !== '' ? $meta['slug'] : $title ),
		'category' => sanitize_text_field( $meta['category'] ?? '' ),
		'tags'     => array_filter( array_map( 'sanitize_text_field', array_map( 'trim', explode( ',', $meta['tags'] ?? '' ) ) ) ),
	);
}

/**
 * Create or update the post for a parsed article.
 *
 * @return array|WP_Error array( 'id' => int, 'updated' => bool ).
 */
function gami_save_article( $article, $status ) {
	$existing = get_posts(
		array(
			'name'           => $article['slug'],
			'post_type'      => 'post',
			'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	$postarr = array(
		'post_type'    => 'post',
		'post_title'   => $article['title'],
		'post_name'    => $article['slug'],
		'post_content' => $article['content'],
		'post_excerpt' => $article['excerpt'],
	);

	if ( $existing ) {
		$postarr['ID'] = $existing[0];
		if ( ! current_user_can( 'edit_post', $existing[0] ) ) {
			return new WP_Error( 'gami_article_cap', __( 'You cannot edit the existing post with this slug.', 'gami-surgical' ) );
		}
		// Keep the status of a post that is already live.
		if ( 'publish' !== get_post_status( $existing[0] ) ) {
			$postarr['post_status'] = $status;
		}
		$post_id = wp_update_post( wp_slash( $postarr ), true );
	} else {
		$postarr['post_status'] = $status;
		$post_id                = wp_insert_post( wp_slash( $postarr ), true );
	}

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	if ( $article['category'] ) {
		$term = term_exists( $article['category'], 'category' );
		if ( ! $term && current_user_can( 'manage_categories' ) ) {
			$term = wp_insert_term( $article['category'], 'category' );
		}
		if ( $term && ! is_wp_error( $term ) ) {
			wp_set_post_categories( $post_id, array( (int) $term['term_id'] ) );
		}
	}
	if ( $article['tags'] ) {
		wp_set_post_tags( $post_id, $article['tags'] );
	}

	return array(
		'id'      => (int) $post_id,
		'updated' => (bool) $existing,
	);
}

/**
 * Handle the upload form. Returns result rows for the page.
 */
function gami_handle_article_upload() {
	if ( empty( $_FILES['gami_articles'] ) || ! check_admin_referer( 'gami_upload_articles' ) ) {
		return array();
	}

	$status = ( isset( $_POST['gami_status'] ) && 'publish' === $_POST['gami_status'] && current_user_can( 'publish_posts' ) ) ? 'publish' : 'draft';
	$files  = $_FILES['gami_articles']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$rows   = array();

	foreach ( (array) $files['name'] as $i => $name ) {
		$name = sanitize_file_name( wp_unslash( $name ) );
		$tmp  = $files['tmp_name'][ $i ];

		if ( UPLOAD_ERR_NO_FILE === $files['error'][ $i ] ) {
			continue;
		}
		if ( UPLOAD_ERR_OK !== $files['error'][ $i ] || ! is_uploaded_file( $tmp ) ) {
			$rows[] = array( $name, new WP_Error( 'gami_article_upload', __( 'Upload failed.', 'gami-surgical' ) ) );
			continue;
		}
		if ( ! preg_match( '/\.html?$/i', $name ) || $files['size'][ $i ] > 2 * MB_IN_BYTES ) {
			$rows[] = array( $name, new WP_Error( 'gami_article_type', __( 'Only .html files up to 2 MB.', 'gami-surgical' ) ) );
			continue;
		}

		$article = gami_parse_article( file_get_contents( $tmp ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$rows[]  = array( $name, is_wp_error( $article ) ? $article : gami_save_article( $article, $status ) );
	}

	return $rows;
}

function gami_article_upload_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	$rows = gami_handle_article_upload();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Upload Articles', 'gami-surgical' ); ?></h1>

		<?php if ( $rows ) : ?>
			<table class="widefat striped" style="max-width:900px;margin:1em 0">
				<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><code><?php echo esc_html( $row[0] ); ?></code></td>
						<?php if ( is_wp_error( $row[1] ) ) : ?>
							<td style="color:#b32d2e"><?php echo esc_html( $row[1]->get_error_message() ); ?></td>
						<?php else : ?>
							<td>
								<?php echo $row[1]['updated'] ? esc_html__( 'Updated', 'gami-surgical' ) : esc_html__( 'Created', 'gami-surgical' ); ?>:
								<strong><?php echo esc_html( get_the_title( $row[1]['id'] ) ); ?></strong>
								(<?php echo esc_html( get_post_status_object( get_post_status( $row[1]['id'] ) )->label ); ?>)
								— <a href="<?php echo esc_url( get_edit_post_link( $row[1]['id'] ) ); ?>"><?php esc_html_e( 'Edit', 'gami-surgical' ); ?></a>
								<?php if ( did_action( 'elementor/loaded' ) ) : ?>
									| <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $row[1]['id'] . '&action=elementor' ) ); ?>"><?php esc_html_e( 'Edit with Elementor', 'gami-surgical' ); ?></a>
								<?php endif; ?>
								| <a href="<?php echo esc_url( get_permalink( $row[1]['id'] ) ); ?>" target="_blank"><?php esc_html_e( 'View', 'gami-surgical' ); ?></a>
							</td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<p style="max-width:70ch">
			<?php esc_html_e( 'Choose one or more article files (.html). Each file becomes a blog post with its title, excerpt, category and tags. Uploading a file again updates its post instead of making a copy. Add a featured image afterwards from the post editor.', 'gami-surgical' ); ?>
		</p>

		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'gami_upload_articles' ); ?>
			<p><input type="file" name="gami_articles[]" accept=".html,.htm,text/html" multiple required></p>
			<p>
				<label><input type="radio" name="gami_status" value="draft" checked> <?php esc_html_e( 'Save as draft (review first)', 'gami-surgical' ); ?></label><br>
				<?php if ( current_user_can( 'publish_posts' ) ) : ?>
					<label><input type="radio" name="gami_status" value="publish"> <?php esc_html_e( 'Publish now', 'gami-surgical' ); ?></label>
				<?php endif; ?>
			</p>
			<?php submit_button( __( 'Upload articles', 'gami-surgical' ) ); ?>
		</form>
	</div>
	<?php
}
