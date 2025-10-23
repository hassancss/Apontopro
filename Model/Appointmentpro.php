<?php

class Appointmentpro_Model_Appointmentpro extends Core_Model_Default
{

    /**
     * @var null
     */
    public static $acl = null;

    /**
     * @var string
     */
    protected $_db_table = Appointmentpro_Model_Db_Table_Appointmentpro::class;

    /**
     * @param $valueId
     * @return array|bool
     */
    public function getInappStates($valueId)
    {
        $inAppStates = [
            [
                "state" => "appointmentpro-home",
                "offline" => false,
                "params" => [
                    'value_id' => $valueId,
                ],
            ],
        ];

        return $inAppStates;
    }

    /**
     * @return null
     */
    public static function getCurrentValueId()
    {
        $app = self::getApplication();
        if ($app) {
            $options = $app->getOptions();
            foreach ($options as $option) {
                if ($option->getCode() === "appointmentpro") {
                    return $option->getId();
                }
            }
        }
        return null;
    }

    /**
     * @return null
     */
    public static function getCurrent()
    {
        $app = self::getApplication();
        if ($app) {
            $options = $app->getOptions();
            foreach ($options as $option) {
                if ($option->getCode() === "appointmentpro") {
                    return $option;
                }
            }
        }
        return null;
    }


    /**
     * @return null
     */
    public static function getDefaultDays()
    {
        return array(
            "monday",
            "tuesday",
            "wednesday",
            "thursday",
            "friday",
            "saturday",
            "sunday",
        );
    }


}
