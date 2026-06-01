<?php
/**
 * Playlist frontend data providers.
 *
 * Registers `jr_home_playlists` and `jr_sidebar_playlist` filters so the
 * active theme can pull structured playlist/video data into its template
 * context without depending on any specific theme implementation.
 *
 * Loaded on every request (frontend + admin).
 *
 * @package JRContentCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'jr_home_playlists', 'jr_content_core_home_playlists', 10, 0 );
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

	$home_playlists = array();
	foreach ( $playlist_posts as $pl ) {
		$video_posts = get_posts(
			array(
				'post_type'      => 'video',
				'posts_per_page' => 20,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key'   => 'wp_playlist_id',
						'value' => $pl->ID,
					),
				),
			)
		);

		$home_playlists[] = array(
			'ID'               => $pl->ID,
			'title'            => get_the_title( $pl->ID ),
			'permalink'        => get_permalink( $pl->ID ),
			'yt_playlist_id'   => get_post_meta( $pl->ID, 'yt_playlist_id', true ),
			'yt_thumbnail_url' => get_post_meta( $pl->ID, 'yt_thumbnail_url', true ),
			'yt_video_count'   => (int) get_post_meta( $pl->ID, 'yt_video_count', true ),
			'videos'           => array_map( 'jr_content_core_format_video', $video_posts ),
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
			'section_title'  => $section_title ? $section_title : __( 'Featured videos', 'jr-content-core' ),
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
		'section_title'  => $section_title ? $section_title : __( 'Featured playlist', 'jr-content-core' ),
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
