<?php
/**
 * Plugin Name: Multiple Shipping Options for WooCommerce
 * Plugin URI: https://minilogics.com/products/
 * Description: The plugin offers affordable shipping rates from multiple carriers, generates printable shipping labels, tracks shipments using various shipping APIs, and conveniently displays the results in the WooCommerce shopping cart and order pages.
 * Version: 1.0.1
 * Author: Mini Logics
 * Author URI: https://minilogics.com/
 * Text Domain: mini-logics
 * WC requires at least: 7.0.0
 * WC tested up to: 7.8.1
 * License: GPL version 3 - https://www.minilogics.com/
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

require_once 'vendor/autoload.php';

define('MSO_MAIN_DIR', __DIR__);
define('MSO_HITTING_URL', 'https://ws.minilogics.com/');
define('MSO_MAIN_FILE', __FILE__);
define('MSO_PLUGIN_URL', plugins_url());
define('MSO_DIR_FILE', plugin_dir_url(MSO_MAIN_FILE));

if (empty(\MsoPrerequisites\MsoPrerequisites::mso_check_prerequisites('Multiple Shipping Options for WooCommerce', '5.6', '5.7', '5.0'))) {
    require_once 'mso-install.php';
}