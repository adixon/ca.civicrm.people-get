<?php
class CRM_PeopleGet_Tasks {
 
  public static function GetContacts(CRM_Queue_TaskContext $ctx, $token) {
    CRM_Core_Error::debug_var('token',$token);
    CRM_Core_Session::setStatus('Task with token: '.$token.' is executed', 'Queue task', 'success');
    return true;
  }
 
}
