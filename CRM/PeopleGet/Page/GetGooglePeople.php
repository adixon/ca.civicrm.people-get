<?php
/* 
 * This is the user page for triggering an import from the users' google contacts
 * 
 * Authentication with google is determined by the existence of a token
 * in the users' session.
 *
 * If already authenticated, provide a url that triggers a task to add
 * the users' contacts to an import queue and then run the queue.
 *
 * If not authenticated, then provide a link that allows the user to get that token
 */

use CRM_PeopleGet_ExtensionUtil as E;

class CRM_PeopleGet_Page_GetGooglePeople extends CRM_Core_Page {
      
  public function run() {
    $token = CRM_Core_Session::singleton()->get('people_get_token');
    // CRM_Core_Error::debug_var('token from session',$token);
    $this->assign('peopleGet', FALSE);
    if (isset($token)) {
      if (empty($token['error'])) { // we can import!
        // CRM_Core_Error::debug_var('token',$token);
        // CRM_Core_Error::debug_var('queue',$queue);
        $this->assign('peopleGet', TRUE);
        $contacts = array();
        $myclient = new CRM_PeopleGet_GoogleClient();
        $myclient->client->setAccessToken($token);
        $people_service = new Google_Service_PeopleService($myclient->client);
        try {
          $connections = $people_service->people_connections->listPeopleConnections(
            'people/me', array(
              'personFields' => 'names,emailAddresses',
              'pageSize' => '2000'
          ));
          // CRM_Core_Error::debug_var('connections',$connections);
        }
        catch (Google_Service_Exception $e) { 
          $errors = $e->getErrors();  
          CRM_Core_Error::debug_var('Google Service errors',$errors);
          $connections = array();
        }
        // CRM_Core_Session::setStatus('Task with token: '.$token->access_token.' is executed', 'Queue task', 'success');
        foreach($connections as $person) {
          $contact = array();
          foreach($person->emailAddresses as $email) {
            $type = empty($email['type']) ? 'primary' : $email['type'];
            $contact['email'][$type] = $email['value'];
          }
          if (!empty($person->names[0])) {
            $contact['first_name'] =  $person->names[0]['givenName'];
            $contact['last_name'] =  $person->names[0]['familyName'];
          }
          if (!empty($contact)) {
            $contacts[] = $contact; 
          }
        }
        // CRM_Core_Error::debug_var('contacts',$contacts);
        $this->assign('contacts', $contacts);
      }
      else { // unexpected bad token
        CRM_Core_Error::debug_var('invalid token',$token);
        CRM_Core_Session::singleton()->set('people_get_token');
        unset($token);
        $this->assign('errorMessage', 'You have a bad token!');
      }
    }
    if (empty($token)) { // generate the google url for authorization
      $authorize_url = $this->authorize_url();
      $this->assign('authorizeUrl', $authorize_url);
    }
    parent::run();
  }

  /*
   * Generate the authorization url over at google
   * */
  private function authorize_url() {
    $myclient = new CRM_PeopleGet_GoogleClient();
    $cid = CRM_Core_Session::singleton()->getLoggedInContactID();
    $contact = civicrm_api3('Contact', 'getsingle', ['return' => ['email'], 'id' => $cid]);
    $email = $contact['email'];
    // only set login hint if it's in the same domain 
    $domain = substr($email, strpos($email, '@') + 1);
    if ($domain == $settings['domain']) {
      $this->client->setLoginHint($email);
    }
    return $myclient->client->createAuthUrl();
  }
}
