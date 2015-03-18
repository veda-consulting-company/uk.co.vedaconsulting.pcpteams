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

    // get PCP-ID for team 
    if ($teamId) {
      $result = civicrm_api('Pcpteams', 
        'getcontactpcp', 
        array(
          'contact_id' => $teamId,
          'version'    => 3
        )
      );
      if (!empty($result['id'])) {
        $teamPcpId = $result['id'];
      }
    }

    if ($teamPcpId) {
      $teamPcpCfId = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
      $params = array(
        'version'   => 3,
        'entity_id' => $form->get('page_id'),
        "custom_{$teamPcpCfId}" => $teamPcpId,
      );
      $result = civicrm_api3('CustomValue', 'create', $params);
       // Get team contact ID
      $teamContactID    = CRM_Pcpteams_Utils::getcontactIdbyPcpId($teamPcpId);
      $userId           = CRM_Pcpteams_Utils::getloggedInUserId();
      // Create Team Member of relation to this Team
      CRM_Pcpteams_Utils::checkORCreateTeamRelationship($userId, $teamContactID, TRUE);
      $form->_teamName  = CRM_Contact_BAO_Contact::displayName($teamContactID);
      $form->set('teamName', $form->_teamName);
      // Team Join: create activity
      CRM_Pcpteams_Utils::createPcpActivity(array($userId,$teamContactID), CRM_Pcpteams_Constant::C_CF_TEAM_JOIN, 'Joined to team'.$form->_teamName, 'PCP Team Join');
    } else {
      // FIXME: this check should be at validation step / form-rule
      CRM_Core_Error::fatal("The team (ID: $teamId) doesn't have a pcp.");
    }
  }

}
