<?php
use CRM_PeopleGet_ExtensionUtil as E;

class CRM_PeopleGet_GoogleClient {

  public $client;
  private $settings;
 /**
   *
   */
  public function __construct($options = array()) {
    require_once 'vendor/autoload.php';
    
    $this->client = new Google_Client();
    $this->settings = CRM_Core_BAO_Setting::getItem('Get Google People Extension', 'people_get_settings');
    // CRM_Core_Error::debug_var('settings',$settings);
    $this->client->setClientId($this->settings['client_id']);
    $this->client->setClientSecret($this->settings['client_secret']);
    $this->client->setRedirectUri($this->settings['redirect_uri']); 
    $this->client->addScope('https://www.googleapis.com/auth/contacts.readonly');
    $this->client->setAccessType('offline');
    // $client->setState('ignoreme');
    $this->client->setIncludeGrantedScopes(true);
  }
}
