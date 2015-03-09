<?php

require_once 'CRM/Core/Form.php';

/**
 * Search Pcp Team Class
 * Civi 4.5
 * Extends Core Form Controller.
 */
class CRM_Pcpteams_Form_Team extends CRM_Core_Form {

  function preProcess(){
    parent::preProcess();
  }

  function buildQuickForm() {

    // add form elements
    $this->addEntityRef('pcp_team_contact', ts('Select Team'), array('api' => array('params' => array('contact_type' => 'Organization', 'contact_sub_type' => 'Team')), 'create' => TRUE), TRUE);
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  function postProcess() {
    $values = $this->exportValues();
    $teamId = $values['pcp_team_contact'];
    $userId = CRM_Pcpteams_Utils::getloggedInUserId();
    CRM_Pcpteams_Utils::checkORCreateTeamRelationship($userId, $teamId, TRUE);
    CRM_Pcpteams_Utils::pcpRedirectUrl('dashboard');
    parent::postProcess();
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
