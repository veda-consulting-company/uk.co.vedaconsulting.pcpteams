<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_TeamInvite {

  function preProcess(&$form) {
    $form->_pcpId = CRM_Utils_Request::retrieve('tpId', 'Positive', $form, TRUE); 
    if (empty($form->_pcpId)) {
      CRM_Core_Error::fatal(ts('Unable to Find Team Record for this URL. Please check the Team is active...'));
    }
    CRM_Utils_System::setTitle(ts('Invited to join a team'));
  }
  
  function setDefaultValues(&$form) {
    $teamContactID = CRM_Pcpteams_Utils::getcontactIdbyPcpId($form->_pcpId);
    $teamPcpResult = civicrm_api('Pcpteams', 
        'get', 
        array(
          'pcp_id'     => $form->_pcpId,
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
    $teamAdminRelationshipTypeID  = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE, 'id', 'name_a_b');
    $relationshipResult = civicrm_api3('Relationship', 'get', array(
      'sequential' => 1,
      'relationship_type_id' => $teamAdminRelationshipTypeID,
      'contact_id_b' => $teamContactID,
      ));
    $teamAdminDisplayName = "Team Captain Not Found";
    if(!civicrm_error($relationshipResult) && $relationshipResult['values']) {
      $teamAdminContactID = $relationshipResult['values'][0]['contact_id_a'];
      $teamAdminDisplayName =  CRM_Contact_BAO_Contact::displayName($teamAdminContactID);
    }
    $defaults = array();
    $defaults['description'] = $teamTitle."<br/><br/>Captain - ".$teamAdminDisplayName."<br/> <br/>Event - ".$eventTitle."<br/><br/>";
    return $defaults;
  }

  function buildQuickForm(&$form) {
    $el = $form->add('textarea', 'description', ts('') , '');
    $el->freeze();
    $teamOptions = array();
    $teamOptions = array(
        ts(' Yes, this is the team'),
        ts(' No, I would like to join another team'),
        ts(' I do not want to join any team')
      );
    $form->addRadio('teamOption', '', $teamOptions, NULL, '<br/><br/>');

    $form->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Next'),
        'isDefault' => TRUE,
      ),
    ));
    $form->assign('elementNames', $form->getRenderableElementNames());
  }

  function postProcess(&$form) {
    $values = $form->exportValues();
  }
}
