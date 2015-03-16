<?php

/**
 * Search Pcp Team Class
 * Civi 4.5
 * Extends Core Form Controller.
 */
class CRM_Pcpteams_Form_TeamJoin {

  function preProcess(&$form){
    CRM_Utils_System::setTitle(ts('Join a Team'));

    $form->_pcpId = $form->controller->get('pcpId');
    //$form->_pcpId = CRM_Utils_Request::retrieve('id', 'Positive');
    $userId = CRM_Pcpteams_Utils::getloggedInUserId();
    if (!$form->_pcpId) {
     $form->_pcpId =  CRM_Pcpteams_Utils::getPcpIdByUserId($userId);
    }
  }

  function buildQuickForm(&$form) {
    // add form elements
    // $form->addEntityRef('pcp_team_contact', ts('Team name'), array('api' => array('params' => array('contact_type' => 'Organization', 'contact_sub_type' => 'Team')), 'create' => TRUE), TRUE);
    $form->add('text', 'pcp_team_contact', ts('Team Name'), array('size' => '40'), TRUE);
    $form->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Save'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $form->assign('elementNames', $form->getRenderableElementNames());
  }

  function postProcess(&$form) {
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
        'entity_id' => $form->_pcpId,
        "custom_{$teamPcpCfId}" => $teamPcpId,
      );
      $result = civicrm_api3('CustomValue', 'create', $params);
       // Get team contact ID
      $teamContactID    = CRM_Pcpteams_Utils::getcontactIdbyPcpId($teamPcpId);
      $form->_teamName  = CRM_Contact_BAO_Contact::displayName($teamContactID);
      $form->set('teamName', $form->_teamName);
    } else {
      // FIXME: this check should be at validation step / form-rule
      CRM_Core_Error::fatal("The team (ID: $teamId) doesn't have a pcp.");
    }
  }

}
