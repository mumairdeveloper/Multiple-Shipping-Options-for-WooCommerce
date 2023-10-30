<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1ed1fea8bfd107ca92e94cf1a2b758d4
{
    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'MsoConnection\\MsoConnection' => __DIR__ . '/../..' . '/admin/tab/server/mso-connection.php',
        'MsoCsv\\MsoCsv' => __DIR__ . '/../..' . '/admin/csv/mso-csv.php',
        'MsoFedexFreight\\MsoFedexFreight' => __DIR__ . '/../..' . '/admin/tab/settings/carriers/lfq/fedexfreight/mso-fedex-lfq.php',
        'MsoFedex\\MsoFedex' => __DIR__ . '/../..' . '/admin/tab/settings/carriers/spq/fedex/mso-fedex.php',
        'MsoLfq\\MsoLfq' => __DIR__ . '/../..' . '/admin/tab/settings/carriers/lfq/mso-lfq.php',
        'MsoLogs\\MsoLogs' => __DIR__ . '/../..' . '/admin/tab/settings/logs/mso-logs.php',
        'MsoOrder\\MsoOrder' => __DIR__ . '/../..' . '/admin/order/mso-order.php',
        'MsoPackage\\MsoPackage' => __DIR__ . '/../..' . '/shipping/package/mso-package.php',
        'MsoPackagingAjax\\MsoPackagingAjax' => __DIR__ . '/../..' . '/admin/tab/settings/packaging/mso-packaging-ajax.php',
        'MsoPackaging\\MsoPackaging' => __DIR__ . '/../..' . '/admin/tab/settings/packaging/mso-packaging.php',
        'MsoPrerequisites\\MsoPrerequisites' => __DIR__ . '/../..' . '/prerequisites/mso-prerequisites.php',
        'MsoProductAjax\\MsoProductAjax' => __DIR__ . '/../..' . '/admin/product/mso-product-ajax.php',
        'MsoProductDetail\\MsoProductDetail' => __DIR__ . '/../..' . '/admin/product/mso-product-detail.php',
        'MsoSettingsAjax\\MsoSettingsAjax' => __DIR__ . '/../..' . '/admin/tab/settings/mso-settings-ajax.php',
        'MsoSettings\\MsoSettings' => __DIR__ . '/../..' . '/admin/tab/settings/mso-settings.php',
        'MsoShipping' => __DIR__ . '/../..' . '/shipping/mso-shipping.php',
        'MsoShippingInit' => __DIR__ . '/../..' . '/shipping/mso-shipping.php',
        'MsoSpq\\MsoSpq' => __DIR__ . '/../..' . '/admin/tab/settings/carriers/spq/mso-spq.php',
        'MsoTab' => __DIR__ . '/../..' . '/admin/tab/mso-tab.php',
        'MsoUpsFreight\\MsoUpsFreight' => __DIR__ . '/../..' . '/admin/tab/settings/carriers/lfq/upsfreight/mso-ups-lfq.php',
        'MsoUps\\MsoUps' => __DIR__ . '/../..' . '/admin/tab/settings/carriers/spq/ups/mso-ups.php',
        'ShippingSettings\\ShippingSettings' => __DIR__ . '/../..' . '/shipping/package/shipping-settings.php',
        'WasaioCurl\\WasaioCurl' => __DIR__ . '/../..' . '/http/curl.php',
        'WasaioReceiverAddress\\WasaioReceiverAddress' => __DIR__ . '/../..' . '/shipping/package/receiver-address.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit1ed1fea8bfd107ca92e94cf1a2b758d4::$classMap;

        }, null, ClassLoader::class);
    }
}
