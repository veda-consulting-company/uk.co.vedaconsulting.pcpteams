<?php

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Pcpteams_Form_Setting extends CRM_Core_Form {

  /**
   * Function to actually build the form
   *
   * @return None
   * @access public
   */
  public function buildQuickForm() {
    
    // Add the Pcp Team list limit Element
    $this->addElement('text', 'team_list_limit', ts('Pcp Team list limit'));    
    
    // Add the SKIP TEAM APPROVAL Element    
    $this->add('checkbox', 'skip_team_approval', ts('Skip Team Approval'));
    
    //This is field list which used in set defaults values,
    //add form field element and amend field name into this array to set default values.
    $this->_settingFields = array(
      'team_list_limit'
      , 'skip_team_approval'
    );


    // Create the Submit Button.
    $buttons = array(
      array(
        'type' => 'submit',
        'name' => ts('Save Pcp Team Settings'),
      ),
    );

    // Add the Buttons.
    $this->addButtons($buttons);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());    
  }

  public function setDefaultValues() {
    $defaults = $details = array();

    //to set existing settings on load form
    $existingSettings = CRM_Pcpteams_Utils::getPcpTeamSettings();

    if (!empty($existingSettings)) {
      foreach ($existingSettings as $key => $value) {
        $defaults[$key] = $value;
      }
    }
    
    return $defaults;
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
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

  /**
   * Function to process the form
   *
   * @access public
   *
   * @return None
   */
  public function postProcess() {
    // Store the submitted values in an array.
    $params = $this->controller->exportValues($this->_name);    
    // var_dump($params);
    // die();
    if (!isset($params['skip_team_approval'])) {
      $params['skip_team_approval'] = FALSE;
    }

    $settings = $params;
    //unset default form variables;
    unset($settings['qfKey']);
    unset($settings['entryURL']);

    CRM_Pcpteams_Utils::setPcpTeamSettings($settings);

    //Set status message
    $message = ts("Pcp Team settings are updated successfully");
    CRM_Core_Session::setStatus($message, ts('PCP Team Settings'), 'success');
  }
}

  