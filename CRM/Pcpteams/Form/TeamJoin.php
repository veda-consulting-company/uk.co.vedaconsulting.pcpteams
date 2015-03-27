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
      $teamCfId = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
      if(isset($result['values'][0]["custom_{$teamCfId}"])){
        $defaults['pcp_team_contact'] = CRM_Pcpteams_Utils::getcontactIdbyPcpId($result['values'][0]["custom_{$teamCfId}"]);
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
    CRM_Pcpteams_Utils::checkORCreateTeamRelationship($userId, $teamId, $customParams, TRUE);
    $form->_teamName  = CRM_Contact_BAO_Contact::displayName($teamId);
    $form->set('teamName', $form->_teamName);
    $form->set('teamContactID', $teamId);
    // Team Join: create activity
    CRM_Pcpteams_Utils::createPcpActivity(array('source' => $userId, 'target' => $teamId), CRM_Pcpteams_Constant::C_CF_TEAM_JOIN, 'Joined to team'.$form->_teamName, 'PCP Team Join');
   
  }

}
