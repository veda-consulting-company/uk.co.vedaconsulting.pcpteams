<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_TeamInvite {

  function preProcess(&$form) {
    $teamPcpId = $form->get('tpId');
    if (empty($teamPcpId)) {
      CRM_Core_Error::fatal(ts('Unable to Find Team Record for this URL. Please check the Team is active...'));
    }

    if (!$form->get('page_id')) {
      CRM_Core_Error::fatal(ts("Can't determine pcp id."));
    }

    $teamContactID  = CRM_Pcpteams_Utils::getcontactIdbyPcpId($teamPcpId);
    $teamName       = CRM_Contact_BAO_Contact::displayName($teamContactID);
    $eventTitle     = CRM_Pcpteams_Utils::getPcpEventTitle($teamPcpId);
    $teamAdminContactID = CRM_Pcpteams_Utils::getTeamAdmin($teamPcpId);
    
    $teamAdminDisplayName   = "Team Captain Not Found";
    if($teamAdminContactID) {
      $teamAdminDisplayName =  CRM_Contact_BAO_Contact::displayName($teamAdminContactID);
    }
    $form->assign('teamTitle', $teamName );
    $form->set('teamName', $teamName);
    $form->assign('teamAdminDisplayName', $teamAdminDisplayName);
    $form->assign('eventTitle', $eventTitle );
    
  }
  
  function buildQuickForm(&$form) {
    $form->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Yes, This is the team'),
        'isDefault' => TRUE,
      ),
    ));
    $form->assign('elementNames', $form->getRenderableElementNames());
  }

  function postProcess(&$form) {
    $values = $form->exportValues();

    // lets forget session var if any
    if (CRM_Core_Session::singleton()->get('pcpteams_tpid')) {
      CRM_Core_Session::singleton()->set('pcpteams_tpid', NULL);
    }

    $teampcpId        = $form->get('tpId');
    $teamId           = CRM_Pcpteams_Utils::getcontactIdbyPcpId($teampcpId);
    $userId           = CRM_Pcpteams_Utils::getloggedInUserId();
    // Create Team Member of relation to this Team
    $cfpcpab = CRM_Pcpteams_Utils::getPcpABCustomFieldId();
    $cfpcpba = CRM_Pcpteams_Utils::getPcpBACustomFieldId();
    $customParams = array(
      "custom_{$cfpcpab}" => $form->get('page_id'),
      "custom_{$cfpcpba}" => $teampcpId
    );
    $result = CRM_Pcpteams_Utils::createTeamRelationship($userId, $teamId, $customParams);
    $form->_teamName  = CRM_Contact_BAO_Contact::displayName($teamId);
    $form->set('teamName', $form->_teamName);
    $form->set('teamContactID', $teamId);
    // Team Join: create activity
    $actParams = array(
      'target_contact_id' => CRM_Pcpteams_Utils::getTeamAdmin($teampcpId),
      'assignee_contact_id' => $teamId
    );
    CRM_Pcpteams_Utils::createPcpActivity($actParams, CRM_Pcpteams_Constant::C_AT_INVITATION_ACCEPTED);
    CRM_Pcpteams_Utils::createPcpActivity($actParams, CRM_Pcpteams_Constant::C_AT_REQ_MADE);
    
     //send email once the team request has done. 
    $teamAdminId    = CRM_Pcpteams_Utils::getTeamAdmin($teampcpId);
    list($teamAdminName, $teamAdminEmail)  = CRM_Contact_BAO_Contact::getContactDetails($teamAdminId);
    $contactDetails = civicrm_api('Contact', 'get', array('version' => 3, 'sequential' => 1, 'id' => $userId));

    $emailParams =  array(
      'tplParams' => array(
        'teamAdminName' => $teamAdminName,
        'userFirstName' => $contactDetails['values'][0]['first_name'],
        'userlastName'  => $contactDetails['values'][0]['last_name'],
        'teamName'      => $form->_teamName,
        'pageURL'       => CRM_Utils_System::url('civicrm/pcp/manage', "reset=1&id={$teampcpId}", TRUE, NULL, FALSE, TRUE),
      ),
      'email' => array(
        $teamAdminName => array(
          'first_name'    => $teamAdminName,
          'last_name'     => $teamAdminName,
          'email-Primary' => $teamAdminEmail,
          'display_name'  => $teamAdminName,
        )
      ),
      'valueName'        => CRM_Pcpteams_Constant::C_MSG_TPL_JOIN_REQUEST,
      // 'email_from' => $fromEmail,
    );
    
    $sendEmail = CRM_Pcpteams_Utils::sendMail($userId, $emailParams);

    if ($result) {
      CRM_Core_Session::setStatus(ts("A notification has been sent to the team. Once approved, team should be visible on your page."), ts("Team Request Sent"));
    }
  }
}
