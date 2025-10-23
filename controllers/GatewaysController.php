<?php

use PaymentStripe\Model\Application as PaymentStripeApplication;

/**
 * Class Appointmentpro_GatewaysController
 */
class Appointmentpro_GatewaysController extends Application_Controller_Default
{
    /**
     *
     */
    public function addpaypalAction()
    {
        $payload = array();

        if ($data = $this->getRequest()->getPost()) {

            try {

                $form = new Appointmentpro_Form_Gateways_Paypal();
                if ($form->isValid($data)) {

                    if (!empty($data['value_id'])) {

                        if ($data['status']) {
                            $data['status'] = 'active';
                        } else {
                            $data['status'] = 'inactive';
                        }

                        if ($data['payment_mode'] == 1) {
                            if (empty($data['username']) || empty($data['signature']) || empty($data['password'])) {
                                throw new Siberian_Exception(p__('appointmentpro', 'Live Username, Password, and Signature must be required.'));
                            }
                        }

                        if ($data['payment_mode'] == 0) {
                            if (empty($data['sandboxusername']) || empty($data['sandboxsignature']) || empty($data['sandboxpassword'])) {
                                throw new Siberian_Exception(p__('appointmentpro', 'Sandbox Username, Password, and Signature must be required.'));
                            }
                        }

                        $gateway = new Appointmentpro_Model_Gateways();
                        $gateway
                            ->find($data['id'])
                            ->setGatewayCode($data['gateway_code'])
                            ->setLocationId($data['location_id'])
                            ->setValueId($data['value_id'])
                            ->setLableName($data['lable_name'])
                            ->setShotDescription($data['shot_description'])
                            ->setPaymentMode($data['payment_mode'])
                            ->setUsername($data['username'])
                            ->setSignature($data['signature'])
                            ->setPassword($data['password'])
                            ->setSandboxusername($data['sandboxusername'])
                            ->setSandboxsignature($data['sandboxsignature'])
                            ->setSandboxpassword($data['sandboxpassword'])
                            ->setStatus($data['status'])
                            ->save();
                    } else {
                        throw new Siberian_Exception(p__('appointmentpro', 'Something went wrong with the update, will retry later.'));
                    }

                    $payload = array(
                        'success' => true,
                        'success_message' => p__('appointmentpro', 'Information successfully saved'),
                        'message_timeout' => 2,
                        'message_button' => 0,
                        'message_loader' => 0
                    );
                } else {
                    /** Do whatever you need when form is not valid */
                    $payload = array(
                        "error" => 1,
                        "message" => $form->getTextErrors(),
                        "errors" => $form->getTextErrors(true),
                    );
                }
            } catch (Exception $e) {
                $payload = array(
                    'error' => true,
                    'message' => $e->getMessage()
                );
            }
        }
        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function addpayfastAction()
    {
        $payload = array();

        if ($data = $this->getRequest()->getPost()) {

            try {

                $form = new Appointmentpro_Form_Gateways_Payfast();
                if ($form->isValid($data)) {

                    if (!empty($data['value_id'])) {

                        if ($data['status']) {
                            $data['status'] = 'active';
                        } else {
                            $data['status'] = 'inactive';
                        }

                        if (empty($data['merchant_id']) || empty($data['merchant_key'])) {
                            throw new Siberian_Exception(p__('appointmentpro', 'Merchant Id and Merchant Key must be required.'));
                        }

                        $gateway = new Appointmentpro_Model_Gateways();
                        $gateway
                            ->find($data['id'])
                            ->setGatewayCode($data['gateway_code'])
                            ->setLocationId($data['location_id'])
                            ->setValueId($data['value_id'])
                            ->setLableName($data['lable_name'])
                            ->setShotDescription($data['shot_description'])
                            ->setMerchantId($data['merchant_id'])
                            ->setMerchantKey($data['merchant_key'])
                            ->setIsLive($data['is_live'])
                            ->setStatus($data['status'])
                            ->save();
                    } else {
                        throw new Siberian_Exception(p__('appointmentpro', 'Something went wrong with the update, will retry later.'));
                    }

                    $payload = array(
                        'success' => true,
                        'success_message' => p__('appointmentpro', 'Information successfully saved'),
                        'message_timeout' => 2,
                        'message_button' => 0,
                        'message_loader' => 0
                    );
                } else {
                    /** Do whatever you need when form is not valid */
                    $payload = array(
                        "error" => 1,
                        "message" => $form->getTextErrors(),
                        "errors" => $form->getTextErrors(true),
                    );
                }
            } catch (Exception $e) {
                $payload = array(
                    'error' => true,
                    'message' => $e->getMessage()
                );
            }
        }
        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function addstripeAction()
    {
        $payload = array();

        if ($data = $this->getRequest()->getPost()) {

            try {

                /*    if(!PaymentStripeApplication::isEnabled($this->getApplication()->getId())){
                     throw new Siberian_Exception(p__('appointmentpro', 'Please check setting in (Menu -> Payment Gateways -> Stripe) is must be active.'));
                }*/

                $form = new Appointmentpro_Form_Gateways_Stripe();
                if ($form->isValid($data)) {

                    if (!empty($data['value_id'])) {

                        if ($data['status']) {
                            $data['status'] = 'active';
                        } else {
                            $data['status'] = 'inactive';
                        }

                        $gateway = new Appointmentpro_Model_Gateways();
                        $gateway
                            ->find($data['id'])
                            ->setGatewayCode($data['gateway_code'])
                            ->setLocationId($data['location_id'])
                            ->setValueId($data['value_id'])
                            ->setLableName($data['lable_name'])
                            ->setShotDescription($data['shot_description'])
                            ->setSecretKey($data['secret_key'])
                            ->setPublishableKey($data['publishable_key'])
                            ->setStatus($data['status'])
                            ->save();
                    } else {
                        throw new Siberian_Exception(p__('appointmentpro', 'Something went wrong with the update, will retry later.'));
                    }

                    $payload = array(
                        'success' => true,
                        'success_message' => p__('appointmentpro', 'Information successfully saved'),
                        'message_timeout' => 2,
                        'message_button' => 0,
                        'message_loader' => 0
                    );
                } else {
                    /** Do whatever you need when form is not valid */
                    $payload = array(
                        "error" => 1,
                        "message" => $form->getTextErrors(),
                        "errors" => $form->getTextErrors(true),
                    );
                }
            } catch (Exception $e) {
                $payload = array(
                    'error' => true,
                    'message' => $e->getMessage()
                );
            }
        }
        $this->_sendJson($payload);
    }
    // addnmi
    /**
     *
     */
    public function addnmiAction()
    {
        $payload = array();

        if ($data = $this->getRequest()->getPost()) {

            try {

                $form = new Appointmentpro_Form_Gateways_Nmi();
                if ($form->isValid($data)) {
                    // processing_fee allow numers only
                    if (!is_numeric($data['processing_fee'])) {
                        throw new Siberian_Exception(p__('appointmentpro', 'Processing fee must be a number.'));
                    }

                    if (!empty($data['value_id'])) {

                        if ($data['status']) {
                            $data['status'] = 'active';
                        } else {
                            $data['status'] = 'inactive';
                        }

                        $gateway = new Appointmentpro_Model_Gateways();
                        $gateway
                            ->find($data['id'])
                            ->setGatewayCode($data['gateway_code'])
                            ->setLocationId($data['location_id'])
                            ->setValueId($data['value_id'])
                            ->setLableName($data['lable_name'])
                            ->setShotDescription($data['shot_description'])
                            ->setSecretKey($data['secret_key'])
                            ->setStatus($data['status'])
                            // is_test_mode
                            ->setIsTestMode($data['is_test_mode'])
                            // processing_fee
                            ->setProcessingFee($data['processing_fee'])
                            ->save();
                    } else {
                        throw new Siberian_Exception(p__('appointmentpro', 'Something went wrong with the update, will retry later.'));
                    }

                    $payload = array(
                        'success' => true,
                        'success_message' => p__('appointmentpro', 'Information successfully saved'),
                        'message_timeout' => 2,
                        'message_button' => 0,
                        'message_loader' => 0
                    );
                } else {
                    /** Do whatever you need when form is not valid */
                    $payload = array(
                        "error" => 1,
                        "message" => $form->getTextErrors(),
                        "errors" => $form->getTextErrors(true),
                    );
                }
            } catch (Exception $e) {
                $payload = array(
                    'error' => true,
                    'message' => $e->getMessage()
                );
            }
        }
        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function addcodAction()
    {
        $payload = array();

        if ($data = $this->getRequest()->getPost()) {

            try {

                $form = new Appointmentpro_Form_Gateways_Cod();
                if ($form->isValid($data)) {

                    if (!empty($data['value_id'])) {

                        if ($data['status']) {
                            $data['status'] = 'active';
                        } else {
                            $data['status'] = 'inactive';
                        }

                        $gateway = new Appointmentpro_Model_Gateways();
                        $gateway
                            ->find($data['id'])
                            ->setGatewayCode($data['gateway_code'])
                            ->setLocationId($data['location_id'])
                            ->setValueId($data['value_id'])
                            ->setLableName($data['lable_name'])
                            ->setShotDescription($data['shot_description'])
                            ->setStatus($data['status'])
                            ->save();
                    } else {
                        throw new Siberian_Exception(p__('appointmentpro', 'Something went wrong with the update, will retry later.'));
                    }

                    $payload = array(
                        'success' => true,
                        'success_message' => p__('appointmentpro', 'Information successfully saved'),
                        'message_timeout' => 2,
                        'message_button' => 0,
                        'message_loader' => 0
                    );
                } else {
                    /** Do whatever you need when form is not valid */
                    $payload = array(
                        "error" => 1,
                        "message" => $form->getTextErrors(),
                        "errors" => $form->getTextErrors(true),
                    );
                }
            } catch (Exception $e) {
                $payload = array(
                    'error' => true,
                    'message' => $e->getMessage()
                );
            }
        }
        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function addbankAction()
    {
        $payload = array();

        if ($data = $this->getRequest()->getPost()) {

            try {

                $form = new Appointmentpro_Form_Gateways_Bank();
                if ($form->isValid($data)) {

                    if (!empty($data['value_id'])) {

                        if ($data['status']) {
                            $data['status'] = 'active';
                        } else {
                            $data['status'] = 'inactive';
                        }

                        $gateway = new Appointmentpro_Model_Gateways();
                        $gateway
                            ->find($data['id'])
                            ->setGatewayCode($data['gateway_code'])
                            ->setLocationId($data['location_id'])
                            ->setValueId($data['value_id'])
                            ->setLableName($data['lable_name'])
                            ->setShotDescription($data['shot_description'])
                            ->setInstructions($data['instructions'])
                            ->setStatus($data['status'])
                            ->save();
                    } else {
                        throw new Siberian_Exception(p__('appointmentpro', 'Something went wrong with the update, will retry later.'));
                    }

                    $payload = array(
                        'success' => true,
                        'success_message' => p__('appointmentpro', 'Information successfully saved'),
                        'message_timeout' => 2,
                        'message_button' => 0,
                        'message_loader' => 0
                    );
                } else {
                    /** Do whatever you need when form is not valid */
                    $payload = array(
                        "error" => 1,
                        "message" => $form->getTextErrors(),
                        "errors" => $form->getTextErrors(true),
                    );
                }
            } catch (Exception $e) {
                $payload = array(
                    'error' => true,
                    'message' => $e->getMessage()
                );
            }
        }
        $this->_sendJson($payload);
    }


    /**
     *
     */
    public function addwalletAction()
    {
        $payload = array();

        if ($data = $this->getRequest()->getPost()) {

            try {

                if (!class_exists("Ewallet_Model_Ewallet")) {
                    throw new Exception(p__('appointmentpro', 'If you need to connect wallet, please contact your administrator!'));
                } else {

                    $_module_deps = (new Installer_Model_Installer_Module())->find(['name' => 'Ewallet']);
                    $_module_info = $_module_deps->getData();

                    if ($_module_info['version'] < "2.0.0") {
                        throw new Exception(p__('appointmentpro', "Your system doesn't meet the requirements for this module, Ewallet version >=%s is required.", "2.0.0"));
                    } else {
                        $wallet_value_id = (new Ewallet_Model_Ewallet())->getCurrentValueId();
                        if (empty($wallet_value_id)) {
                            throw new Exception(p__('appointmentpro', 'For online payment Ewallet module is not activated, Please add a feature in app!'));
                        }
                    }
                }

                $form = new Appointmentpro_Form_Gateways_Cod();
                if ($form->isValid($data)) {

                    if (!empty($data['value_id'])) {

                        if ($data['status']) {
                            $data['status'] = 'active';
                        } else {
                            $data['status'] = 'inactive';
                        }

                        $gateway = new Appointmentpro_Model_Gateways();
                        $gateway
                            ->find($data['id'])
                            ->setGatewayCode($data['gateway_code'])
                            ->setValueId($data['value_id'])
                            ->setLableName($data['lable_name'])
                            ->setShotDescription($data['shot_description'])
                            ->setStatus($data['status'])
                            ->save();
                    } else {
                        throw new Siberian_Exception(p__('appointmentpro', 'Something went wrong with the update, will retry later.'));
                    }

                    $payload = array(
                        'success' => true,
                        'success_message' => p__('appointmentpro', 'Information successfully saved'),
                        'message_timeout' => 2,
                        'message_button' => 0,
                        'message_loader' => 0
                    );
                } else {
                    /** Do whatever you need when form is not valid */
                    $payload = array(
                        "error" => 1,
                        "message" => $form->getTextErrors(),
                        "errors" => $form->getTextErrors(true),
                    );
                }
            } catch (Exception $e) {
                $payload = array(
                    'error' => true,
                    'message' => $e->getMessage()
                );
            }
        }
        $this->_sendJson($payload);
    }

    public function loadformAction()
    {

        if ($code = $this->getRequest()->getParam("code")) {
            try {
                $value_id = $this->getRequest()->getParam("value_id");
                $gatewayModel = (new Appointmentpro_Model_Gateways())
                    ->find(['gateway_code' => $code, 'value_id' => $value_id, 'location_id' => 0]);

                if ($code == 'stripe') {
                    $form = new Appointmentpro_Form_Gateways_Stripe();
                    $form->setElementValueById('value_id', $this->getCurrentOptionValue()->getId());
                    $form->addNav("edit-nav-appointmentpro", "Save", false);
                    $form->removeNav("nav-add-appointmentpro");
                    if ($gatewayModel->getId()) {
                        $data = $gatewayModel->getData();
                        $data['status'] = $data['status'] == 'active' ? 1 : 0;
                        $form->populate($data);
                        $form->setElementValueById('id', $gatewayModel->getId());
                    } else {
                        $form->populate([
                            'gateway_code' => "stripe",
                            'lable_name' => "Credit card"
                        ]);
                    }
                }

                if ($code == 'cod') {
                    $form = new Appointmentpro_Form_Gateways_Cod();
                    $form->setElementValueById('value_id', $this->getCurrentOptionValue()->getId());
                    $form->addNav("edit-nav-appointmentpro", "Save", false);
                    $form->removeNav("nav-add-appointmentpro");
                    if ($gatewayModel->getId()) {
                        $data = $gatewayModel->getData();
                        $data['status'] = $data['status'] == 'active' ? 1 : 0;
                        $form->populate($data);
                        $form->setElementValueById('id', $gatewayModel->getId());
                    } else {
                        $form->populate(['gateway_code' => "cod"]);
                    }
                }

                if ($code == 'banktransfer') {
                    $form = new Appointmentpro_Form_Gateways_Bank();
                    $form->setElementValueById('value_id', $this->getCurrentOptionValue()->getId());
                    $form->addNav("edit-nav-appointmentpro", "Save", false);
                    $form->removeNav("nav-add-appointmentpro");
                    if ($gatewayModel->getId()) {
                        $data = $gatewayModel->getData();
                        $data['status'] = $data['status'] == 'active' ? 1 : 0;
                        $form->populate($data);
                        $form->setElementValueById('id', $gatewayModel->getId());
                    } else {
                        $form->populate(['gateway_code' => "banktransfer"]);
                    }
                }

                if ($code == 'wallet') {
                    $form = new Appointmentpro_Form_Gateways_Wallet();
                    $form->setElementValueById('value_id', $this->getCurrentOptionValue()->getId());
                    $form->addNav("edit-nav-appointmentpro", "Save", false);
                    $form->removeNav("nav-add-appointmentpro");
                    if ($gatewayModel->getId()) {
                        $data = $gatewayModel->getData();
                        $data['status'] = $data['status'] == 'active' ? 1 : 0;
                        $form->populate($data);
                        $form->setElementValueById('id', $gatewayModel->getId());
                    } else {
                        $form->populate(['gateway_code' => "wallet"]);
                    }
                }

                if ($code == 'paypal') {
                    $form = new Appointmentpro_Form_Gateways_Paypal();
                    $form->setElementValueById('value_id', $this->getCurrentOptionValue()->getId());
                    $form->addNav("edit-nav-appointmentpro", "Save", false);
                    $form->removeNav("nav-add-appointmentpro");
                    if ($gatewayModel->getId()) {
                        $data = $gatewayModel->getData();
                        $data['status'] = $data['status'] == 'active' ? 1 : 0;
                        $form->populate($data);
                        $form->setElementValueById('id', $gatewayModel->getId());
                    } else {
                        $form->populate([
                            'gateway_code' => "paypal",
                            'lable_name' => "Paypal"
                        ]);
                    }
                }

                if ($code == 'payfast') {
                    $form = new Appointmentpro_Form_Gateways_Payfast();
                    $form->setElementValueById('value_id', $this->getCurrentOptionValue()->getId());
                    $form->addNav("edit-nav-appointmentpro", "Save", false);
                    $form->removeNav("nav-add-appointmentpro");
                    if ($gatewayModel->getId()) {
                        $data = $gatewayModel->getData();
                        $data['status'] = $data['status'] == 'active' ? 1 : 0;
                        $form->populate($data);
                        $form->setElementValueById('id', $gatewayModel->getId());
                    } else {
                        $form->populate([
                            'gateway_code' => "payfast",
                            'lable_name' => "PayFast"
                        ]);
                    }
                }
                // nmi payment
                if ($code == 'nmi') {
                    $form = new Appointmentpro_Form_Gateways_Nmi();
                    $form->setElementValueById('value_id', $this->getCurrentOptionValue()->getId());
                    $form->addNav("edit-nav-appointmentpro", "Save", false);
                    $form->removeNav("nav-add-appointmentpro");
                    if ($gatewayModel->getId()) {
                        $data = $gatewayModel->getData();
                        $data['status'] = $data['status'] == 'active' ? 1 : 0;
                        $form->populate($data);
                        $form->setElementValueById('id', $gatewayModel->getId());
                    } else {
                        $form->populate([
                            'gateway_code' => "nmi",
                            'lable_name' => "NMI"
                        ]);
                    }
                }

                $payload = array(
                    "check" => 'ready for use',
                    "success"   => true,
                    "form"      => $form->render(),
                    "message"   => p__('appointmentpro', "Success."),
                );
            } catch (Exception $e) {
                $payload = array(
                    'error' => true,
                    'message' => $e->getMessage()
                );
            }
        }

        $this->_sendHtml($payload);
    }
}
