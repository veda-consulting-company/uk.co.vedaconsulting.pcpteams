<?php

/**
 * Search Pcp Team Class
 * Civi 4.5
 * Extends Core Form Controller.
 */
class CRM_Pcpteams_Form_EventConfirm extends CRM_Pcpteams_Form_Workflow {

  function preProcess() {
    parent::preProcess();
  }

  function buildQuickForm() {
    $eventDetails = CRM_Pcpteams_Utils::getEventDetailsbyEventId($this->controller->get('component_page_id'));
    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Yes I do'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('eventDetails', $eventDetails);
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  function postProcess() {
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/pcp/support', "code=cpftq&qfKey={$this->controller->_key}"));
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
