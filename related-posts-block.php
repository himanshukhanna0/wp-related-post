<?php
/**
 * Plugin Name: Related Posts Block
 * Description: Outputs 3 recent related posts from the same category. Supports a shortcode and a Gutenberg block.
 * Version: 1.0
 * Author: Your Name
 * Text Domain: related-posts-block
 */

if (!defined('ABSPATH')) exit; // Just a safety check — stops someone from loading this file directly in the browser.

// ------------------------------------------------------
// Load our CSS file for styling the related posts box.
// ------------------------------------------------------
function rpb_enqueue_assets() {
    wp_enqueue_style(
        'rpb-style',
        plugin_dir_url(__FILE__) . 'style.css', // Where the CSS file is
        [], // No dependencies
        '1.0' // Version number
    );
}
add_action('wp_enqueue_scripts', 'rpb_enqueue_assets');

// ------------------------------------------------------------------
// This function handles all the logic for fetching and displaying 
// 3 related posts from the same category as the current post.
// ------------------------------------------------------------------
function rpb_get_related_posts_html($title = '') {
    // Only show this on single post pages — no need on home, archive, etc.
    if (!is_single()) return '';

    global $post;
    $post_id = $post->ID;

    // Grab the categories this post belongs to
    $categories = get_the_category($post_id);
    if (empty($categories)) return ''; // If there are no categories, nothing to relate

    // Get only the category IDs into an array
    $category_ids = wp_list_pluck($categories, 'term_id');
    sort($category_ids); // Helps keep our cache key consistent

    // Create a unique cache key using the post ID + category IDs
    $cache_key = 'rpb_related_' . $post_id . '_' . md5(implode('_', $category_ids));

    // If we already have the result cached, just return that
    $cached = get_transient($cache_key);
    if ($cached !== false) return $cached;

    // No cache? Let’s run the query to find related posts
    $query = new WP_Query([
        'post_type'           => 'post',
        'posts_per_page'      => 3,
        'post__not_in'        => [$post_id], // Don’t include the current post
        'category__in'        => $category_ids,
        'orderby'             => 'date', // Show most recent ones first
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
    ]);

    // No posts found? We’re done.
    if (!$query->have_posts()) return '';

    // Start capturing the HTML output
    ob_start();

    echo '<div class="rpb-related-posts">';

    // If the user gave us a title, show it
    if (!empty($title)) {
        echo '<h3>' . esc_html($title) . '</h3>';
    }

    echo '<ul>';

    // Loop through the results and output the list
    while ($query->have_posts()) {
        $query->the_post();
        echo '<li><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
    }

    echo '</ul>';
    echo '</div>';

    wp_reset_postdata(); // Always reset after a custom loop

    $output = ob_get_clean();

    // Save this output in a transient so it loads faster next time
    set_transient($cache_key, $output, HOUR_IN_SECONDS);

    return $output;
}

// --------------------------------------------------------
// This makes the [related_posts title="..."] shortcode work.
// --------------------------------------------------------
function rpb_related_posts_shortcode($atts) {
    $atts = shortcode_atts(['title' => ''], $atts, 'related_posts');
    return rpb_get_related_posts_html($atts['title']);
}
add_shortcode('related_posts', 'rpb_related_posts_shortcode');

// --------------------------------------------------------
// This is where we register the Gutenberg block.
// --------------------------------------------------------
function rpb_register_block() {
    // Let’s make sure Gutenberg functions are available
    if (!function_exists('register_block_type')) return;

    // Register the JS file used in the block editor
    wp_register_script(
        'rpb-block-editor',
        plugin_dir_url(__FILE__) . 'block.js',
        ['wp-blocks', 'wp-element', 'wp-editor'],
        filemtime(__FILE__) // Use the file time to avoid caching issues
    );

    // Register the block with WordPress
    register_block_type('rpb/related-posts', [
        'editor_script'   => 'rpb-block-editor', // Load our JS file in the editor
        'render_callback' => 'rpb_render_block', // Render using PHP (not saved HTML)
        'attributes'      => [
            'title' => [
                'type'    => 'string',
                'default' => '',
            ],
        ],
    ]);
}
add_action('init', 'rpb_register_block');

// --------------------------------------------------------
// This is the function that renders the block on the front end.
// --------------------------------------------------------
function rpb_render_block($attributes) {
    $title = isset($attributes['title']) ? sanitize_text_field($attributes['title']) : '';
    return rpb_get_related_posts_html($title);
}
