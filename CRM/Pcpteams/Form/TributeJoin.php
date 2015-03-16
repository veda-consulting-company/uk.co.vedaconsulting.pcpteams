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
    
    $this->_pcpId = $this->controller->get('pcpId');
    $selectedValue = $this->get('workflowTribute');
    if( $selectedValue == 2){
      $this->_tributeReason   = CRM_Pcpteams_Constant::C_CF_IN_CELEBRATION;
      $this->_contactSubType  = CRM_Pcpteams_Constant::C_CONTACTTYPE_IN_CELEB; 
    }else{
      $this->_tributeReason   = CRM_Pcpteams_Constant::C_CF_IN_MEMORY;
      $this->_contactSubType  = CRM_Pcpteams_Constant::C_CONTACTTYPE_IN_MEM;
    }

    $this->assign('tributeReason', $this->_tributeReason);
    $this->assign('tributeContact', $this->_contactSubType);
    parent::preProcess();  
  }
  
  function setDefaultValues() {
    $defaults = array();
    if ($this->_pcpId) {
      $result = civicrm_api('Pcpteams', 'get', array('version' => 3, 'sequential' => 1, 'pcp_id' => $this->_pcpId));
      $tributeCCfId = CRM_Pcpteams_Utils::getPcpTypeContactCustomFieldId();
      if(isset($result['values'][0]["custom_{$tributeCCfId}"])){
        $defaults['pcp_tribute_contact'] = $result['values'][0]["custom_{$tributeCCfId}_id"];
        $defaultValues = array(
          'id' => $result['values'][0]["custom_{$tributeCCfId}_id"],
          'label' => CRM_Contact_BAO_Contact::displayName( $result['values'][0]["custom_{$tributeCCfId}_id"] ),
        );
        $this->assign('defaultValues', json_encode($defaultValues));
      }
    }
    return $defaults;
  }
  
  function buildQuickForm() {

    // add form elements
    $this->add('text', 'pcp_tribute_contact', ts('Select '.$this->_tributeReason), array('size' => '40'), TRUE);
    
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

    
    if ($tributeId && $this->_tributeReason) {
      $tributeCfId        = CRM_Pcpteams_Utils::getPcpTypeCustomFieldId();
      $tributeContactCfId = CRM_Pcpteams_Utils::getPcpTypeContactCustomFieldId();
      $selectedReason     = CRM_Core_OptionGroup::getValue(CRM_Pcpteams_Constant::C_PCP_TYPE, $this->_tributeReason, 'name');
      $tributeContatparams= array(
        'version'   => 3,
        'entity_id' => $this->_pcpId,
        "custom_{$tributeCfId}" => $selectedReason,
        "custom_{$tributeContactCfId}" => $tributeId,
      );
      $result = civicrm_api3('CustomValue', 'create', $tributeContatparams);
      if(!civicrm_error($result)){
        $tributeName = CRM_Contact_BAO_Contact::displayName($tributeId);
        CRM_Core_Session::setStatus(ts("Successfully added to {$this->_tributeReason} of {$tributeName}"), '', 'success');
      }      
    } 
    //FIXME: need to discuss with DS, to redirect the after completed the form entries
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/pcp/page', 'reset=1&id='.$this->_pcpId));
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
