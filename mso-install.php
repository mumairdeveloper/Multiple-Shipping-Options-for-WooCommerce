<?php

use MsoCsv\MsoCsv;

/**
 * install hook
 */
function mso_install()
{
    apply_filters('mso_activation_hook', false);
}

register_activation_hook(MSO_MAIN_FILE, 'mso_install');

/**
 * uninstall hook
 */
function mso_uninstall()
{
    apply_filters('company_name_deactivation_hook', false);
}

register_deactivation_hook(MSO_MAIN_FILE, 'mso_uninstall');


/**
 * init
 */
function mso_init()
{
    define('MSO_CURRENCY_SYMBOL', get_woocommerce_currency_symbol(get_option('woocommerce_currency')));
}

add_filter('init', 'mso_init');

/**
 * Custom error message
 * @param $message
 * @return string
 */
function mso_default_cart_error_message($message)
{
    $cart_error_message = apply_filters('mso_default_cart_error_message', '');
    strlen($cart_error_message) > 0 ? $message = $cart_error_message : '';
    return $message;
}

add_filter('woocommerce_cart_no_shipping_available_html', 'mso_default_cart_error_message', 9999999999, 1);

/**
 * Form template
 */
function mso_form_template($form_fields)
{
    $template = '<table class="form-table mso_table">';
    $template .= '<tbody>';
    foreach ($form_fields as $key => $form_field) {
        $name = $type = $default = $desc = $id = $class = $options = $tr_class = $value = '';
        extract($form_field);

        // Label for
        $template .= '<tr valign="top" class="' . $tr_class . '">';
        $template .= '<th scope="row" class="titledesc">';
        $template .= '<label for="' . $name . '">' . $name . '</label>';
        $template .= '</th>';

        // Form type
        switch ($type) {

            case 'title':
                $template .= '<td class="forminp forminp-text">';
                $template .= '<p class="description">' . $desc . '</p>';
                $template .= '</td>';
                break;

            case 'select':
                $template .= '<td class="forminp forminp-select">';
                $template .= '<select name="' . $id . '" class="' . $id . '">';
                foreach ($options as $option_id => $option) {
                    $selected = $option_id == $default ? 'selected="selected"' : '';
                    $template .= '<option value="' . $option_id . '" ' . $selected . '>' . $option . '</option>';
                }
                $template .= '</select>';
                $template .= $id == 'mso_order_shipment_origin' ? '<span class="description">' . $desc . '</span>' : '<p class="description">' . $desc . '</p>';
                $template .= '</td>';
                break;

            case 'checkbox':
                $checked = $default == 'yes' ? 'checked="checked"' : '';
                $template .= '<td class="forminp forminp-checkbox">';
                $template .= '<input name="' . $id . '" id="' . $id . '" type="checkbox" class="' . $class . '" ' . $checked . '>';
                $template .= '<span class="description">' . $desc . '</span>';
                $template .= '</td>';
                break;

            case 'radio':
                $checked = $default == 'yes' ? 'checked="checked"' : '';
                $template .= '<td class="forminp forminp-checkbox">';
                $template .= '<input value = "' . $value . '" name="' . $id . '" id="' . $id . '" type="radio" class="' . $class . '" ' . $checked . '>';
                $template .= '<span class="description">' . $desc . '</span>';
                $template .= '</td>';
                break;

            case 'shipping_order_radio':
                $template .= '<td class="forminp forminp-radio">';
                foreach ($options as $option_id => $option) {
                    $template .= '<input type="radio" name="' . $id . '" id="' . $option_id . '">';
                    $template .= '<label for="' . $option_id . '">' . $option['label'] . ':  ' . get_woocommerce_currency_symbol() . $option['cost'] . '</label><br>';
                }
                $template .= '</td>';
                break;

            case 'text':
                $template .= '<td class="forminp forminp-text">';
                $template .= '<input name="' . $id . '" id="' . $id . '" type="text" class="' . $class . '">';
                $template .= '<p class="description">' . $desc . '</p>';
                $template .= '</td>';
                break;
        }

        $template .= '</tr>';
    }
    $template .= '</tbody>';
    $template .= '</table>';

    return $template;
}

add_filter('mso_form_template', 'mso_form_template', 10, 1);

/**
 * Load tab file
 * @param $settings
 * @return array
 */
function mso_settings_pages($settings)
{
    $settings[] = include('admin/tab/mso-tab.php');
    return $settings;
}

add_filter('woocommerce_get_settings_pages', 'mso_settings_pages');

/**
 * Show action links on plugins page
 * @param $actions
 * @param $plugin_file
 * @return array
 */
function mso_action_links($actions, $plugin_file)
{
    static $plugin;
    if (!isset($plugin)) {
        $plugin = plugin_basename(MSO_MAIN_FILE);
    }

    if ($plugin == $plugin_file) {
        $settings = array('settings' => '<a href="admin.php?page=wc-settings&tab=mso">' . __('Settings', 'General') . '</a>');
        $site_link = array('support' => '<a href="https://minilogics.com" target="_blank">Support</a>');
        $actions = array_merge($settings, $actions);
        $actions = array_merge($site_link, $actions);
    }

    return $actions;
}

add_filter('plugin_action_links', 'mso_action_links', 10, 2);

/**
 * Mso admin load admin side files of css and js hook
 */
function mso_admin_enqueue_scripts()
{
    // css
    wp_register_style('mso_admin_style', MSO_DIR_FILE . '/admin/assets/css/mso-admin.css', false, '1.0.1');
    wp_enqueue_style('mso_admin_style');

    // default bootstrap css library
//    wp_register_style('mso_bootstrap_iso', MSO_DIR_FILE . '/admin/assets/css/mso-bootstrap.css', false, '1.0.0');
//    wp_enqueue_style('mso_bootstrap_iso');

    // Print label css library
    wp_register_style('mso_print_style', MSO_DIR_FILE . '/admin/assets/css/mso-print.css', false, '1.0.1');
    wp_enqueue_style('mso_print_style');

    // JTV css library
    wp_register_style('mso_jtv_style', MSO_DIR_FILE . '/admin/assets/css/mso-jtv.css', false, '1.0.1');
    wp_enqueue_style('mso_jtv_style');

    // js
    wp_enqueue_script('mso_admin_script', MSO_DIR_FILE . '/admin/assets/js/mso-admin.js', [], '1.0.1');
    wp_localize_script('mso_admin_script', 'mso_script', [
        'mso_paid_plan_feature' => MSO_PAID_PLAN_FEATURE_DIALOG,
//        'mso_url' => MSO_PLUGIN_URL,
//        'mso_fedex_sqp' => get_option('mso_fedex_sqp'),
//        'mso_ups_sqp' => get_option('mso_ups_sqp'),
//        'mso_fedex_lfq' => get_option('mso_fedex_lfq'),
//        'mso_ups_lfq' => get_option('mso_ups_lfq')
    ]);

    // Print label js library
    wp_enqueue_script('mso_print_script', MSO_DIR_FILE . '/admin/assets/js/mso-print.js', ['jquery'], '1.0.1');
    wp_localize_script('mso_print_script', 'mso_print_script', []);

    // JTV js library
    wp_enqueue_script('mso_jtv_script', MSO_DIR_FILE . '/admin/assets/js/mso-jtv.js', ['jquery'], '1.0.1');
    wp_localize_script('mso_jtv_script', 'mso_jtv_script', []);
}

add_action('admin_enqueue_scripts', 'mso_admin_enqueue_scripts');


/**
 * Mso frontend load side files of css and js hook
 */
function mso_frontend_enqueue_scripts()
{
    // js
    wp_enqueue_script('mso_frontend_script', MSO_DIR_FILE . '/shipping/checkout/assets/js/mso-frontend.js', ['jquery'], '1.0.0');
    wp_localize_script('mso_frontend_script', 'mso_script', []);
}

add_action('wp_enqueue_scripts', 'mso_frontend_enqueue_scripts');

/**
 * Mso method in woo method list
 * @param $methods
 * @return string
 */
function mso_add_shipping_method($methods)
{
    $methods['mso'] = 'MsoShipping';
    return $methods;
}

add_filter('woocommerce_shipping_methods', 'mso_add_shipping_method', 10, 1);

/**
 * Get Host
 * @param type $url
 * @return type
 */
function mso_get_host($url)
{
    $parse_url = parse_url(trim($url));
    if (isset($parse_url['host'])) {
        $host = $parse_url['host'];
    } else {
        $path = explode('/', $parse_url['path']);
        $host = $path[0];
    }
    return trim($host);
}

/**
 * Add shipping zone, shipping method
 */
function mso_aszsm()
{
    $shipping_method_mso = false;
    $shop_country = WC()->countries->get_base_country();
    if (class_exists('WC_Shipping_Zones')) {
        $shipping_zones = new WC_Shipping_Zones();
        $get_zones = $shipping_zones::get_zones();
        foreach ($get_zones as $key => $get_zone) {
            $zone_id = isset($get_zone['id']) ? $get_zone['id'] : 0;
            $zone_locations = isset($get_zone['zone_locations']) ? $get_zone['zone_locations'] : [];
            $shipping_methods = isset($get_zone['shipping_methods']) ? $get_zone['shipping_methods'] : [];
            foreach ($shipping_methods as $key => $shipping_method) {
                $shipping_method_id = isset($shipping_method->id) ? $shipping_method->id : '';
                $shipping_method_id == 'mso' ? $shipping_method_mso = true : '';
            }

            if (!$shipping_method_mso) {
                foreach ($zone_locations as $key => $zone_location) {
                    $type = isset($zone_location->type) ? $zone_location->type : '';
                    $code = isset($zone_location->code) ? $zone_location->code : '';
                    if ($type == 'country' && strtolower($shop_country) == strtolower($code)) {
                        $shipping_method_mso = true;
                        $zone = $shipping_zones::get_zone($zone_id);
                        $zone->add_shipping_method('mso');
                        $zone->save();
                        continue;
                    }
                }
            }
        }
    }

    if (!$shipping_method_mso && class_exists('WC_Shipping_Zone')) {
        $shipping_zone = new WC_Shipping_Zone();
        $shipping_zone->set_zone_name($shop_country);
        $shipping_zone->set_locations([[
            'code' => $shop_country,
            'type' => 'country'
        ]]);
        $shipping_zone->add_shipping_method('mso');
        $shipping_zone->save();
    }
}

add_filter('mso_activation_hook', 'mso_aszsm');

// Receive request for update plan
add_action('rest_api_init', function () {
    register_rest_route('mso', '/v1', [
        'methods' => 'GET',
        'callback' => 'msoup',
        'permission_callback' => '__return_true'
    ]);

    // Update plan
    function msoup($request)
    {
        if (isset($request['key'], $request['domain'])) {
            $post_data = [
                'mso_key' => $request['key'],
                'domain' => $request['domain'],
                'mso_type' => 'key'
            ];
            $url = MSO_HITTING_URL . 'key.php';
            $wasaio_curl = new \WasaioCurl\WasaioCurl();
            $mso_api_results = json_decode($wasaio_curl::wasaio_http_request($url, $post_data), true);
            if (isset($mso_api_results['severity'], $mso_api_results['message'])) {
                $severity = $mso_api_results['severity'];
                $style_color = $show_status = '';
                $subscriptions = [];
                switch ($severity) {
                    case 'error':
                        $action = false;
                        $style_color = 'red';
                        $show_status = 'Error';
                        break;
                    case 'success':
                        $action = true;
                        $style_color = 'green';
                        $show_status = 'Success';
                        $subscriptions = $mso_api_results['subscriptions'];
                        break;
                }

                $message = '<span style="color: ' . $style_color . ';"><b>' . $show_status . '! </b> ' . $mso_api_results['message'] . '</span>';
                update_option('mso_key_status', $message);
                update_option('mso_key_direction', $severity);
                update_option('mso_key_subscriptions', json_encode($subscriptions));
            }
        }
    }
});

/**
 * Check API connection status
 */
function mso_cfas($status)
{
    return isset($status) && is_string($status) && strlen($status) > 0 ? $status : '<span style="color: black;">To see the updated status, please click on the "Test Connection" button.</span>';
}

/**
 * Implode carrier
 */
function mso_implode_carriers($carriers)
{
    $carrier_str = '';
    foreach ($carriers as $key => $carrier) {
        $carrier_name = "<span class='mso_implode_carrier'>$carrier</span>";
        $carrier_str .= strlen($carrier_str) > 0 ? ", $carrier_name" : $carrier_name;
    }
    return $carrier_str;
}

/**
 * Store shop address
 */
function mso_store_shop_address()
{
    $mso_state = $mso_country = '';
    $country_state = explode(':', get_option('woocommerce_default_country'));
    $country_state_count = count($country_state);
    switch ($country_state_count) {
        case 1:
            $mso_state = isset($country_state[0]) ? $country_state[0] : '';
            break;
        case 2:
            $mso_country = isset($country_state[0]) ? $country_state[0] : '';
            $mso_state = isset($country_state[1]) ? $country_state[1] : '';
            break;
    }

    return [
        'mso_city' => get_option('woocommerce_store_city'),
        'mso_state' => $mso_state,
        'mso_zip' => get_option('woocommerce_store_postcode'),
        'mso_country' => $mso_country,
        'address_1' => get_option('woocommerce_store_address'),
        'address_2' => get_option('woocommerce_store_address_2'),
    ];
}

/**
 * Get Domain Name
 */
function mso_get_server_name()
{
    global $wp;
    $wp_request = (isset($wp->request)) ? $wp->request : '';
    $url = home_url($wp_request);
    return mso_get_host($url);
}

// Define server name
define('MSO_SERVER_NAME', mso_get_server_name());
define('MSO_SERVER_KEY', trim(get_option('mso_paid_key')));

// Don't auth for specific stores
$dont_auth_action = false;
$dont_auth_store = [];
if (in_array(MSO_SERVER_NAME, $dont_auth_store)) {
    $dont_auth_action = true;
}

define('MSO_DONT_AUTH', $dont_auth_action);

// Define plan status
define('MSO_SUBS_LINK', '<a target="_blank" href="https://minilogics.com/subscription">Mini Logics</a>');
define('MSO_PAID_PLAN_REQUIRE_SINGLE_CARRIER', 'The following features are paid; you need to purchase at least one carrier subscription from ' . MSO_SUBS_LINK . '.');
define('MSO_PAID_PLAN_FEATURE', 'The following features are paid; you need to purchase a desired carrier subscription from ' . MSO_SUBS_LINK . ' in order to use their services.');
define('MSO_PAID_PLAN_FEATURE_DIALOG', 'This particular feature requires a paid subscription. To utilize this service, please purchase the desired carrier subscription from Mini Logics.');
define('MSO_PAID_PLAN_MESSAGE', 'Your current subscription is active for %s; You can manage the desired carrier subscription from ' . MSO_SUBS_LINK . ' in order to use their services.');
define('MSO_KEY_ERROR', '<span style="color: red;"><b>Error! </b>Please make sure to synchronize the plugin with ' . MSO_SUBS_LINK . ' by entering the correct MSO key for authorizations at the top of the plugin settings page.</span>');
define('MSO_PALLET_DESC', 'Pallets are used to identify a pallet solution before obtaining shipping rate estimates and are part of the pallet process available for UPS LTL freight shipping and Fedex LTL freight shipping.');
define('MSO_BOXES_DESC', 'Packaging is used to identify a packaging solution before obtaining shipping rate estimates and is part of the packaging process available for UPS small package shipping and Fedex small package shipping.');
//define('MSO_PLAN_DESC', 'Upgrade to access premium features by visiting our website, <a href="https://minilogics.com">Mini Logics</a>, and creating a subscription.');
//define('MSO_BELOW_PLAN_DESC', 'To access the premium features below, please upgrade by visiting our website, <a href="https://minilogics.com">Mini Logics</a>, and creating a subscription.');
//define('MSO_ONE_PLAN_DESC', 'To access all the features below, you are required to have a minimum of one paid subscription by visiting our website, <a href="https://minilogics.com">Mini Logics</a>, and creating an account.');
define('MSO_PLAN_STATUS', get_option('mso_key_direction'));
define('MSO_KEY_STATUS', get_option('mso_key_status'));
$mks = get_option('mso_key_subscriptions');
define('MSO_SUBSCRIPTIONS', isset($mks) && strlen($mks) > 0 && $mks != NULL ? json_decode($mks, true) : []);

// Carrier Id's
define('MSO_UPS_GET', '1');
define('MSO_FEDEX_GET', '2');
define('MSO_UPS_FREIGHT_GET', '3');
define('MSO_FEDEX_FREIGHT_GET', '4');

// Woocommerce shipping init
new MsoShippingInit();

// Packaging
new \MsoPackagingAjax\MsoPackagingAjax();

// Order page
new MsoOrder\MsoOrder();

// Carrier settings ajax
new \MsoSettingsAjax\MsoSettingsAjax();

// Shipping settings
new \ShippingSettings\ShippingSettings();

// Ups Carrier list
//new \MsoUpsCarriers\MsoUpsCarriers();
//
//// Fedex Carrier list
//new \MsoFedexCarriers\MsoFedexCarriers();

// Product detail page
$mso_product_obj = new \MsoProductDetail\MsoProductDetail();

// CSV import/export
require_once __DIR__ . '/admin/csv/mso-csv.php';
new MsoCsv($mso_product_obj->mso_locations());