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

require_once WSSC_PLUGIN_PATH . 'includes/class-wssc-sidecart.php';
require_once WSSC_PLUGIN_PATH . 'includes/class-wssc-admin.php';
require_once WSSC_PLUGIN_PATH . 'includes/class-wssc-ajax.php';
require_once WSSC_PLUGIN_PATH . 'includes/class-wssc-db.php';

register_activation_hook(__FILE__, ['WSSC_DB', 'create_table']);
