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
    $pcp_type = CRM_Core_OptionGroup::values('pcp_type_20150219182347', FALSE);

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
    $pcp_inmem_contact  = $values['pcp_contact_id'];
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
      if($dao->contactID == $pcp_inmem_contact) {
        CRM_Core_Session::setStatus('PCP Found. Redirecting to dashboard');
        CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/pcp/dashboard', 'reset=1'));
        $pcpFound = TRUE;
        break;
      }
    }
    
    if(!$pcpFound) {
      CRM_Core_Session::setStatus('PCP Not Found. Please try again or create new PCP Contact');
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/pcp/reason', 'reset=1'));
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
