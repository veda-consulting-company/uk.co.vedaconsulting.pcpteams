<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_CreatePcpTeam extends CRM_Core_Form {
  CONST C_CONTACT_SUB_TYPE = 'Team';
  function preProcess() {
    //FIXME : get pcp Id and use Id to relate the team and pcp.
    parent::preProcess();
  }

  function buildQuickForm() {

    // add form elements
    $this->add('text', 'organization_name', ts('Organization Name'), array(), TRUE);
    $this->add('text', 'email-primary', ts('Email'), CRM_Core_DAO::getAttribute('CRM_Core_DAO_Email', 'email'));

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    $this->addFormRule(array('CRM_Pcpteams_Form_CreatePcpTeam', 'formRule'));
    parent::buildQuickForm();
  }

  static function formRule($params) {
    if (!empty($params['email-primary']) && !filter_var($params['email-primary'], FILTER_VALIDATE_EMAIL)) {
      $errors['email-primary'] = ts('Not Valid Email');
    }
    return empty($errors) ? TRUE : $errors;
  }

  function postProcess() {
    $values   = $this->exportValues();
    $orgName  = $values['organization_name'];
    $email    = $values['email-primary'];
    $cSubType = self::C_CONTACT_SUB_TYPE;

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
    }
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
}
