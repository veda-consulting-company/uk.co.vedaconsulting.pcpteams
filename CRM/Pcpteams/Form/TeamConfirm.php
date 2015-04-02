<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Pcpteams_Form_TeamConfirm extends CRM_Core_Form {
  function preProcess(){
    $workflow = $this->get('workflowTeam');
    $teamTitle= $this->get('teamName');
    
    $this->assign('teamTitle', $teamTitle);
    
    $teamTitle = 'Team - '.$teamTitle;
    CRM_Utils_System::setTitle($teamTitle);
    $this->assign('workflow', $workflow);
    
    $this->_contactID = CRM_Pcpteams_Utils::getloggedInUserId();
    if (!$this->get('page_id')) {
      CRM_Core_Error::fatal(ts("Can't determine pcp id."));
    }
  }
  
  function setDefaultValues() {
    $defaults = array();
    list($fromName, $fromEmail) = CRM_Contact_BAO_Contact::getContactDetails($this->_contactID);
    $defaults['from_name'] = $fromName;
    $defaults['from_email'] = $fromEmail;
    $this->setDefaults($defaults);
    return $defaults;
  }
  
  function buildQuickForm() {
    // Details of User
    $name = &$this->add('text',
      'from_name',
      ts('From'),
      CRM_Core_DAO::getAttribute('CRM_Contact_DAO_Contact', 'first_name')
    );
    $name->freeze();

    $email = &$this->add('text',
      'from_email',
      ts('Your Email'),
      CRM_Core_DAO::getAttribute('CRM_Core_DAO_Email', 'email'),
      TRUE
    );
    $email->freeze();
    // $this->addWysiwyg('suggested_message', ts('Your Message'), CRM_Core_DAO::getAttribute('CRM_Friend_DAO_Friend', 'suggested_message'));
    $friend    = array();
    $mailLimit = CRM_Pcpteams_Constant::C_INVITE_MAIL_LIMIT;
   
    $this->assign('mailLimit', $mailLimit + 1);
    for ($i = 1; $i <= $mailLimit; $i++) {
      $this->add('text', "friend[$i][first_name]", ts("Friend's First Name"));
      $this->add('text', "friend[$i][last_name]", ts("Friend's Last Name"));
      $this->add('text', "friend[$i][email]", ts("Friend's Email"));
      $this->addRule("friend[$i][email]", ts('The format of this email address is not valid.'), 'email');
    }
    $this->addFormRule(array('CRM_Pcpteams_Form_TeamConfirm', 'formRule'));
    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Continue'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
  }
  
  static function formRule($fields) {
    $errors = array();
    $valid = FALSE;
    foreach ($fields['friend'] as $key => $val) {
      if (trim($val['email'])) {
        $valid = TRUE;
        if (!trim($val['first_name'])) {
          $errors["friend[{$key}][first_name]"] = ts('Please enter your friend\'s first name.');
        }
        if (!trim($val['last_name'])) {
          $errors["friend[{$key}][last_name]"] = ts('Please enter your friend\'s last name.');
        }
       
      }
    }
   
    return empty($errors) ? TRUE : $errors;
  }

  function postProcess() {
    //return TRUE;
    $values = $this->controller->exportValues($this->_name); 
    // Find the msg_tpl ID of sample invite template
    $result = civicrm_api3('MessageTemplate', 'get', array( 'sequential' => 1, 'version'=> 3, 'msg_title' => "Sample Team Invite Template",));
    $teampcpId = CRM_Pcpteams_Utils::getPcpIdByContactAndEvent($this->get('component_page_id'), $this->get('teamContactID'));

    if(!civicrm_error($result) && $result['id'] && !empty($values)) {
      $contactDisplayName = CRM_Contact_BAO_Contact::displayName($this->_contactID);
      // Create Team Invite activity
      $actParams = array(
        'source_contact_id'  =>  $this->_contactID,
        'assignee_contact_id'=>  $this->get('teamContactID'),
      );
      $activity = CRM_Pcpteams_Utils::createPcpActivity($actParams, CRM_Pcpteams_Constant::C_AT_TEAM_INVITE);
      // Send Invitation emails
      CRM_Pcpteams_Utils::sendInviteEmail($result['id'], $this->_contactID, $values, $teampcpId, $activity['id']);
    }
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
