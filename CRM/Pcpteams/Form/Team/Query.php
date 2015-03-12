<?php

require_once 'CRM/Core/Form.php';

class CRM_Pcpteams_Form_Team_Query extends CRM_Core_Form {

  function preProcess() {
    CRM_Utils_System::setTitle(ts('Team Question'));
  }

  function buildQuickForm() {
    //$this->createElement('radio','delete_participant' , '', 'Kajan', 1, '<br />');
    //$this->addRadio('delete_participant', NULL, NULL, NULL, '<br />');
    $teamOptions = array();
    $teamOptions[] = $this->createElement('radio',
      NULL, NULL, ts(' No, I am doing this event on my own'), 1, '<br />'
    );
    $teamOptions[] = $this->createElement('radio',
      NULL, NULL, ts(' Yes, I would like to create my own team'), 2, '<br />'
    );
    $teamOptions[] = $this->createElement('radio',
      NULL, NULL, ts(' Yes, I would like to join an existing team'), 3, '<br />'
    );

    $this->addGroup($teamOptions, 'teamOption'
    );
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
