<?php

class Appointmentpro_Model_GoogleService extends Core_Model_Default
{

	    var $client_id, $client_secret, $url, $action, $access_token, $curldata;
        public static $urlGoogleAccount = 'https://accounts.google.com/';
        public static $urlGoogleAPI = 'https://www.googleapis.com/';
       
        public function setClientId($client_id)
        {
            $this->client_id = $client_id;
           
            return $this;
        }


        public function setRedirectUri($redirect_uri)
        {
            $this->redirect_uri = $redirect_uri;
           
            return $this;
        }


        public function setClientSecret($client_secret)
        {
            $this->client_secret = $client_secret;
           
            return $this;
        }

        public function setAccessToken($access_token)
        {
            $this->access_token = $access_token;
           
            return $this;
        }


        public function setAction($action)
        {
            $this->action = $action;
           
            return $this;
        }

        public function setRoute($route, $apiType)
        {
        	if($apiType == 'account'){
        		$this->url = self::$urlGoogleAccount.''.$route;
        	}else{
        		$this->url = self::$urlGoogleAPI.''.$route;
        	}

            return $this;
        }
        

        public function GetAccessToken($code){
        	
        	$this->action = 'POST';
        	//$route = 'o/oauth2/token';            
            $route = 'oauth2/v4/token';
        	$this->setRoute($route, 'api');
        	
        	$curlPost = 'client_id=' . $this->client_id . '&redirect_uri=' . $this->redirect_uri . '&client_secret=' . $this->client_secret . '&code='. $code . '&grant_type=authorization_code&access_type=offline';

        	return $this->curlAction($curlPost);
        }

        public function GetAccessTokenUsingRefreshToken($refresh_token){
            
            $this->action = 'POST';
            $route = 'o/oauth2/token';

            $this->setRoute($route, 'account');
            
            $curlPost = 'client_id=' . $this->client_id . '&redirect_uri=' . $this->redirect_uri . '&client_secret=' . $this->client_secret . '&refresh_token='. $refresh_token . '&grant_type=refresh_token&access_type=offline';

            return $this->curlAction($curlPost);
        }
 
        public function GetUserCalendarTimezone($access_token){
        	
        	$this->access_token = $access_token;
        	$route = 'calendar/v3/users/me/settings/timezone';
        	
        	$this->setRoute($route, 'api');
        	
        	return $this->curlAction();
        }

        public function GetCalendarsList($access_token){

        	$parmas = array();
			$parmas['fields'] = 'items(id,summary,timeZone)';
			$parmas['minAccessRole'] = 'owner';
			$route = 'calendar/v3/users/me/calendarList?'. http_build_query($parmas);

        	$this->access_token = $access_token;
			$this->setRoute($route, 'api');

        	return $this->curlAction();
        }


        public function CreateCalendarEvent($access_token, $curlPost){
        	if(empty($curlPost)) return false;
     		
     		$route = 'calendar/v3/calendars/primary/events';
            $this->action = 'POST';
        	$this->access_token = $access_token;
			$this->setRoute($route, 'api');

        	return $this->curlAction(json_encode($curlPost));
        }


        public function curlAction($curlPost = '')
        {


            $ch = curl_init();		
			curl_setopt($ch, CURLOPT_URL, $this->url);		
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			
			if($this->action == 'POST'){
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);

                if(!empty($this->access_token)){ 
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $this->access_token, 'Content-Type: application/json'));
                }    	
			}else{
                if(!empty($this->access_token)){
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $this->access_token));
                 } 
            }

					

			$this->curldata = json_decode(curl_exec($ch), true);
			 
            return $this->curldata;
        }



}