<?php

/**
 * Search Pcp Team Class
 * Civi 4.5
 * Extends Core Form Controller.
 */
class CRM_Pcpteams_Form_TeamJoin {

  static function preProcess(&$form) {
    if (!$form->get('page_id')) {
      CRM_Core_Error::fatal(ts("Can't determine pcp id."));
    }
    $form->assign('component_page_id', $form->get('component_page_id'));
  }

  static function buildQuickForm(&$form) {
    // add form elements
    // $form->addEntityRef('pcp_team_contact', ts('Team name'), array('api' => array('params' => array('contact_type' => 'Organization', 'contact_sub_type' => 'Team')), 'create' => TRUE), TRUE);
    $form->add('text', 'pcp_team_contact', ts('Team Name'), array('size' => '40'), TRUE);
    $form->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Continue'),
        'isDefault' => TRUE,
      ),
    ));
    // export form elements
    $form->assign('elementNames', $form->getRenderableElementNames());
  }
  
  static function setDefaultValues(&$form) {
    $defaults = array();
    if ($form->get('page_id')) {
      $result = civicrm_api('Pcpteams', 'get', array('version' => 3, 'sequential' => 1, 'pcp_id' => $form->get('page_id')));
      // $teamCfId = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
      if(isset($result['values'][0]["team_pcp_id"])){
        $defaults['pcp_team_contact'] = CRM_Pcpteams_Utils::getcontactIdbyPcpId($result['values'][0]["team_pcp_id"]);
      }
    }
    $form->setDefaults($defaults);
  }
  
  static function postProcess(&$form) {
    $values = $form->exportValues();
    $teamId = $values['pcp_team_contact'];
    $teampcpId        = CRM_Pcpteams_Utils::getPcpIdByContactAndEvent($form->get('component_page_id'), $teamId);
    $userId           = CRM_Pcpteams_Utils::getloggedInUserId();
    // Create Team Member of relation to this Team
    $cfpcpab = CRM_Pcpteams_Utils::getPcpABCustomFieldId();
    $cfpcpba = CRM_Pcpteams_Utils::getPcpBACustomFieldId();
    $customParams = array(
      "custom_{$cfpcpab}" => $form->get('page_id'),
      "custom_{$cfpcpba}" => $teampcpId
    );
    CRM_Pcpteams_Utils::createTeamRelationship($userId, $teamId, $customParams);
    $form->_teamName  = CRM_Contact_BAO_Contact::displayName($teamId);
    $form->set('teamName', $form->_teamName);
    $form->set('teamContactID', $teamId);
    // Team Join: create activity
    $actParams = array(
      'target_contact_id' => $teamId
    );    
    CRM_Pcpteams_Utils::createPcpActivity($actParams, CRM_Pcpteams_Constant::C_AT_REQ_MADE);
    CRM_Core_Session::setStatus(ts('A notification has been sent to the team. Once approved, team should be visible on your page.'), ts('Team Request Sent'));
    
    //send email once the team request has done. 
    $teamAdminId    = CRM_Pcpteams_Utils::getTeamAdmin($teampcpId);
    list($teamAdminName, $teamAdminEmail)  = CRM_Contact_BAO_Contact::getContactDetails($teamAdminId);
    $contactDetails = civicrm_api('Contact', 'get', array('version' => 3, 'sequential' => 1, 'id' => $userId));
    $msgTplId       = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_MessageTemplate', CRM_Pcpteams_Constant::C_JOIN_REQUEST_MSG_TPL, 'id', 'msg_title'); 

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
      'messageTemplateID' => $msgTplId,
      // 'email_from' => $fromEmail,
    );
    
    $sendEmail = CRM_Pcpteams_Utils::sendMail($userId, $emailParams);
  }

}
