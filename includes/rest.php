<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jr_content_core_register_rest_routes() {
	register_rest_route(
		'jr/v1',
		'/video/without-thumbnails',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'jr_content_core_rest_videos_without_thumbnails',
			'permission_callback' => 'jr_content_core_rest_auth',
			'args'                => array(
				'per_page' => array(
					'default'           => 100,
					'sanitize_callback' => 'absint',
					'validate_callback' => function ( $value ) {
						return $value >= 1 && $value <= 100;
					},
				),
				'page'     => array(
					'default'           => 1,
					'sanitize_callback' => 'absint',
					'validate_callback' => function ( $value ) {
						return $value >= 1;
					},
				),
			),
		)
	);

	register_rest_route(
		'jr/v1',
		'/playlist/without-thumbnails',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'jr_content_core_rest_playlists_without_thumbnails',
			'permission_callback' => 'jr_content_core_rest_auth',
			'args'                => array(
				'per_page' => array(
					'default'           => 100,
					'sanitize_callback' => 'absint',
					'validate_callback' => function ( $value ) {
						return $value >= 1 && $value <= 100;
					},
				),
				'page'     => array(
					'default'           => 1,
					'sanitize_callback' => 'absint',
					'validate_callback' => function ( $value ) {
						return $value >= 1;
					},
				),
			),
		)
	);
}

function jr_content_core_rest_auth() {
	return current_user_can( 'edit_posts' );
}

function jr_content_core_rest_videos_without_thumbnails( WP_REST_Request $request ) {
	$per_page = (int) $request->get_param( 'per_page' );
	$page     = (int) $request->get_param( 'page' );

	$post_statuses = array( 'publish', 'draft', 'pending', 'future' );
	if ( current_user_can( 'edit_private_posts' ) ) {
		$post_statuses[] = 'private';
	}

	$query_args = array(
		'post_type'      => 'video',
		'post_status'    => $post_statuses,
		'posts_per_page' => $per_page,
		'paged'          => $page,
		'fields'         => 'ids',
		'meta_query'     => array(
			'relation' => 'OR',
			array(
				'key'     => '_thumbnail_id',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_thumbnail_id',
				'value'   => array( '', '0' ),
				'compare' => 'IN',
			),
		),
	);

	if ( ! current_user_can( 'edit_others_posts' ) ) {
		$query_args['author'] = get_current_user_id();
	}

	$query = new WP_Query( $query_args );

	$total       = (int) $query->found_posts;
	$total_pages = (int) ceil( $total / $per_page );

	$items = array();
	foreach ( $query->posts as $post_id ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			continue;
		}
		$post    = get_post( $post_id );
		$items[] = array(
			'id'               => (int) $post_id,
			'slug'             => $post->post_name,
			'yt_thumbnail_url' => (string) get_post_meta( $post_id, 'yt_thumbnail_url', true ),
		);
	}

	$response = rest_ensure_response( $items );
	$response->header( 'X-WP-Total', $total );
	$response->header( 'X-WP-TotalPages', $total_pages );

	return $response;
}

function jr_content_core_rest_playlists_without_thumbnails( WP_REST_Request $request ) {
	$per_page = (int) $request->get_param( 'per_page' );
	$page     = (int) $request->get_param( 'page' );

	$post_statuses = array( 'publish', 'draft', 'pending', 'future' );
	if ( current_user_can( 'edit_private_posts' ) ) {
		$post_statuses[] = 'private';
	}

	$query_args = array(
		'post_type'      => 'playlist',
		'post_status'    => $post_statuses,
		'posts_per_page' => $per_page,
		'paged'          => $page,
		'fields'         => 'ids',
		'meta_query'     => array(
			'relation' => 'OR',
			array(
				'key'     => '_thumbnail_id',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_thumbnail_id',
				'value'   => array( '', '0' ),
				'compare' => 'IN',
			),
		),
	);

	if ( ! current_user_can( 'edit_others_posts' ) ) {
		$query_args['author'] = get_current_user_id();
	}

	$query = new WP_Query( $query_args );

	$total       = (int) $query->found_posts;
	$total_pages = (int) ceil( $total / $per_page );

	$playlist_ids = array();
	foreach ( $query->posts as $playlist_id ) {
		if ( current_user_can( 'edit_post', $playlist_id ) ) {
			$playlist_ids[] = (int) $playlist_id;
		}
	}

	$items = array();

	if ( ! empty( $playlist_ids ) ) {
		$video_query = new WP_Query(
			array(
				'post_type'      => 'video',
				'post_status'    => $post_statuses,
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'ASC',
				'no_found_rows'  => true,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => 'wp_playlist_id',
						'value'   => $playlist_ids,
						'compare' => 'IN',
						'type'    => 'NUMERIC',
					),
					array(
						'key'     => '_thumbnail_id',
						'value'   => '0',
						'compare' => '>',
						'type'    => 'NUMERIC',
					),
				),
			)
		);

		$video_ids = array_map( 'intval', $video_query->posts );

		update_meta_cache( 'post', $video_ids );
		update_meta_cache( 'post', $playlist_ids );

		// Build playlist_id => oldest qualifying video_id map (query is ASC so first match wins).
		$video_by_playlist = array();
		foreach ( $video_ids as $video_id ) {
			$pid = (int) get_post_meta( $video_id, 'wp_playlist_id', true );
			if ( $pid && ! isset( $video_by_playlist[ $pid ] ) ) {
				$video_by_playlist[ $pid ] = $video_id;
			}
		}

		foreach ( $playlist_ids as $playlist_id ) {
			if ( ! isset( $video_by_playlist[ $playlist_id ] ) ) {
				continue;
			}
			$video_id = $video_by_playlist[ $playlist_id ];
			if ( ! current_user_can( 'edit_post', $video_id ) ) {
				continue;
			}
			$items[] = array(
				'id'                         => $playlist_id,
				'yt_playlist_id'             => (string) get_post_meta( $playlist_id, 'yt_playlist_id', true ),
				'first_video_featured_media' => (int) get_post_meta( $video_id, '_thumbnail_id', true ),
			);
		}
	}

	$response = rest_ensure_response( $items );
	$response->header( 'X-WP-Total', $total );
	$response->header( 'X-WP-TotalPages', $total_pages );

	return $response;
}
