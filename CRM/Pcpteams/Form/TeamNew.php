<?php

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_TeamNew {

  function preProcess(&$form) {
    CRM_Utils_System::setTitle(ts('Team Name'));
    $form->_pcpId   = $form->controller->get('pcpId');
    $form->_pageId  = $form->controller->get('component_page_id');
    $userId = CRM_Pcpteams_Utils::getloggedInUserId();
    if (!$form->_pcpId) {
     $form->_pcpId =  CRM_Pcpteams_Utils::getPcpIdByUserId($userId);
    }
  }

  function buildQuickForm(&$form) {
    // add form elements
    $form->add('text', 'organization_name', ts('Team Name'), array(), TRUE);
    $form->add('text', 'email-primary', ts('Email'), CRM_Core_DAO::getAttribute('CRM_Core_DAO_Email', 'email'));

    $form->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Continue'),
        'isDefault' => TRUE,
      ),
    ));
    $groupURL = CRM_Utils_System::url('civicrm/pcp/page', 'reset=1');
    $form->assign('skipURL', $groupURL);

    // export form elements
    $form->assign('elementNames', $form->getRenderableElementNames());
    $form->addFormRule(array('CRM_Pcpteams_Form_TeamNew', 'formRule'));
  }

  static function formRule($params) {
    if (!empty($params['email-primary']) && !filter_var($params['email-primary'], FILTER_VALIDATE_EMAIL)) {
      $errors['email-primary'] = ts('Not Valid Email');
    }
    if(CRM_Pcpteams_Utils::checkTeamExists($params['organization_name'])) {
      $errors['organization_name'] = ts('Team Already Exists');
    }
    return empty($errors) ? TRUE : $errors;
  }

  function postProcess(&$form) {
    $dedupeParams = CRM_Dedupe_Finder::formatParams($dedupeParams, 'Individual');
    $dedupeParams['first_name'] = 'madav';
    $dedupeParams['email'] = 'madavatest@gmail.com';
    $dedupeParams['postal_code'] = 'KT5 5EH';
    $ruleGrpID = CRM_Core_DAO::getFieldValue('CRM_Dedupe_DAO_RuleGroup', 'Sports Import Dedupe Rule', 'id', 'title');
    $dedupeParams = CRM_Dedupe_Finder::formatParams($dedupeParams, 'Individual');
    $ids = CRM_Dedupe_Finder::dupesByParams($dedupeParams, 'Individual', 'Supervised', array(), $ruleGrpID);
    print_r($ids);
    die();
    $values   = $form->exportValues();
    $orgName  = $values['organization_name'];
    $email    = $values['email-primary'];
    $cSubType = CRM_Pcpteams_Constant::C_CONTACT_SUB_TYPE;

    $params   = array(
                'version'          => '1',
                'contact_type'     => 'Organization',
                'contact_sub_type' => $cSubType,
                'organization_name'=> $orgName,
                'api.Email.create' => $email,
                );
    $createTeam = civicrm_api3('Contact', 'create', $params);

    // Create Dummy Team PCP Page
    $teamPcpId = CRM_Pcpteams_Utils::createDummyPcp($createTeam['id'], $form->_pageId);
    // Create/Update custom record with team pcp id and create relationship with user as Team Admin
    if($teamPcpId) {
      $userId = CRM_Pcpteams_Utils::getloggedInUserId();
      CRM_Pcpteams_Utils::checkORCreateTeamRelationship($userId, $createTeam['id'], TRUE, 'create');
      $teamPcpCfId = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
        $params = array(
          'version'   => 3,
          'entity_id' => $form->_pcpId,
          "custom_{$teamPcpCfId}" =>$teamPcpId,
        );
      $result = civicrm_api3('CustomValue', 'create', $params);
      $form->set('teamName', $orgName);
      CRM_Pcpteams_Utils::createPcpActivity($userId, CRM_Pcpteams_Constant::C_CF_TEAM_CREATE, 'Team is created'.$orgName, 'New Team Creation');
    }
    else{
      CRM_Core_Session::setStatus(ts("Failed to Create Team \"{$orgName}\" ..."));
    }
  }
}
