<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_TeamThankYou extends CRM_Core_Form {
  function preProcess(){
    $workflow  = $this->get('workflowTeam');
    $this->assign('workflow', $workflow);
    $this->assign('teamTitle', $this->get('teamName'));

    if (!$this->get('page_id')) {
      CRM_Core_Error::fatal(ts("Can't determine pcp id."));
    }

    $texts = array();
    if (!$this->get('team_thank_message')) {
      $allStatus = CRM_Core_Session::singleton()->getStatus(TRUE);
      if ($allStatus) {
        foreach ($allStatus as $status) {
          $texts[] = "<p>{$status['text']}</p>";
        }
      }
      $texts = implode("<br/>", $texts);
      $this->set('team_thank_message', $texts);
    }
    $this->assign("team_thank_message", $this->get('team_thank_message'));
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

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
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
