<?php

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_TeamNew {

  static function preProcess(&$form) {
    if (!$form->get('page_id')) {
      CRM_Core_Error::fatal(ts("Can't determine pcp id."));
    }
    
    //Find the component_page_id from URL
    if(!$form->get('component_page_id')){
      $componentPageId = CRM_Utils_Request::retrieve('pageId', 'Positive', CRM_Core_DAO::$_nullArray, TRUE);
      $form->set('component_page_id', $componentPageId);
    }
    
    //If not found by URL then find component Page id from DB using PCP id
    if(!$form->get('component_page_id')){
      $componentPageId = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $form->get('page_id'), 'page_id');
      $form->set('component_page_id', $componentPageId);
    }
  }

  static function buildQuickForm(&$form) {
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
    $groupURL = CRM_Utils_System::url('civicrm/pcp/support', "code=cpfgq&qfKey={$form->controller->_key}");
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

  static function postProcess(&$form) {
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
    $teamPcpId = CRM_Pcpteams_Utils::createDefaultPcp($createTeam['id'], $form->get('component_page_id'));

    // Create/Update custom record with team pcp id and create relationship with user as Team Admin
    if($teamPcpId) {
      $userId = CRM_Pcpteams_Utils::getloggedInUserId();
      CRM_Pcpteams_Utils::createTeamRelationship($userId, $createTeam['id'], $custom = array(), 'create');
        $params = array(
          'version'   => 3,
          'entity_id' => $form->get('page_id'),
          "team_pcp_id" =>$teamPcpId,
        );
      $result = civicrm_api3('pcpteams', 'customcreate', $params);
      $form->set('teamName', $orgName);
      $form->set('teamContactID', $createTeam['id']);
      $actParams = array(
        'target_contact_id' => $createTeam['id']
      );        
      CRM_Pcpteams_Utils::createPcpActivity($actParams, CRM_Pcpteams_Constant::C_AT_TEAM_CREATE);
      CRM_Core_Session::setStatus(ts("Your Team %1 has been created, you can invite members from your team page.", array(1 => $orgName)), ts('New Team Created'));
    }
    else{
      CRM_Core_Session::setStatus(ts("Failed to Create Team \"{$orgName}\" ..."));
    }
  }
}
