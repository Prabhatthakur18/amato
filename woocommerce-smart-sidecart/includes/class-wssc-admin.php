<?php
if (!defined('ABSPATH')) exit;

class WSSC_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_post_wssc_upload_csv', [$this, 'handle_csv']);
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
                <div class="notice notice-success is-dismissible">
                    <p>✅ CSV uploaded successfully!</p>
                </div>
                <script>
                    alert("✅ CSV Uploaded Successfully!");
                </script>
            <?php endif; ?>

            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="wssc_upload_csv">
                <?php wp_nonce_field('wssc_csv', 'wssc_nonce'); ?>
                <input type="file" name="wssc_csv" required accept=".csv">
                <button type="submit" class="button button-primary">Upload</button>
            </form>
        </div>
        <?php
    }

    public function handle_csv() {
        if (!isset($_POST['wssc_nonce']) || !wp_verify_nonce($_POST['wssc_nonce'], 'wssc_csv')) {
            wp_die('Invalid nonce');
        }

        if (!empty($_FILES['wssc_csv']['tmp_name'])) {
            $file = fopen($_FILES['wssc_csv']['tmp_name'], 'r');

            while (($row = fgetcsv($file)) !== false) {
                $product_id = intval($row[0]);
                $recommended = sanitize_text_field($row[1]);
                $interested = sanitize_text_field($row[2]);

                update_post_meta($product_id, '_wssc_recommended', $recommended);
                update_post_meta($product_id, '_wssc_interested', $interested);
            }

            fclose($file);
        }

        // Redirect with success flag
        wp_redirect(admin_url('admin.php?page=wssc-settings&success=1'));
        exit;
    }

    public function requests_page() {
        global $wpdb;
        $table = $wpdb->prefix . 'wssc_bulk_requests';
        $results = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
        ?>
        <div class="wrap">
            <h1>Bulk Requests</h1>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Qty</th>
                        <th>Message</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row):
                        $product = wc_get_product($row->product_id); ?>
                        <tr>
                            <td><?php echo esc_html($row->id); ?></td>
                            <td><?php echo $product ? esc_html($product->get_name()) : 'Deleted'; ?></td>
                            <td><?php echo esc_html($row->name); ?></td>
                            <td><?php echo esc_html($row->phone); ?></td>
                            <td><?php echo esc_html($row->quantity); ?></td>
                            <td><?php echo esc_html($row->message); ?></td>
                            <td><?php echo esc_html($row->created_at); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
new WSSC_Admin();
