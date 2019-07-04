<?php

use CRM_PeopleGet_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_PeopleGet_Form_GoogleAuth extends CRM_Core_Form {
  public function buildQuickForm() {

    // add form elements
    $this->add(
      'text', // field type
      'client_id', // field name
      'Client ID', // field label
      array(),
      TRUE // is required
    );
    $this->add(
      'text', // field type
      'client_secret', // field name
      'Client Secret', // field label
      array(),
      TRUE // is required
    );
    $this->add(
      'text', // field type
      'domain', // field name
      'Domain', // field label
      array(),
      TRUE // is required
    );

    $result = CRM_Core_BAO_Setting::getItem('Get Google People Extension', 'people_get_settings');
    $defaults = (empty($result)) ? array() : $result;
    $this->setDefaults($defaults);

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();
    foreach (array('qfKey', '_qf_default', '_qf_Settings_submit', 'entryURL') as $key) {
      if (isset($values[$key])) {
        unset($values[$key]);
      }
    }
    $values['redirect_uri'] = 'https://' . $_SERVER['HTTP_HOST'] . '/civicrm/google/oauth/';
    CRM_Core_BAO_Setting::setItem($values, 'Get Google People Extension', 'people_get_settings');

    parent::postProcess();
  }


  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
