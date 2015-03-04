<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 */
class CRM_Pcpteams_Form_Reason extends CRM_Core_Form {
  function preProcess() {
    $this->_PcpId = CRM_Utils_Request::retrieve('id', 'Positive');

    //Fixme: validate the contact id, and check permission can view / edit this pcp.

    //Fixme: check the Pcp Custom Set values, In memory / In celebration

    parent::preProcess();
  }

  function buildQuickForm() {

    $this->addEntityRef('pcp_contact_id', ts('Search Contact'), array('create' => TRUE), TRUE);

    // InMemory - Deceased date 
    $this->addDate('deceased_date', ts('Deceased date'), FALSE, array('formatType' => 'birth'));

    // InCelebration - Event type 
    $event_type = CRM_Core_OptionGroup::values('event_type', FALSE);

    $this->add("select", "event_type", ts('Event Type'), $event_type);

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  function postProcess() {
    $values = $this->exportValues();
    //Fixme:
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
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
}
