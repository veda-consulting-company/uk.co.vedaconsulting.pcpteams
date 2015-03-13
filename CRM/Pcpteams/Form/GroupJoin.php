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
    parent::preProcess();  
  }
  
  function buildQuickForm() {

    // add form elements
    //FIXME
    //name filter in contact ref should be branch contact or corporate partner contact
    $this->addEntityRef('pcp_branch_contact', ts('Select Branch'), array('api' => array('params' => array('contact_type' => 'Organization')), 'create' => TRUE), TRUE);
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
