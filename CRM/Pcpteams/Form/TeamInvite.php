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
    // Get team contact ID
    $teamContactID    = CRM_Pcpteams_Utils::getcontactIdbyPcpId($form->_pcpId);
    $form->_teamName  = CRM_Contact_BAO_Contact::displayName($teamContactID);
    // Get Event Title
    $eventTitle       = CRM_Pcpteams_Utils::getPcpEventTitle($form->_pcpId);
    // Get Team Admin Contact ID
    $teamAdminContactID = CRM_Pcpteams_Utils::getTeamAdmin($form->_pcpId);
    
    $teamAdminDisplayName   = "Team Captain Not Found";
    if($teamAdminContactID) {
      $teamAdminDisplayName =  CRM_Contact_BAO_Contact::displayName($teamAdminContactID);
    }
    $form->assign('teamTitle', $form->_teamName );
    $form->assign('teamAdminDisplayName', $teamAdminDisplayName);
    $form->assign('eventTitle', $eventTitle );
    
  }
  
  function buildQuickForm(&$form) {
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
    if($values['teamOption'] == 0) {
      $form->set('teamName', $form->_teamName);
    }
  }
}
