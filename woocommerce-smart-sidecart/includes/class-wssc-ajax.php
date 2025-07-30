<?php
if (!defined('ABSPATH')) exit;

class WSSC_Ajax {
    public function __construct() {
        add_action('wp_ajax_wssc_buy_bulk', [$this, 'save_request']);
        add_action('wp_ajax_nopriv_wssc_buy_bulk', [$this, 'save_request']);
        add_action('wp_ajax_wssc_update_request_status', [$this, 'update_request_status']);
        add_action('wp_ajax_wssc_delete_request', [$this, 'delete_request']);
    }

public function save_request() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['_ajax_nonce'], 'wssc_nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    global $wpdb;
    $table = $wpdb->prefix . 'wssc_bulk_requests';
    
    $data = [
        'product_id' => intval($_POST['product_id']),
        'name' => sanitize_text_field($_POST['name']),
        'phone' => sanitize_text_field($_POST['phone']),
        'email' => sanitize_email($_POST['email']),
        'quantity' => intval($_POST['quantity']),
        'message' => sanitize_textarea_field($_POST['message']),
        'status' => 'pending',
        'created_at' => current_time('mysql')
    ];

    $result = $wpdb->insert($table, $data);
    
    if ($result) {
        wp_send_json_success(['message' => 'Request submitted successfully']);
    } else {
        wp_send_json_error('Failed to save request');
    }
}

    public function update_request_status() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'wssc_bulk_requests';
        $id = intval($_POST['id']);
        $status = sanitize_text_field($_POST['status']);

        $result = $wpdb->update(
            $table,
            ['status' => $status],
            ['id' => $id],
            ['%s'],
            ['%d']
        );

        if ($result !== false) {
            wp_send_json_success(['message' => 'Status updated successfully']);
        } else {
            wp_send_json_error('Failed to update status');
        }
    }

    public function delete_request() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'wssc_bulk_requests';
        $id = intval($_POST['id']);

        $result = $wpdb->delete($table, ['id' => $id], ['%d']);

        if ($result !== false) {
            wp_send_json_success(['message' => 'Request deleted successfully']);
        } else {
            wp_send_json_error('Failed to delete request');
        }
    }
}
new WSSC_Ajax();