<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_Team_Invited extends CRM_Core_Form {

  function preProcess() {
    CRM_Utils_System::setTitle(ts('Invited to join a team'));
  }
  
  function setDefaultValues() {
    $defaults = array();
    $defaults['description'] = "Richs Bikeathon army <br/><br/>Captain - Rich Williams <br/> <br/>Event - London Bikeathon<br/><br/>";
    return $defaults;
  }

  function buildQuickForm() {
    $el = $this->add('textarea', 'description', ts('') , '',
      CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Group', 'description')
    );
    $el->freeze();
    $teamOptions = array();
    $teamOptions = array(
        ts(' Yes, this is the team'),
        ts(' No, I would like to join another team'),
        ts(' I do not want to join any team')
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
    return TRUE;
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
