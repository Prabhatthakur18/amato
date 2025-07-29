<?php
if (!defined('ABSPATH')) exit;

class WSSC_SideCart {
    public function __construct() {
        add_action('woocommerce_widget_shopping_cart_after_buttons', [$this, 'render_sections']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_script('wssc-js', WSSC_PLUGIN_URL . 'assets/js/wssc.js', ['jquery'], null, true);
        wp_localize_script('wssc-js', 'wsscAjax', [
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wssc_nonce')
        ]);

        wp_enqueue_style('wssc-css', WSSC_PLUGIN_URL . 'assets/css/wssc.css');
    }

    public function render_sections() {
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product_id = $cart_item['product_id'];
            $recommended = get_post_meta($product_id, '_wssc_recommended', true);
            $interested = get_post_meta($product_id, '_wssc_interested', true);

            // Ye Bhi Jaruri He
            if ($recommended) {
                echo '<div class="wssc-section"><h4>Ye Bhi Jaruri he 🛒</h4>';
                foreach (explode(',', $recommended) as $id) {
                    $prod = wc_get_product($id);
                    if ($prod) {
                        $qty = $this->get_cart_quantity($id);
                        echo "<div class='wssc-product' data-id='{$id}'>
                                <button class='wssc-remove' data-id='{$id}'>➖</button>
                                {$prod->get_image('thumbnail')}
                                <p>{$prod->get_name()}</p>
                                <button class='wssc-add' data-id='{$id}'>➕</button>";
                        if ($qty > 0) {
                            echo "<span class='wssc-badge'>{$qty}</span>";
                        }
                        echo "</div>";
                    }
                }
                echo '</div>';
            }

            // Hume Bhi Dekh Lo!
            if ($interested) {
                echo '<div class="wssc-section"><h4>Hume bhi dekh lo! 👀</h4>';
                foreach (explode(',', $interested) as $id) {
                    $prod = wc_get_product($id);
                    if ($prod) {
                        $qty = $this->get_cart_quantity($id);
                        echo "<div class='wssc-product' data-id='{$id}'>
                                <button class='wssc-remove' data-id='{$id}'>➖</button>
                                {$prod->get_image('thumbnail')}
                                <p>{$prod->get_name()}</p>
                                <button class='wssc-add' data-id='{$id}'>➕</button>";
                        if ($qty > 0) {
                            echo "<span class='wssc-badge'>{$qty}</span>";
                        }
                        echo "</div>";
                    }
                }
                echo '</div>';
            }

            echo '<button class="wssc-bulk-btn" data-product="' . $product_id . '">Buy Bulk</button>';
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
}
new WSSC_SideCart();
