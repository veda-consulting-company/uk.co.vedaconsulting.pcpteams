<?php

require_once 'CRM/Core/Form.php';

/**
 * Workflow base class
 * Civi 4.5
 * Extends Core Form Controller.
 */
class CRM_Pcpteams_Form_Workflow extends CRM_Core_Form {

  function preProcess() {
    if (!CRM_Utils_Array::value('pageId', $_GET)) {
      if ($this->get('pcpComponent') == 'event' && $this->get('component_page_id')) {
        $eventTitle = CRM_Core_DAO::getFieldValue('CRM_Event_BAO_Event', $this->get('component_page_id'), 'title');
        CRM_Utils_System::setTitle($eventTitle);
      }
      // already initialized
      return TRUE;
    }
    $session          = CRM_Core_Session::singleton();
    $config           = CRM_Core_Config::singleton();
    $this->_tpId      = CRM_Utils_Request::retrieve('tpId', 'Positive', $this);
    $this->_code      = CRM_Utils_Request::retrieve('code', 'String', $this);
    $this->_pageId    = CRM_Utils_Request::retrieve('pageId', 'Positive', $this);

    if (!$session->get('userID')) {
      $code  = $this->_code;
      $query = "?pageId={$this->_pageId}&component=event";
      if($this->_tpId){
        $query .= "&tpId={$this->_tpId}";
        $code   = "cpftn";
      }
      if($code) {
        $query .= "&code={$code}";
      }
      // FIXME: only valid for drupal
      $url  = CRM_Utils_System::url('user', 'destination=civicrm/pcp/support');
      $url .= urlencode($query);
      CRM_Utils_System::redirect($url);
    }

    $this->_action    = CRM_Utils_Request::retrieve('action', 'String', $this, FALSE);
    $this->_component = CRM_Utils_Request::retrieve('component', 'String', $this);
    $this->_id        = CRM_Utils_Request::retrieve('id', 'Positive', $this);

    if (!$this->_pageId && $config->userFramework == 'Joomla' && $config->userFrameworkFrontend) {
      $this->_pageId = $this->_id;
    }

    $this->_contactID = isset($contactID) ? $contactID : $session->get('userID');
    if (!$this->_pageId) {
      if (!$this->_id) {
        $msg = ts('We can\'t load the requested web page due to an incomplete link. This can be caused by using your browser\'s Back button or by using an incomplete or invalid link.');
        CRM_Core_Error::fatal($msg);
      }
      else {
        $this->_pageId = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $this->_id, 'page_id');
      }
    }

    if (!$this->_pageId) {
      CRM_Core_Error::fatal(ts('Could not find source page id.'));
    }
    if (!$this->_component) {
      $this->_component = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $this->_id, 'page_type');
    }

    $this->_single = $this->get('single');

    if (!$this->_single) {
      $this->_single = $session->get('singleForm');
    }

    $this->set('action', $this->_action);
    $this->set('page_id', $this->_id);
    $this->set('component_page_id', $this->_pageId);
    $this->set('pcpComponent', $this->_component);
    if ($this->_tpId) {
      // users returning after event registration may no longer have tpId in
      // controller session, and therefore we need to set it in php session
      $session->set('pcpteams_tpid', $this->_tpId);
    }
    if ($this->get('pcpComponent') == 'event' && $this->get('component_page_id')) {
      $eventTitle = CRM_Core_DAO::getFieldValue('CRM_Event_BAO_Event', $this->get('component_page_id'), 'title');
      CRM_Utils_System::setTitle($eventTitle);
    }

    // we do not want to display recently viewed items, so turn off
    $this->assign('displayRecent', FALSE);

    $this->assign('pcpComponent', $this->_component);

    $session->pushUserContext(CRM_Utils_System::url('civicrm/pcp/manage', 'id='.$this->get('page_id')));
  }
}
