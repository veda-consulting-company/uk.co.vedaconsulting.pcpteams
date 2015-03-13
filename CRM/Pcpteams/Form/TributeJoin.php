<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_TributeJoin extends CRM_Core_Form {
  function preProcess(){
    CRM_Utils_System::setTitle(ts('Tribute Contact'));
    parent::preProcess();  
  }
  
  function buildQuickForm() {

    // add form elements
    $this->addEntityRef('pcp_tribute_contact', ts('Select Tribute Contact'), array('api' => array('params' => array('contact_type' => 'Organization',)), 'create' => TRUE), TRUE);
    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Next'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  function postProcess() {
    $values   = $this->exportValues();
    $tributeId = $values['pcp_tribute_contact'];

    $this->_SelectedReason= CRM_Pcpteams_Constant::C_CF_IN_MEMORY; //FIXME : selected reason 
    
    if ($tributeId) {
      $tributeCfId        = CRM_Pcpteams_Utils::getPcpTypeCustomFieldId();
      $tributeContactCfId = CRM_Pcpteams_Utils::getPcpTypeContactCustomFieldId();
      $selectedReason     = CRM_Core_OptionGroup::getValue(CRM_Pcpteams_Constant::C_PCP_TYPE, $this->_SelectedReason, 'name');
      $tributeContatparams= array(
        'version'   => 3,
        'entity_id' => $this->_pcpId,
        "custom_{$tributeCfId}" => $selectedReason,
        "custom_{$tributeContactCfId}" => $tributeId,
      );
      $result = civicrm_api3('CustomValue', 'create', $tributeContatparams);
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
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
}
