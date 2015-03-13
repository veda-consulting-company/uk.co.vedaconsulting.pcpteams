<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_TeamThankYou extends CRM_Core_Form {
  function preProcess(){
    $this->assign('teamTitle', $this->get('teamName'));
    CRM_Utils_System::setTitle(ts('Team setup succesful'));

    $this->_pcpId = $this->controller->get('pcpId');
    //$this->_pcpId = CRM_Utils_Request::retrieve('id', 'Positive');
    $userId = CRM_Pcpteams_Utils::getloggedInUserId();
    if (!$this->_pcpId) {
     $this->_pcpId =  CRM_Pcpteams_Utils::getPcpIdByUserId($userId);
    }
    parent::preProcess();
  }
  function buildQuickForm() {

      $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  function postProcess() {
    return TRUE;
    parent::postProcess();
  }
  
  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
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
