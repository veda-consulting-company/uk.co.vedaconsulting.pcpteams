<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_GroupQuery extends CRM_Core_Form {
  function preProcess(){
    CRM_Utils_System::setTitle(ts('Group Question'));
    parent::preProcess();  
  }
  
  function buildQuickForm() {

    $teamOptions = array();
    $teamOptions = array(
        ts(' No, Iam doing this event on my own'),
        ts(' Yes, Iam fundraising with a corporate partner'),
        ts(' Yes, Iam fundraising with a local branch')
      );
    $this->addRadio('teamOption', '', $teamOptions, NULL, '<br/><br/>');
    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Next'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  function postProcess() {
    $values = $this->exportValues();
    //FIXME: postProcess
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    $elementNames = array();
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
}
