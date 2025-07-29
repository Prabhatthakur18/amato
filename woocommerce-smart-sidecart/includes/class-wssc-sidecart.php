<?php
if (!defined('ABSPATH')) exit;

class WSSC_SideCart {
    public function __construct() {
        add_action('woocommerce_after_mini_cart', [$this, 'render_sections']); // Correct hook
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
    $recommended_html = '';
    $interested_html = '';
    $products_html = [];

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product_id = $cart_item['product_id'];
        $recommended = get_post_meta($product_id, '_wssc_recommended', true);
        $interested = get_post_meta($product_id, '_wssc_interested', true);

        if ($recommended) {
            $recommended_html .= '<div class="wssc-section"><h4>Ye Bhi Jaruri he 🛒</h4>';
            foreach (explode(',', $recommended) as $id) {
                $prod = wc_get_product($id);
                if ($prod) {
                    $qty = $this->get_cart_quantity($id);
                    $recommended_html .= "<div class='wssc-product' data-id='{$id}'>
                        <img src='{$prod->get_image_id() ? wp_get_attachment_image_url($prod->get_image_id(), 'thumbnail') : wc_placeholder_img_src()}' />
                        <p>{$prod->get_name()}</p>
                        <div class='wssc-controls'>
                            <button class='wssc-remove' data-id='{$id}'>–</button>
                            <span class='wssc-qty'>{$qty}</span>
                            <button class='wssc-add' data-id='{$id}'>+</button>
                        </div>
                    </div>";
                }
            }
            $recommended_html .= '</div>';
        }

        if ($interested) {
            $interested_html .= '<div class="wssc-section"><h4>Hume bhi dekh lo! 👀</h4>';
            foreach (explode(',', $interested) as $id) {
                $prod = wc_get_product($id);
                if ($prod) {
                    $qty = $this->get_cart_quantity($id);
                    $interested_html .= "<div class='wssc-product' data-id='{$id}'>
                        <img src='{$prod->get_image_id() ? wp_get_attachment_image_url($prod->get_image_id(), 'thumbnail') : wc_placeholder_img_src()}' />
                        <p>{$prod->get_name()}</p>
                        <div class='wssc-controls'>
                            <button class='wssc-remove' data-id='{$id}'>–</button>
                            <span class='wssc-qty'>{$qty}</span>
                            <button class='wssc-add' data-id='{$id}'>+</button>
                        </div>
                    </div>";
                }
            }
            $interested_html .= '</div>';
        }
    }

    echo $recommended_html . $interested_html;

    echo '<div class="wssc-cart-actions">
        <a href="' . wc_get_cart_url() . '" class="button">View Cart</a>
        <a href="' . wc_get_checkout_url() . '" class="button">Checkout</a>
        <button class="button wssc-bulk-open">Buy Bulk</button>
    </div>';
}


    private function render_product($id) {
        $product = wc_get_product($id);
        if (!$product) return '';

        $qty = $this->get_cart_quantity($id);
        $img = $product->get_image('woocommerce_thumbnail');
        $name = $product->get_name();

        return "
        <div class='wssc-product' data-id='{$id}'>
            <div class='wssc-product-inner'>
                <div class='wssc-controls'>
                    <button class='wssc-remove' data-id='{$id}'>➖</button>
                    <span class='wssc-qty'>{$qty}</span>
                    <button class='wssc-add' data-id='{$id}'>➕</button>
                </div>
                <div class='wssc-img'>{$img}</div>
                <p class='wssc-title'>{$name}</p>
            </div>
        </div>";
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
