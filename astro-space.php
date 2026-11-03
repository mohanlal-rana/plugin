<?php
/**
 * Plugin Name: Astro Space
 * Description: Public image upload plugin for astronomical images.
 * Version: 1.1.0
 * Author: Mohan
 * Text Domain: astro-space
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ASTRO_SPACE_VERSION', '1.1.0');
define('ASTRO_SPACE_PATH', plugin_dir_path(__FILE__));
define('ASTRO_SPACE_URL', plugin_dir_url(__FILE__));

require_once ASTRO_SPACE_PATH . 'includes/class-database.php';
require_once ASTRO_SPACE_PATH . 'includes/class-upload.php';

/**
 * Load frontend CSS and JavaScript.
 */
function astro_space_enqueue_assets()
{
    $css_file = ASTRO_SPACE_PATH . 'public/css/astro-space.css';
    $js_file = ASTRO_SPACE_PATH . 'public/js/astro-space.js';

    wp_enqueue_style(
        'astro-space-style',
        ASTRO_SPACE_URL . 'public/css/astro-space.css',
        [],
        file_exists($css_file) ? filemtime($css_file) : ASTRO_SPACE_VERSION
    );

    wp_enqueue_script(
        'astro-space-script',
        ASTRO_SPACE_URL . 'public/js/astro-space.js',
        [],
        file_exists($js_file) ? filemtime($js_file) : ASTRO_SPACE_VERSION,
        true
    );
}

add_action('wp_enqueue_scripts', 'astro_space_enqueue_assets');

register_activation_hook(
    __FILE__,
    ['Astro_Space_Database', 'create_tables']
);

function astro_space_init()
{
    new Astro_Space_Upload();
}

add_action('plugins_loaded', 'astro_space_init');