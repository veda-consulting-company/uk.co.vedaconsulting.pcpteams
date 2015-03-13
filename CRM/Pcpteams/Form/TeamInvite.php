<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_TeamInvite extends CRM_Core_Form {

  function preProcess() {
    $this->_pcpId = CRM_Utils_Request::retrieve('id', 'Positive', CRM_Core_DAO::$_nullArray, TRUE); 
    if (empty($this->_pcpId)) {
      CRM_Core_Error::fatal(ts('Unable to Find Team Record for this URL. Please check the Team is active...'));
    }
    CRM_Utils_System::setTitle(ts('Invited to join a team'));
  }
  
  function setDefaultValues() {
    $teamContactID   = CRM_Pcpteams_Utils::getcontactIdbyPcpId($this->_pcpId);
    $teamPcpResult = civicrm_api('Pcpteams', 
        'get', 
        array(
          'pcp_id'     => $this->_pcpId,
          'version'    => 3,
          'sequential' => 1,
        )
    );
    if(!civicrm_error($teamPcpResult)){
      $teamTitle    = $teamPcpResult['values'][0]['title'];
      $teamPageID   = $teamPcpResult['values'][0]['page_id'];
      $teamPageType = $teamPcpResult['values'][0]['page_type'];
      if($teamPageType == 'event' && !empty($teamPageID)) {
        $eventDetails   = CRM_Pcpteams_Utils::getEventDetailsbyEventId( $teamPageID);
        $eventTitle     = $eventDetails['title'];
      }
    }
    $relationshipTypeResult = civicrm_api3('RelationshipType', 'get', array(
      'sequential' => 1,
      'name_a_b' => "Team Admin of",
    ));
    
    if(!civicrm_error($relationshipTypeResult)) {
      $teamAdminRelationshipTypeID  = $relationshipTypeResult['id'];
      $relationshipResult = civicrm_api3('Relationship', 'get', array(
        'sequential' => 1,
        'relationship_type_id' => $teamAdminRelationshipTypeID,
        'contact_id_b' => $teamContactID,
        ));
      $teamAdminDisplayName = "";
      if(!civicrm_error($relationshipResult) && $relationshipResult['values']) {
        $teamAdminContactID = $relationshipResult['values'][0]['contact_id_a'];
        $contactResult  = civicrm_api3('Contact', 'get', array('sequential' => 1, 'id' => $teamAdminContactID,));
        if(!civicrm_error($contactResult)) {
          $teamAdminDisplayName  = $contactResult['values'][0]['display_name'] ;
        }
      }
    }
    if(empty($teamAdminDisplayName)){
      $teamAdminDisplayName = "Team Captain Not Found";
    }
    $defaults = array();
    $defaults['description'] = $teamTitle."<br/><br/>Captain - ".$teamAdminDisplayName."<br/> <br/>Event - ".$eventTitle."London Bikeathon<br/><br/>";
    return $defaults;
  }

  function buildQuickForm() {
    $el = $this->add('textarea', 'description', ts('') , ''
    );
    $el->freeze();
    $teamOptions = array();
    $teamOptions = array(
        ts(' Yes, this is the team'),
        ts(' No, I would like to join another team'),
        ts(' I do not want to join any team')
      );
      $this->addRadio('teamOption', '', $teamOptions, NULL, '<br/><br/>');

    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Next'),
        'isDefault' => TRUE,
      ),
    ));
    $this->assign('elementNames', $this->getRenderableElementNames());
  }

  function postProcess() {
    return TRUE;
    $values = $this->exportValues();
  }

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
