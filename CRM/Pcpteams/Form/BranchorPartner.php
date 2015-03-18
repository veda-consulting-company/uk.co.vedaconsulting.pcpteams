<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_BranchorPartner extends CRM_Core_Form {
  function preProcess(){
    if (!$form->get('page_id')) {
      CRM_Core_Error::fatal(ts("Can't determine pcp id."));
    }
  }

  function buildQuickForm() {
    // add form elements
    $this->addEntityRef('pcp_team_contact', ts('Branch or Corporate Patner'), array('api' => array('params' => array('contact_type' => 'Organization')), 'create' => TRUE), TRUE);
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
  }

  function postProcess() {
    $values = $this->exportValues();
    $branchOrPartnerID = $values['pcp_team_contact'];
    
    $cfId = CRM_Pcpteams_Utils::getBranchorPartnerCustomFieldId();
    $params = array(
      'version' => 3,
      'entity_id' => $form->get('page_id'),
      "custom_{$cfId}" => $branchOrPartnerID
    );
    civicrm_api3('CustomValue', 'create', $params);
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
