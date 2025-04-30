<?php
/**
 * Plugin Name: Related Posts Block
 * Description: Shows 3 related posts from the same category. Use via a shortcode [related_posts] or Gutenberg block.
 * Version: 1.3
 * Author: Your Name
 * Text Domain: related-posts-block
 */

if (!defined('ABSPATH')) exit; // Don't run if accessed directly

/**
 * Enqueue plugin stylesheet on the frontend
 */
function rpb_enqueue_assets() {
    wp_enqueue_style(
        'rpb-style',
        plugin_dir_url(__FILE__) . 'style.css',
        [],
        '1.3'
    );
}
add_action('wp_enqueue_scripts', 'rpb_enqueue_assets');

/**
 * Add admin submenu under Tools to clear the related posts cache
 */
function rpb_register_admin_menu() {
    add_submenu_page(
        'tools.php',
        'Related Posts Cache',
        'Related Posts Cache',
        'manage_options',
        'rpb-clear-cache',
        'rpb_cache_admin_page'
    );
}
add_action('admin_menu', 'rpb_register_admin_menu');

/**
 * Admin page UI for clearing transients
 */
function rpb_cache_admin_page() {
    // If the form was submitted and nonce is valid
    if (isset($_POST['rpb_clear_cache']) && check_admin_referer('rpb_clear_cache_action')) {
        global $wpdb;
        // Delete cached related posts transients
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_rpb_related_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_rpb_related_%'");
        echo '<div class="notice notice-success is-dismissible"><p>Related posts cache cleared.</p></div>';
    }

    // Output form
    echo '<div class="wrap"><h1>Related Posts Cache</h1>';
    echo '<form method="POST">';
    wp_nonce_field('rpb_clear_cache_action'); // Security nonce
    submit_button('Clear Related Posts Cache', 'primary', 'rpb_clear_cache');
    echo '</form></div>';
}

/**
 * Generate HTML for related posts
 */
function rpb_get_related_posts_html($title = '') {
    if (!is_single() || (defined('REST_REQUEST') && REST_REQUEST)) return '';

    global $post;
    $post_id = $post->ID;

    // Get current post categories
    $categories = get_the_category($post_id);
    if (empty($categories)) return '';

    $category_ids = wp_list_pluck($categories, 'term_id');
    sort($category_ids); // Ensure consistent cache key
    $cache_key = 'rpb_related_' . $post_id . '_' . md5(implode('_', $category_ids));

    // Check if we should bypass cache (for debugging)
    $debug = isset($_GET['rpb_debug']) && $_GET['rpb_debug'] === '1';

    if (!$debug) {
        $cached = get_transient($cache_key);
        if ($cached !== false) return $cached;
    }

    // Query for 3 recent posts in the same category, excluding current post
    $query = new WP_Query([
        'post_type' => 'post',
        'posts_per_page' => 3,
        'post__not_in' => [$post_id],
        'category__in' => $category_ids,
        'orderby' => 'date',
        'order' => 'DESC',
        'ignore_sticky_posts' => true,
    ]);

    if (!$query->have_posts()) {
        wp_reset_postdata();
        return '';
    }

    // Build the output HTML
    ob_start();
    echo '<div class="rpb-related-posts">';
    if (!empty($title)) echo '<h3 class="rpb-related-heading">' . esc_html($title) . '</h3>';
    echo '<ul>';

    while ($query->have_posts()) {
        $query->the_post();
        $post_time = get_the_date();
        $category = !empty($categories) ? $categories[0]->name : '';
        
        echo '<li class="rpb-item">';
        if (has_post_thumbnail()) {
            echo '<a href="' . esc_url(get_permalink()) . '" class="rpb-thumb">';
            echo get_the_post_thumbnail(get_the_ID(), 'medium'); 
            echo '</a>';
        }
        echo '<div class="rpb-content">';
        echo '<a href="' . esc_url(get_permalink()) . '" class="rpb-title">' . esc_html(get_the_title()) . '</a>';
        echo '<div class="rpb-meta">' . esc_html($post_time) . ' • ' . esc_html($category) . '</div>';
        echo '</div>'; // Close .rpb-content
        echo '</li>';
    }

    echo '</ul></div>';
    wp_reset_postdata();

    $output = ob_get_clean();

    // Save output to cache
    if (!$debug) {
        set_transient($cache_key, $output, HOUR_IN_SECONDS);
    }

    return $output;
}

/**
 * Register [related_posts] shortcode
 */
function rpb_related_posts_shortcode($atts) {
    $atts = shortcode_atts(['title' => ''], $atts, 'related_posts');
    return rpb_get_related_posts_html($atts['title']);
}
add_shortcode('related_posts', 'rpb_related_posts_shortcode');

/**
 * Register Gutenberg block
 */
function rpb_register_block() {
    if (!function_exists('register_block_type')) return;

    wp_register_script(
        'rpb-block-editor',
        plugin_dir_url(__FILE__) . 'block.js',
        ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components'],
        filemtime(__FILE__)
    );

    register_block_type('rpb/related-posts', [
        'editor_script' => 'rpb-block-editor',
        'render_callback' => 'rpb_render_block',
        'attributes' => [
            'title' => [
                'type' => 'string',
                'default' => '',
            ],
        ],
    ]);
}
add_action('init', 'rpb_register_block');

/**
 * Server-side render callback for the block
 */
function rpb_render_block($attributes) {
    $title = isset($attributes['title']) ? sanitize_text_field($attributes['title']) : '';
    return rpb_get_related_posts_html($title);
}

