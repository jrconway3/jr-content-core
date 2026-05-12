<?php

if (!defined('ABSPATH')) {
    exit;
}

function jr_content_core_post_types()
{
    return array('review', 'playlist');
}

function jr_content_core_taxonomies()
{
    return array('games', 'characters', 'platform');
}

function jr_content_core_sanitize_string($value)
{
    if (is_array($value) || is_object($value)) {
        return '';
    }

    return sanitize_text_field((string) $value);
}

function jr_content_core_sanitize_boolean($value)
{
    return empty($value) ? false : true;
}

function jr_content_core_sanitize_rating($value)
{
    // Allow clearing/unsetting the rating by returning empty string for empty input
    if (empty($value) || $value === '') {
        return '';
    }

    $value = (float) $value;
    if ($value < 0) {
        return 0.0;
    }
    if ($value > 10) {
        return 10.0;
    }
    return round($value, 1);
}

function jr_content_core_sanitize_video_source($value)
{
    $value = jr_content_core_sanitize_string($value);
    $allowed = array('youtube', 'vimeo');

    if ($value === '') {
        return '';
    }

    return in_array($value, $allowed, true) ? $value : '';
}

function jr_content_core_sanitize_feature_position($value)
{
    $value = jr_content_core_sanitize_string($value);
    $allowed = array('full', 'left', 'right', 'gantry');

    if ($value === '') {
        return '';
    }

    return in_array($value, $allowed, true) ? $value : 'full';
}

function jr_content_core_register_single_meta(array $post_types, $meta_key, array $args)
{
    foreach ($post_types as $post_type) {
        register_post_meta($post_type, $meta_key, $args);
    }
}

function jr_content_core_supports_post_type($post_type)
{
    return in_array($post_type, jr_content_core_post_types(), true);
}
