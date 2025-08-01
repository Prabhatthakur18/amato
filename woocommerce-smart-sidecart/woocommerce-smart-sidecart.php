<?php
/*
Plugin Name: WooCommerce Smart Side Cart + Bulk Buy
Description: Adds a side cart with recommended products and bulk buying options.
Version: 1.0.0
Author: Custom
*/

if (!defined('ABSPATH')) exit;

define('WSSC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WSSC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include files only if WooCommerce is active
add_action('plugins_loaded', function() {
    if (class_exists('WooCommerce')) {
        require_once WSSC_PLUGIN_PATH . 'includes/class-wssc-db.php';
        require_once WSSC_PLUGIN_PATH . 'includes/class-wssc-sidecart.php';
        require_once WSSC_PLUGIN_PATH . 'includes/class-wssc-admin.php';
        require_once WSSC_PLUGIN_PATH . 'includes/class-wssc-ajax.php';
        
        // Initialize classes
        new WSSC_SideCart();
        new WSSC_Admin();
        new WSSC_Ajax();
    } else {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>WooCommerce Smart Side Cart requires WooCommerce to be installed and active.</p></div>';
        });
    }
});

// Register activation hook
register_activation_hook(__FILE__, function() {
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('WooCommerce Smart Side Cart requires WooCommerce to be installed and active. Please install WooCommerce first.', 'wssc'), 'Plugin dependency check', array('back_link' => true));
    }
    
    require_once WSSC_PLUGIN_PATH . 'includes/class-wssc-db.php';
    WSSC_DB::create_table();
});