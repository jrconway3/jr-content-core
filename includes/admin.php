<?php
/**
 * Playlist admin settings page and frontend data filters.
 *
 * Provides:
 *  - "Settings" sub-page under the Playlist CPT admin menu
 *  - Searchable pill-picker UI for choosing featured/sidebar playlists
 *  - `jr_home_playlists` filter  → array of playlist context objects
 *  - `jr_sidebar_playlist` filter → single sidebar playlist context object or null
 *
 * @package JRContentCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -------------------------------------------------------------------------
// Frontend data filters — run on every request so the theme can consume them
// -------------------------------------------------------------------------

add_filter( 'jr_home_playlists',   'jr_content_core_home_playlists',   10, 0 );
add_filter( 'jr_sidebar_playlist', 'jr_content_core_sidebar_playlist', 10, 0 );

/**
 * Returns the home-page playlists array for the `jr_home_playlists` filter.
 *
 * Override mode (jr_pinned_playlist_ids set): returns exactly those IDs in order.
 * Default mode: returns the 3 most recent published playlists.
 *
 * @return array
 */
function jr_content_core_home_playlists() {
	$pinned_ids = array_values(
		array_filter( array_map( 'absint', explode( ',', get_option( 'jr_pinned_playlist_ids', '' ) ) ) )
	);

	if ( ! empty( $pinned_ids ) ) {
		$playlist_posts = get_posts(
			array(
				'post_type'      => 'playlist',
				'posts_per_page' => count( $pinned_ids ),
				'post__in'       => $pinned_ids,
				'orderby'        => 'post__in',
				'post_status'    => 'publish',
			)
		);
	} else {
		$playlist_posts = get_posts(
			array(
				'post_type'      => 'playlist',
				'posts_per_page' => 3,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'post_status'    => 'publish',
			)
		);
	}

	if ( empty( $playlist_posts ) ) {
		return array();
	}

	// Fetch all videos for every playlist in one query, then group in PHP.
	$playlist_ids    = array_map( fn( $pl ) => $pl->ID, $playlist_posts );
	$all_video_posts = get_posts(
		array(
			'post_type'      => 'video',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'     => 'wp_playlist_id',
					'value'   => $playlist_ids,
					'compare' => 'IN',
				),
			),
		)
	);

	// Group videos by playlist ID; meta cache is warm after the query above.
	$videos_by_playlist = array();
	foreach ( $all_video_posts as $v ) {
		$pl_id = (int) get_post_meta( $v->ID, 'wp_playlist_id', true );
		if ( ! isset( $videos_by_playlist[ $pl_id ] ) ) {
			$videos_by_playlist[ $pl_id ] = array();
		}
		if ( count( $videos_by_playlist[ $pl_id ] ) < 20 ) {
			$videos_by_playlist[ $pl_id ][] = $v;
		}
	}

	$home_playlists = array();
	foreach ( $playlist_posts as $pl ) {
		$home_playlists[] = array(
			'ID'               => $pl->ID,
			'title'            => get_the_title( $pl->ID ),
			'permalink'        => get_permalink( $pl->ID ),
			'yt_playlist_id'   => get_post_meta( $pl->ID, 'yt_playlist_id', true ),
			'yt_thumbnail_url' => get_post_meta( $pl->ID, 'yt_thumbnail_url', true ),
			'yt_video_count'   => (int) get_post_meta( $pl->ID, 'yt_video_count', true ),
			'videos'           => array_map( 'jr_content_core_format_video', $videos_by_playlist[ $pl->ID ] ?? array() ),
		);
	}

	return $home_playlists;
}

/**
 * Returns the sidebar playlist context object for the `jr_sidebar_playlist` filter.
 * Returns null when no playlist/video data is available.
 *
 * @return array|null
 */
function jr_content_core_sidebar_playlist() {
	$source        = get_option( 'jr_sidebar_source', 'playlist' );
	$section_title = get_option( 'jr_sidebar_playlist_title', '' );

	if ( 'videos' === $source ) {
		$video_ids = array_values(
			array_filter( array_map( 'absint', explode( ',', get_option( 'jr_sidebar_video_ids', '' ) ) ) )
		);
		if ( empty( $video_ids ) ) {
			return null;
		}
		$video_posts = get_posts(
			array(
				'post_type'      => 'video',
				'post__in'       => $video_ids,
				'orderby'        => 'post__in',
				'posts_per_page' => count( $video_ids ),
				'post_status'    => 'publish',
			)
		);
		return array(
			'section_title'  => $section_title ?: __( 'Featured videos', 'jr-content-core' ),
			'playlist_name'  => null,
			'type'           => 'videos',
			'permalink'      => null,
			'yt_video_count' => count( $video_posts ),
			'videos'         => array_map( 'jr_content_core_format_video', $video_posts ),
		);
	}

	// Playlist mode.
	$playlist_id = absint( get_option( 'jr_sidebar_playlist_id', 0 ) );
	if ( ! $playlist_id ) {
		$recent = get_posts(
			array(
				'post_type'      => 'playlist',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);
		if ( empty( $recent ) ) {
			return null;
		}
		$playlist_id = $recent[0]->ID;
	}

	$pl = get_post( $playlist_id );
	if ( ! $pl || 'publish' !== $pl->post_status ) {
		return null;
	}

	$video_posts = get_posts(
		array(
			'post_type'      => 'video',
			'posts_per_page' => 10,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'   => 'wp_playlist_id',
					'value' => $playlist_id,
				),
			),
		)
	);

	return array(
		'section_title'  => $section_title ?: __( 'Featured playlist', 'jr-content-core' ),
		'playlist_name'  => get_the_title( $playlist_id ),
		'type'           => 'playlist',
		'permalink'      => get_permalink( $playlist_id ),
		'yt_video_count' => (int) get_post_meta( $playlist_id, 'yt_video_count', true ),
		'videos'         => array_map( 'jr_content_core_format_video', $video_posts ),
	);
}

/**
 * Normalises a video WP_Post into the shape consumed by Twig templates.
 *
 * @param WP_Post $v Video post object.
 * @return array
 */
function jr_content_core_format_video( $v ) {
	return array(
		'ID'               => $v->ID,
		'title'            => get_the_title( $v->ID ),
		'yt_video_id'      => get_post_meta( $v->ID, 'yt_video_id', true ),
		'yt_thumbnail_url' => get_post_meta( $v->ID, 'yt_thumbnail_url', true ),
		'yt_duration'      => get_post_meta( $v->ID, 'yt_duration', true ),
		'yt_view_count'    => (int) get_post_meta( $v->ID, 'yt_view_count', true ),
	);
}

// -------------------------------------------------------------------------
// Admin-only: settings page, assets, settings registration, ajax handlers
// -------------------------------------------------------------------------

add_action( 'admin_menu',             'jr_content_core_playlist_admin_menu' );
add_action( 'admin_enqueue_scripts',  'jr_content_core_playlist_admin_assets' );
add_action( 'admin_init',             'jr_content_core_register_playlist_settings' );
add_action( 'wp_ajax_jr_search_playlists', 'jr_content_core_ajax_search_playlists' );
add_action( 'wp_ajax_jr_search_videos',    'jr_content_core_ajax_search_videos' );

function jr_content_core_playlist_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=playlist',
		__( 'Playlist Settings', 'jr-content-core' ),
		__( 'Settings', 'jr-content-core' ),
		'manage_options',
		'jr-playlist-settings',
		'jr_content_core_render_playlist_settings_page'
	);
}

function jr_content_core_playlist_admin_assets( $hook ) {
	if ( 'playlist_page_jr-playlist-settings' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'jr-playlist-settings',
		JR_CONTENT_CORE_URL . 'assets/css/admin-playlist-settings.css',
		array(),
		JR_CONTENT_CORE_VERSION
	);

	wp_enqueue_script(
		'jr-playlist-settings',
		JR_CONTENT_CORE_URL . 'assets/js/admin-playlist-settings.js',
		array(),
		JR_CONTENT_CORE_VERSION,
		true
	);

	wp_localize_script(
		'jr-playlist-settings',
		'jrPlaylistSettings',
		array(
			'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
			'nonce'           => wp_create_nonce( 'jr_playlist_search' ),
			'pinnedPlaylists' => jr_content_core_saved_post_objects( get_option( 'jr_pinned_playlist_ids', '' ), 'playlist' ),
			'sidebarPlaylist' => jr_content_core_saved_post_objects( get_option( 'jr_sidebar_playlist_id', '' ), 'playlist' ),
			'sidebarVideos'   => jr_content_core_saved_post_objects( get_option( 'jr_sidebar_video_ids', '' ), 'video' ),
		)
	);
}

function jr_content_core_register_playlist_settings() {
	$settings = array(
		'jr_pinned_playlist_ids'    => 'jr_content_core_sanitize_id_list',
		'jr_sidebar_playlist_title' => 'sanitize_text_field',
		'jr_sidebar_source'         => 'jr_content_core_sanitize_sidebar_source',
		'jr_sidebar_playlist_id'    => 'jr_content_core_sanitize_single_id',
		'jr_sidebar_video_ids'      => 'jr_content_core_sanitize_id_list',
	);
	foreach ( $settings as $option => $cb ) {
		register_setting( 'jr_playlist_settings_group', $option, array( 'sanitize_callback' => $cb ) );
	}
}

function jr_content_core_sanitize_id_list( $raw ) {
	$ids = array_filter( array_map( 'absint', explode( ',', (string) $raw ) ) );
	return implode( ',', $ids );
}

function jr_content_core_sanitize_single_id( $raw ) {
	return (string) absint( $raw );
}

function jr_content_core_sanitize_sidebar_source( $raw ) {
	return in_array( $raw, array( 'playlist', 'videos' ), true ) ? $raw : 'playlist';
}

function jr_content_core_ajax_search_playlists() {
	check_ajax_referer( 'jr_playlist_search', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( '', '', array( 'response' => 403 ) );
	}
	$q = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );
	wp_send_json_success( jr_content_core_search_posts( 'playlist', $q ) );
}

function jr_content_core_ajax_search_videos() {
	check_ajax_referer( 'jr_playlist_search', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( '', '', array( 'response' => 403 ) );
	}
	$q = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );
	wp_send_json_success( jr_content_core_search_posts( 'video', $q ) );
}

function jr_content_core_search_posts( $post_type, $q ) {
	$posts = get_posts(
		array(
			'post_type'      => $post_type,
			'posts_per_page' => 10,
			's'              => $q,
			'post_status'    => 'publish',
			'orderby'        => $q ? 'relevance' : 'date',
			'order'          => 'DESC',
		)
	);
	return array_map(
		fn( $p ) => array( 'id' => $p->ID, 'text' => get_the_title( $p->ID ) ),
		$posts
	);
}

/**
 * Return [{id, text}] for a comma-separated string of saved post IDs.
 *
 * @param string $ids_string Comma-separated post IDs.
 * @param string $post_type  Post type for the query.
 * @return array
 */
function jr_content_core_saved_post_objects( $ids_string, $post_type ) {
	$ids = array_values( array_filter( array_map( 'absint', explode( ',', (string) $ids_string ) ) ) );
	if ( empty( $ids ) ) {
		return array();
	}
	$posts = get_posts(
		array(
			'post_type'      => $post_type,
			'post__in'       => $ids,
			'orderby'        => 'post__in',
			'posts_per_page' => count( $ids ),
			'post_status'    => 'any',
		)
	);
	return array_map(
		fn( $p ) => array( 'id' => $p->ID, 'text' => get_the_title( $p->ID ) ),
		$posts
	);
}

// -------------------------------------------------------------------------
// Settings page renderer
// -------------------------------------------------------------------------

function jr_content_core_render_playlist_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$source        = get_option( 'jr_sidebar_source', 'playlist' );
	$sidebar_title = get_option( 'jr_sidebar_playlist_title', '' );
	?>
	<div class="wrap jr-playlist-settings">
		<h1><?php esc_html_e( 'Playlist Settings', 'jr-content-core' ); ?></h1>

		<?php settings_errors( 'jr_playlist_settings_group' ); ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'jr_playlist_settings_group' ); ?>

			<h2 class="title"><?php esc_html_e( 'Home page playlists', 'jr-content-core' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Choose which playlists appear in the Playlists section, in order. Leave empty to automatically show the 3 most recent.', 'jr-content-core' ); ?>
			</p>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Featured playlists', 'jr-content-core' ); ?></label></th>
					<td>
						<?php jr_content_core_render_pill_picker( 'jr_pinned_playlist_ids', 'jr_search_playlists', true, get_option( 'jr_pinned_playlist_ids', '' ) ); ?>
					</td>
				</tr>
			</table>

			<hr>

			<h2 class="title"><?php esc_html_e( 'Sidebar playlist', 'jr-content-core' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Controls the playlist panel shown in the home page sidebar.', 'jr-content-core' ); ?>
			</p>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="jr_sidebar_playlist_title"><?php esc_html_e( 'Section title', 'jr-content-core' ); ?></label>
					</th>
					<td>
						<input
							type="text"
							id="jr_sidebar_playlist_title"
							name="jr_sidebar_playlist_title"
							class="regular-text"
							value="<?php echo esc_attr( $sidebar_title ); ?>"
							placeholder="<?php esc_attr_e( 'Featured playlist', 'jr-content-core' ); ?>"
						>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Show', 'jr-content-core' ); ?></th>
					<td>
						<label>
							<input type="radio" name="jr_sidebar_source" value="playlist" <?php checked( $source, 'playlist' ); ?>>
							<?php esc_html_e( 'A specific playlist', 'jr-content-core' ); ?>
						</label>
						<br>
						<label>
							<input type="radio" name="jr_sidebar_source" value="videos" <?php checked( $source, 'videos' ); ?>>
							<?php esc_html_e( 'Specific videos', 'jr-content-core' ); ?>
						</label>
					</td>
				</tr>
				<tr class="jr-source-row jr-source-row--playlist">
					<th scope="row"><label><?php esc_html_e( 'Playlist', 'jr-content-core' ); ?></label></th>
					<td>
						<?php jr_content_core_render_pill_picker( 'jr_sidebar_playlist_id', 'jr_search_playlists', false, get_option( 'jr_sidebar_playlist_id', '' ) ); ?>
						<p class="description"><?php esc_html_e( 'Leave empty to use the most recent playlist.', 'jr-content-core' ); ?></p>
					</td>
				</tr>
				<tr class="jr-source-row jr-source-row--videos">
					<th scope="row"><label><?php esc_html_e( 'Videos', 'jr-content-core' ); ?></label></th>
					<td>
						<?php jr_content_core_render_pill_picker( 'jr_sidebar_video_ids', 'jr_search_videos', true, get_option( 'jr_sidebar_video_ids', '' ) ); ?>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function jr_content_core_render_pill_picker( $field_name, $ajax_action, $multiple, $current_val ) {
	$placeholder = 'jr_search_playlists' === $ajax_action
		? __( 'Search playlists…', 'jr-content-core' )
		: __( 'Search videos…', 'jr-content-core' );
	?>
	<div class="jr-pill-picker"
		 data-action="<?php echo esc_attr( $ajax_action ); ?>"
		 data-multiple="<?php echo $multiple ? 'true' : 'false'; ?>">
		<div class="jr-pill-picker__pills"></div>
		<div class="jr-pill-picker__input-wrap">
			<input
				type="text"
				class="jr-pill-picker__search"
				placeholder="<?php echo esc_attr( $placeholder ); ?>"
				autocomplete="off"
			>
			<ul class="jr-pill-picker__dropdown" hidden></ul>
		</div>
		<input
			type="hidden"
			name="<?php echo esc_attr( $field_name ); ?>"
			class="jr-pill-picker__value"
			value="<?php echo esc_attr( $current_val ); ?>"
		>
	</div>
	<?php
}
