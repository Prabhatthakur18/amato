<?php
/*
Plugin Name: WooCommerce Smart Side Cart + Bulk Buy
Description: Adds a side cart with recommended products and bulk buying options.
Version: 1.0.1
Author: Prabhat Thakur
*/

if (!defined('ABSPATH')) exit;

define('WSSC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WSSC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include all required classes
require_once WSSC_PLUGIN_PATH . 'includes/class-wssc-db.php';
require_once WSSC_PLUGIN_PATH . 'includes/class-wssc-ajax.php';
require_once WSSC_PLUGIN_PATH . 'includes/class-wssc-sidecart.php';
require_once WSSC_PLUGIN_PATH . 'includes/class-wssc-admin.php';

// Initialize the plugin
class WSSC_Plugin {
    public function __construct() {
        // Hook into WordPress initialization
        add_action('plugins_loaded', [$this, 'init']);
        
        // Register activation hook
        register_activation_hook(__FILE__, [$this, 'activate']);
        
        // Add debug info for troubleshooting
        add_action('wp_footer', [$this, 'debug_info']);
    }
    
    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'woocommerce_missing_notice']);
            return;
        }
        
        // Initialize classes
        new WSSC_Ajax();
        new WSSC_SideCart();
        new WSSC_Admin();
    }
    
    public function activate() {
        // Create database table
        WSSC_DB::create_table();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function woocommerce_missing_notice() {
        echo '<div class="notice notice-error"><p>';
        echo __('WooCommerce Smart Side Cart requires WooCommerce to be installed and active.', 'wssc');
        echo '</p></div>';
    }
    
    public function debug_info() {
        // Only show debug info for administrators
        if (!current_user_can('manage_options') || !isset($_GET['wssc_debug'])) {
            return;
        }
        
        echo '<div style="position: fixed; bottom: 10px; left: 10px; background: #fff; padding: 10px; border: 1px solid #ccc; z-index: 9999; font-size: 12px;">';
        echo '<strong>WSSC Debug Info:</strong><br>';
        echo 'Plugin URL: ' . WSSC_PLUGIN_URL . '<br>';
        echo 'WooCommerce Active: ' . (class_exists('WooCommerce') ? 'Yes' : 'No') . '<br>';
        echo 'Cart Items: ' . (WC()->cart ? WC()->cart->get_cart_contents_count() : '0') . '<br>';
        echo 'Ajax URL: ' . admin_url('admin-ajax.php') . '<br>';
        echo '</div>';
    }
}

// Initialize the plugin
new WSSC_Plugin();