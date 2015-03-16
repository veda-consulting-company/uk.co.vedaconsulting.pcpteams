<?php

require_once 'CRM/Core/Form.php';

class CRM_Pcpteams_Form_TeamQuery extends CRM_Core_Form {

  function preProcess() {
    CRM_Utils_System::setTitle(ts('Team Question'));
  }

  function buildQuickForm() {
    $teamOptions = array();
    $teamOptions = array(
        ts(' No, I am doing this event on my own'),
        ts(' Yes, I would like to create my own team'),
        ts(' Yes, I would like to join an existing team')
      );
    $this->addRadio('teamOption', '', $teamOptions, NULL, '<br/><br/>');
    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Next'),
        'isDefault' => TRUE,
      ),
    ));
    $this->assign('elementNames', $this->getRenderableElementNames());
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
