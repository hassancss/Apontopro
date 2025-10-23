<?php

namespace Appointmentpro;

use Siberian\Exception;
use Siberian\Json;

/**
 * Class Extension
 * @package Appointmentpro
 */
class Extension
{

    /**
     * @var null
     */
    public static $extensions = [];

    /**
     * @param $code
     * @param $module
     */
    public static function enable($code, $module = 'AppointmentproPremium')
    {
        try {
            self::checkModuleExpiration($module);
            if (!in_array($code, self::$extensions, true)) {
                self::$extensions[] = $code;
            }
        } catch (\Exception $e) {
            // If any exception, do not enable!
        }
    }

    /**
     * @param $code
     * @return bool
     */
    public static function isEnabled($code): bool
    {
        return in_array($code, self::$extensions, true);
    }

    /**
     * @param $module
     * @return bool
     */
    public static function checkModuleExpiration($module): bool
    {
        // Check cached DB
        $logPath = path('/app/local/modules/' . $module . '/resources/premium.log');
        $cachePath = path('/app/local/modules/' . $module . '/resources/cache.db');
        try {
            if (is_readable($cachePath)) {
                $content = Json::decode(base64_decode(file_get_contents($cachePath)));
                $active = (bool) $content['active'];
                if ($active &&
                    (($content['time'] + 604800) > time())) {
                    return true;
                }
            }

            // If we are here, we need to check validity again
            $package = Json::decode(file_get_contents(path('/app/local/modules/' . $module . '/package.json')));
            $result = self::checkModuleLicense($package['code'], $package['item_id']);

            // Log
            $jsonResult = Json::encode($result);
            file_put_contents($logPath, sprintf("\n[AppointmentPro::%s] %s", date('Y-m-d H:i:s'), $jsonResult), FILE_APPEND);

            // Cache again no matter what
            file_put_contents($cachePath, base64_encode($jsonResult));

            $resultActive = (bool) $result['active'];
            if ($resultActive) {
                return true;
            }
        } catch (\Exception $e) {
            // Nothing more to do!
            file_put_contents($logPath, sprintf("\n[AppointmentPro::%s] %s", date('Y-m-d H:i:s'), $e->getMessage()), FILE_APPEND);
        }
        // If we reach here, so it's not ok
        return false;
    }

    /**
     * @param $code
     * @param $itemId
     * @return array
     */
    public static function checkModuleLicense($code, $itemId): array
    {
        try {
            $licenseStatus = false;
            $sk = __get('siberiancms_key');
            $lk = __get($code . '_key');
            $query = http_build_query([
                'edd_action' => 'check_license',
                'item_id' => $itemId,
                'license' => $lk,
                'url' => $sk,
            ]);
            $urlCheck = 'https://extensions.siberiancms.com/?' . $query;
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $urlCheck,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
            ]);
            $response = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ((int)$status === 400) {
                throw new Exception($code . ': ' . $response);
            }

            if ((int)$status === 200) {
                // Decode json and ensure all is ok
                $licenseResult = json_decode($response, true);
                if ($licenseResult['license'] === 'valid') {
                    $licenseStatus = true;
                }
                if ($licenseResult['license'] === 'expired') {
                    throw new Exception($code . ': Your license has expired.');
                }
                if ($licenseResult['license'] === 'disabled') {
                    throw new Exception($code . ': Your license is disabled');
                }
                if ($licenseResult['license'] === 'site_inactive') {
                    throw new Exception($code . ': Your license was not correctly activated, please request a new download package.');
                }
                if ($licenseResult['license'] === 'invalid') {
                    throw new Exception($code . ': Your license is not valid.');
                }
                if ($licenseResult['license'] === 'invalid_item_id') {
                    throw new Exception($code . ': Your license is not for this module.');
                }
                if ($licenseResult['license'] === 'inactive') {
                    throw new Exception($code . ': Your license was not correctly activated, please request a new download package.');
                }
            }
            $payload = [
                'active' => $licenseStatus,
                'time' => time(),
            ];
        } catch (\Exception $e) {
            $payload = [
                'active' => false,
                'time' => time(),
                'message' => $e->getMessage()
            ];
        }

        return $payload;
    }
}
