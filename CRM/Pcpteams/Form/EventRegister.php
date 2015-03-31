<?php

class CRM_Pcpteams_Form_EventRegister extends CRM_Pcpteams_Form_Workflow {

  function preProcess() {
    parent::preProcess();
    CRM_Utils_System::setTitle(ts('Join Event'));
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/event/register', 
      "reset=1&id=" . $this->controller->get('component_page_id')));
    CRM_Core_Error::debug_var('$this->controller->get(component_page_id)', $this->controller->get('component_page_id'));
  }
}
