<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_TeamInvite {

  function preProcess(&$form) {
    CRM_Utils_System::setTitle(ts('Invited to join a team'));

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
        'name' => ts('Continue'),
        'isDefault' => TRUE,
      ),
    ));
    $form->assign('elementNames', $form->getRenderableElementNames());
  }

  function postProcess(&$form) {
    $values = $form->exportValues();
    if ($values['teamOption'] == 1) { // join team
      $this->set("workflowTeam", 2); // follow the flow as if teamQuery would have chosen join
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/pcp/support', "code=cpftn&qfKey={$this->controller->_key}"));
    }
    else if ($values['teamOption'] == 2) {
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/pcp/support', "code=cpfgq&qfKey={$this->controller->_key}"));
    }
    else if ($values['teamOption'] == 0) {
      if($form->_pcpId) {
        $teamPcpCfId = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
        $params = array(
          'version'   => 3,
          'entity_id' => $form->get('page_id'),
          "custom_{$teamPcpCfId}" => $form->get('tpId')
        );
        $result = civicrm_api3('CustomValue', 'create', $params);
      }
    }
  }
}
