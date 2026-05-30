<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jr_content_core_register_rest_routes() {
	register_rest_route(
		'jr/v1',
		'/playlists/by-yt-id/(?P<yt_playlist_id>[a-zA-Z0-9_-]+)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'jr_content_core_rest_playlist_by_yt_id',
			'permission_callback' => 'jr_content_core_rest_auth',
			'args'                => array(
				'yt_playlist_id' => array(
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}

function jr_content_core_rest_auth() {
	return current_user_can( 'edit_posts' );
}

function jr_content_core_rest_playlist_by_yt_id( WP_REST_Request $request ) {
	$yt_playlist_id = $request->get_param( 'yt_playlist_id' );

	$query = new WP_Query(
		array(
			'post_type'      => 'playlist',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
			'meta_query'     => array(
				array(
					'key'   => 'yt_playlist_id',
					'value' => $yt_playlist_id,
				),
			),
		)
	);

	if ( empty( $query->posts ) ) {
		return new WP_Error( 'not_found', __( 'No playlist found with that YouTube ID.', 'jr-content-core' ), array( 'status' => 404 ) );
	}

	$post_id = (int) $query->posts[0]->ID;

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to access this playlist.', 'jr-content-core' ), array( 'status' => 403 ) );
	}

	return rest_ensure_response( array( 'post_id' => $post_id ) );
}
