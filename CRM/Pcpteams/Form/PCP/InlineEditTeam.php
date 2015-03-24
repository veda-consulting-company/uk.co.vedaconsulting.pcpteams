<?php

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_PCP_InlineEditTeam extends CRM_Core_Form {

  function preProcess() {
    parent::preProcess();
    $workflowTeam       = CRM_Utils_Request::retrieve('op', 'Positive');
    $page_id            = CRM_Utils_Request::retrieve('id', 'Positive');
    $component_page_id  = CRM_Utils_Request::retrieve('pageId', 'Positive');
    
    $this->set('component_page_id', $component_page_id);
    $this->set('page_id', $page_id);
    
    if ($workflowTeam) {
      $this->_reactToFile = $this->getTeamReactFile($workflowTeam);
    } 

    $className = 'CRM_Pcpteams_Form_' . $this->_reactToFile;
    $className::preProcess($this);
    $this->assign('reactClass', $this->_reactToFile);
  }

  function setDefaultValues() {
    if($this->_reactToFile == 'TeamJoin'){
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
        return 'TeamInvite';
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
