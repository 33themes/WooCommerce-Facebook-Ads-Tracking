<?php
/**
 * Plugin Name: WooCommerce Facebook Pixel
 * Plugin URI: https://github.com/gabrielperezs/WooCommerce-Facebook-Ads-Tracking
 * Description: Plugin integration with Facebook Pxiel and WooCommerce
 * Version: 0.4
 * Author: gabrielperezs
 * Author URI: http://www.guero.net
 * License: GPL2
 */

if (!defined('ABSPATH')) exit;


if(!class_exists('WooCommerce_Facebook_Pixel')) {

    class WooCommerce_Facebook_Pixel {

        const VERSION = '0.4';

        private static $instance = null;

        public static function get_instance() {
            if (is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

        public function __construct() {
            if (class_exists('WC_Integration')) {
                require_once dirname(__FILE__). '/integration.php';

                add_filter('woocommerce_integrations', array($this, 'add_integration'));
                add_filter('wc_add_to_cart_message', array($this,'wc_add_to_cart_message'), 1, 2 );
                add_action('woocommerce_thankyou', array($this, 'thankyou'), 9, 1);

                add_action('wp_head', array($this, 'load_main_js'));

                add_action('woocommerce_before_shop_loop_item', array($this,'json_info_item'));

                //add_action('woocommerce_after_add_to_cart_button', array( $this, 'add_to_cart' ), 9, 1);
            }
        }

        public function json_info_item() {
            global $product;

            echo '<script type="text/json" id="facebook_pixel_'. $product->get_id().'">';
            echo json_encode(array(
                'content_ids' => $product->get_sku(),
                'content_name' => $product->get_title(),
                'currency' => get_woocommerce_currency(),
                'value' => number_format($product->get_price(),2),
                'content_type' => 'product'
            ));
            echo '</script>';
        }

        public function add_integration($integrations) {
            $integrations[] = 'WooCommerce_Facebook_Pixel_Integration';
            return $integrations;
        }

        public function load_main_js() {

            $product = false;

            if (is_singular('product'))
                $product = new WC_Product( get_the_ID() );

            $integration = $this->load_integration();
            ?>

            <?php if (!empty($integration->get_option('facebookpixel'))): ?>
                <!-- Facebook Pixel Code -->
                <script>
                !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
                n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
                document,'script','//connect.facebook.net/en_US/fbevents.js');

                fbq('init', '<?php echo $integration->get_option('facebookpixel'); ?>');
                fbq('track', 'PageView');

                <?php if ($product): ?>

                    fbq('track', 'ViewContent', {
                        content_ids: ['<?php echo $product->get_sku(); ?>'],
                        content_name: '<?php echo $product->get_title(); ?>',
                        currency: '<?php echo get_woocommerce_currency(); ?>',
                        value: <?php echo number_format($product->get_price(),2); ?>,
                        content_type: 'product'
                    });

                <?php endif; ?>

                jQuery(document).ready(function($) {
                    $(document.body).on('adding_to_cart', function(event, thisbutton, data) {
                        var product_id = data['product_id'];
                        var json = $.parseJSON( $('#facebook_pixel_'+product_id).html() );
                        fbq('track', 'AddToCart', json);
                    })
                });

                </script>
                <noscript>

                    <?php if ($product): ?>

                        <img height="1" width="1" style="display:none"
                        src="https://www.facebook.com/tr?<?php echo http_build_query(array(
                            'id' => $integration->get_option('facebookpixel'),
                            'ev' => 'ViewContent',
                            'noscript' => 1,
                            'content_ids' => $product->get_sku(),
                            'content_name' => $product->get_title(),
                            'currency' => get_woocommerce_currency(),
                            'value' => $product->get_price(),
                            'content_type' => 'product'
                        )); ?>"/>

                    <?php else: ?>

                        <img height="1" width="1" style="display:none"
                        src="https://www.facebook.com/tr?<?php echo http_build_query(array(
                            'id' => $integration->get_option('facebookpixel'),
                            'ev' => 'PageView',
                            'noscript' => 1
                        )); ?>"/>

                    <?php endif; ?>

                </noscript>
                <!-- End Facebook Pixel Code -->
            <?php endif; ?>

            <?php
        }

        public function wc_add_to_cart_message($message, $product_id) {

            $integration = $this->load_integration();
            if (empty($integration->get_option('facebookpixel'))) return;

            $product = new WC_Product($product_id);


            ob_start('tracker');
            ?>

            <img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?<?php echo http_build_query(array(
                'id' => $integration->get_option('facebookpixel'),
                'ev' => 'AddToCart',
                'noscript' => 1,
                'content_ids' => $product->get_sku(),
                'content_name' => $product->get_title(),
                'currency' => get_woocommerce_currency(),
                'value' => $product->get_price(),
                'content_type' => 'product'
            )); ?>"/>

            <?php
            $_tracker = ob_get_clean();

            $message .= ' '.$_tracker;

            return $message;
        }

        public function add_to_cart() {
            global $product;

            $integration = $this->load_integration();

            ?>

            <?php if (!empty($integration->get_option('facebookpixel'))): ?>

                <script type="text/javascript">
                    if (typeof(fbq) == 'function') {
                        fbq('track', 'AddToCart', {
                            content_ids: '<?php echo $product->get_sku(); ?>',
                            content_name: '<?php echo $product->get_title(); ?>',
                            currency: '<?php echo get_woocommerce_currency(); ?>',
                            value: <?php echo number_format($product->get_price(),2); ?>,
                            content_type: 'product'
                        });
                    }
                </script>
                <noscript>
                    <img height="1" width="1" style="display:none"
                    src="https://www.facebook.com/tr?<?php echo http_build_query(array(
                        'id' => $integration->get_option('facebookpixel'),
                        'ev' => 'AddToCart',
                        'noscript' => 1,
                        'content_ids' => $product->get_sku(),
                        'content_name' => $product->get_title(),
                        'currency' => get_woocommerce_currency(),
                        'value' => $product->get_price(),
                        'content_type' => 'product'
                    )); ?>"/>
                </noscript>

            <?php endif; ?>

            <?php

        }

        public function thankyou($order_id) {
            $integration = $this->load_integration();

            $order = new WC_Order($order_id);
            if ( !$order || empty($integration->get_option('facebookpixel')) ) return;

            $_total = number_format($order->get_total(),2);

            unset($content_ids);
            foreach ( $order->get_items() as $item_key => $item ) {
                $product = $order->get_product_from_item( $item );
                $content_ids[] = $product->get_sku();
            }
            ?>

            <?php if (!empty($integration->get_option('facebookpixel'))): ?>

                <script type="text/javascript">
                    if (typeof(fbq) == 'function') {
                        fbq('track', 'Purchase', {
                            content_type: 'product',
                            content_ids: <?php echo json_encode($content_ids) ?>,
                            value: <?php echo $_total; ?>,
                            currency: '<?php echo get_woocommerce_currency(); ?>'
                        });
                    }
                </script>
                <noscript>
                    <img height="1" width="1" style="display:none"
                        src="https://www.facebook.com/tr?<?php echo http_build_query(array(
                            'id' => $integration->get_option('facebookpixel'),
                            'ev' => 'Purchase',
                            'value' => $_total,
                            'currency' => get_woocommerce_currency(),
                            'noscript' => 1,
                            'content_ids' => join(',', $content_ids)
                        )); ?>" />
                </noscript>

            <?php endif; ?>


            <?php
        }

        public function load_integration() {
            if (!$this->_load_integration)
                $this->_load_integration = new WooCommerce_Facebook_Pixel_Integration();

            return $this->_load_integration;
        }
    }

}


/**
* Initialize the plugin.
*/
add_action('plugins_loaded', array('WooCommerce_Facebook_Pixel', 'get_instance'));
