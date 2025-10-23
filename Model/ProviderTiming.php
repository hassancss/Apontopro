<?php

class Appointmentpro_Model_ProviderTiming extends Core_Model_Default
{

    /**
     * @var null
     */
    public static $acl = null;

    /**
     * @var string
     */
    protected $_db_table = Appointmentpro_Model_Db_Table_ProviderTiming::class;

    /**
     * @var string
     */
    public static $day_break_template = '<div class="day-break">#DATE#<input type="hidden" name="timing[day_breaks][#ID#][day]" value="#DAY#"><input type="hidden" name="timing[day_breaks][#ID#][start_time]" value="#START_TIME#"><input type="hidden" name="timing[day_breaks][#ID#][end_time]" value="#END_TIME#"><i class="fa fa-times remove_day_break"></i></div>';

    /**
     * @var string
     */
    public static $date_break_template = '<div class="date-break">#DATE#<input type="hidden" name="timing[date_break][]" value="#DATE#"><i class="fa fa-times remove_date_break"></i></div>';


    /**
     * @param $values
     */
    public function setDayBreak($values)
    {
        $breaks = "";
        $index = 1;
        foreach ($values as $key => $value) {
            $text_date = sprintf("%s, %s to %s", __(ucfirst($value["day"])), Appointmentpro_Model_Utils::timestampTotime($value["start_time"], "A"), Appointmentpro_Model_Utils::timestampTotime($value["end_time"], "A"));

            $template = self::$day_break_template;
            $template = str_replace("#DATE#", $text_date, $template) . "\n";
            $template = str_replace("#ID#", $index, $template) . "\n";
            $template = str_replace("#DAY#", $value["day"], $template) . "\n";
            $template = str_replace("#START_TIME#", $value["start_time"], $template) . "\n";
            $template = str_replace("#END_TIME#", $value["end_time"], $template) . "\n";
            $breaks .= $template;
            $index++;
        }

        return $breaks;
    }


}
