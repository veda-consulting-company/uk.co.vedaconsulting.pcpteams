<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_Team_New extends CRM_Core_Form {
  function preProcess() {
    CRM_Utils_System::setTitle(ts('Team Name'));
  }

  function buildQuickForm() {

    // add form elements
    $this->add('text', 'organization_name', ts('Organization Name'), array(), TRUE);
    $this->add('text', 'email-primary', ts('Email'), CRM_Core_DAO::getAttribute('CRM_Core_DAO_Email', 'email'));

    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    $this->addFormRule(array('CRM_Pcpteams_Form_Team_New', 'formRule'));
    parent::buildQuickForm();
  }

  static function formRule($params) {
    if (!empty($params['email-primary']) && !filter_var($params['email-primary'], FILTER_VALIDATE_EMAIL)) {
      $errors['email-primary'] = ts('Not Valid Email');
    }
    return empty($errors) ? TRUE : $errors;
  }

  function postProcess() {
    return TRUE; // remove me

    $values   = $this->exportValues();
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

    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    $elementNames = array();
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
}
