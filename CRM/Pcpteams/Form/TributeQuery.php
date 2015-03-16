<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_TributeQuery extends CRM_Core_Form {
  function preProcess(){
    CRM_Utils_System::setTitle(ts('Reason'));
    parent::preProcess();  
  }
  
  function buildQuickForm() {
    
    $teamOptions = array();
    $teamOptions = array(
        ts(' I just want to support you'),
        ts(' Iam doing this in memory of someone'),
        ts(' Iam doing this in celebration of an event')
      );
    $this->addRadio('teamOption', '', $teamOptions, NULL, '<br/><br/>');
    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Continue'),
        'isDefault' => TRUE,
      ),
    ));
    $this->addFormRule(array('CRM_Pcpteams_Form_TributeQuery', 'formRule'), $this);
    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
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
    $this->set("workflowTribute", $values['teamOption']);
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
