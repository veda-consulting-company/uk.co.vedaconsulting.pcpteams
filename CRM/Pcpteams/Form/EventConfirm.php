<?php

/**
 * Search Pcp Team Class
 * Civi 4.5
 * Extends Core Form Controller.
 */
class CRM_Pcpteams_Form_EventConfirm extends CRM_Pcpteams_Form_Workflow {

  function preProcess() {
    parent::preProcess();
    CRM_Utils_System::setTitle(ts('Confirm If you have a place'));
  }

  function buildQuickForm() {
    $this->_pageId = $this->controller->get('pageId');
    $eventDetails = CRM_Pcpteams_Utils::getEventDetailsbyEventId($this->_pageId);
    
    $redirectRegistration     = CRM_Utils_System::url('civicrm/pcp/support', "code=cpftq&qfKey={$this->controller->_key}", TRUE, Null, FALSE);
    $redirectSkipRegistration = CRM_Utils_System::url('civicrm/event/register', "reset=1&id={$this->get('component_page_id')}", TRUE, NULL, FALSE);
    
    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Yes I do'),
        'js' => array('onclick' => "location.href='{$redirectRegistration}'; return false;"),
      ),
      array(
        'type' => 'submit',
        'name' => ts('No I need to register'),
        'js' => array('onclick' => "location.href='{$redirectSkipRegistration}'; return false;"),
      )
    ));

    // export form elements
    $this->assign('eventDetails', $eventDetails);
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  function postProcess() {
   
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
