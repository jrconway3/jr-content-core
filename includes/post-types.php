<?php

if (!defined('ABSPATH')) {
    exit;
}

function jr_content_core_register_post_types()
{
    register_post_type('review', array(
        'labels' => array(
            'name'                  => __('Reviews', 'jr-content-core'),
            'singular_name'         => __('Review', 'jr-content-core'),
            'menu_name'             => __('Reviews', 'jr-content-core'),
            'add_new_item'          => __('Add New Review', 'jr-content-core'),
            'edit_item'             => __('Edit Review', 'jr-content-core'),
            'new_item'              => __('New Review', 'jr-content-core'),
            'view_item'             => __('View Review', 'jr-content-core'),
            'search_items'          => __('Search Reviews', 'jr-content-core'),
            'not_found'             => __('No reviews found.', 'jr-content-core'),
            'not_found_in_trash'    => __('No reviews found in Trash.', 'jr-content-core'),
        ),
        'public'        => true,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'show_in_rest'  => true,
        'has_archive'   => true,
        'rewrite'       => array('slug' => 'reviews'),
        'supports'      => array('title', 'editor', 'thumbnail', 'author', 'revisions'),
        'menu_icon'     => 'dashicons-star-filled',
    ));

    register_post_type('playlist', array(
        'labels' => array(
            'name'                  => __('Playlists', 'jr-content-core'),
            'singular_name'         => __('Playlist', 'jr-content-core'),
            'menu_name'             => __('Playlists', 'jr-content-core'),
            'add_new_item'          => __('Add New Playlist', 'jr-content-core'),
            'edit_item'             => __('Edit Playlist', 'jr-content-core'),
            'new_item'              => __('New Playlist', 'jr-content-core'),
            'view_item'             => __('View Playlist', 'jr-content-core'),
            'search_items'          => __('Search Playlists', 'jr-content-core'),
            'not_found'             => __('No playlists found.', 'jr-content-core'),
            'not_found_in_trash'    => __('No playlists found in Trash.', 'jr-content-core'),
        ),
        'public'        => true,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'show_in_rest'  => true,
        'has_archive'   => true,
        'rewrite'       => array('slug' => 'playlists'),
        'supports'      => array('title', 'editor', 'thumbnail', 'author', 'revisions'),
        'menu_icon'     => 'dashicons-playlist-video',
    ));
}
