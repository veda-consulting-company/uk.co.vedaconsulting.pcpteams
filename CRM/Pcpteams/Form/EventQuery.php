<?php

/**
 * Search Pcp Team Class
 * Civi 4.5
 * Extends Core Form Controller.
 */
class CRM_Pcpteams_Form_EventQuery extends CRM_Pcpteams_Form_Workflow {

  function preProcess() {
    parent::preProcess();
    CRM_Utils_System::setTitle(ts('Specify Event'));
  }

  function buildQuickForm() {
    $teamOptions = array(
        ts(' Doing my own event'),
        ts(' Join an existing event')
      );
    $this->addRadio('teamOption', '', $teamOptions, NULL, '<br/><br/>');
    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Continue'),
        'isDefault' => TRUE,
      ),
    ));
    $this->assign('elementNames', $this->getRenderableElementNames());
  }

  function postProcess() {
    $values = $this->exportValues();
    $this->set("workflowEvent", $values['teamOption']);
 }
  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
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
