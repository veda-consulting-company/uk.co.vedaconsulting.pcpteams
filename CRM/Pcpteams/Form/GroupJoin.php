<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_GroupJoin extends CRM_Core_Form {
  function preProcess(){
    CRM_Utils_System::setTitle(ts('Join Group'));
    
    $this->_pcpId = $this->controller->get('pcpId');
    $selectedValue = $this->get('workflowGroup');
    if( $selectedValue == 1){
      $this->_contactSubType  = CRM_Pcpteams_Constant::C_CONTACTTYPE_PARTNER; 
    }else{
      $this->_contactSubType  = CRM_Pcpteams_Constant::C_CONTACTTYPE_BRANCH;
    }
    $this->assign('branchOrPartner', str_replace('_', ' ', $this->_contactSubType));
    $this->assign('contactSubType',  $this->_contactSubType);
    parent::preProcess();  
  }
  
  function setDefaultValues() {
    $defaults = array();
    if ($this->_pcpId) {
      $result = civicrm_api('Pcpteams', 'get', array('version' => 3, 'sequential' => 1, 'pcp_id' => $this->_pcpId));
      $branchCfId = CRM_Pcpteams_Utils::getBranchorPartnerCustomFieldId();
      if(isset($result['values'][0]["custom_{$branchCfId}"])){
        $defaults['pcp_branch_contact'] = $result['values'][0]["custom_{$branchCfId}_id"];
        $defaultValues = array(
          'id' => $result['values'][0]["custom_{$branchCfId}_id"],
          'label' => CRM_Contact_BAO_Contact::displayName( $result['values'][0]["custom_{$branchCfId}_id"] ),
        );
        $this->assign('defaultValues', json_encode($defaultValues));
      }
    }
    return $defaults;
  }
  
  function buildQuickForm() {

    // add form elements
    $this->add('text', 'pcp_branch_contact', ts('Select '.$this->_contactSubType), array('size' => '40'), TRUE);
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
    $branchId = $values['pcp_branch_contact'];

    if ($branchId && $this->_pcpId) {
      $branchCfId = CRM_Pcpteams_Utils::getBranchorPartnerCustomFieldId();
      $params     = array(
        'version'   => 3,
        'entity_id' => $this->_pcpId,
        "custom_{$branchCfId}" => $branchId,
      );
      $result = civicrm_api3('CustomValue', 'create', $params);
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
