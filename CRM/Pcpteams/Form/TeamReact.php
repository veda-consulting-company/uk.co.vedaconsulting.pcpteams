<?php

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_TeamReact extends CRM_Core_Form {
  function preProcess() {
    $workflowTeam   = $this->get("workflowTeam");
    if ($workflowTeam) {
      $this->_reactToFile = $this->getTeamReactFile($workflowTeam);
    } else {
      $option = CRM_Utils_Request::retrieve('option', 'String', CRM_Core_DAO::$_nullObject);
      $this->set("workflowTeam", $option);
      $this->_reactToFile = $this->getTeamReactFile($option);
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
