<?php

require_once 'CRM/Core/Form.php';

/**
 * Search Pcp Team Class
 * Civi 4.5
 * Extends Core Form Controller.
 */
class CRM_Pcpteams_Form_ProfilePic extends CRM_Core_Form {

  function preProcess(){
    parent::preProcess();
  }

  
  function buildQuickForm() {
    $contactID = CRM_Pcpteams_Utils::getloggedInUserId();
    $result = civicrm_api3('Contact', 'get', array(
        'sequential' => 1,
        'id' => $contactID,
        ));
    if($result['is_error'] == 0) {
      $profilePicUrl = $result['values'][0]['image_URL'];
    }
    if(!empty($profilePicUrl)) {
      $this->assign('profilePicUrl', $profilePicUrl);
    }
    
    $this->addElement('file', 'image_URL', ts('Browse/Upload Image'), 'size=30 maxlength=60');
    $this->addUploadElement('image_URL');

    // add form elements
    $buttons = array(
      array(
        'type' => 'upload',
        'name' => ts('Save'),
        'subName' => 'view',
        'isDefault' => TRUE,
      ),
    );
    
    $this->addButtons($buttons);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  function postProcess() {
    $params = $this->controller->exportValues($this->_name);
    $contactID = CRM_Pcpteams_Utils::getloggedInUserId();
    
    if (!empty($params['image_URL'])) {
      CRM_Contact_BAO_Contact::processImageParams($params);
      $contactParams  = array(
        'sequential' => 1,
        'id' => $contactID,
        'image_URL' => $params['image_URL'],);
      $result = civicrm_api3('Contact', 'create', $contactParams);
    }
    $urlParams = array(
      // 'id'  => 1, // could be pcpId
    );
    CRM_Pcpteams_Utils::pcpRedirectUrl('dashboard', $urlParams);

    //Fixme
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
