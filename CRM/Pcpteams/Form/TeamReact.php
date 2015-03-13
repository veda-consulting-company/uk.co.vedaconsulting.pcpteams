<?php

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_TeamReact extends CRM_Core_Form {
  function preProcess() {
    $workflowTeam = $this->get("workflowTeam");

    if ($workflowTeam == 1) { // create team
      $this->_reactToFile = "TeamNew";
    }
    else if ($workflowTeam == 'invite') { // join team
      $this->_reactToFile = "TeamInvite";
    }
    else {// join team
      $this->_reactToFile = "TeamJoin";
    }

    $className = 'CRM_Pcpteams_Form_' . $this->_reactToFile;
    $className::preProcess($this);
    $this->assign('reactClass', $this->_reactToFile);
  }

  function buildQuickForm() {
    $className = 'CRM_Pcpteams_Form_' . $this->_reactToFile;
    $className::buildQuickForm($this);
  }

  function postProcess() {
    $className = 'CRM_Pcpteams_Form_' . $this->_reactToFile;
    $className::postProcess($this);
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
