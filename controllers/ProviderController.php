<?php

/**
 * Class Appointmentpro_ProviderController
 */
class Appointmentpro_ProviderController extends Application_Controller_Default
{

    /**
     *Home screen
     */
    public function listAction()
    {
        $this->loadPartials();
    }

    /**
     *Provider add
     */
    public function addAction()
    {
        $this->loadPartials();
    }

    /**
     *Provider bussiness days
     */
    public function bussinessAction()
    {
        $model = (new Appointmentpro_Model_Provider());
        if ($id = $this->getRequest()->getParam('id')) {
            $model->find($id);
            if (!$model->getProviderId()) {
                $this->getRequest()->addError(p__("appointmentpro",  "This provider does not exist."));
            }
        }
        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setProvider($model);
    }

    /**
     *Provider Edit
     */
    public function editAction()
    {
        $model = (new Appointmentpro_Model_Provider());
        if ($id = $this->getRequest()->getParam('id')) {
            $model->find($id);
            if (!$model->getProviderId()) {
                $this->getRequest()->addError(p__("appointmentpro",  "This provider does not exist."));
            }
        }
        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setProvider($model);
    }

    /**
     *Provider delete
     */
    public function deleteAction()
    {

        try {

            $model = (new Appointmentpro_Model_Provider());
            if ($id = $this->getRequest()->getParam('id')) {
                $model->find($id);
                if (!$model->getProviderId()) {
                    $this->getRequest()->addError(p__("appointmentpro",  "This provider does not exist."));
                } else {
                    $model->setIsDelete(1);
                    $model->save();
                }
            }

            $payload = [
                'success' => true,
                'message' => p__('appointmentpro', 'Successfully deleted'),
                'datas' => $datas
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *Provider Save
     */
    public function saveAction()
    {

        if ($param = $this->getRequest()->getPost()) {

            try {

                $is_active = $param['is_active'] == 1 ? 1 : 0;
                $is_popular = $param['is_popular'] == 1 ? 1 : 0;
                $param['is_mobile_user'] = empty($param['is_mobile_user']) ? 0 : $param['is_mobile_user'];
                $model = (new Appointmentpro_Model_Provider())
                    ->find(['provider_id' => $param['provider_id']])
                    ->setValueId($param['value_id'])
                    ->setName($param['name'])
                    ->setEmail($param['email'])
                    ->setMobileNumber($param['mobile_number'])
                    ->setIsPopular($is_popular)
                    ->setDescription($param['description'])
                    ->setLocationId($param['location'])
                    ->setDesignation($param['designation'])
                    ->setIsMobileUser($param['is_mobile_user'])
                    ->setUserRole($param['user_role'])
                    ->setCalendarHeaderBg($param['calendar_header_bg'])
                    ->setCalendarHeaderColor($param['calendar_header_color'])
                    ->setCalendarBodyBg($param['calendar_body_bg'])
                    ->setCalendarBodyColor($param['calendar_body_color'])
                    ->setIsActive($is_active);

                if (!empty($param['profile_image'])) {
                    foreach ($param['profile_image'] as $iKey => $iValue) {
                        if (file_exists(Core_Model_Directory::getTmpDirectory(true) . "/" . $iValue)) {
                            list($relativePath, $filename) = $this->_getImageData($iValue);
                            $imageURL = $relativePath . '/' . $filename;
                            $model->setImage($imageURL);
                        }
                    }
                }

                $model->save();
                $provider_id = $model->getId();


                if ($param['is_mobile_user'] == '1') {

                    $dummy = new Customer_Model_Customer();
                    $dummy->find([
                        'email' => $param['email'],
                        'app_id' => $this->getApplication()->getId(),
                    ]);

                    if (!$dummy->getId()) {
                        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                        $password = substr(str_shuffle($chars), 0, 8);

                        $customer = new Customer_Model_Customer();
                        $data = [
                            'email' => $param['email'],
                            'firstname' => $param['name'],
                            'password' => $password,
                            'mobile' => $param['mobile_number']
                        ];
                        $customer
                            ->setData($data)
                            ->setAppId($this->getApplication()->getId())
                            ->setPassword($password)
                            ->save();

                        $this->_sendNewAccountEmail($customer, $password);
                    }
                }


                $payload = [
                    'success' => true,
                    'message' => p__('appointmentpro', 'Successfully save'),
                    'datas' => $datas
                ];
            } catch (\Exception $e) {
                $payload = [
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            }

            $this->_sendJson($payload);
        }
    }



    /**
     * @param $customer
     * @param $password
     */
    protected function _sendNewAccountEmail($customer, $password)
    {
        try {
            // E-Mail back the user!
            $application = $this->getApplication();
            $applicationName = $application->getName();

            $subject = p__('appointmentpro', '%s - Account creation', $applicationName);
            $layout = new Siberian\Layout();
            $layout = $layout->loadEmail('appointmentpro', 'create_provider_account');

            $layout->getPartial('content_email')
                ->setCustomer($customer)
                ->setPassword($password)
                ->setApp($this->getApplication()->getName())->setIcon($this->getApplication()->getIcon());


            $content = $layout->render();

            $mail = new \Siberian_Mail();
            $mail->setBodyHtml($content);
            $mail->addTo($customer->getEmail(), $customer->getName());
            $mail->setSubject($subject);
            $mail->send();
        } catch (\Exception $e) {
            // Something went wrong with the-mail!
        }
    }


    /**
     * @param $nodeName
     * @param $title
     * @param $message
     * @param $showLegals
     * @return Siberian_Layout|Siberian_Layout_Email
     * @throws Zend_Layout_Exception
     */
    public function baseEmail(
        $nodeName,
        $title,
        $message = '',
        $showLegals = false
    ) {
        $layout = new Siberian\Layout();
        $layout = $layout->loadEmail('customer', $nodeName);
        $layout
            ->setContentFor('base', 'email_title', $title)
            ->setContentFor('content_email', 'message', $message)
            ->setContentFor('footer', 'show_legals', $showLegals);

        return $layout;
    }

    /**
     *Location savebussiness
     */
    public function savebussinessAction()
    {

        if ($param = $this->getRequest()->getPost()) {

            try {
                $model = (new Appointmentpro_Model_ProviderTiming())
                    ->find(['location_id' => $param['location_id'], 'provider_id' => $param['provider_id']])
                    ->setTiming(Siberian_Json::encode($param['timing']))
                    ->setProviderId($param['provider_id'])
                    ->setLocationId($param['location_id'])
                    ->save();

                $payload = [
                    'success' => true,
                    'message' => p__('appointmentpro', 'Successfully save'),
                    'json' => $param['timing']
                ];
            } catch (\Exception $e) {
                $payload = [
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            }

            $this->_sendJson($payload);
        }
    }

    /**
     * @param $image
     * @return array
     * @throws Siberian_Exception
     */
    private function _getImageData($image)
    {

        $img_src = Core_Model_Directory::getTmpDirectory(true) . "/" . $image;

        $info = pathinfo($img_src);
        $filename = $info['basename'];
        $relativePath = '/appointmentpro/' . $this->getApplication()->getId() . '/provider';
        $img_dst = Application_Model_Application::getBaseImagePath() . $relativePath;

        if (!is_dir($img_dst)) {
            mkdir($img_dst, 0777, true);
        }
        $img_dst .= '/' . $filename;
        rename($img_src, $img_dst);

        if (!file_exists($img_dst)) {
            throw new Siberian_Exception(p__('appointmentpro', 'An error occurred while saving your picture. Please try againg later.'));
        }
        return [$relativePath, $filename];
    }



    /**
     * fetch Provider
     */
    public function findAllAction()
    {

        try {
            $request = $this->getRequest();
            $limit = $request->getParam("perPage", 100);
            $offset = $request->getParam("offset", 0);
            $sorts = $request->getParam("sorts", []);
            $queries = $request->getParam("queries", []);

            $filter = null;
            if (array_key_exists("search", $queries)) {
                $filter = $queries["search"];
            }

            $params = [
                "limit" => $limit,
                "offset" => $offset,
                "sorts" => $sorts,
                "filter" => $filter,
            ];

            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();

            $providers = (new Appointmentpro_Model_Provider())
                ->findByValueId($value_id, $params);

            $countAll = (new Appointmentpro_Model_Provider())->countAllForApp($value_id);
            $countFiltered =   (new Appointmentpro_Model_Provider())->countAllForApp($value_id, $params);

            $providerJson = [];
            foreach ($providers as $provider) {
                $data = $provider->getData();
                $data['is_active'] = $data['is_active'] == 1 ? p__('appointmentpro', "Active") : p__('appointmentpro', "In Active");
                $data['image'] = empty($data['image']) ? '/app/local/modules/Appointmentpro/resources/design/desktop/flat/images/dummy-image.jpg' : '/images/application' . $data['image'];

                $data['location_name'] = !empty($data['location_name']) ? $data['location_name'] : '-';
                $data['user_role'] = p__('appointmentpro', ucfirst($data['user_role']));
                $data['is_mobile_user'] = (bool) $data['is_mobile_user'] ? 'show' : 'hide';
                $providerJson[] = $data;
            }

            $payload = [
                "records" => $providerJson,
                "queryRecordCount" => $countFiltered[0],
                "totalRecordCount" => $countAll[0]
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * service Provider
     */
    public function serviceAction()
    {
        $model = (new Appointmentpro_Model_Provider());
        if ($id = $this->getRequest()->getParam('id')) {
            $model->find($id);
            if (!$model->getProviderId()) {
                $this->getRequest()->addError(p__("appointmentpro",  "This provider does not exist."));
            }
        }

        $servicesLocation = (new Appointmentpro_Model_ServiceLocation())->findByLocationId($model->getLocationId(), ['service_type' => 1]);

        $modelTiming = (new Appointmentpro_Model_ProviderTiming());
        $modelTiming->find(['provider_id' => $id]);


        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setProvider($model)->setLocationService($servicesLocation)->setProviderTimingId($modelTiming->getId());
    }

    /**
     *Service Save
     */
    public function saveServiceAction()
    {

        if ($param = $this->getRequest()->getPost()) {

            try {

                $modelUpdateLocation =  (new Appointmentpro_Model_ServiceProvider())->updateDeleteStatus('provider_id', $param['provider_id']);

                foreach ($param['service'] as $lKey => $lValue) {
                    $modelServiceLocation = (new Appointmentpro_Model_ServiceProvider())
                        ->find(['provider_timing_id' => $param['provider_timing_id'], 'provider_id' => $param['provider_id'], 'service_location_id' => $lValue])
                        ->setProviderTimingId($param['provider_timing_id'])
                        ->setProviderId($param['provider_id'])
                        ->setServiceLocationId($lValue)
                        ->setIsDelete(0)
                        ->save();
                }

                $payload = [
                    'success' => true,
                    'message' => p__('appointmentpro', 'Successfully save')
                ];
            } catch (\Exception $e) {
                $payload = [
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            }

            $this->_sendJson($payload);
        }
    }


    /**
     * @param $param
     * @return array
     * @throws Siberian_Exception
     */
    public function sortableAction()
    {
        $payload = array();
        if ($data = $this->getRequest()->getPost('data')) {
            $model = new Appointmentpro_Model_Provider();
            $model->sortable($data);
            $payload = array("success" => 1);
        }

        $this->_sendJson($payload);
    }
}
