<?php
/* 
 * Add a getcontacts task to the queue and then run it!
 *
 */
use CRM_PeopleGet_ExtensionUtil as E;

class CRM_PeopleGet_Page_Get extends CRM_Core_Page {

  public function run() {
    $token = CRM_Core_Session::singleton()->get('people_get_token');
    //retrieve the queue
    $queue = CRM_PeopleGet_Queue::singleton()->getQueue();
    $task = new CRM_Queue_Task(array('CRM_PeopleGet_Tasks', 'GetContacts'), $token);
    // add this task to the queue
    $queue->createItem($task);
    // and now run the queue.
    $runner = new CRM_Queue_Runner(array(
      'title' => ts('Google People Get runner'), //title fo the queue
      'queue' => $queue, //the queue object
      'errorMode'=> CRM_Queue_Runner::ERROR_ABORT, //abort upon error and keep task in queue
      'onEnd' => array('CRM_PeopleGet_Page_Get', 'onEnd'), //method which is called as soon as the queue is finished
      'onEndUrl' => CRM_Utils_System::url('civicrm/import/googlepeople', 'reset=1'), //go to page after all tasks are finished
    ));
 
    $runner->runAllViaWeb(); // does not return
  }
 
  /**
   * Handle the final step of the queue
   */
  static function onEnd(CRM_Queue_TaskContext $ctx) {
    //set a status message for the user
    CRM_Core_Session::setStatus('All tasks in queue are executed', 'Queue', 'success');
  }

}
