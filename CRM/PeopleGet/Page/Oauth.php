<?php
use CRM_PeopleGet_ExtensionUtil as E;

class CRM_PeopleGet_Page_Oauth extends CRM_Core_Page {

  public function run() {
    $myclient = new CRM_PeopleGet_GoogleClient();
    if (isset($_GET['code'])) { // we received the positive auth callback, get the token and store it in cache
      $result = $myclient->client->authenticate($_GET['code']);
      if ($result['access_token']) {
        CRM_Core_Error::debug_var('get access token', $myclient->client);
        $token = $myclient->client->getAccessToken();
        // CRM_Core_Error::debug_var('token', $token);
        // todo - should we cache these tokens in a setting per contact? What about offline access?
	CRM_Core_Session::singleton()->set('people_get_token',$token);
      }
      else {
        CRM_Core_Error::debug_var('authenticate result', $result);
        $message = print_r($result, TRUE);
        $title = ts('Unexpected authentication Error');
	CRM_Core_Session::setStatus('<pre>'.$message.'</pre>', $title, 'error', array('expires' => 0));
      }
    }
    elseif (isset($_GET['error'])) { // no auth!
      CRM_Core_Error::debug_var('google api return error', $_GET);
      $message = $_GET['error'];
      $title = ts('Authentication Error');
      CRM_Core_Session::setStatus($message, $title, 'error', array('expires' => 0));
    }
    else {
      CRM_Core_Error::debug_var('GET', $_GET);
      $message = 'No code or error';
      $title = ts('Unexpected authentication Error');
      CRM_Core_Session::setStatus($message, $title, 'error', array('expires' => 0));
    }
    // we never display this page, always redirect.
    $redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/civicrm/import/googlepeople?reset=1';
    CRM_Utils_System::redirect($redirectUrl);
  }
}
