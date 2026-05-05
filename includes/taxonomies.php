<?php

if (!defined('ABSPATH')) {
    exit;
}

function jr_content_core_register_taxonomies()
{
    register_taxonomy('company', array('character', 'game'), array(
        'labels' => array(
            'name' => __('Companies', 'jr-content-core'),
            'singular_name' => __('Company', 'jr-content-core'),
            'menu_name' => __('Company', 'jr-content-core'),
        ),
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'company'),
    ));

    register_taxonomy('franchise', array('character', 'game'), array(
        'labels' => array(
            'name' => __('Franchises', 'jr-content-core'),
            'singular_name' => __('Franchise', 'jr-content-core'),
            'menu_name' => __('Franchise', 'jr-content-core'),
        ),
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'franchise'),
    ));

    register_taxonomy('alliance', array('character'), array(
        'labels' => array(
            'name' => __('Alliances', 'jr-content-core'),
            'singular_name' => __('Alliance', 'jr-content-core'),
            'menu_name' => __('Alliance', 'jr-content-core'),
        ),
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'alliance'),
    ));

    register_taxonomy('genre', array('game'), array(
        'labels' => array(
            'name' => __('Genres', 'jr-content-core'),
            'singular_name' => __('Genre', 'jr-content-core'),
            'menu_name' => __('Genre', 'jr-content-core'),
        ),
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'genre'),
    ));

    register_taxonomy('platform', array('post', 'page', 'game', 'review'), array(
        'labels' => array(
            'name' => __('Platforms', 'jr-content-core'),
            'singular_name' => __('Platform', 'jr-content-core'),
            'menu_name' => __('Platform', 'jr-content-core'),
        ),
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'platform'),
    ));

    register_taxonomy('progression', array('game', 'review'), array(
        'labels' => array(
            'name' => __('Progression', 'jr-content-core'),
            'singular_name' => __('Progression', 'jr-content-core'),
            'menu_name' => __('Progression', 'jr-content-core'),
        ),
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'progression'),
    ));
}
