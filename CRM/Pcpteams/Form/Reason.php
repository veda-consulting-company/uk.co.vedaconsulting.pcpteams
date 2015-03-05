<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 */
class CRM_Pcpteams_Form_Reason extends CRM_Core_Form {
  function preProcess() {
    $this->_PcpId = CRM_Utils_Request::retrieve('id', 'Positive');

    //Fixme: validate the contact id, and check permission can view / edit this pcp.

    //Fixme: check the Pcp Custom Set values, In memory / In celebration

    parent::preProcess();
  }

  function buildQuickForm() {
    // InCelebration - Event type 
    $pcp_type = CRM_Core_OptionGroup::values(CRM_Pcpteams_Utils::C_PCP_TYPE, FALSE);

    $this->add("select", "pcp_type", ts('PCP Type'), $pcp_type);

    $this->addEntityRef('pcp_contact_id', ts('Search Contact'), array('create' => TRUE), TRUE);

    // InMemory - Deceased date 
    $this->addDate('deceased_date', ts('Deceased date'), FALSE, array('formatType' => 'birth'));

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
    $pcp_type_contact  = $values['pcp_contact_id'];
    $pcp_type     = $values['pcp_type'];
    
    $custom_group_name = CRM_Pcpteams_Utils::C_PCP_CUSTOM_GROUP_NAME;
    $customGroupParams = array(
        'version'     => 3,
        'sequential'  => 1,
        'name'        => $custom_group_name,
    );
    $custom_group_ret = civicrm_api('CustomGroup', 'GET', $customGroupParams);
    
    $customGroupID = $custom_group_ret['id'];
    $customGroupTableName = $custom_group_ret['values'][0]['table_name'];
   
    $query          = "SELECT ct.pcp_type_contact as contactID FROM $customGroupTableName ct WHERE ct.pcp_type = '$pcp_type'";
    $dao            = CRM_Core_DAO::executeQuery($query);
    $pcpFound = FALSE;
    while($dao->fetch()) {
      if($dao->contactID == $pcp_type_contact) {
        CRM_Core_Session::setStatus('PCP Found. Redirecting to dashboard');
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/pcp/dashboard', 'reset=1'));
        $pcpFound = TRUE;
        break;
      }
    }
    
    if(!$pcpFound) {
      CRM_Core_Session::setStatus('PCP Not Found. Creating New PCP Record');
      $PcpID  = CRM_Pcpteams_Utils::C_PCP_ID;
      $insertQuery  = "
        INSERT INTO `civicrm_value_pcp_custom_set` (`id`, `entity_id`, `team_pcp_id`, `pcp_type`, `pcp_type_contact`) VALUES (NULL, $PcpID, NULL, '$pcp_type', $pcp_type_contact)";
      $dao  = CRM_Core_DAO::executeQuery($insertQuery);
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/pcp/dashboard', 'reset=1'));
    }
    //Fixme:
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
