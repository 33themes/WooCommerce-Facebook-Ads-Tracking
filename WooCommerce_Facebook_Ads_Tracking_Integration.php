<?php


class WooCommerce_Facebook_Ads_Tracking_Integration extends WC_Integration {

    public function __construct() {

        $this->id = 'woocommerce-facebook-ads-tracking';
        $this->method_title = __('Facebook Ads Tracking','woocommerce-facebook-ads-tracking');
        $this->method_description = __('Pixel tracking for Facebook Ads','woocommerce-facebook-ads-tracking');

        $this->init_form_fields();
        $this->init_settings();

        $this->add_to_cart = $this->get_option('add_to_cart');
        $this->thankyou = $this->get_option('thankyou');
        $this->facebookpixel = $this->get_option('facebookpixel');

        add_action('woocommerce_update_options_integration_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'add_to_cart'     => array(
                'title'         => 'Tracking code: Add to cart',
                'type'          => 'decimal',
                'description'   => __('Code for track every "add to cart" action', 'woocommerce-facebook-ads-tracking'),
                'default'       => '',
            ),
            'thankyou'     => array(
                'title'         => 'Tracking code: Order tranks',
                'type'          => 'decimal',
                'description'   => __('Thanyou screen in the last step of the order', 'woocommerce-facebook-ads-tracking'),
                'default'       => '',
            ),
            'facebookpixel'     => array(
                'title'         => 'NEW Facebook pixel',
                'type'          => 'decimal',
                'description'   => __('Uniq ID and track all actions', 'woocommerce-facebook-ads-tracking'),
                'default'       => '',
            ),
        );
    }

}
