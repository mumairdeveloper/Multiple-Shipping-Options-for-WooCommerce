<?php

namespace ShippingSettings;

use MsoUps\MsoUps;
use MsoFedex\MsoFedex;

class ShippingSettings
{

    public function __construct()
    {
        // UPS
        add_filter('mso_ups_domestic_carriers', [$this, 'mso_ups_domestic_carriers'], 10, 1);
        add_filter('mso_ups_international_carriers', [$this, 'mso_ups_international_carriers'], 10, 1);
        // Fedex
        add_filter('mso_fedex_domestic_carriers', [$this, 'mso_fedex_domestic_carriers'], 10, 1);
        add_filter('mso_fedex_international_carriers', [$this, 'mso_fedex_international_carriers'], 10, 1);
    }

    // Get ups domestic carriers
    public function mso_ups_domestic_carriers($carriers)
    {
        $carrier_enabled = false;
        $carriers = [
            '03' => 'ground',
            '02' => '2nd_day_air',
            '59' => '2nd_day_air_am',
            '13' => 'next_day_air_saver',
            '01' => 'next_day_air',
            '14' => 'next_day_air_early',
            '12' => '3_day_select'
        ];

        $carriers_force = MSO_PLAN_STATUS != 'success' || !(!empty(MSO_SUBSCRIPTIONS) && isset(MSO_SUBSCRIPTIONS[MSO_UPS_GET])) ? true : false;
        $template = MsoUps::mso_init();
        $carrier_common = 'mso_ups_spq_';
        foreach ($carriers as $key => $carrier) {
            $action = get_option($carrier_common . $carrier . '_action');
            $label_path = $carrier_common . $carrier . '_label';
            $label = get_option($label_path);
            $label = (isset($label) && strlen($label) > 0) ? $label : $template[$label_path]['placeholder'];
            if ($action == 'yes' || $carriers_force) {
                $carrier_enabled = true;
                $carriers[$key] = [
                    'label' => $label,
                    'markup' => get_option($carrier_common . $carrier . '_markup'),
                ];
            }
        }

        !$carrier_enabled ? $carriers = [] : '';
        return $carriers;
    }

    // Get ups international carriers
    public function mso_ups_international_carriers($carriers)
    {
        $carrier_enabled = false;
        $carriers = [
            '11' => 'standard',
            '08' => 'expedited',
            '65' => 'express_saver',
            '07' => 'express',
            '54' => 'express_plus'
        ];

        $carriers_force = MSO_PLAN_STATUS != 'success' || !(!empty(MSO_SUBSCRIPTIONS) && isset(MSO_SUBSCRIPTIONS[MSO_UPS_GET])) ? true : false;
        $template = MsoUps::mso_init();
        $carrier_common = 'mso_ups_spq_';
        foreach ($carriers as $key => $carrier) {
            $action = get_option($carrier_common . $carrier . '_action');
            $label_path = $carrier_common . $carrier . '_label';
            $label = get_option($label_path);
            $label = (isset($label) && strlen($label) > 0) ? $label : $template[$label_path]['placeholder'];
            if ($action == 'yes' || $carriers_force) {
                $carrier_enabled = true;
                $carriers[$key] = [
                    'label' => $label,
                    'markup' => get_option($carrier_common . $carrier . '_markup'),
                ];
            }
        }

        !$carrier_enabled ? $carriers = [] : '';
        return $carriers;
    }

    // Get fedex domestic carriers
    public function mso_fedex_domestic_carriers($carriers)
    {
        $carrier_enabled = false;
        $carriers = [
            'GROUND_HOME_DELIVERY' => 'home_delivery',
            'FEDEX_GROUND' => 'ground',
            'FEDEX_EXPRESS_SAVER' => 'express_saver',
            'FEDEX_2_DAY' => '2nd_day',
            'FEDEX_2_DAY_AM' => '2nd_day_am',
            'STANDARD_OVERNIGHT' => 'standard_overnight',
            'PRIORITY_OVERNIGHT' => 'priority_overnight',
            'FIRST_OVERNIGHT' => 'first_overnight',
//            'SMART_POST' => 'smart_post'
        ];

        $carriers_force = MSO_PLAN_STATUS != 'success' || !(!empty(MSO_SUBSCRIPTIONS) && isset(MSO_SUBSCRIPTIONS[MSO_FEDEX_GET])) ? true : false;
        $template = MsoFedex::mso_init();
        $carrier_common = 'mso_fedex_spq_';
        foreach ($carriers as $key => $carrier) {
            $action = get_option($carrier_common . $carrier . '_action');
            $label_path = $carrier_common . $carrier . '_label';
            $label = get_option($label_path);
            $label = (isset($label) && strlen($label) > 0) ? $label : $template[$label_path]['placeholder'];
            if ($action == 'yes' || $carriers_force) {
                $carrier_enabled = true;
                $carriers[$key] = [
                    'label' => $label,
                    'markup' => get_option($carrier_common . $carrier . '_markup'),
                ];
            }
        }

        !$carrier_enabled ? $carriers = [] : '';
        return $carriers;
    }

    // Get fedex international carriers
    public function mso_fedex_international_carriers($carriers)
    {
        $carrier_enabled = false;
        $carriers = [
            'FEDEX_GROUND' => 'international_ground',
            'INTERNATIONAL_ECONOMY' => 'international_economy',
            'international_economy_distribution' => 'international_economy_distribution',
            'international_economy_freight' => 'international_economy_freight',
            'international_first' => 'international_first',
            'INTERNATIONAL_PRIORITY' => 'international_priority',
            'international_priority_distribution' => 'international_priority_distribution',
            'international_priority_freight' => 'international_priority_freight',
            'international_distribution_freight' => 'international_distribution_freight'
        ];

        $carriers_force = MSO_PLAN_STATUS != 'success' || !(!empty(MSO_SUBSCRIPTIONS) && isset(MSO_SUBSCRIPTIONS[MSO_FEDEX_GET])) ? true : false;
        $template = MsoFedex::mso_init();
        $carrier_common = 'mso_fedex_spq_';
        foreach ($carriers as $key => $carrier) {
            $action = get_option($carrier_common . $carrier . '_action');
            $label_path = $carrier_common . $carrier . '_label';
            $label = get_option($label_path);
            $label = (isset($label) && strlen($label) > 0) ? $label : $template[$label_path]['placeholder'];
            if ($action == 'yes' || $carriers_force) {
                $carrier_enabled = true;
                $carriers[$key] = [
                    'label' => $label,
                    'markup' => get_option($carrier_common . $carrier . '_markup'),
                ];
            }
        }

        !$carrier_enabled ? $carriers = [] : '';
        return $carriers;
    }

    // Get fedex one rate carriers
    public function mso_fedex_one_rate_carriers($carriers)
    {
        $carrier_enabled = false;
        $carriers = [
            'FEDEX_EXPRESS_SAVER' => 'express_saver',
            'FEDEX_2_DAY' => '2nd_day',
            'FEDEX_2_DAY_AM' => '2nd_day_am',
            'STANDARD_OVERNIGHT' => 'standard_overnight',
            'PRIORITY_OVERNIGHT' => 'priority_overnight',
            'FIRST_OVERNIGHT' => 'first_overnight'
        ];

        $template = MsoFedex::mso_init();
        $carrier_common = 'mso_fedex_spq_one_rate_';
        foreach ($carriers as $key => $carrier) {
            $action = get_option($carrier_common . $carrier . '_action');
            $label_path = $carrier_common . $carrier . '_label';
            $label = get_option($label_path);
            $label = (isset($label) && strlen($label) > 0) ? $label : $template[$label_path]['placeholder'];
            if ($action == 'yes' || !$this->carrier_ps) {
                $carrier_enabled = true;
                $carriers[$key] = [
                    'label' => $label,
                    'markup' => get_option($carrier_common . $carrier . '_markup'),
                ];
            }
        }

        !$carrier_enabled ? $carriers = [] : '';
        return $carriers;
    }
}