<?php

require_once 'CRM/Core/Form.php';

/**
 * Search Pcp Team Class
 * Civi 4.5
 * Extends Core Form Controller.
 */
class CRM_Pcpteams_Form_EventJoin {

  function preProcess(&$form) {
    CRM_Utils_System::setTitle(ts('Event Search'));
  }
  
  static function setDefaultValues(&$form) {
   
  }

  function buildQuickForm(&$form) {
    $form->_pageId = $form->controller->get('pageId');
    $eventId = $form->addEntityRef('event_id', ts('Event Name'), array(
       'entity' => 'event',
       'placeholder' => ts('- any -'),
       'select' => array('minimumInputLength' => 0),
     ));
    
    $form->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Continue'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $form->assign('elementNames', $this->getRenderableElementNames());
  }

  function postProcess(&$form) {
    $values  = $form->exportValues();
    $eventId = $values['event_id'];
    $form->set('pageId', $eventId);
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames(&$form) {
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
