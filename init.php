<?php

use Siberian\Assets;
use Siberian\Translation;

/**
 * @param $bootstrap
 */
$init = static function ($bootstrap) {
    Assets::registerScss([
        '/app/local/modules/Appointmentpro/features/appointmentpro/scss/appointmentpro.scss'
    ]);
    Translation::registerExtractor(
        'appointmentpro',
        'Appointmentpro',
        '/app/local/modules/Appointmentpro/resources/translations/default/appointmentpro.po');

    // Extension
    require_once path('/app/local/modules/Appointmentpro/resources/extensions/Extension.php');

    // Premium check
    $featurePath = path('/app/local/modules/AppointmentproPremium/resources/design/desktop/flat/template/premium/feature.phtml');
    if (is_readable($featurePath)) {
        require_once $featurePath;
    }
};

