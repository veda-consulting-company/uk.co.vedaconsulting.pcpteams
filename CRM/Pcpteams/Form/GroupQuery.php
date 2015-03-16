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
        'name' => ts('Continue'),
        'isDefault' => TRUE,
      ),
    ));
    $this->addFormRule(array('CRM_Pcpteams_Form_GroupQuery', 'formRule'), $this);
    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
  }
  
  static function formRule($fields){
    $errors = array();
    if (empty($fields['teamOption'])) {
      $errors['teamOption'] = ts('Please select at least one field.');
    }

    return empty($errors) ? TRUE : $errors;
  }
  
  function postProcess() {
    $values = $this->exportValues();
    $this->set("workflowGroup", $values['teamOption']);
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
