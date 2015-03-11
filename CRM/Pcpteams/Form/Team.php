<?php

require_once 'CRM/Core/Form.php';

/**
 * Search Pcp Team Class
 * Civi 4.5
 * Extends Core Form Controller.
 */
class CRM_Pcpteams_Form_Team extends CRM_Core_Form {

  function preProcess(){
    $this->_pcpId = $this->controller->get('pcpId');
    //$this->_pcpId = CRM_Utils_Request::retrieve('id', 'Positive');
    $userId = CRM_Pcpteams_Utils::getloggedInUserId();
    if (!$this->_pcpId) {
      $result = civicrm_api('Pcpteams', 
        'getcontactpcp', 
        array(
          'contact_id' => $userId,
          'version'    => 3
        )
      );
      if (!empty($result['id'])) {
        $this->_pcpId = $result['id'];
      }
    }
    parent::preProcess();
  }

  function buildQuickForm() {
    // add form elements
    $this->addEntityRef('pcp_team_contact', ts('Select Team'), array('api' => array('params' => array('contact_type' => 'Organization', 'contact_sub_type' => 'Team')), 'create' => TRUE), TRUE);
    //$this->add('hidden', 'pcpId', $this->_pcpId);
    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Save'),
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

    // get PCP-ID for team 
    if ($teamId) {
      $result = civicrm_api('Pcpteams', 
        'getcontactpcp', 
        array(
          'contact_id' => $teamId,
          'version'    => 3
        )
      );
      if (!empty($result['id'])) {
        $teamPcpId = $result['id'];
      }
    }

    if ($teamPcpId) {
      $teamPcpCfId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', 'Team_PCP_ID', 'id', 'name');
      $params = array(
        'version'   => 3,
        'entity_id' => $this->_pcpId,
        "custom_{$teamPcpCfId}" => $teamPcpId,
      );

      $result = civicrm_api3('CustomValue', 'create', $params);
    } else {
      // FIXME: this check should be at validation step / form-rule
      CRM_Core_Error::fatal("The team (ID: $teamId) doesn't have a pcp.");
    }
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
