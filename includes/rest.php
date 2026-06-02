<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jr_content_core_register_rest_routes() {
	register_rest_route(
		'jr/v1',
		'/videos/without-thumbnails',
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
}

function jr_content_core_rest_auth() {
	return current_user_can( 'edit_posts' );
}

function jr_content_core_rest_videos_without_thumbnails( WP_REST_Request $request ) {
	$per_page = $request->get_param( 'per_page' ) ?: 100;
	$page     = $request->get_param( 'page' ) ?: 1;

	$query = new WP_Query(
		array(
			'post_type'      => 'video',
			'post_status'    => 'any',
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
		)
	);

	$total       = (int) $query->found_posts;
	$total_pages = (int) ceil( $total / $per_page );

	$items = array();
	foreach ( $query->posts as $post_id ) {
		$items[] = array(
			'id'               => (int) $post_id,
			'yt_thumbnail_url' => (string) get_post_meta( $post_id, 'yt_thumbnail_url', true ),
		);
	}

	$response = rest_ensure_response( $items );
	$response->header( 'X-WP-Total', $total );
	$response->header( 'X-WP-TotalPages', $total_pages );

	return $response;
}
