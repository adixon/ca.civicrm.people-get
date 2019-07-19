<?php

class CRM_PeopleGet_Tasks {
 
  public static function GetContacts(CRM_Queue_TaskContext $ctx, $token) {
    // CRM_Core_Error::debug_var('token',$token);
    $myclient = new CRM_PeopleGet_GoogleClient();
    // CRM_Core_Error::debug_var('myclient',$myclient);
    $myclient->client->setAccessToken($token);
    // CRM_Core_Error::debug_var('client',$myclient->client);
    // CRM_Core_Error::debug_var('access token result',$result);
    $people_service = new Google_Service_PeopleService($myclient->client);
    try {
      $connections = $people_service->people_connections->listPeopleConnections(
        'people/me', array('personFields' => 'names,emailAddresses'));
      // CRM_Core_Error::debug_var('connections',$connections);
    }
    catch (Google_Service_Exception $e) { 
      $errors = $e->getErrors();  
      CRM_Core_Error::debug_var('Google Service errors',$errors);
      $connections = array();
    }
    $cid = CRM_Core_Session::singleton()->getLoggedInContactID();
    $loggedin = civicrm_api3('Contact', 'getsingle', ['return' => ['display_name'], 'id' => $cid ]);
    // CRM_Core_Session::setStatus('Task with token: '.$token->access_token.' is executed', 'Queue task', 'success');
    foreach($connections as $person) {
      $contact = [];
      foreach($person->emailAddresses as $email) {
        $type = empty($email['type']) ? 'primary' : $email['type'];
        $contact['email'][$type] = $email['value'];
      }
      if (!empty($person->names[0])) {
        $contact['first_name'] =  $person->names[0]['givenName'];
        $contact['last_name'] =  $person->names[0]['familyName'];
      }
      if (!empty($contact)) {
        $contact['source'] = 'Google Contact Import from  '.$loggedin['display_name'];
        // CRM_Core_Error::debug_var('contact to import',$contact);
        $task = new CRM_Queue_Task(array('CRM_PeopleGet_Tasks', 'ImportContact'), [$contact], 'Processed '.$contact['email']['primary']);
        // add this task to the queue
        $ctx->queue->createItem($task);
      }
    }
    return true;
  }

  public static function ImportContact(CRM_Queue_TaskContext $ctx, $contact) {
    if (!empty($contact['email']['primary'])) {
      // CRM_Core_Error::debug_var('contact to import', $contact);
      $result = civicrm_api3('Contact', 'get', ['sequential' => 1, 'email' => $contact['email']['primary']]);
      if ($result['count'] == 0) {
	$contact['contact_type'] = 'Individual';
        $contact['api.email']['email'] = $contact['email']['primary'];
        $result = civicrm_api3('Contact', 'create', $contact);
      }
      else {
        CRM_Core_Error::debug_var('Already exists',$result['values'][0]);
      }
    }
    return true;
  }
 
}
