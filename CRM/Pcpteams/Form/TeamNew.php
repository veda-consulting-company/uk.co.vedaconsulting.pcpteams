<?php

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_TeamNew {

  function preProcess(&$form) {
    CRM_Utils_System::setTitle(ts('Team Name'));
  }

  function buildQuickForm(&$form) {
    // add form elements
    $form->add('text', 'organization_name', ts('Team Name'), array(), TRUE);
    $form->add('text', 'email-primary', ts('Email'), CRM_Core_DAO::getAttribute('CRM_Core_DAO_Email', 'email'));

    $form->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Next'),
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
    return empty($errors) ? TRUE : $errors;
  }

  function postProcess(&$form) {
    return TRUE; // remove me

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

    //FIXME: relate the PCP and Created Team

    if(!civicrm_error($createTeam)){
      CRM_Core_Session::setStatus(ts("Team \"{$orgName}\" has Created"), '', 'success');
      $userId = CRM_Pcpteams_Utils::getloggedInUserId();
      CRM_Pcpteams_Utils::checkORCreateTeamRelationship($userId, $createTeam['id'], TRUE);
    }else{
      CRM_Core_Session::setStatus(ts("Failed to Create Team \"{$orgName}\" ..."));
    }
  }
}
