<?php
if (!defined('ABSPATH')) exit;

class WSSC_SideCart {
    public function __construct() {
        add_action('woocommerce_widget_shopping_cart_after_buttons', [$this, 'render_sections']);
        add_action('woocommerce_widget_shopping_cart_buttons', [$this, 'add_bulk_button']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_wssc_add_to_cart', [$this, 'ajax_add_to_cart']);
        add_action('wp_ajax_nopriv_wssc_add_to_cart', [$this, 'ajax_add_to_cart']);
        add_action('wp_ajax_wssc_remove_from_cart', [$this, 'remove_from_cart']);
        add_action('wp_ajax_nopriv_wssc_remove_from_cart', [$this, 'remove_from_cart']);
    }

    public function enqueue_assets() {
        wp_enqueue_script('wssc-js', WSSC_PLUGIN_URL . 'assets/js/wssc.js', ['jquery'], '1.0.1', true);
        wp_localize_script('wssc-js', 'wsscAjax', [
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wssc_nonce')
        ]);

        wp_enqueue_style('wssc-css', WSSC_PLUGIN_URL . 'assets/css/wssc.css', [], '1.0.1');
    }

    public function render_sections() {
        $all_recommended = [];
        $all_interested = [];

        // Collect all recommended and interested products from cart items
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            
            $recommended = get_post_meta($product_id, '_wssc_recommended', true);
            $interested = get_post_meta($product_id, '_wssc_interested', true);

            if ($recommended) {
                $all_recommended = array_merge($all_recommended, explode(',', $recommended));
            }
            if ($interested) {
                $all_interested = array_merge($all_interested, explode(',', $interested));
            }
        }

        // Remove duplicates and clean up
        $all_recommended = array_unique(array_filter(array_map('trim', $all_recommended)));
        $all_interested = array_unique(array_filter(array_map('trim', $all_interested)));

        // Render "Ye Bhi Jaruri He" section
        if (!empty($all_recommended)) {
            echo '<div class="wssc-section">';
            echo '<h4 class="wssc-section-title">Ye Bhi Jaruri he 🛒</h4>';
            echo '<div class="wssc-products-grid">';
            
            foreach ($all_recommended as $id) {
                $prod = wc_get_product($id);
                if ($prod && $prod->is_purchasable()) {
                    $this->render_product_card($prod, $id);
                }
            }
            echo '</div></div>';
        }

        // Render "Hume Bhi Dekh Lo!" section
        if (!empty($all_interested)) {
            echo '<div class="wssc-section">';
            echo '<h4 class="wssc-section-title">Hume bhi dekh lo! 👀</h4>';
            echo '<div class="wssc-products-grid">';
            
            foreach ($all_interested as $id) {
                $prod = wc_get_product($id);
                if ($prod && $prod->is_purchasable()) {
                    $this->render_product_card($prod, $id);
                }
            }
            echo '</div></div>';
        }
    }

    private function render_product_card($product, $product_id) {
        $qty = $this->get_cart_quantity($product_id);
        $price = $product->get_price_html();
        $image = $product->get_image('woocommerce_gallery_thumbnail');
        $name = $product->get_name();
        
        echo '<div class="wssc-product-card" data-id="' . esc_attr($product_id) . '">';
        echo '<div class="wssc-product-image">' . $image . '</div>';
        echo '<div class="wssc-product-info">';
        echo '<h5 class="wssc-product-name">' . esc_html($name) . '</h5>';
        echo '<div class="wssc-product-price">' . $price . '</div>';
        echo '<div class="wssc-product-actions">';
        echo '<button type="button" class="wssc-add-btn" data-product-id="' . esc_attr($product_id) . '">+ ADD</button>';
        echo '</div>';
        
        if ($qty > 0) {
            echo '<span class="wssc-qty-badge">' . esc_html($qty) . '</span>';
        }
        
        echo '</div></div>';
    }

public function add_bulk_button() {
    if (!WC()->cart->is_empty()) {
        $cart_product_ids = [];
        foreach (WC()->cart->get_cart() as $cart_item) {
            $cart_product_ids[] = $cart_item['product_id'];
        }
        
        echo '<a href="#" class="button wssc-bulk-btn" data-product="' . implode(',', $cart_product_ids) . '">Buy Bulk</a>';
    }
}

    public function ajax_add_to_cart() {
        if (!wp_verify_nonce($_POST['nonce'], 'wssc_nonce')) {
            wp_send_json_error('Invalid nonce');
        }

        $product_id = intval($_POST['product_id']);
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

        $result = WC()->cart->add_to_cart($product_id, $quantity);

        if ($result) {
            wp_send_json_success([
                'message' => 'Product added to cart',
                'cart_count' => WC()->cart->get_cart_contents_count()
            ]);
        } else {
            wp_send_json_error('Failed to add product to cart');
        }
    }

    private function get_cart_quantity($product_id) {
        foreach (WC()->cart->get_cart() as $item) {
            if ($item['product_id'] == $product_id) {
                return $item['quantity'];
            }
        }
        return 0;
    }

    public function remove_from_cart() {
        if (!wp_verify_nonce($_POST['nonce'], 'wssc_nonce')) {
            wp_die('Invalid nonce');
        }

        $product_id = intval($_POST['product_id']);
        
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if ($cart_item['product_id'] == $product_id) {
                $current_qty = $cart_item['quantity'];
                if ($current_qty > 1) {
                    WC()->cart->set_quantity($cart_item_key, $current_qty - 1);
                } else {
                    WC()->cart->remove_cart_item($cart_item_key);
                }
                break;
            }
        }

        wp_send_json_success();
    }
}
new WSSC_SideCart();