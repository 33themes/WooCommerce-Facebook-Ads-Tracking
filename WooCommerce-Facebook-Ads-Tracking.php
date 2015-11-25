<?php
/**
 * Plugin Name: WooCommerce Facebook Ads Tracking
 * Plugin URI: https://github.com/33themes/WooCommerce-Facebook-Ads-Tracking
 * Description: Plugin integration with Facebook Ads
 * Version: 0.0.2
 * Author: gabrielperezs
 * Author URI: http://www.33themes.com
 * License: GPL2
 */

if (!defined('ABSPATH')) exit;

if(!class_exists('WooCommerce_Facebook_Ads_Tracking')) {

    class WooCommerce_Facebook_Ads_Tracking {

        const VERSION = '0.0.2';

        private static $instance = null;

        public static function get_instance() {
            if (is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

        public function __construct() {
            if (class_exists('WC_Integration')) {
                require_once dirname(__FILE__). '/WooCommerce_Facebook_Ads_Tracking_Integration.php';

                add_filter('woocommerce_integrations', array($this, 'add_integration'));
                add_action('wp_head', array($this, 'load_main_js'));
                add_action('woocommerce_after_add_to_cart_button', array( $this, 'add_to_cart' ), 9, 1);

                add_filter('wc_add_to_cart_message', array($this,'wc_add_to_cart_message') );

                add_action('woocommerce_thankyou', array($this, 'thankyou'), 9, 1);
            }
        }

        public function add_integration($integrations) {
            $integrations[] = 'WooCommerce_Facebook_Ads_Tracking_Integration';
            return $integrations;
        }

        public function load_main_js() {
            global $product;

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
                fbq('track', "PageView");

                jQuery(document).ready(function() {
                    jQuery(document.body).on('added_to_cart', function(event) {
                        fbq('track', 'AddToCart');
                    })
                });
                </script>
                <noscript><img height="1" width="1" style="display:none"
                src="https://www.facebook.com/tr?id=<?php echo $integration->get_option('facebookpixel'); ?>&ev=PageView&noscript=1"
                /></noscript>
                <!-- End Facebook Pixel Code -->
            <?php endif; ?>

            <?php if (!empty($integration->get_option('add_to_cart'))): ?>

                <script type="text/javascript">
                (function() {
                var _fbq = window._fbq || (window._fbq = []);
                if (!_fbq.loaded) {
                var fbds = document.createElement('script');
                fbds.async = true;
                fbds.src = '//connect.facebook.net/en_US/fbds.js';
                var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(fbds, s);
                _fbq.loaded = true;
                }
                })();
                window._fbq = window._fbq || [];
                jQuery(document).ready(function() {
                    jQuery(document.body).on('added_to_cart', function(event) {
                        window._fbq.push([
                            'track', '<?php echo $integration->get_option('add_to_cart'); ?>',
                            {'value':'0.00','currency':'<?php echo get_woocommerce_currency(); ?>'}
                        ]);
                    })
                });
                </script>

            <?php endif; ?>

            <?php
        }

        public function wc_add_to_cart_message($data) {

            $integration = $this->load_integration();

            ob_start('tracker');
            ?>
            <?php if (!empty($integration->get_option('facebookpixel'))): ?>

                <img height="1" width="1" style="display:none"
                    src="https://www.facebook.com/tr?id=<?php echo $integration->get_option('facebookpixel'); ?>&ev=AddToCart&noscript=1"
                    />

            <?php endif; ?>

            <?php if (!empty($integration->get_option('add_to_cart'))): ?>

                <img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/tr?ev=<?php echo $integration->get_option('add_to_cart'); ?>&amp;cd[value]=0.00&amp;cd[currency]=<?php echo get_woocommerce_currency(); ?>&amp;noscript=1" />

            <?php endif; ?>

            <?php
            $_tracker = ob_get_clean();

            $data .= ' '.$_tracker;

            return $data;
        }

        public function add_to_cart() {
            global $product;

            $integration = $this->load_integration();

            ?>

            <?php if (!empty($integration->get_option('facebookpixel'))): ?>

                <script type="text/javascript">
                    if (typeof(fbq) == 'function') fbq('track', 'AddToCart');
                </script>
                <noscript>
                    <img height="1" width="1" style="display:none"
                        src="https://www.facebook.com/tr?id=<?php echo $integration->get_option('facebookpixel'); ?>&ev=AddToCart&noscript=1"
                        />
                </noscript>

            <?php endif; ?>

            <?php if (!empty($integration->get_option('add_to_cart'))): ?>

                <script type="text/javascript">
                    window._fbq.push([
                        'track', '<?php echo $integration->get_option('add_to_cart'); ?>',
                        {'value':'0.00','currency':'<?php echo get_woocommerce_currency(); ?>'}
                    ]);
                </script>
                <noscript>
                    <img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/tr?ev=<?php echo $integration->get_option('add_to_cart'); ?>&amp;cd[value]=0.00&amp;cd[currency]=<?php echo get_woocommerce_currency(); ?>&amp;noscript=1" />
                </noscript>

            <?php endif; ?>

            <?php

        }

        public function thankyou($order_id) {
            $integration = $this->load_integration();

            $order = new WC_Order($order_id);
            if ( !$order || ( empty($integration->get_option('facebookpixel')) && empty($integration->get_option('thankyou')) ) ) return;
            $_total = number_format($order->get_total(),2);
            ?>

            <?php if (!empty($integration->get_option('facebookpixel'))): ?>

                <script type="text/javascript">
                    if (typeof(fbq) == 'function') fbq('track', 'Purchase', {'value':'<?php echo $_total; ?>','currency':'<?php echo get_woocommerce_currency(); ?>'});
                </script>
                <noscript>
                    <img height="1" width="1" style="display:none"
                        src="https://www.facebook.com/tr?id=<?php echo $integration->get_option('facebookpixel'); ?>&ev=Purchase&value=<?php echo $_total; ?>&currency=<?php echo get_woocommerce_currency(); ?>&noscript=1"
                        />
                </noscript>

            <?php endif; ?>
            
            <?php if (!empty($integration->get_option('thankyou'))): ?>

                <script type="text/javascript">
                window._fbq.push(['track', '<?php echo $integration->get_option('thankyou'); ?>', {'value':'<?php echo $_total; ?>','currency':'<?php echo get_woocommerce_currency(); ?>'}]);
                </script>
                <noscript>
                    <img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/tr?ev=<?php echo $integration->get_option('thankyou'); ?>&amp;cd[value]=<?php echo $_total; ?>&amp;cd[currency]=<?php echo get_woocommerce_currency(); ?>&amp;noscript=1" />
                </noscript>

            <?php endif; ?>

            <?php
        }

        public function load_integration() {
            if (!$this->_load_integration)
                $this->_load_integration = new WooCommerce_Facebook_Ads_Tracking_Integration();

            return $this->_load_integration;
        }
    }

}


/**
* Initialize the plugin.
*/
add_action('plugins_loaded', array('WooCommerce_Facebook_Ads_Tracking', 'get_instance'));
