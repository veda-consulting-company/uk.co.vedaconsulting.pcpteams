<?php

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_Inline_TeamEdit extends CRM_Core_Form {

  function preProcess() {
    parent::preProcess();
    $workflowTeam       = CRM_Utils_Request::retrieve('op', 'String');
    $page_id            = CRM_Utils_Request::retrieve('id', 'Positive');
    $component_page_id  = CRM_Utils_Request::retrieve('pageId', 'Positive');
    $snippet            = CRM_Utils_Request::retrieve('snippet', 'String');
    
    $this->set('component_page_id', $component_page_id);
    $this->set('page_id', $page_id);
    
    if ($workflowTeam) {
      $this->_reactToFile = $this->getTeamReactFile($workflowTeam);
    }
    
    if($workflowTeam == 'invite'){
      $this->_contactID = CRM_Pcpteams_Utils::getloggedInUserId();
      //team contactName
      $teamContactId = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $page_id, 'contact_id');
      $teamName = CRM_Contact_BAO_Contact::displayName($teamContactId);
      $this->set('teamName', $teamName);
    } 

    $className = 'CRM_Pcpteams_Form_' . $this->_reactToFile;
    $className::preProcess($this);
    $this->assign('reactClass', $this->_reactToFile);
    $this->assign('snippet', $snippet);
  }

  function setDefaultValues() {
    if($this->_reactToFile == 'TeamJoin' || $this->_reactToFile == 'TeamConfirm'){
      $className = 'CRM_Pcpteams_Form_' . $this->_reactToFile;
      $className::setDefaultValues($this);
    }
  }
  
  function buildQuickForm() {
    $className = 'CRM_Pcpteams_Form_' . $this->_reactToFile;
    $className::buildQuickForm($this);
  }  
  

  function postProcess() {
    $className = 'CRM_Pcpteams_Form_' . $this->_reactToFile;
    $className::postProcess($this);
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
  
  function getTeamReactFile($workflowTeam){
   switch ($workflowTeam) {
      case 'invite':
        return 'TeamConfirm';
        break;      
      case '1':
        return 'TeamNew';
        break;
      
      default:
        return 'TeamJoin';
        break;
    } 
  }  
}
