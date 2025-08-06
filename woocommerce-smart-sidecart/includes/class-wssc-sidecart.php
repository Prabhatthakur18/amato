<?php
if (!defined('ABSPATH')) exit;

class WSSC_SideCart {
    public function __construct() {
        add_action('woocommerce_widget_shopping_cart_after_buttons', [$this, 'render_bulk_button']);
        add_action('woocommerce_widget_shopping_cart_after_buttons', [$this, 'render_sections']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        if (is_woocommerce() || is_cart() || is_checkout() || is_shop() || is_product_category() || is_product_tag() || is_product()) {
            wp_enqueue_script('wssc-js', WSSC_PLUGIN_URL . 'assets/js/wssc.js', ['jquery'], '1.0.2', true);
            wp_localize_script('wssc-js', 'wsscAjax', [
                'url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wssc_nonce')
            ]);

            wp_enqueue_style('wssc-css', WSSC_PLUGIN_URL . 'assets/css/wssc.css', [], '1.0.2');
        }
    }

    public function render_sections() {
        // Prevent double rendering
        static $has_run = false;
        if ($has_run) return;
        $has_run = true;

        if (WC()->cart->is_empty()) return;

        $all_recommended = [];
        $all_interested = [];
        $cart_product_ids = [];

        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            $cart_product_ids[] = $product_id;

            $recommended = get_post_meta($product_id, '_wssc_recommended', true);
            $interested = get_post_meta($product_id, '_wssc_interested', true);

            if ($recommended) {
                $recommended_ids = array_map('trim', explode(',', $recommended));
                $all_recommended = array_merge($all_recommended, $recommended_ids);
            }

            if ($interested) {
                $interested_ids = array_map('trim', explode(',', $interested));
                $all_interested = array_merge($all_interested, $interested_ids);
            }
        }

        $all_recommended = array_unique(array_filter($all_recommended, function ($id) use ($cart_product_ids) {
            return !empty($id) && is_numeric($id) && !in_array((int)$id, $cart_product_ids);
        }));

        $all_interested = array_unique(array_filter($all_interested, function ($id) use ($cart_product_ids) {
            return !empty($id) && is_numeric($id) && !in_array((int)$id, $cart_product_ids);
        }));

        if (!empty($all_recommended)) {
            echo '<div class="wssc-section">';
            echo '<h4 class="wssc-section-title">Ye Bhi Jaruri he 🛒</h4>';
            echo '<div class="wssc-products-grid">';
            foreach ($all_recommended as $id) {
                $product = wc_get_product((int)$id);
                if ($product && $product->is_purchasable() && $product->is_in_stock()) {
                    $this->render_product_card($product, (int)$id);
                }
            }
            echo '</div></div>';
        }

        if (!empty($all_interested)) {
            echo '<div class="wssc-section">';
            echo '<h4 class="wssc-section-title">Hume bhi dekh lo! 👀</h4>';
            echo '<div class="wssc-products-grid">';
            foreach ($all_interested as $id) {
                $product = wc_get_product((int)$id);
                if ($product && $product->is_purchasable() && $product->is_in_stock()) {
                    $this->render_product_card($product, (int)$id);
                }
            }
            echo '</div></div>';
        }
    }

    public function render_bulk_button() {
        if (WC()->cart->is_empty()) return;

        $cart_product_ids = array_map(function($item) {
            return $item['product_id'];
        }, WC()->cart->get_cart());

        $main_product_id = $cart_product_ids[0] ?? 0;

        echo '<div class="wssc-bulk-btn-wrapper" style="margin-top: 10px; margin-bottom: 0; padding: 0;">';
        echo '<a href="#" class="button wssc-bulk-btn" data-product="' . esc_attr($main_product_id) . '">BUY BULK</a>';
        echo '</div>';
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
        echo '</div>';
        echo '<div class="wssc-product-actions">';
        echo '<button type="button" class="wssc-add-btn" data-product-id="' . esc_attr($product_id) . '">+ ADD</button>';
        echo '</div>';

        if ($qty > 0) {
            echo '<span class="wssc-qty-badge">' . esc_html($qty) . '</span>';
        }

        echo '</div>';
    }

    public function add_bulk_button() {
        // Disabled intentionally to avoid duplication
    }

    private function get_cart_quantity($product_id) {
        foreach (WC()->cart->get_cart() as $item) {
            if ($item['product_id'] == $product_id) {
                return $item['quantity'];
            }
        }
        return 0;
    }
}

new WSSC_SideCart();
