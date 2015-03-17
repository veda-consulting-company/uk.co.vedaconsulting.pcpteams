<?php

require_once 'CRM/Core/Form.php';

/**
 * Search Pcp Team Class
 * Civi 4.5
 * Extends Core Form Controller.
 */
class CRM_Pcpteams_Form_EventDetails extends CRM_Core_Form {

  function preProcess() {
    CRM_Utils_System::setTitle(ts('Event Details'));

    $obj = new CRM_Event_Page_EventInfo();
    $obj->set('id', $this->get('pageId'));

    ob_start();
    $op = $obj->run();
    $tvar = $obj->get_template_vars();
    ob_end_clean();
  }

  function buildQuickForm() {
    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Continue'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
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
