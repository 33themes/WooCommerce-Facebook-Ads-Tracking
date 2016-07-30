<?php


class WooCommerce_Facebook_Pixel_Integration extends WC_Integration {

    public function __construct() {

        $this->id = 'woocommerce-facebook-pixel';
        $this->method_title = __('Facebook Pixel','woocommerce-facebook-pixel');
        $this->method_description = __('With the Facebook Pixel, you can report and optimize for conversions, build audiences and get insights about how people use your website.','woocommerce-facebook-pixel');

        $this->init_form_fields();
        $this->init_settings();

        $this->facebookpixel = $this->get_option('facebookpixel');

        add_action('woocommerce_update_options_integration_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'facebookpixel'     => array(
                'title'         => 'NEW Facebook pixel',
                'type'          => 'decimal',
                'description'   => __('Uniq ID and track all actions', 'woocommerce-facebook-pixel'),
                'default'       => '',
            )
        );
    }

}
