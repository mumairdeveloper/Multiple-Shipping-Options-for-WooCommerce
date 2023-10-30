<?php

namespace MsoPackage;

use MsoProductDetail\MsoProductDetail;
use ShippingSettings\ShippingSettings;
use WasaioReceiverAddress\WasaioReceiverAddress;
use WasaioCurl\WasaioCurl;

class MsoPackage
{
    public static $running_request = '';
    public static $cart_weight = 0;

    // Get package details
    static public function mso_init($package)
    {
        // Locations
        $shipments = [];
        $mso_product_detail = new MsoProductDetail();
        $locations = $mso_product_detail->mso_locations();
        $mso_mswrflfq = get_option('mso_mswrflfq');
        $mso_min_weight = isset($mso_mswrflfq) && strlen($mso_mswrflfq) > 0 && is_numeric($mso_mswrflfq) ? $mso_mswrflfq : 150;

        foreach ($package['contents'] as $key => $product) {
            $product_data = $product['data'];
            $product_id = (isset($product['variation_id']) && $product['variation_id'] > 0) ? $product['variation_id'] : $product_data->get_id();
            $settings = get_post_meta($product_id, 'mso_enable_product_setting', true);
            $origin_id = get_post_meta($product_id, 'mso_product_locations', true);

            if (isset($origin_id) && is_array($origin_id)) {
                $origin_id = reset($origin_id);
            }

            if ($origin_id > 0 && isset($locations[$origin_id])) {
                $location = $locations[$origin_id];
                $mso_address = $mso_zip = $mso_city = $mso_state = $mso_country = '';
                extract($location);
                $origin = [
                    'id' => $origin_id,
                    'address_1' => $mso_address,
                    'city' => $mso_city,
                    'postcode' => $mso_zip,
                    'state' => $mso_state,
                    'country' => $mso_country
                ];
            } else {
                $origin = self::mso_shop_base_address();
            }

            $mso_zip = isset($origin['postcode']) ? $origin['postcode'] : '';
            $shipments[$mso_zip]['ship_from'] = $origin;

            $dimension_unit = strtolower(get_option('woocommerce_dimension_unit'));
            $calculate_dimension = [
                'ft' => 12,
                'cm' => 0.3937007874,
                'mi' => 63360,
                'km' => 39370.1,
            ];

            $product_height = $product_data->get_height();
            $product_length = $product_data->get_length();
            $product_width = $product_data->get_width();

            switch ($dimension_unit) {
                case (isset($calculate_dimension[$dimension_unit])):
                    $get_height = $product_height > 0 ? round($product_height * $calculate_dimension[$dimension_unit], 2) : 0;
                    $get_length = $product_length > 0 ? round($product_length * $calculate_dimension[$dimension_unit], 2) : 0;
                    $get_width = $product_width > 0 ? round($product_width * $calculate_dimension[$dimension_unit], 2) : 0;
                    break;
                default;
                    $get_height = wc_get_dimension($product_data->get_height(), 'in');
                    $get_length = wc_get_dimension($product_data->get_length(), 'in');
                    $get_width = wc_get_dimension($product_data->get_width(), 'in');
                    break;
            }

            $weight = wc_get_weight($product_data->get_weight(), 'lbs');
            self::$cart_weight += $weight;

            (!isset($shipments[$mso_zip]['ship_weight'])) ? $shipments[$mso_zip]['ship_weight'] = 0 : '';
            $shipments[$mso_zip]['ship_weight'] += $weight;
            $quantity = $product['quantity'];
            $freight_class = self::mso_calculate_class($get_length, $get_width, $get_height, $weight, $quantity);
            $shipments[$mso_zip]['items'][] = [
                'product_id' => $product_data->get_id(),
                'variation_id' => $product['variation_id'],
                'freight_class' => $freight_class,
                'height' => $get_height,
                'length' => $get_length,
                'width' => $get_width,
                'weight' => $weight,
                'quantity' => $quantity,
                'price' => $product_data->get_price(),
                'title' => $product_data->get_title()
            ];

            if ($shipments[$mso_zip]['ship_weight'] > $mso_min_weight) {
                $shipments[$mso_zip]['action'] = 'lfq';
            }
        }

        return self::mso_request_settings($shipments);
    }

    // Calculate class
    static function mso_calculate_class($length, $width, $height, $weight, $quantity)
    {
        $pcf_classes = [
            400 => [1, 2],
            300 => [2, 3],
            250 => [3, 4],
            200 => [4, 5],
            175 => [5, 6],
            150 => [6, 7],
            125 => [7, 8],
            110 => [8, 9],
            100 => [9, 10.5],
            '92.5' => [10.5, 12],
            85 => [12, 13.5],
            '77.5' => [13.5, 15],
            70 => [15, 22.5],
            65 => [22.5, 30],
            60 => [30, 35],
            55 => [35, 50]
        ];

        $cubic_inches = ($length * $width * $height) * $quantity;
        $cubic_foot = $cubic_inches / 1728;
        $weiquan = $weight * $quantity;
        $pcf = $cubic_foot > 0 ? $weiquan / $cubic_foot : 0; // Pounds per cubic feet

        $freight_class = 50;
        switch ($pcf) {
            case $pcf < 1:
                $freight_class = 500;
                break;
            case $pcf >= 50:
                $freight_class = 50;
                break;
            default:
                foreach ($pcf_classes as $selected_class => $pcf_pair) {
                    $equgre = (isset($pcf_pair['0'])) ? $pcf_pair['0'] : 0;
                    $less = (isset($pcf_pair['1'])) ? $pcf_pair['1'] : 0;
                    if ($pcf >= $equgre && $pcf < $less) {
                        return $selected_class;
                    }
                }
                break;
        }

        return $freight_class;

    }

    // UPS SPQ credentials
    static function mso_ups_request($mso_packages, $ups)
    {
        $mso_fields = self::mso_fields([
            'mso_ups_spq_username',
            'mso_ups_spq_password'
        ]);

        $domestic_carriers = apply_filters('mso_ups_domestic_carriers', []);
        $international_carriers = apply_filters('mso_ups_international_carriers', []);
        if (get_option('mso_ups_spq_carrier_enable') == 'yes' && !empty($mso_fields) && (!empty($domestic_carriers) || !empty($international_carriers))) {
            $mso_ups_spq_username = $mso_ups_spq_password = '';
            extract($mso_fields);
            $residential_delivery = '';
            extract($ups);
            $mso_packages['carriers']['ups_spq'] = [
                'carrier_get' => MSO_UPS_GET,
                'access' => get_option('mso_ups_spq_access_key'),
                'user_id' => $mso_ups_spq_username,
                'password' => $mso_ups_spq_password,
                'shipper_number' => get_option('mso_ups_spq_account_number'),
                'residential_delivery' => $residential_delivery
            ];
        }

        return $mso_packages;
    }

    // FEDEX SPQ credentials
    static function mso_fedex_request($mso_packages, $fedex)
    {
        $mso_fields = self::mso_fields([
            'mso_fedex_spq_user_key',
            'mso_fedex_spq_user_password'
        ]);

        $domestic_carriers = apply_filters('mso_fedex_domestic_carriers', []);
        $international_carriers = apply_filters('mso_fedex_international_carriers', []);
        if (get_option('mso_fedex_spq_carrier_enable') == 'yes' && !empty($mso_fields) && (!empty($domestic_carriers) || !empty($international_carriers))) {
            $mso_fedex_spq_user_key = $mso_fedex_spq_user_password = '';
            extract($mso_fields);
            $residential_delivery = '';
            extract($fedex);
            $mso_packages['carriers']['fedex_small'] = [
                'carrier_get' => MSO_FEDEX_GET,
                'parent_key' => $mso_fedex_spq_user_key,
                'parent_password' => $mso_fedex_spq_user_password,
                'key' => $mso_fedex_spq_user_key,
                'password' => $mso_fedex_spq_user_password,
                'account_number' => get_option('mso_fedex_spq_account_number'),
                'meter_number' => get_option('mso_fedex_spq_meter_number'),
                'residential_delivery' => $residential_delivery,
                'simple' => !empty($domestic_carriers) || !empty($international_carriers) ? true : false,
            ];
        }

        return $mso_packages;
    }

    // UPS LFQ credentials
    static function mso_ups_lfq_request($mso_packages, $ups_lfq)
    {
        $mso_fields = self::mso_fields([
            'mso_ups_lfq_username',
            'mso_ups_lfq_password'
        ]);
        if (get_option('mso_ups_lfq_carrier_enable') == 'yes' && !empty($mso_fields)) {
            $mso_ups_lfq_username = $mso_ups_lfq_password = '';
            extract($mso_fields);
            $liftgate_delivery = $residential_delivery = '';
            extract($ups_lfq);
            $mso_packages['carriers']['ups_lfq'] = [
                'carrier_get' => MSO_UPS_FREIGHT_GET,
                'access' => get_option('mso_ups_lfq_access_key'),
                'user_id' => $mso_ups_lfq_username,
                'password' => $mso_ups_lfq_password,
                'shipper_number' => get_option('mso_ups_lfq_account_number'),
                'residential_delivery' => $residential_delivery,
                'liftgate_delivery' => $liftgate_delivery
            ];
        }

        return $mso_packages;
    }

    // FEDEX LFQ credentials
    static function mso_fedex_lfq_request($mso_packages, $fedex_lfq)
    {
        $mso_fields = self::mso_fields([
            'mso_fedex_lfq_user_key',
            'mso_fedex_lfq_user_password'
        ]);
        if (get_option('mso_fedex_lfq_carrier_enable') == 'yes' && !empty($mso_fields)) {
            $mso_fedex_lfq_user_key = $mso_fedex_lfq_user_password = '';
            extract($mso_fields);
            $liftgate_delivery = $residential_delivery = '';
            extract($fedex_lfq);
            $mso_packages['carriers']['fedex_lfq'] = [
                'carrier_get' => MSO_FEDEX_FREIGHT_GET,
                'parent_key' => $mso_fedex_lfq_user_key,
                'parent_password' => $mso_fedex_lfq_user_password,
                'key' => $mso_fedex_lfq_user_key,
                'password' => $mso_fedex_lfq_user_password,
                'account_number' => get_option('mso_fedex_lfq_account_number'),
                'meter_number' => get_option('mso_fedex_lfq_meter_number'),
                'billing_account_number' => get_option('mso_fedex_lfq_billing_account_number'),
                'third_party_account_number' => get_option('mso_fedex_lfq_third_party_account_number'),
                // Billing Details
                'address_1' => get_option('mso_fedex_lfq_billing_address'),
                'city' => get_option('mso_fedex_lfq_billing_city'),
                'state' => get_option('mso_fedex_lfq_billing_state'),
                'postcode' => get_option('mso_fedex_lfq_billing_zip'),
                'country' => get_option('mso_fedex_lfq_billing_country'),
                // Physical Details
                'physical_address_1' => get_option('mso_fedex_lfq_physical_address'),
                'physical_city' => get_option('mso_fedex_lfq_physical_city'),
                'physical_state' => get_option('mso_fedex_lfq_physical_state'),
                'physical_postcode' => get_option('mso_fedex_lfq_physical_zip'),
                'physical_country' => get_option('mso_fedex_lfq_physical_country'),
                'residential_delivery' => $residential_delivery,
                'liftgate_delivery' => $liftgate_delivery
            ];
        }

        return $mso_packages;
    }

    // Request settings
    static function mso_request_settings($shipments, $ship_to = [], $mso_type = 'rate')
    {
        global $woocommerce;
        $mso_packages = [];
        $mso_packages['shipments'] = $shipments;
        $mso_packages['ship_to'] = isset($ship_to) && !empty($ship_to) ? $ship_to : WasaioReceiverAddress::get_address();
        $accessorials = self::msofw_accessorials();
        $mso_packages['accessorials'] = $accessorials;
        $ups = $fedex = $fedex_lfq = $ups_lfq = [];
        extract($accessorials);

        // UPS SPQ
        $mso_packages = self::mso_ups_request($mso_packages, $ups);

        // FEDEX SPQ
        $mso_packages = self::mso_fedex_request($mso_packages, $fedex);

        // UPS LFQ
        $mso_packages = self::mso_ups_lfq_request($mso_packages, $ups_lfq);

        // FEDEX LFQ
        $mso_packages = self::mso_fedex_lfq_request($mso_packages, $fedex_lfq);

        // Pallets
        $mso_edppf = get_option('mso_edppf');
        if ($mso_edppf == 'yes') {
            $mso_packages['custom_pallets'] = self::msofw_custom_pp('mso_pallet');
        }

        // Boxes
        $mso_edpf = get_option('mso_edpf');
        if ($mso_edpf == 'yes') {
            $mso_packages['custom_bins'] = self::msofw_custom_pp('mso_packaging');
        }

        // Shipping Settings
        $mso_packages['shipping_settings'] = [
            'mso_csrfac' => get_option('mso_csrfac'),
            'mso_csrfec' => get_option('mso_csrfec'),
            'mso_mswrflfq' => get_option('mso_mswrflfq'),
            'ups' => [
                'domestic' => apply_filters('mso_ups_domestic_carriers', []),
                'international' => apply_filters('mso_ups_international_carriers', [])
            ],
            'fedex' => [
                'domestic' => apply_filters('mso_fedex_domestic_carriers', []),
                'international' => apply_filters('mso_fedex_international_carriers', [])
            ],
            'ups_lfq' => [
                'markup' => get_option('mso_ups_lfq_markup')
            ],
            'fedex_lfq' => [
                'markup' => get_option('mso_fedex_lfq_markup')
            ],
            'free_shipping' => [
                'weight_limit' => get_option('mso_free_shipping_option_weight_threshold'),
                'subtotal_limit' => get_option('mso_free_shipping_option_cart_total'),
                'cart_subtotal' => $woocommerce->cart->subtotal,
                'cart_weight' => self::$cart_weight,
//                'label' => get_option('mso_free_shipping_option_custom_rate_label'),
                'label' => 'Free shipping',
//                'cost' => get_option('mso_free_shipping_option_custom_rate_cost'),
                'cost' => 0
            ],
            'no_shipping_options' => [
                'no_ship' => get_option('mso_no_shipping_cost_enable'),
                'option' => get_option('mso_no_shipping_cost_options'),
                'message' => get_option('mso_no_shipping_option_error_message'),
                'label' => get_option('mso_no_shipping_option_custom_rate_label'),
                'cost' => get_option('mso_no_shipping_option_custom_rate_cost')
            ]
        ];

        $mso_packages['domain'] = MSO_SERVER_NAME;
        $mso_packages['mso_key'] = MSO_SERVER_KEY;
        $mso_packages['mso_currency_symbol'] = MSO_CURRENCY_SYMBOL;
        $mso_packages['api_test_mode'] = get_option('mso_api_test_mode');
//        $mso_packages['mso_type'] = isset($mso_type) && $mso_type == 'ship' ? $mso_type : 'rate';
        $mso_packages['mso_type'] = isset($mso_type) && strlen($mso_type) > 0 ? $mso_type : 'rate';

        $url = MSO_HITTING_URL . 'index.php';
        return WasaioCurl::wasaio_http_request($url, $mso_packages);

        // Requests storage in session
        self::$running_request = $running_request = md5(json_encode($mso_packages));
        $previous_request = WC()->session->get('mso_previous_requests');
        $previous_request = (is_array($previous_request) && (!empty($previous_request))) ? $previous_request : [];
        if (isset($previous_request[$running_request]) && (!empty($previous_request[$running_request]))) {
            return $previous_request[$running_request];
        } else {
            $url = MSO_HITTING_URL . 'index.php';
            return WasaioCurl::wasaio_http_request($url, $mso_packages);
        }
    }

    // Connection settings fields.
    static function mso_fields($fields)
    {
        $triggered = true;
        foreach ($fields as $index => $field_ind) {
            $field_val = sanitize_text_field(get_option($field_ind));
            $fields[$field_ind] = $field_val;
            !(isset($field_val) && is_string($field_val) && strlen($field_val) > 0) ? $triggered = false : '';
        }
        return $triggered ? $fields : [];
    }

    // Utility function that outputs the shop base address in an array
    static function mso_shop_base_address()
    {
//        $mso_state = $mso_country = '';
//        $country_state = explode(':', get_option('woocommerce_default_country'));
//        $country_state_count = count($country_state);
//        switch ($country_state_count) {
//            case 1:
//                $mso_state = isset($country_state[0]) ? $country_state[0] : '';
//                break;
//            case 2:
//                $mso_country = isset($country_state[0]) ? $country_state[0] : '';
//                $mso_state = isset($country_state[1]) ? $country_state[1] : '';
//                break;
//        }
//
//        $mso_city = get_option('woocommerce_store_city');
//        $mso_zip = get_option('woocommerce_store_postcode');

        $mso_store_shop_address = mso_store_shop_address();
        $mso_city = $mso_state = $mso_zip = $mso_country = $address_1 = $address_2 = '';
        extract($mso_store_shop_address);

        return [
            'id' => 'store_address',
            'address_1' => $address_1,
            'address_2' => $address_2,
            'city' => $mso_city,
            'postcode' => $mso_zip,
            'state' => $mso_state,
            'country' => $mso_country,
            'mso_city' => $mso_city,
            'mso_zip' => $mso_zip,
            'mso_state' => $mso_state,
            'mso_country' => $mso_country
        ];
    }

    // Accessorials
    static public function msofw_accessorials()
    {
        return
            [
                'fedex' => [
                    'residential_delivery' => get_option('mso_fedex_spq_rad')
                ],
                'ups' => [
                    'residential_delivery' => get_option('mso_ups_spq_rad')
                ],
                'ups_lfq' => [
                    'residential_delivery' => get_option('mso_ups_lfq_rad'),
                    'liftgate_delivery' => get_option('mso_ups_lfq_liftgate')
                ],
                'fedex_lfq' => [
                    'residential_delivery' => get_option('mso_fedex_lfq_rad'),
                    'liftgate_delivery' => get_option('mso_fedex_lfq_liftgate')
                ]
            ];
    }

    // Custom bins
    static public function msofw_custom_pp($post_type)
    {
        $custom_pp = [];
        $args = [
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'order' => 'ASC'
        ];

        $mso_available = '';
        switch ($post_type) {
            case 'mso_pallet':
                $mso_available = 'mso_pallet_available';
                break;
            case 'mso_packaging':
                $mso_available = 'mso_box_available';
                break;
        }

        $posts_array = get_posts($args);
        foreach ($posts_array as $post) {
            $mso_packaging_id = $post->ID;
            $mso_packaging = get_post_meta($mso_packaging_id, $post_type, true);
            $box_type = isset($mso_packaging['box_name']) ? $mso_packaging['box_name'] : '';
            $mso_apb = isset($mso_packaging[$mso_available]) ? $mso_packaging[$mso_available] : 'off';
            if ($mso_apb == 'on') {
                $mso_packaging['id'] = $mso_packaging_id;
                $mso_packaging['box_type'] = $box_type;
                $custom_pp[] = $mso_packaging;
            }
        }

        return $custom_pp;
    }

    // Fedex bins
//    static public function msofw_fedex_bins()
//    {
//        return [
//            [
//                'box_type' => 'FEDEX_ENVELOPE',
//                'box_name' => 'Fedex Envelope',
//                'inner_length' => 9.5,
//                'inner_width' => 12.5,
//                'inner_height' => 0.5,
//                'outer_length' => 0,
//                'outer_width' => 0,
//                'outer_height' => 0,
//                'box_weight' => 0,
//                'max_weight' => 10,
//            ],
//            [
//                'box_type' => 'FEDEX_ENVELOPE',
//                'box_name' => 'Fedex Reusable Envelope',
//                'inner_length' => 9.5,
//                'inner_width' => 15.5,
//                'inner_height' => 0.5,
//                'outer_length' => 0,
//                'outer_width' => 0,
//                'outer_height' => 0,
//                'box_weight' => 0,
//                'max_weight' => 10,
//            ],
//            [
//                'box_type' => 'FEDEX_PAK',
//                'box_name' => 'Fedex Pak - Small',
//                'inner_length' => 10.25,
//                'inner_width' => 12.75,
//                'inner_height' => 1.5,
//                'outer_length' => 0,
//                'outer_width' => 0,
//                'outer_height' => 0,
//                'box_weight' => 0,
//                'max_weight' => 50,
//            ],
//            [
//                'box_type' => 'FEDEX_PAK',
//                'box_name' => 'Fedex Pak - Large',
//                'inner_length' => 12,
//                'inner_width' => 15.5,
//                'inner_height' => 1.5,
//                'outer_length' => 0,
//                'outer_width' => 0,
//                'outer_height' => 0,
//                'box_weight' => 0,
//                'max_weight' => 50,
//            ],
//            [
//                'box_type' => 'FEDEX_PAK',
//                'box_name' => 'Fedex Pak - Padded',
//                'inner_length' => 11.75,
//                'inner_width' => 14.75,
//                'inner_height' => 1.25,
//                'outer_length' => 0,
//                'outer_width' => 0,
//                'outer_height' => 0,
//                'box_weight' => 0,
//                'max_weight' => 50,
//            ],
//            [
//                'box_type' => 'FEDEX_PAK',
//                'box_name' => 'Fedex Pak - Reusable',
//                'inner_length' => 10,
//                'inner_width' => 14.5,
//                'inner_height' => 1.25,
//                'outer_length' => 0,
//                'outer_width' => 0,
//                'outer_height' => 0,
//                'box_weight' => 0,
//                'max_weight' => 50,
//            ],
//            [
//                'box_type' => 'FEDEX_SMALL_BOX',
//                'box_name' => 'Fedex Small Box',
//                'inner_length' => 10.875,
//                'inner_width' => 1.5,
//                'inner_height' => 12.375,
//                'outer_length' => 0,
//                'outer_width' => 0,
//                'outer_height' => 0,
//                'box_weight' => 0,
//                'max_weight' => 50,
//            ],
//            [
//                'box_type' => 'FEDEX_SMALL_BOX',
//                'box_name' => 'Fedex Small Box',
//                'inner_length' => 8.75,
//                'inner_width' => 2.625,
//                'inner_height' => 11.25,
//                'outer_length' => 0,
//                'outer_width' => 0,
//                'outer_height' => 0,
//                'box_weight' => 0,
//                'max_weight' => 50,
//            ],
//            [
//                'box_type' => 'FEDEX_MEDIUM_BOX',
//                'box_name' => 'Fedex Medium Box',
//                'inner_length' => 11.5,
//                'inner_width' => 2.375,
//                'inner_height' => 13.25,
//                'outer_length' => 0,
//                'outer_width' => 0,
//                'outer_height' => 0,
//                'box_weight' => 0,
//                'max_weight' => 50,
//            ],
//            [
//                'box_type' => 'FEDEX_MEDIUM_BOX',
//                'box_name' => 'Fedex Medium Box',
//                'inner_length' => 8.75,
//                'inner_width' => 4.375,
//                'inner_height' => 11.25,
//                'outer_length' => 0,
//                'outer_width' => 0,
//                'outer_height' => 0,
//                'box_weight' => 0,
//                'max_weight' => 50,
//            ],
//            [
//                'box_type' => 'FEDEX_LARGE_BOX',
//                'box_name' => 'Fedex Large Box',
//                'inner_length' => 12.375,
//                'inner_width' => 3,
//                'inner_height' => 17.5,
//                'outer_length' => 0,
//                'outer_width' => 0,
//                'outer_height' => 0,
//                'box_weight' => 0,
//                'max_weight' => 50,
//            ],
//            [
//                'box_type' => 'FEDEX_LARGE_BOX',
//                'box_name' => 'Fedex Large Box',
//                'inner_length' => 8.75,
//                'inner_width' => 7.75,
//                'inner_height' => 11.25,
//                'outer_length' => 0,
//                'outer_width' => 0,
//                'outer_height' => 0,
//                'box_weight' => 0,
//                'max_weight' => 50,
//            ],
//            [
//                'box_type' => 'FEDEX_EXTRA_LARGE_BOX',
//                'box_name' => 'Fedex Extra Large Box',
//                'inner_length' => 11.875,
//                'inner_width' => 10.75,
//                'inner_height' => 11,
//                'outer_length' => 0,
//                'outer_width' => 0,
//                'outer_height' => 0,
//                'box_weight' => 0,
//                'max_weight' => 50,
//            ],
//            [
//                'box_type' => 'FEDEX_EXTRA_LARGE_BOX',
//                'box_name' => 'Fedex Extra Large Box',
//                'inner_length' => 15.75,
//                'inner_width' => 14.125,
//                'inner_height' => 6,
//                'outer_length' => 0,
//                'outer_width' => 0,
//                'outer_height' => 0,
//                'box_weight' => 0,
//                'max_weight' => 50,
//            ],
//        ];
//    }

    /**
     * Shipment rates
     * @param array $shipments
     * @param array $accessorials
     * @return array|void
     */
//    static public function mso_shipment_rates($shipments, $accessorials, $ship_to, $order_page = false)
//    {
//        $mso_rates = [];
//        $error_from_api_detected = false;
//        $mso_package_obj = new MsoPackage();
//        foreach ($shipments as $zip => $response) {
//
//            $ship_from = (isset($response['ship_from'])) ? $response['ship_from'] : [];
//            $ship_from_country = isset($ship_from['country']) ? $ship_from['country'] : '';
//            $ship_to_country = isset($ship_to['country']) ? $ship_to['country'] : '';
//            $is_shipment = $ship_from_country != $ship_to_country ? 'international' : 'domestic';
//
//            // Fedex SPQ Domestic Rate
//            $fedex_carriers_obj = new \MsoFedexCarriers\MsoFedexCarriers();
//            $rates = (isset($response['fedex_rate']['domestic_rate']['rate'])) ? $response['fedex_rate']['domestic_rate']['rate'] : [];
//            $packaging_type = (isset($response['fedex_rate']['domestic_rate']['rate']['package']['box_type'])) ? $response['fedex_rate']['domestic_rate']['rate']['package']['box_type'] : 'YOUR_PACKAGING';
//            $get_rates = $fedex_carriers_obj->mso_fedex_rates($rates, $response, $packaging_type, $accessorials, $is_shipment);
//            if ($order_page && isset($get_rates['error'])) {
//                $mso_rates[$zip]['spq']['fedex'] = $get_rates;
//            } else {
//                $get_rates = $mso_package_obj->mso_csrec($get_rates);
//                !empty($get_rates) ? $mso_rates[$zip]['spq']['fedex'] = $get_rates : '';
//            }
//
//            // Fedex SPQ One Rate
////            $rates = (isset($response['fedex_rate']['one_rate']['rate'])) ? $response['fedex_rate']['one_rate']['rate'] : [];
////            $packaging_type = (isset($response['fedex_rate']['one_rate']['package']['box_type'])) ? $response['fedex_rate']['one_rate']['package']['box_type'] : 'YOUR_PACKAGING';
////            $get_rates = $fedex_carriers_obj->mso_fedex_rates($rates, $response, $packaging_type, $accessorials);
////            $get_rates = $mso_package_obj->mso_csrec($get_rates);
////            !empty($get_rates) ? $mso_rates[$zip]['spq']['fedex_one_rate'] = $get_rates : '';
//
//            // UPS SPQ
//            $rates = (isset($response['ups_rate'])) ? $response['ups_rate'] : [];
//            $ups_carriers_obj = new \MsoUpsCarriers\MsoUpsCarriers();
//            $get_rates = $ups_carriers_obj->mso_ups_rates($rates, $response, $accessorials, $is_shipment);
//            if ($order_page && isset($get_rates['error'])) {
////                $error_from_api_detected = true;
//                $mso_rates[$zip]['spq']['ups'] = $get_rates;
//            } else {
//                $get_rates = $mso_package_obj->mso_csrec($get_rates);
//                !empty($get_rates) ? $mso_rates[$zip]['spq']['ups'] = $get_rates : '';
//            }
//
//            // UPS LFQ
//            $rates = (isset($response['ups_lfq_rate'])) ? $response['ups_lfq_rate'] : [];
//            $ups_carriers_obj = new \MsoUpsLfqCarriers\MsoUpsLfqCarriers();
//            $get_rates = $ups_carriers_obj->mso_ups_rates($rates, $response, $accessorials);
//            if ($order_page && isset($get_rates['error'])) {
////                $error_from_api_detected = true;
//                $mso_rates[$zip]['lfq']['ups'] = $get_rates;
//            } else {
//                $get_rates = $mso_package_obj->mso_csrec($get_rates);
//                !empty($get_rates) ? $mso_rates[$zip]['lfq']['ups'] = $get_rates : '';
//            }
//
//            // Fedex LFQ
//            $rates = (isset($response['fedex_lfq_rate'])) ? $response['fedex_lfq_rate'] : [];
//            $fedex_carriers_obj = new \MsoFedexLfqCarriers\MsoFedexLfqCarriers();
//            $get_rates = $fedex_carriers_obj->mso_fedex_rates($rates, $response, $accessorials);
//            if ($order_page && isset($get_rates['error'])) {
////                $error_from_api_detected = true;
//                $mso_rates[$zip]['lfq']['fedex'] = $get_rates;
//            } else {
//                $get_rates = $mso_package_obj->mso_csrec($get_rates);
//                !empty($get_rates) ? $mso_rates[$zip]['lfq']['fedex'] = $get_rates : '';
//            }
//        }
//
////        if (!$error_from_api_detected) {
////            $previous_request = WC()->session->get('mso_previous_requests');
////            $previous_request[$running_request] = $running_response;
////            WC()->session->set('mso_previous_requests', $previous_request);
////        }
//
//        return $mso_rates;
//    }

    // Lowest shipping option from each carrier
//    public function mso_csrec($rates)
//    {
//        $mso_csrfec = get_option('mso_csrfec');
//        if ($mso_csrfec == 'yes') {
//            $rates = $this->mso_sort_asec($rates, 'cost');
//            $rates = $this->mso_cheapest_single_rate($rates, 1);
//        }
//        return $rates;
//    }
//
//    // Cheapest single rate
//    public function mso_cheapest_single_rate($rates, $partition_point)
//    {
//        return !empty($rates) && is_array($rates) ? array_slice($rates, 0, $partition_point) : [];
//    }

    // Sorting array
    public function mso_sort_asec($rates, $index)
    {
        $price_sorted_key = array();
        foreach ($rates as $key => $cost_carrier) {
            $price_sorted_key[$key] = (isset($cost_carrier[$index])) ? $cost_carrier[$index] : 0;
        }
        array_multisort($price_sorted_key, SORT_ASC, $rates);

        return $rates;
    }
}