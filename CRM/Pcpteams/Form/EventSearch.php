<?php

require_once 'CRM/Core/Form.php';

/**
 * Search Pcp Team Class
 * Civi 4.5
 * Extends Core Form Controller.
 */
class CRM_Pcpteams_Form_EventSearch extends CRM_Core_Form {

  function preProcess() {
    CRM_Utils_System::setTitle(ts('Event Search'));
  }

  function buildQuickForm() {
    $this->_pageId = $this->controller->get('pageId');
    $eventId = $this->addEntityRef('event_id', ts('Event Name'), array(
       'entity' => 'event',
       'placeholder' => ts('- any -'),
       'select' => array('minimumInputLength' => 0),
     ));
    
    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Continue'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  function postProcess() {
    $values  = $this->exportValues();
    $eventId = $values['event_id'];
    $this->set('pageId', $eventId);
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
