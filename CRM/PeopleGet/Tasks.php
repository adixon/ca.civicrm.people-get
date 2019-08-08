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
        'people/me', array(
          'personFields' => 'names,emailAddresses,addresses,phoneNumbers',
          'pageSize' => '2000'
        ));
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
        foreach($person->phoneNumbers as $phone) {
	  $type = empty($phone['formattedType']) ? 'Home' : $phone['formattedType'];
          $contact['phone'][$type] = $phone['value'];
        }
        foreach($person->addresses as $address) {
          $type = empty($address['type']) ? 'primary' : $address['type'];
          $contact['address'][$type]['street_address'] = $address['streetAddress'];
          $contact['address'][$type]['city'] = $address['city'];
          $contact['address'][$type]['state_province'] = $address['region'];
          $contact['address'][$type]['country'] = $address['country'];
          $contact['address'][$type]['postal_code'] = $address['postalCode'];
        }
        // CRM_Core_Error::debug_var('contact to import',$contact);
        $task = new CRM_Queue_Task(array('CRM_PeopleGet_Tasks', 'ImportContact'), [$contact], 'Processed '.$contact['email']['primary']);
        // add this task to the queue
        $ctx->queue->createItem($task);
      }
    }
    return true;
  }

  public static function ImportContact(CRM_Queue_TaskContext $ctx, $contact) {
    if (!empty($contact['email'])) {
      $matches = 0;
      foreach($contact['email'] as $type => $email_address) {
      // CRM_Core_Error::debug_var('contact to import', $contact);
        $result = civicrm_api3('Contact', 'get', ['sequential' => 1, 'email' => $email_address]);
        $matches += $result['count'];
      }
      if ($matches == 0) {
        $contact['contact_type'] = 'Individual';
        // $contact['api.email']['email'] = $contact['email']['primary'];
        $i = 0;
        foreach($contact['email'] as $type => $email_address) {
          $contact['api.email.create.'.$i++] = array('location_type_id' => $type, 'email' => $email_address);
        }
        if (count($contact['phone'])) {
          $i = 0;
	  foreach($contact['phone'] as $type => $phoneNumber) {
	    $type_key = ($type == 'Mobile') ? 'phone_type_id' : 'location_type_id';
            $contact['api.phone.create.'.$i++] = array($type_key => $type, 'phone' => $phoneNumber);
          }
        }
        if (count($contact['address'])) {
          $i = 0;
          foreach($contact['address'] as $type => $address) {
            $address['location_type_id'] = $type;
            $contact['api.address.create.'.$i++] = $address;
          }
        }
        $result = civicrm_api3('Contact', 'create', $contact);
      }
      else {
        $crm_contact = $result['values'][0];
        CRM_Core_Error::debug_var('Contact already exists',$crm_contact);
        $update_contact = ['id' => $crm_contact['contact_id']];
        if (empty($crm_contact['first_name'])) {
          $update_contact['first_name'] = $contact['first_name'];
        }
        if (empty($crm_contact['last_name'])) {
          $update_contact['last_name'] = $contact['last_name'];
        }
        if (empty($crm_contact['phone_id']) && count($contact['phone'])) {
          $i = 0;
          foreach($contact['phone'] as $type => $phoneNumber) {
	    $type_key = ($type == 'Mobile') ? 'phone_type_id' : 'location_type_id';
            $update_contact['api.phone.create.'.$i++] = array($type_key => $type, 'phone' => $phoneNumber);
          }
        }
        if (empty($crm_contact['address_id']) && count($contact['address'])) {
          $i = 0;
          foreach($contact['address'] as $type => $address) {
            $address['location_type_id'] = $type;
            $update_contact['api.address.create.'.$i++] = $address;
          }
        }
        if (count($update_contact) > 1) {
          $result = civicrm_api3('Contact', 'create', $update_contact);
        }
      }
    }
    return true;
  }
 
}
