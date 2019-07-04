<?php
use CRM_PeopleGet_ExtensionUtil as E;

class CRM_PeopleGet_Page_GetGooglePeople extends CRM_Core_Page {
      
  public function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(E::ts('Import Your Google Contacts'));

    // Example: Assign a variable for use in a template
    $this->assign('currentTime', date('Y-m-d H:i:s'));
    $token = CRM_Core_Session::singleton()->get('people_get_token');
    CRM_Core_Error::debug_var('token from session',$token);
    $this->assign('peopleGet', FALSE);
    if (isset($token)) {
      if (empty($token['error'])) { // we can import!
        // CRM_Core_Error::debug_var('token',$token);
        //retrieve the queue
        $queue = CRM_PeopleGet_Queue::singleton()->getQueue();
        $task = new CRM_Queue_Task(
          array('CRM_PeopleGet_Tasks', 'GetContacts'), //call back method
          $token //parameter
        );
        // add this task to the queue
	$queue->createItem($task);
        // CRM_Core_Error::debug_var('queue',$queue);
        $this->assign('peopleGet', TRUE);
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

  private function addTaskWithParameter(&$queue) {
    $number = rand(1, 100); //set parameter for task a random number between 1 and 100
 
    //create a task without parameters
  }
}
