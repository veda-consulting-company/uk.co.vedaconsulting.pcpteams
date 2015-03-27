<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_PCP_InlineProfilePic extends CRM_Core_Form {
  function prepProcess(){
    $this->_pcpId             = CRM_Utils_Request::retrieve('id', 'Positive');
    $this->component_page_id  = CRM_Utils_Request::retrieve('pageId', 'Positive');
    parent::preProcess();
  }
  
  function buildQuickForm() {
    $this->_pcpId = CRM_Utils_Request::retrieve('id', 'Positive', CRM_Core_DAO::$_nullArray, TRUE, NULL, 'GET');
    $this->_fileId = CRM_Utils_Request::retrieve('fileid', 'Positive', CRM_Core_DAO::$_nullArray, TRUE, NULL, 'GET');
    if ($this->_fileId) {
       $imageUrl = CRM_Utils_System::url('civicrm/file',"reset=1&id={$this->_fileId}&eid={$this->_pcpId}");
       $this->assign('defaultImageUrl', $imageUrl);
    }
    // add form elements
    $this->addElement('file', 'image_URL', ts('Browse/Upload Image'), 'size=30 maxlength=60');
    $this->addUploadElement('image_URL');
    $this->addButtons(array(
      array(
        'type' => 'upload',
        'name' => ts('Upload'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  function postProcess() {
    $params = $this->controller->exportValues($this->_name);
    $config = CRM_Core_Config::singleton();
    $customDir = $config->customFileUploadDir;
    $uri = str_replace($customDir, '', $params['image_URL']['name']);

    $apiParams = array(
      'version' => 3,
      'mime_type' => $params['image_URL']['type'],
      'uri' => $uri,
      'upload_date' => date('Y-m-d H:m:s'),
    );
    if($this->_fileId){
      $apiParams['id'] = $this->_fileId;
    }
    $file = civicrm_api3('File', 'create', $apiParams);
    if($file['id'] && $this->_pcpId){
      $sql = "
       Insert Into civicrm_entity_file ( entity_table, entity_id, file_id )
       Values( %1, %2, %3 )
     ";
      $sqlParams = array(
        1 => array('civicrm_pcp', 'String'),
        2 => array( $this->_pcpId, 'Integer'),
        3 => array( $file['id'], 'Integer'),
      );
      CRM_Core_DAO::executeQuery($sql, $sqlParams);
    }
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
