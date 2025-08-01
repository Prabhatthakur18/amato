<?php
if (!defined('ABSPATH')) exit;

class WSSC_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_post_wssc_upload_csv', [$this, 'handle_csv']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function enqueue_admin_assets($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'wssc') !== false) {
            wp_enqueue_style('wssc-admin-css', WSSC_PLUGIN_URL . 'assets/css/wssc-admin.css');
            wp_enqueue_script('wssc-admin-js', WSSC_PLUGIN_URL . 'assets/js/wssc-admin.js', ['jquery'], null, true);
            wp_localize_script('wssc-admin-js', 'wsscAdmin', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wssc_admin_nonce')
            ]);

               
        // Add this line to enqueue the main JS file for admin pages
        wp_enqueue_script('wssc-js', WSSC_PLUGIN_URL . 'assets/js/wssc.js', ['jquery'], null, true);
        }
    }

    public function add_menu() {
        add_menu_page('Side Cart Settings', 'Side Cart', 'manage_options', 'wssc-settings', [$this, 'settings_page'], 'dashicons-cart');
        add_submenu_page('wssc-settings', 'Bulk Requests', 'Bulk Requests', 'manage_options', 'wssc-bulk-requests', [$this, 'requests_page']);
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Upload CSV for Product Relations</h1>

            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="wssc-admin-toast success">
                    ✅ CSV uploaded successfully!
                </div>
            <?php elseif (isset($_GET['error']) && $_GET['error'] == 1): ?>
                <div class="wssc-admin-toast error">
                    ❌ Error uploading CSV. Please try again.
                </div>
            <?php endif; ?>

            <div class="wssc-upload-section">
                <h3>CSV Format Instructions</h3>
                <p>Your CSV should have 3 columns:</p>
                <ol>
                    <li><strong>Product ID</strong> - The main product ID</li>
                    <li><strong>Recommended Products</strong> - Comma-separated product IDs for "Ye Bhi Jaruri he" section</li>
                    <li><strong>Interested Products</strong> - Comma-separated product IDs for "Hume bhi dekh lo" section</li>
                </ol>
                
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data" class="wssc-upload-form">
                    <input type="hidden" name="action" value="wssc_upload_csv">
                    <?php wp_nonce_field('wssc_csv', 'wssc_nonce'); ?>
                    
                    <div class="upload-area">
                        <input type="file" name="wssc_csv" id="wssc_csv" required accept=".csv" class="file-input">
                        <label for="wssc_csv" class="file-label">
                            <span class="file-icon">📁</span>
                            <span class="file-text">Choose CSV File</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="button button-primary button-large">
                        <span class="upload-icon">⬆️</span>
                        Upload CSV
                    </button>
                </form>
            </div>
        </div>
        <?php
    }

    public function handle_csv() {
        if (!isset($_POST['wssc_nonce']) || !wp_verify_nonce($_POST['wssc_nonce'], 'wssc_csv')) {
            wp_die('Invalid nonce');
        }

        $success = false;

        if (!empty($_FILES['wssc_csv']['tmp_name'])) {
            $file = fopen($_FILES['wssc_csv']['tmp_name'], 'r');

            if ($file) {
                $row_count = 0;
                while (($row = fgetcsv($file)) !== false) {
                    // Skip header row if exists
                    if ($row_count === 0 && !is_numeric($row[0])) {
                        $row_count++;
                        continue;
                    }

                    $product_id = intval($row[0]);
                    $recommended = isset($row[1]) ? sanitize_text_field($row[1]) : '';
                    $interested = isset($row[2]) ? sanitize_text_field($row[2]) : '';

                    if ($product_id > 0) {
                        update_post_meta($product_id, '_wssc_recommended', $recommended);
                        update_post_meta($product_id, '_wssc_interested', $interested);
                    }
                    $row_count++;
                }

                fclose($file);
                $success = true;
            }
        }

        // Redirect with success or error flag
        $redirect_url = admin_url('admin.php?page=wssc-settings');
        if ($success) {
            $redirect_url .= '&success=1';
        } else {
            $redirect_url .= '&error=1';
        }

        wp_redirect($redirect_url);
        exit;
    }

public function requests_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'wssc_bulk_requests';
    $results = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
    ?>
    <div class="wrap">
        <h1>Bulk Requests</h1>
        
        <?php if (empty($results)): ?>
            <div class="no-requests">
                <p>No bulk requests found.</p>
            </div>
        <?php else: ?>
            <table class="widefat fixed striped wssc-requests-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Qty</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr data-id="<?php echo $row->id; ?>">
                            <td><?php echo esc_html($row->id); ?></td>
                            <td><?php echo esc_html($row->product_id); ?></td>
                            <td><?php echo esc_html($row->name); ?></td>
                            <td><?php echo esc_html($row->phone); ?></td>
                            <td><?php echo esc_html($row->email); ?></td>
                            <td><?php echo esc_html($row->quantity); ?></td>
                            <td><?php echo esc_html($row->message); ?></td>
                            <td><?php echo ucfirst(esc_html($row->status)); ?></td>
                            <td><?php echo esc_html(date('M j, Y g:i A', strtotime($row->created_at))); ?></td>
                            <td>
                                <button class="edit-status-btn" data-id="<?php echo $row->id; ?>">Edit</button>
                                <button class="delete-request-btn" data-id="<?php echo $row->id; ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}
new WSSC_Admin();