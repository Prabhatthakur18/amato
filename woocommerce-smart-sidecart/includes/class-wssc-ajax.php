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
        try {
            // Verify nonce and required fields
            if (!wp_verify_nonce($_POST['_ajax_nonce'] ?? '', 'wssc_nonce')) {
                throw new Exception('Security verification failed');
            }

            $required_fields = ['name', 'phone', 'email', 'quantity', 'product_ids'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception(ucfirst($field) . ' is required');
                }
            }

            global $wpdb;
            $table = $wpdb->prefix . 'wssc_bulk_requests';
            
            // Prepare and validate data
            $data = [
                'product_id' => $this->sanitize_product_ids($_POST['product_ids']),
                'name' => sanitize_text_field($_POST['name']),
                'phone' => $this->validate_phone($_POST['phone']),
                'email' => $this->validate_email($_POST['email']),
                'quantity' => $this->validate_quantity($_POST['quantity']),
                'message' => isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '',
                'status' => 'pending',
                'created_at' => current_time('mysql')
            ];

            // Insert into database
            $result = $wpdb->insert($table, $data);
            
            if ($result === false) {
                throw new Exception('Database error: ' . $wpdb->last_error);
            }

            wp_send_json_success([
                'message' => 'Request submitted successfully',
                'request_id' => $wpdb->insert_id
            ]);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    public function update_request_status() {
        try {
            if (!current_user_can('manage_options')) {
                throw new Exception('Unauthorized access');
            }

            if (!wp_verify_nonce($_POST['_ajax_nonce'] ?? '', 'wssc_admin_nonce')) {
                throw new Exception('Security verification failed');
            }

            global $wpdb;
            $table = $wpdb->prefix . 'wssc_bulk_requests';
            $id = intval($_POST['id'] ?? 0);
            $status = in_array($_POST['status'] ?? '', ['pending', 'done']) ? $_POST['status'] : 'pending';

            if ($id <= 0) {
                throw new Exception('Invalid request ID');
            }

            $result = $wpdb->update(
                $table,
                ['status' => $status],
                ['id' => $id],
                ['%s'],
                ['%d']
            );

            if ($result === false) {
                throw new Exception('Database update failed');
            }

            wp_send_json_success([
                'message' => 'Status updated successfully',
                'new_status' => $status
            ]);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    public function delete_request() {
        try {
            if (!current_user_can('manage_options')) {
                throw new Exception('Unauthorized access');
            }

            if (!wp_verify_nonce($_POST['_ajax_nonce'] ?? '', 'wssc_admin_nonce')) {
                throw new Exception('Security verification failed');
            }

            global $wpdb;
            $table = $wpdb->prefix . 'wssc_bulk_requests';
            $id = intval($_POST['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception('Invalid request ID');
            }

            $result = $wpdb->delete($table, ['id' => $id], ['%d']);

            if ($result === false) {
                throw new Exception('Database deletion failed');
            }

            wp_send_json_success([
                'message' => 'Request deleted successfully',
                'deleted_id' => $id
            ]);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    // Helper methods for validation
    private function validate_email($email) {
        $email = sanitize_email($email);
        if (!is_email($email)) {
            throw new Exception('Please enter a valid email address');
        }
        return $email;
    }

    private function validate_phone($phone) {
        $phone = sanitize_text_field($phone);
        if (!preg_match('/^[0-9\+\-\s]{10,20}$/', $phone)) {
            throw new Exception('Please enter a valid phone number');
        }
        return $phone;
    }

    private function validate_quantity($quantity) {
        $quantity = intval($quantity);
        if ($quantity < 1 || $quantity > 1000) {
            throw new Exception('Quantity must be between 1 and 1000');
        }
        return $quantity;
    }

    private function sanitize_product_ids($ids) {
        $ids = sanitize_text_field($ids);
        $product_ids = explode(',', $ids);
        $valid_ids = array_filter($product_ids, function($id) {
            return is_numeric(trim($id)) && trim($id) > 0;
        });
        
        if (empty($valid_ids)) {
            throw new Exception('No valid product IDs found');
        }
        
        return implode(',', $valid_ids);
    }
}
new WSSC_Ajax();