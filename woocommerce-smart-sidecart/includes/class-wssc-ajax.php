<?php
if (!defined('ABSPATH')) exit;

class WSSC_Ajax {
    public function __construct() {
        add_action('wp_ajax_wssc_buy_bulk', [$this, 'save_request']);
        add_action('wp_ajax_nopriv_wssc_buy_bulk', [$this, 'save_request']);
    }

    public function save_request() {
        global $wpdb;
        $table = $wpdb->prefix . 'wssc_bulk_requests';
        $wpdb->insert($table, [
            'product_id' => intval($_POST['product_id']),
            'name' => sanitize_text_field($_POST['name']),
            'phone' => sanitize_text_field($_POST['phone']),
            'quantity' => intval($_POST['quantity']),
            'message' => sanitize_textarea_field($_POST['message']),
            'created_at' => current_time('mysql')
        ]);
        wp_send_json_success(['message' => 'Request Submitted!']);
    }
}
new WSSC_Ajax();
