<?php

if (!defined('ABSPATH')) {
    exit;
}

function jr_content_core_register_taxonomies()
{
    register_taxonomy('games', array('post', 'review', 'playlist', 'game', 'character'), array(
        'labels' => array(
            'name'                       => __('Games', 'jr-content-core'),
            'singular_name'              => __('Game', 'jr-content-core'),
            'menu_name'                  => __('Games', 'jr-content-core'),
            'all_items'                  => __('All Games', 'jr-content-core'),
            'edit_item'                  => __('Edit Game', 'jr-content-core'),
            'view_item'                  => __('View Game', 'jr-content-core'),
            'update_item'                => __('Update Game', 'jr-content-core'),
            'add_new_item'               => __('Add New Game', 'jr-content-core'),
            'new_item_name'              => __('New Game Name', 'jr-content-core'),
            'search_items'               => __('Search Games', 'jr-content-core'),
            'not_found'                  => __('No games found.', 'jr-content-core'),
        ),
        'hierarchical'      => true,
        'public'            => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => array('slug' => 'game'),
    ));

    register_taxonomy('characters', array('post', 'review'), array(
        'labels' => array(
            'name'                       => __('Characters', 'jr-content-core'),
            'singular_name'              => __('Character', 'jr-content-core'),
            'menu_name'                  => __('Characters', 'jr-content-core'),
            'all_items'                  => __('All Characters', 'jr-content-core'),
            'edit_item'                  => __('Edit Character', 'jr-content-core'),
            'view_item'                  => __('View Character', 'jr-content-core'),
            'update_item'                => __('Update Character', 'jr-content-core'),
            'add_new_item'               => __('Add New Character', 'jr-content-core'),
            'new_item_name'              => __('New Character Name', 'jr-content-core'),
            'search_items'               => __('Search Characters', 'jr-content-core'),
            'not_found'                  => __('No characters found.', 'jr-content-core'),
        ),
        'hierarchical'      => true,
        'public'            => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => array('slug' => 'character'),
    ));

    register_taxonomy('platform', array('post', 'review', 'playlist'), array(
        'labels' => array(
            'name'          => __('Platforms', 'jr-content-core'),
            'singular_name' => __('Platform', 'jr-content-core'),
            'menu_name'     => __('Platform', 'jr-content-core'),
        ),
        'hierarchical'  => true,
        'public'        => true,
        'show_ui'       => true,
        'show_in_rest'  => true,
        'rewrite'       => array('slug' => 'platform'),
    ));
}
