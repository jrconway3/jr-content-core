<?php

if (!defined('ABSPATH')) {
    exit;
}

function jr_content_core_register_post_types()
{
    register_post_type('portfolio', array(
        'labels' => array(
            'name' => __('Portfolio', 'jr-content-core'),
            'singular_name' => __('Portfolio', 'jr-content-core'),
            'menu_name' => __('Portfolio', 'jr-content-core'),
            'add_new_item' => __('Add New Portfolio Item', 'jr-content-core'),
            'edit_item' => __('Edit Portfolio Item', 'jr-content-core'),
            'new_item' => __('New Portfolio Item', 'jr-content-core'),
            'view_item' => __('View Portfolio Item', 'jr-content-core'),
            'search_items' => __('Search Portfolio', 'jr-content-core'),
            'not_found' => __('No portfolio items found.', 'jr-content-core'),
            'not_found_in_trash' => __('No portfolio items found in Trash.', 'jr-content-core'),
        ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'portfolio'),
        'supports' => array('title', 'editor', 'thumbnail', 'author', 'revisions'),
        'menu_icon' => 'dashicons-portfolio',
    ));

    register_post_type('game', array(
        'labels' => array(
            'name' => __('Games', 'jr-content-core'),
            'singular_name' => __('Game', 'jr-content-core'),
            'menu_name' => __('Games', 'jr-content-core'),
            'add_new_item' => __('Add New Game', 'jr-content-core'),
            'edit_item' => __('Edit Game', 'jr-content-core'),
            'new_item' => __('New Game', 'jr-content-core'),
            'view_item' => __('View Game', 'jr-content-core'),
            'search_items' => __('Search Games', 'jr-content-core'),
            'not_found' => __('No games found.', 'jr-content-core'),
            'not_found_in_trash' => __('No games found in Trash.', 'jr-content-core'),
        ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'games'),
        'supports' => array('title', 'editor', 'thumbnail', 'author', 'revisions'),
        'menu_icon' => 'dashicons-games',
    ));

    register_post_type('character', array(
        'labels' => array(
            'name' => __('Characters', 'jr-content-core'),
            'singular_name' => __('Character', 'jr-content-core'),
            'menu_name' => __('Characters', 'jr-content-core'),
            'add_new_item' => __('Add New Character', 'jr-content-core'),
            'edit_item' => __('Edit Character', 'jr-content-core'),
            'new_item' => __('New Character', 'jr-content-core'),
            'view_item' => __('View Character', 'jr-content-core'),
            'search_items' => __('Search Characters', 'jr-content-core'),
            'not_found' => __('No characters found.', 'jr-content-core'),
            'not_found_in_trash' => __('No characters found in Trash.', 'jr-content-core'),
        ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'characters'),
        'supports' => array('title', 'editor', 'thumbnail', 'author', 'revisions'),
        'menu_icon' => 'dashicons-universal-access-alt',
    ));

    register_post_type('review', array(
        'labels' => array(
            'name' => __('Reviews', 'jr-content-core'),
            'singular_name' => __('Review', 'jr-content-core'),
            'menu_name' => __('Reviews', 'jr-content-core'),
            'add_new_item' => __('Add New Review', 'jr-content-core'),
            'edit_item' => __('Edit Review', 'jr-content-core'),
            'new_item' => __('New Review', 'jr-content-core'),
            'view_item' => __('View Review', 'jr-content-core'),
            'search_items' => __('Search Reviews', 'jr-content-core'),
            'not_found' => __('No reviews found.', 'jr-content-core'),
            'not_found_in_trash' => __('No reviews found in Trash.', 'jr-content-core'),
        ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'reviews'),
        'supports' => array('title', 'editor', 'thumbnail', 'author', 'revisions'),
        'menu_icon' => 'dashicons-star-filled',
    ));
}
