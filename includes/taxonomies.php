<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jr_content_core_register_taxonomies() {
	register_taxonomy(
		'games',
		array( 'post', 'review', 'playlist', 'video' ),
		array(
			'labels'            => array(
				'name'          => __( 'Games', 'jr-content-core' ),
				'singular_name' => __( 'Game', 'jr-content-core' ),
				'menu_name'     => __( 'Games', 'jr-content-core' ),
				'all_items'     => __( 'All Games', 'jr-content-core' ),
				'edit_item'     => __( 'Edit Game', 'jr-content-core' ),
				'view_item'     => __( 'View Game', 'jr-content-core' ),
				'update_item'   => __( 'Update Game', 'jr-content-core' ),
				'add_new_item'  => __( 'Add New Game', 'jr-content-core' ),
				'new_item_name' => __( 'New Game Name', 'jr-content-core' ),
				'search_items'  => __( 'Search Games', 'jr-content-core' ),
				'not_found'     => __( 'No games found.', 'jr-content-core' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'rewrite'           => array( 'slug' => 'game' ),
		)
	);

	register_taxonomy(
		'characters',
		array( 'post', 'review' ),
		array(
			'labels'            => array(
				'name'          => __( 'Characters', 'jr-content-core' ),
				'singular_name' => __( 'Character', 'jr-content-core' ),
				'menu_name'     => __( 'Characters', 'jr-content-core' ),
				'all_items'     => __( 'All Characters', 'jr-content-core' ),
				'edit_item'     => __( 'Edit Character', 'jr-content-core' ),
				'view_item'     => __( 'View Character', 'jr-content-core' ),
				'update_item'   => __( 'Update Character', 'jr-content-core' ),
				'add_new_item'  => __( 'Add New Character', 'jr-content-core' ),
				'new_item_name' => __( 'New Character Name', 'jr-content-core' ),
				'search_items'  => __( 'Search Characters', 'jr-content-core' ),
				'not_found'     => __( 'No characters found.', 'jr-content-core' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'character' ),
		)
	);

	register_taxonomy(
		'platform',
		array( 'post', 'review', 'playlist', 'video' ),
		array(
			'labels'            => array(
				'name'          => __( 'Platforms', 'jr-content-core' ),
				'singular_name' => __( 'Platform', 'jr-content-core' ),
				'menu_name'     => __( 'Platforms', 'jr-content-core' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_in_nav_menus' => true,
			'rewrite'           => array( 'slug' => 'platform' ),
		)
	);
}

// ─── Hierarchical Permalink Rewrite ───────────────────────────────────────────

function jr_content_core_taxonomy_rewrite_rules() {
	// add_rewrite_rule( ..., 'top' ) appends to $wp_rewrite->extra_rules_top,
	// which is merged before WordPress's generated rules via array_merge().
	// Within extra_rules_top, PHP insertion order is preserved: FIRST added
	// rule is FIRST evaluated. Add most-specific rules first.
	//
	// Desired evaluation order per taxonomy:
	//   1. top-level paged  (/game/<slug>/page/<n>/)
	//   2. hierarchical paged  (/game/.../leaf/page/<n>/)
	//   3. top-level feed  (/game/<slug>/feed/ or /game/<slug>/rss2/)
	//   4. hierarchical feed  (/game/.../leaf/feed/)
	//   5. hierarchical catch-all  (/game/.../leaf/)
	//
	// Rules 1-4 must precede the catch-all (rule 5) so that pagination and
	// feed suffixes are not mistaken for child term slugs, and so our rules
	// beat WordPress's default ^game/(.+?)/?$ which matches the full path
	// including slashes (resulting in a 404 for any child term URL).

	// --- game (most specific first) ---
	add_rewrite_rule(
		'^game/([^/]+)/page/([0-9]{1,})/?$',
		'index.php?games=$matches[1]&paged=$matches[2]',
		'top'
	);
	add_rewrite_rule(
		'^game/(?:[^/]+/)+([^/]+)/page/([0-9]{1,})/?$',
		'index.php?games=$matches[1]&paged=$matches[2]',
		'top'
	);
	add_rewrite_rule(
		'^game/([^/]+)/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$',
		'index.php?games=$matches[1]&feed=$matches[2]',
		'top'
	);
	add_rewrite_rule(
		'^game/(?:[^/]+/)+([^/]+)/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$',
		'index.php?games=$matches[1]&feed=$matches[2]',
		'top'
	);
	add_rewrite_rule(
		'^game/(?:[^/]+/)+([^/]+)/?$',
		'index.php?games=$matches[1]',
		'top'
	);

	// --- platform (same order) ---
	add_rewrite_rule(
		'^platform/([^/]+)/page/([0-9]{1,})/?$',
		'index.php?platform=$matches[1]&paged=$matches[2]',
		'top'
	);
	add_rewrite_rule(
		'^platform/(?:[^/]+/)+([^/]+)/page/([0-9]{1,})/?$',
		'index.php?platform=$matches[1]&paged=$matches[2]',
		'top'
	);
	add_rewrite_rule(
		'^platform/([^/]+)/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$',
		'index.php?platform=$matches[1]&feed=$matches[2]',
		'top'
	);
	add_rewrite_rule(
		'^platform/(?:[^/]+/)+([^/]+)/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$',
		'index.php?platform=$matches[1]&feed=$matches[2]',
		'top'
	);
	add_rewrite_rule(
		'^platform/(?:[^/]+/)+([^/]+)/?$',
		'index.php?platform=$matches[1]',
		'top'
	);
}

function jr_content_core_term_link( $termlink, $term, $taxonomy ) {
	if ( 'games' !== $taxonomy && 'platform' !== $taxonomy ) {
		return $termlink;
	}

	// Plain permalinks: pretty URLs are not routed, so fall back to default.
	if ( empty( get_option( 'permalink_structure' ) ) ) {
		return $termlink;
	}

	global $wp_rewrite;
	// index.php-style permalinks prefix every path with /index.php.
	$prefix = ( isset( $wp_rewrite ) && $wp_rewrite->using_index_permalinks() )
		? '/' . ltrim( $wp_rewrite->index, '/' )
		: '';

	$base = ( 'games' === $taxonomy ) ? 'game' : 'platform';

	if ( ! $term->parent ) {
		return home_url( user_trailingslashit( "{$prefix}/{$base}/{$term->slug}", 'category' ) );
	}

	$ancestors = get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );
	$ancestors = array_reverse( $ancestors );

	$slugs = array();
	foreach ( $ancestors as $ancestor_id ) {
		$ancestor = get_term( $ancestor_id, $taxonomy );
		if ( ! $ancestor || is_wp_error( $ancestor ) ) {
			return $termlink;
		}
		$slugs[] = $ancestor->slug;
	}
	$slugs[] = $term->slug;

	return home_url( user_trailingslashit( "{$prefix}/{$base}/" . implode( '/', $slugs ), 'category' ) );
}

// ─── Game Taxonomy Term Meta (game_type) ──────────────────────────────────────

function jr_content_core_game_add_form_fields() {
	?>
	<div class="form-field">
		<label for="game_type"><?php esc_html_e( 'Game Type', 'jr-content-core' ); ?></label>
		<select id="game_type" name="game_type">
			<option value="game"><?php esc_html_e( 'Game', 'jr-content-core' ); ?></option>
			<option value="franchise"><?php esc_html_e( 'Franchise', 'jr-content-core' ); ?></option>
			<option value="genre"><?php esc_html_e( 'Genre', 'jr-content-core' ); ?></option>
		</select>
	</div>
	<?php
}

function jr_content_core_game_edit_form_fields( $term ) {
	$game_type = get_term_meta( $term->term_id, 'game_type', true );
	if ( ! $game_type ) {
		$game_type = 'game';
	}
	?>
	<tr class="form-field">
		<th scope="row">
			<label for="game_type"><?php esc_html_e( 'Game Type', 'jr-content-core' ); ?></label>
		</th>
		<td>
			<select id="game_type" name="game_type">
				<option value="game" <?php selected( $game_type, 'game' ); ?>><?php esc_html_e( 'Game', 'jr-content-core' ); ?></option>
				<option value="franchise" <?php selected( $game_type, 'franchise' ); ?>><?php esc_html_e( 'Franchise', 'jr-content-core' ); ?></option>
				<option value="genre" <?php selected( $game_type, 'genre' ); ?>><?php esc_html_e( 'Genre', 'jr-content-core' ); ?></option>
			</select>
		</td>
	</tr>
	<?php
}

function jr_content_core_save_game_type( $term_id ) {
	$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
	if ( 'editedtag' === $action ) {
		$nonce_key    = '_wpnonce';
		$nonce_action = 'update-tag_' . $term_id;
	} else {
		$nonce_key    = '_wpnonce_add-tag';
		$nonce_action = 'add-tag';
	}
	if ( ! isset( $_POST[ $nonce_key ] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ $nonce_key ] ) ), $nonce_action ) ) {
		return;
	}
	if ( ! isset( $_POST['game_type'] ) ) {
		return;
	}
	$raw   = sanitize_key( wp_unslash( $_POST['game_type'] ) );
	$value = in_array( $raw, array( 'franchise', 'game', 'genre' ), true ) ? $raw : 'game';
	update_term_meta( $term_id, 'game_type', $value );
}

function jr_content_core_games_columns( $columns ) {
	$columns['game_type'] = __( 'Type', 'jr-content-core' );
	return $columns;
}

function jr_content_core_games_column_content( $content, $column_name, $term_id ) {
	if ( 'game_type' !== $column_name ) {
		return $content;
	}
	$type = get_term_meta( (int) $term_id, 'game_type', true );
	if ( ! $type ) {
		$type = 'game';
	}
	return 'franchise' === $type
		? esc_html__( 'Franchise', 'jr-content-core' )
		: esc_html__( 'Game', 'jr-content-core' );
}

// ─── Platform Taxonomy Term Meta (platform_type) ──────────────────────────────

function jr_content_core_platform_add_form_fields() {
	?>
	<div class="form-field">
		<label for="platform_type"><?php esc_html_e( 'Platform Type', 'jr-content-core' ); ?></label>
		<select id="platform_type" name="platform_type">
			<option value="platform"><?php esc_html_e( 'Platform', 'jr-content-core' ); ?></option>
			<option value="manufacturer"><?php esc_html_e( 'Manufacturer', 'jr-content-core' ); ?></option>
		</select>
	</div>
	<?php
}

function jr_content_core_platform_edit_form_fields( $term ) {
	$platform_type = get_term_meta( $term->term_id, 'platform_type', true );
	if ( ! $platform_type ) {
		$platform_type = 'platform';
	}
	?>
	<tr class="form-field">
		<th scope="row">
			<label for="platform_type"><?php esc_html_e( 'Platform Type', 'jr-content-core' ); ?></label>
		</th>
		<td>
			<select id="platform_type" name="platform_type">
				<option value="platform" <?php selected( $platform_type, 'platform' ); ?>><?php esc_html_e( 'Platform', 'jr-content-core' ); ?></option>
				<option value="manufacturer" <?php selected( $platform_type, 'manufacturer' ); ?>><?php esc_html_e( 'Manufacturer', 'jr-content-core' ); ?></option>
			</select>
		</td>
	</tr>
	<?php
}

function jr_content_core_save_platform_type( $term_id ) {
	$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
	if ( 'editedtag' === $action ) {
		$nonce_key    = '_wpnonce';
		$nonce_action = 'update-tag_' . $term_id;
	} else {
		$nonce_key    = '_wpnonce_add-tag';
		$nonce_action = 'add-tag';
	}
	if ( ! isset( $_POST[ $nonce_key ] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ $nonce_key ] ) ), $nonce_action ) ) {
		return;
	}
	if ( ! isset( $_POST['platform_type'] ) ) {
		return;
	}
	$raw   = sanitize_key( wp_unslash( $_POST['platform_type'] ) );
	$value = in_array( $raw, array( 'manufacturer', 'platform' ), true ) ? $raw : 'platform';
	update_term_meta( $term_id, 'platform_type', $value );
}

function jr_content_core_platform_columns( $columns ) {
	$columns['platform_type'] = __( 'Type', 'jr-content-core' );
	return $columns;
}

function jr_content_core_platform_column_content( $content, $column_name, $term_id ) {
	if ( 'platform_type' !== $column_name ) {
		return $content;
	}
	$type = get_term_meta( (int) $term_id, 'platform_type', true );
	if ( ! $type ) {
		$type = 'platform';
	}
	return 'manufacturer' === $type
		? esc_html__( 'Manufacturer', 'jr-content-core' )
		: esc_html__( 'Platform', 'jr-content-core' );
}
