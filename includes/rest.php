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

	register_rest_route(
		'jr/v1',
		'/playlist/games-terms',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'jr_content_core_rest_playlists_games_terms',
			'permission_callback' => 'jr_content_core_rest_auth',
			'args'                => array(
				'modified_after' => array(
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => function ( $value ) {
						return false !== strtotime( $value );
					},
				),
				'per_page'       => array(
					'default'           => 100,
					'sanitize_callback' => 'absint',
					'validate_callback' => function ( $value ) {
						return $value >= 1 && $value <= 100;
					},
				),
				'page'           => array(
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
		'/video/by-playlist/(?P<playlist_id>\d+)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'jr_content_core_rest_videos_by_playlist',
			'permission_callback' => 'jr_content_core_rest_auth',
			'args'                => array(
				'playlist_id' => array(
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
		'/video/(?P<id>\d+)/set-games-terms',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'jr_content_core_rest_video_set_games_terms',
			'permission_callback' => 'jr_content_core_rest_auth',
			'args'                => array(
				'id'          => array(
					'sanitize_callback' => 'absint',
					'validate_callback' => function ( $value ) {
						return $value >= 1;
					},
				),
				'games_terms' => array(
					'required'          => true,
					'validate_callback' => function ( $value ) {
						if ( ! is_array( $value ) ) {
							return false;
						}
						foreach ( $value as $term_id ) {
							if ( ! is_numeric( $term_id ) || absint( $term_id ) < 1 ) {
								return false;
							}
						}
						return true;
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
				'post_status'    => 'any',
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
						'compare' => 'EXISTS',
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

function jr_content_core_rest_playlists_games_terms( WP_REST_Request $request ) {
	$per_page       = (int) $request->get_param( 'per_page' );
	$page           = (int) $request->get_param( 'page' );
	$modified_after = $request->get_param( 'modified_after' );

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
		'tax_query'      => array(
			array(
				'taxonomy' => 'games',
				'operator' => 'EXISTS',
			),
		),
	);

	if ( ! current_user_can( 'edit_others_posts' ) ) {
		$query_args['author'] = get_current_user_id();
	}

	if ( $modified_after ) {
		$query_args['date_query'] = array(
			array(
				'column' => 'post_modified_gmt',
				'after'  => $modified_after,
			),
		);
	}

	$query = new WP_Query( $query_args );

	$total        = (int) $query->found_posts;
	$total_pages  = (int) ceil( $total / $per_page );
	$playlist_ids = array();
	foreach ( $query->posts as $playlist_id ) {
		if ( current_user_can( 'edit_post', $playlist_id ) ) {
			$playlist_ids[] = (int) $playlist_id;
		}
	}

	$terms_by_playlist = array();
	if ( ! empty( $playlist_ids ) ) {
		$all_terms = wp_get_object_terms( $playlist_ids, 'games', array( 'fields' => 'all_with_object_id' ) );
		if ( ! is_wp_error( $all_terms ) ) {
			foreach ( $all_terms as $term ) {
				$terms_by_playlist[ (int) $term->object_id ][] = (int) $term->term_id;
			}
		}
	}

	$items = array();
	foreach ( $playlist_ids as $playlist_id ) {
		$items[] = array(
			'playlist_id' => $playlist_id,
			'games_terms' => isset( $terms_by_playlist[ $playlist_id ] ) ? $terms_by_playlist[ $playlist_id ] : array(),
		);
	}

	$response = rest_ensure_response( $items );
	$response->header( 'X-WP-Total', $total );
	$response->header( 'X-WP-TotalPages', $total_pages );

	return $response;
}

function jr_content_core_rest_videos_by_playlist( WP_REST_Request $request ) {
	$playlist_id = (int) $request->get_param( 'playlist_id' );

	$post = get_post( $playlist_id );
	if ( ! $post || ! current_user_can( 'edit_post', $playlist_id ) ) {
		return new WP_Error(
			'rest_forbidden',
			__( 'You do not have permission to access this playlist.', 'jr-content-core' ),
			array( 'status' => 403 )
		);
	}

	$post_statuses = array( 'publish', 'draft', 'pending', 'future' );
	if ( current_user_can( 'edit_private_posts' ) ) {
		$post_statuses[] = 'private';
	}

	$query_args = array(
		'post_type'      => 'video',
		'post_status'    => $post_statuses,
		'posts_per_page' => -1,
		'no_found_rows'  => true,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'     => 'wp_playlist_id',
				'value'   => $playlist_id,
				'compare' => '=',
				'type'    => 'NUMERIC',
			),
		),
	);

	if ( ! current_user_can( 'edit_others_posts' ) ) {
		$query_args['author'] = get_current_user_id();
	}

	$query     = new WP_Query( $query_args );
	$video_ids = array();
	foreach ( $query->posts as $video_id ) {
		if ( current_user_can( 'edit_post', $video_id ) ) {
			$video_ids[] = (int) $video_id;
		}
	}

	if ( ! empty( $video_ids ) ) {
		update_meta_cache( 'post', $video_ids );
	}

	$terms_by_video = array();
	if ( ! empty( $video_ids ) ) {
		$all_terms = wp_get_object_terms( $video_ids, 'games', array( 'fields' => 'all_with_object_id' ) );
		if ( ! is_wp_error( $all_terms ) ) {
			foreach ( $all_terms as $term ) {
				$terms_by_video[ (int) $term->object_id ][] = (int) $term->term_id;
			}
		}
	}

	$items = array();
	foreach ( $video_ids as $video_id ) {
		$items[] = array(
			'video_id'            => $video_id,
			'current_games_terms' => isset( $terms_by_video[ $video_id ] ) ? $terms_by_video[ $video_id ] : array(),
		);
	}

	return rest_ensure_response( $items );
}

function jr_content_core_rest_video_set_games_terms( WP_REST_Request $request ) {
	$video_id    = (int) $request->get_param( 'id' );
	$games_terms = $request->get_param( 'games_terms' );

	$post = get_post( $video_id );
	if ( ! $post || 'video' !== $post->post_type ) {
		return new WP_Error(
			'rest_not_found',
			__( 'Video not found.', 'jr-content-core' ),
			array( 'status' => 404 )
		);
	}

	if ( ! current_user_can( 'edit_post', $video_id ) ) {
		return new WP_Error(
			'rest_forbidden',
			__( 'You do not have permission to edit this video.', 'jr-content-core' ),
			array( 'status' => 403 )
		);
	}

	$term_ids = array_map( 'absint', (array) $games_terms );
	$result   = wp_set_object_terms( $video_id, $term_ids, 'games' );

	if ( is_wp_error( $result ) ) {
		return $result;
	}

	$updated_terms = wp_get_object_terms( $video_id, 'games', array( 'fields' => 'ids' ) );
	$updated_ids   = is_wp_error( $updated_terms ) ? array() : array_map( 'intval', $updated_terms );

	return rest_ensure_response(
		array(
			'video_id'        => $video_id,
			'games_terms_set' => $updated_ids,
		)
	);
}
