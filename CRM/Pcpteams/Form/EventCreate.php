<?php

require_once 'CRM/Core/Form.php';

/**
 * Search Pcp Team Class
 * Civi 4.5
 * Extends Core Form Controller.
 */
class CRM_Pcpteams_Form_EventCreate {

  function preProcess(&$form) {
    if (!$form->get('page_id')) {
      CRM_Core_Error::fatal(ts("Can't determine pcp id."));
    }
  }
  
  static function setDefaultValues(&$form) {
  }

  function buildQuickForm(&$form) {
    $form->add('text', 'event_name', ts('Event Name'), array(), TRUE);
    
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
    $params      = array(
      'version' => 3,
      'id'      => $form->get('page_id'),
      'title'   => $values['event_name']
    );
    
    $result = civicrm_api('pcpteams', 'create', $params);
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
