<?php
/**
 * Playlist admin settings page, AJAX search handlers, and settings registration.
 *
 * Provides:
 *  - "Settings" sub-page under the Playlist CPT admin menu
 *  - Searchable pill-picker UI for choosing featured/sidebar playlists
 *
 * Loaded only when is_admin() is true (covers both wp-admin pages and
 * admin-ajax.php requests).
 *
 * Frontend data filters live in includes/playlist-data.php.
 *
 * @package JRContentCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'jr_content_core_playlist_admin_menu' );
add_action( 'admin_enqueue_scripts', 'jr_content_core_playlist_admin_assets' );
add_action( 'admin_init', 'jr_content_core_register_playlist_settings' );
add_action( 'wp_ajax_jr_search_playlists', 'jr_content_core_ajax_search_playlists' );
add_action( 'wp_ajax_jr_search_videos', 'jr_content_core_ajax_search_videos' );

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
			'i18n'            => array(
				/* translators: %s: playlist or video title */
				'remove' => __( 'Remove %s', 'jr-content-core' ),
			),
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
		'jr_sidebar_video_limit'    => 'jr_content_core_sanitize_video_limit',
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
	return in_array( $raw, array( 'most_recent', 'playlist', 'videos' ), true ) ? $raw : 'most_recent';
}

function jr_content_core_sanitize_video_limit( $raw ) {
	$val = absint( $raw );
	return ( $val >= 1 && $val <= 20 ) ? $val : 5;
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
		function ( $p ) {
			return array(
				'id'   => $p->ID,
				'text' => get_the_title( $p->ID ),
			);
		},
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
		function ( $p ) {
			return array(
				'id'   => $p->ID,
				'text' => get_the_title( $p->ID ),
			);
		},
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
							<input type="radio" name="jr_sidebar_source" value="most_recent" <?php checked( $source, 'most_recent' ); ?>>
							<?php esc_html_e( 'Most recent videos', 'jr-content-core' ); ?>
						</label>
						<br>
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
				<tr class="jr-source-row jr-source-row--count">
					<th scope="row">
						<label for="jr_sidebar_video_limit"><?php esc_html_e( 'Number of videos', 'jr-content-core' ); ?></label>
					</th>
					<td>
						<input
							type="number"
							id="jr_sidebar_video_limit"
							name="jr_sidebar_video_limit"
							value="<?php echo esc_attr( get_option( 'jr_sidebar_video_limit', 5 ) ); ?>"
							min="1"
							max="20"
							class="small-text"
						>
						<p class="description"><?php esc_html_e( 'How many videos to display (1–20). Not used for Specific videos mode.', 'jr-content-core' ); ?></p>
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
