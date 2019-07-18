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
        $task = new CRM_Queue_Task(
           array('CRM_PeopleGet_Tasks', 'ImportContact'), //call back method
           $contact
        );
        // add this task to the queue
	$ctx->queue->createItem($task);
      }
    }
    return true;
  }

  public static function ImportContact(CRM_Queue_TaskContext $ctx, $contact) {
    // CRM_Core_Error::debug_var('contact to import',$contact);
    return true;
  }
 
}
