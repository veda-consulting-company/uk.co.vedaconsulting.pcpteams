<?php

require_once 'CRM/Core/Form.php';

class CRM_Pcpteams_Form_TeamQuery extends CRM_Pcpteams_Form_Workflow {

  function preProcess() {
    parent::preProcess();

    // If invitation detected forward to invitation screen
    // Note: controller is responsible for making sure tpId is in session
    $teamPcpId = $this->get('tpId');
    if ($teamPcpId) {
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/pcp/support', "code=cpftn&option=invite&qfKey={$this->controller->_key}"));
    }
  }

  function buildQuickForm() {
    $teamOptions = array(
        ts(' No, I am doing this event on my own'),
        ts(' Yes, I would like to create my own team'),
        ts(' Yes, I would like to join an existing team')
      );
    $this->addRadio('teamOption', '', $teamOptions, NULL, '<br/><br/>');
    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Continue'),
        'isDefault' => TRUE,
      ),
    ));
    $this->addFormRule(array('CRM_Pcpteams_Form_TeamQuery', 'formRule'), $this);
    $this->assign('elementNames', $this->getRenderableElementNames());
  }
  
  static function formRule($fields){
    $errors = array();
    if (!isset($fields['teamOption'])) {
      $errors['_qf_default'] = ts("Please select at least one option.");
    }

    return empty($errors) ? TRUE : $errors;
  }
  
  function postProcess() {
    $values = $this->exportValues();
    if ($values['teamOption'] == 0) {
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/pcp/support', "code=cpfgq&qfKey={$this->controller->_key}"));
    } else {
      $this->set("workflowTeam", $values['teamOption']);
    }
  }

  function getRenderableElementNames() {
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
