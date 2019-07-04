<?php
use CRM_PeopleGet_ExtensionUtil as E;

class CRM_PeopleGet_Page_Get extends CRM_Core_Page {

  public function run() {
    //retrieve the queue
    $queue = CRM_PeopleGet_Queue::singleton()->getQueue();
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
