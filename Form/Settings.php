<?php

class Appointmentpro_Form_Settings extends Siberian_Form_Abstract
{

    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/appointmentpro/settings/editpost"))
            ->setAttrib("id", "form-add-settings");

        self::addClass("create", $this);

        $value_id = $this->addSimpleHidden("value_id");
        $store_id = $this->addSimpleHidden("appointment_setting_id");

        $h15 = p__("appointmentpro", "Home Screen");
        $helpText15 = '<div class="col-md-12"><div  class="alert alert-info">' . $h15 . '</div></div>';
        $this->addSimpleHtml("helpText15", $helpText15);

        $this->addSimpleCheckbox('enable_location', p__('appointmentpro', 'Enable Location Permission'));
        $this->addSimpleCheckbox('home_slider', p__('appointmentpro', 'Display Home Slider'));
        $this->addSimpleCheckbox('home_provider', p__('appointmentpro', 'Enable Popular Provider'));
        $this->addSimpleCheckbox('home_category', p__('appointmentpro', 'Enable Top Category'));


        $this->addSimpleSelect("list_design", p__('appointmentpro', "List Design"), [
            0 => p__('appointmentpro', "List View"),
            1 => p__('appointmentpro', "Card View"),
        ]);

        $this->addSimpleSelect("default_location_sorting", p__('appointmentpro', "Default Location Sorting"), [
            "distance" => p__('appointmentpro', "Distance"),
            "alpha" => p__('appointmentpro', "Alpha"),
            "date" => p__('appointmentpro', "Created"),
        ]);

        $h1 = p__("appointmentpro", "Booking Settings");
        $helpText1 = '<div class="col-md-12"><div  class="alert alert-info">' . $h1 . '</div></div>';
        $this->addSimpleHtml("helpText1", $helpText1);

        $email = $this->addSimpleText("owner_email", p__('appointmentpro', "Admin E-mail"));
        $email->addValidator("EmailAddress");

        $bookingOpts = [
            '1' => p__('appointmentpro', 'Service Booking')
        ];
        if (\Appointmentpro\Extension::isEnabled('classes')) {
            $bookingOpts = [
                '1' => p__('appointmentpro', 'Service Booking'),
                '2' => p__('appointmentpro', 'Class Booking'),
                '3' => p__('appointmentpro', 'Both Booking')
            ];
        }

        $booking_type = $this->addSimpleRadio("booking_type",
            p__('appointmentpro', "Booking Type"),
            $bookingOpts
        );


        $booking_type->addClass("color-blue");
        self::removeClass("color-red", $booking_type);

        $this->addSimpleCheckbox('enable_booking', p__('appointmentpro', 'Enable Booking'));
        $this->addSimpleCheckbox('enable_acceptance_rejection', p__('appointmentpro', 'Auto Accepted Bookings'));
 
        $h13 = p__("appointmentpro", "Payment Setting");
        $helpText13 = '<div class="col-md-12"><div  class="alert alert-info">' . $h13 . '</div></div>';
        $this->addSimpleHtml("helpText13", $helpText13);
        $this->addSimpleCheckbox("price_hide", p__('appointmentpro', "Price Hide"));
        $this->addSimpleCheckbox('booking_without_payment', p__('appointmentpro', 'Booking Without Payment'));

        $this->addSimpleCheckbox('online_payment', p__('appointmentpro', 'Enable Online Payment'));
        $this->addSimpleCheckbox('offline_payment', p__('appointmentpro', 'Enable Offline Payment'));

        $this->addSimpleCheckbox('display_tax', p__('appointmentpro', 'Display Tax'));
        $this->addSimpleSlider('tax_percentage', p__('appointmentpro', "Tax Percentage"), array(
            'min' => 0,
            'max' => 100,
            'step' => 0.1,
            'unit' => '%'
        ), true);
        $this->addSimpleCheckbox("enable_plc_points", p__('appointmentpro', "Enable PLC points system"));
 
        $h12 = p__("appointmentpro", "Date, Time Format and Distance Setting");
        $helpText12 = '<div class="col-md-12"><div  class="alert alert-info">' . $h12 . '</div></div>';
        $this->addSimpleHtml("helpText12", $helpText12);

        $this->addSimpleSelect("distance_unit", p__('appointmentpro', "Distance Unit"), [
            "km" => p__('appointmentpro', "Kilometers"),
            "m" => p__('appointmentpro', "Meters"),
            "mi" => p__('appointmentpro', "Miles"),
        ]);


        $timezone_identifiers = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

        $this->addSimpleSelect(
        "timezone",
        p__("attendance", "Timezone"),
        $timezone_identifiers
         )->setRequired(true);

        $time_format = $this->addSimpleRadio("time_format",
            p__('appointmentpro', "Time Format"),
            ['0' => p__('appointmentpro', '12 Hour'),
                '1' => p__('appointmentpro', '24 Hour')
            ]
        );
        $time_format->addClass("color-blue");
        self::removeClass("color-red", $time_format);

        $date_format = $this->addSimpleRadio("date_format",
            p__('appointmentpro', "Date Format"),
            ['0' => p__('appointmentpro', 'dd/mm/yyyy'),
                '1' => p__('appointmentpro', 'mm/dd/yyyy')
            ]
        );
        $date_format->addClass("color-blue");
        self::removeClass("color-red", $date_format);
 
        $h11 = p__("appointmentpro", "Cancellation Setting");
        $helpText11 = '<div class="col-md-12"><div  class="alert alert-info">' . $h11 . '</div></div>';
        $this->addSimpleHtml("helpText11", $helpText11);

        /**Cancellation policy fields**/
        $cancel_criteria = $this->addSimpleSelect("cancel_criteria", p__('appointmentpro', "Cancellation Criteria"), [
            "0" => p__('appointmentpro', "Never"),
            "1" => p__('appointmentpro', "Always"),
            "2" => p__('appointmentpro', "1 Hour Before Appointment"),
            "3" => p__('appointmentpro', "2 Hours Before Appointment"),
            "4" => p__('appointmentpro', "6 Hours Before Appointment"),
            "5" => p__('appointmentpro', "12 Hours Before Appointment"),
            "6" => p__('appointmentpro', "1 Day Before Appointment"),
            "7" => p__('appointmentpro', "1 Week Before Appointment"),
        ]);

        $this->addSimpleSlider('cancellation_charges', p__('appointmentpro', "Cancellation Charges"), array(
            'min' => 0,
            'max' => 100,
            'step' => 0.1,
            'unit' => '%'
        ), true);

        $cancel_policy = $this->addSimpleTextarea("cancel_policy", p__('appointmentpro', "Cancellation Policy"));
        $cancel_policy->setRichtext();

        
        $h3 = p__("appointmentpro", "Currency options");
        $helpText3 = '<div class="col-md-12"><div  class="alert alert-info">' . $h3 . '</div></div>';
        $this->addSimpleHtml("helpText3", $helpText3);

        $this->addSimpleSelect('currency_position', p__('appointmentpro', 'Currency position'), [
            'left' => p__('appointmentpro', 'Left'),
            'right' => p__('appointmentpro', 'Right'),
            'left_with_space' => p__('appointmentpro', 'Left with space'),
            'right_with_space' => p__('appointmentpro', 'Right with space'),
        ]);
        $this->addSimpleText('decimal_separator', p__('appointmentpro', 'Decimal separator'));
        $this->addSimpleText('thousand_separator', p__('appointmentpro', 'Thousand separator'));
        $this->addSimpleText('number_of_decimals', p__('appointmentpro', 'Number of decimals'));

        if (\Appointmentpro\Extension::isEnabled('classes')) {
          
            $h4 = p__("appointmentpro", "Google Calendar Credentials");
            $helpText4 = '<div class="col-md-12"><div  class="alert alert-info">' . $h4 .'  </div></div>';
            $this->addSimpleHtml("helpText4", $helpText4); 
            $this->addSimpleText('client_id', p__('appointmentpro', 'Google App Client Id'));
            $this->addSimpleText('client_secret', p__('appointmentpro', 'Google App Client Secret'));
            $this->addSimpleText('google_redirect_URL', p__('appointmentpro', 'Google App Redirect Url')); 
            $this->addSimpleCheckbox("enable_google_calendar", p__('appointmentpro', "Enable google calendar"));
             
        }

    }

    public function setElementValueById($id, $value, $required = false)
    {
        $element = $this->getElement($id)->setValue($value);
        if ($required) {
            $element->setRequired(true);
        }
    }

}

?>
