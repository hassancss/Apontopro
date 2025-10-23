<?php

/**
 * Class Appointmentpro_ServiceController
 */
class Appointmentpro_ServiceController extends Application_Controller_Default
{

    /**
     *Home screen
     */
    public function listAction()
    {
        $this->loadPartials();
    }

    /**
     *Service add
     */
    public function addAction()
    {
        $this->loadPartials();
    }

    /**
     *Service Edit
     */
    public function editAction()
    {
        $model = (new Appointmentpro_Model_Service());
        if ($id = $this->getRequest()->getParam('id')) {
            $model->find($id);
            if (!$model->getServiceId()) {
                $this->getRequest()->addError(p__("appointmentpro",  "This service does not exist."));
            }
        }
        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setService($model);
    }

    /**
     * Get services by location and provider
     */
    public function getByLocationProviderAction()
    {
        try {
            $location_id = $this->getRequest()->getParam('location_id');
            $provider_id = $this->getRequest()->getParam('provider_id');
            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();

            // Get services filtered by location and provider using database join
            $db = Zend_Db_Table::getDefaultAdapter();
            $select = $db->select()
                ->from(['s' => 'appointment_service'], [
                    's.service_id',
                    's.name',
                    's.price',
                    's.service_time'
                ])
                ->joinLeft(['sl' => 'appointment_service_location'], 's.service_id = sl.service_id', [])
                ->joinLeft(['sp' => 'appointment_service_provider'], 'sl.service_location_id = sp.service_location_id', [])
                ->where('s.value_id = ?', $value_id)
                ->where('sl.location_id = ?', $location_id)
                ->where('sp.provider_id = ?', $provider_id)
                ->where('s.is_delete = ?', 0)
                ->where('s.status = ?', 1)
                ->where('sl.is_delete = ?', 0)
                ->order('s.name ASC');

            $services = $db->fetchAll($select);

            $servicesJson = [];
            foreach ($services as $service) {
                $servicesJson[] = [
                    'service_id' => $service['service_id'],
                    'name' => $service['name']
                ];
            }

            $payload = [
                'success' => true,
                'services' => $servicesJson
            ];
        } catch (\Exception $e) {
            $payload = [
                'success' => false,
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }
    /**
     * Get service details
     */
    public function getDetailsAction()
    {
        try {
            $service_id = $this->getRequest()->getParam('service_id');
            $value_id = (new Appointmentpro_Model_Appointmentpro())->getCurrentValueId();

            // Get service details using direct database query
            $db = Zend_Db_Table::getDefaultAdapter();
            $select = $db->select()
                ->from(['s' => 'appointment_service'], [
                    's.service_id',
                    's.name',
                    's.price',
                    's.service_time',
                    's.description',
                    's.special_price'
                ])
                ->where('s.service_id = ?', $service_id)
                ->where('s.value_id = ?', $value_id)
                ->where('s.is_delete = ?', 0);

            $service = $db->fetchRow($select);

            if (!$service) {
                throw new Exception('Service not found');
            }

            $payload = [
                'success' => true,
                'service' => [
                    'service_id' => $service['service_id'],
                    'name' => $service['name'],
                    'price' => $service['price'],
                    'duration' => $service['service_time'],
                    'description' => $service['description'],
                    'special_price' => $service['special_price']
                ]
            ];
        } catch (\Exception $e) {
            $payload = [
                'success' => false,
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *Service delete
     */
    public function deleteAction()
    {

        try {

            $model = (new Appointmentpro_Model_Service());
            if ($id = $this->getRequest()->getParam('id')) {
                $model->find($id);
                if (!$model->getServiceId()) {
                    $this->getRequest()->addError(p__("appointmentpro",  "This service does not exist."));
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
     *Service Save
     */
    public function saveAction()
    {

        if ($param = $this->getRequest()->getPost()) {

            try {

                if (empty($param['total_booking_per_slot']) || ($param['total_booking_per_slot'] <= 0)) {
                    throw new Exception(p__('appointmentpro', 'The number of bookings per time slot must be required or greater and equal to 1'));
                }

                // Handle service_time validation with break time fallback
                $serviceTime = $param['service_time'] ?? null;

                // Validate break configuration if provided
                if (!empty($param['has_break_time'])) {
                    $workBefore = (int)($param['work_time_before_break'] ?? 0);
                    $breakDuration = (int)($param['break_duration'] ?? 0);
                    $workAfter = (int)($param['work_time_after_break'] ?? 0);
                    $calculatedTime = $workBefore + $breakDuration + $workAfter;

                    // Break configuration must not exceed the original service time
                    if ($calculatedTime != (int)$serviceTime) {
                        throw new Exception(p__('appointmentpro', 'Break configuration total (' . $calculatedTime . ' min) must equal the service time (' . $serviceTime . ' min). Please adjust your break configuration.'));
                    }
                }

                // Final validation
                if (!is_numeric($serviceTime) || (int)$serviceTime <= 0) {
                    throw new Exception(p__('appointmentpro', 'Service time is required and must be a valid number greater than 0. Received: ' . var_export($param['service_time'] ?? 'NOT_SET', true)));
                }
                $status = $param['status'] == 1 ? 1 : 0;
                $model = (new Appointmentpro_Model_Service())
                    ->find(['service_id' => $param['service_id']])
                    ->setValueId($param['value_id'])
                    ->setName($param['name'])
                    ->setPrice($param['price'])
                    ->setSpecialPrice($param['special_price'])
                    ->setSpecialStart($param['special_start'])
                    ->setSpecialEnd($param['special_end'])
                    ->setServiceTime((int) $serviceTime)
                    ->setBufferTime((int) ($param['buffer_time'] ?? 0))
                    ->setServicePoints($param['service_points'])
                    ->setCategoryId($param['category_id'])
                    ->setDescription($param['description'])
                    ->setTotalBookingPerSlot($param['total_booking_per_slot'])
                    ->setVisibleToUser(!empty($param['visible_to_user']) ? 1 : 0)
                    ->setStatus($status);

                if (!empty($param['featured_image'])) {
                    foreach ($param['featured_image'] as $iKey => $iValue) {
                        if (file_exists(Core_Model_Directory::getTmpDirectory(true) . "/" . $iValue)) {
                            list($relativePath, $filename) = $this->_getImageData($iValue);
                            $imageURL = $relativePath . '/' . $filename;
                            $model->setFeaturedImage($imageURL);
                        }
                    }
                }

                $model->save();
                $service_id = $model->getId();

                // Handle break configuration
                if (!empty($param['has_break_time'])) {
                    $breakConfig = (new Appointmentpro_Model_ServiceBreakConfig())
                        ->find(['service_id' => $service_id]);

                    $breakConfig->setServiceId($service_id)
                        ->setHasBreakTime(1)
                        ->setWorkTimeBeforeBreak($param['work_time_before_break'] ?? 0)
                        ->setBreakDuration($param['break_duration'] ?? 0)
                        ->setWorkTimeAfterBreak($param['work_time_after_break'] ?? 0)
                        ->setBreakIsBookable(!empty($param['break_is_bookable']) ? 1 : 0)
                        ->save();
                } else {
                    // Remove break configuration if has_break_time is not checked
                    $breakConfig = (new Appointmentpro_Model_ServiceBreakConfig())
                        ->find(['service_id' => $service_id]);
                    if ($breakConfig->getId()) {
                        $breakConfig->delete();
                    }
                }

                $modelUpdateLocation =  (new Appointmentpro_Model_ServiceLocation())->updateDeleteStatus('service_id', $service_id);
                if (!empty($param['locations'])) {
                    foreach ($param['locations'] as $lKey => $lValue) {
                        $modelServiceLocation = (new Appointmentpro_Model_ServiceLocation())
                            ->find(['service_id' => $service_id, 'location_id' => $lValue])
                            ->setServiceId($service_id)
                            ->setLocationId($lValue)
                            ->setIsDelete(0)
                            ->save();
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
     * @param $image
     * @return array
     * @throws Siberian_Exception
     */
    private function _getImageData($image)
    {

        $img_src = Core_Model_Directory::getTmpDirectory(true) . "/" . $image;

        $info = pathinfo($img_src);
        $filename = $info['basename'];
        $relativePath = '/appointmentpro/' . $this->getApplication()->getId() . '/service';
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
     * fetch Service
     */
    public function findAllAction()
    {

        try {
            $request = $this->getRequest();
            $limit = $request->getParam("perPage", 25);
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
            $settingModel = (new Appointmentpro_Model_Settings())->find(['value_id' => $value_id]);
            $settings = $settingModel->getData();
            $services = (new Appointmentpro_Model_Service())
                ->findByValueId($value_id, $params);

            $countAll = (new Appointmentpro_Model_Service())->countAllForApp($value_id);
            $countFiltered =   (new Appointmentpro_Model_Service())->countAllForApp($value_id, $params);

            $servicesJson = [];
            foreach ($services as $service) {
                $data = $service->getData();
                $data['status'] = $data['status'] == 1 ? p__('appointmentpro', "Active") : p__('appointmentpro', "In Active");
                $data['visible_to_user'] = $data['visible_to_user'] == 1 ? p__('appointmentpro', "Visible") : p__('appointmentpro', "Hidden");
                $data['featured_image'] = empty($data['featured_image']) ? '/app/local/modules/Appointmentpro/resources/design/desktop/flat/images/dummy-image.jpg' : '/images/application/' . $data['featured_image'];
                $data['currency'] = $this->getApplication()->getCurrency();
                $data['price_with_currency'] = Appointmentpro_Model_Utils::displayPrice($data['price'], $this->getApplication()->getCurrency(), $settings['number_of_decimals'], $settings['decimal_separator'], $settings['thousand_separator'], $settings['currency_position']);
                $servicesJson[] = $data;
            }

            $payload = [
                "records" => $servicesJson,
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
}
