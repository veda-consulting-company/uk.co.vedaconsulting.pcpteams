<?php

require_once 'CRM/Core/Page.php';

class CRM_Pcpteams_Page_Dashboard extends CRM_Core_Page {
  public $_contactId = NULL;

  /**
   * @throws Exception
   */
  function __construct() {
    parent::__construct();

    $this->_contactId = CRM_Utils_Request::retrieve('id', 'Positive', $this);
    $session = CRM_Core_Session::singleton();
    $userID = $session->get('userID');

    if (!$this->_contactId) {
      $this->_contactId = $userID;
    }
    elseif ($this->_contactId != $userID) {
      if (!CRM_Contact_BAO_Contact_Permission::allow($this->_contactId, CRM_Core_Permission::VIEW)) {
        CRM_Core_Error::fatal(ts('You do not have permission to view this contact'));
      }
      if (!CRM_Contact_BAO_Contact_Permission::allow($this->_contactId, CRM_Core_Permission::EDIT)) {
        $this->_edit = FALSE;
      }
    }
  }

  /*
     * Heart of the viewing process. The runner gets all the meta data for
     * the contact and calls the appropriate type of page to view.
     *
     * @return void
     * @access public
     *
     */
  function preProcess() {
    if (!$this->_contactId) {
      CRM_Core_Error::fatal(ts('You must be logged in to view this page.'));
    }

    list($displayName, $contactImage) = CRM_Contact_BAO_Contact::getDisplayAndImage($this->_contactId);

    $this->set('displayName', $displayName);
    $this->set('contactImage', $contactImage);

    CRM_Utils_System::setTitle(ts('Dashboard - %1', array(1 => $displayName)));

    $this->assign('recentlyViewed', FALSE);
  }

  /**
   * Function to build user dashboard
   *
   * @return void
   * @access public
   */
  function buildUserDashBoard() {
    //build component selectors
    $dashboardElements = array();
    $config = CRM_Core_Config::singleton();

    $this->_userOptions = CRM_Core_BAO_Setting::valueOptions(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
      'user_dashboard_options'
    );
    $this->assign('contactId', $this->_contactId);

    if (!empty($this->_userOptions['PCP'])) {
      $dashboardElements[] = array(
        'class' => 'crm-dashboard-pcp',
        'templatePath' => 'CRM/Pcpteams/Page/Dashboard/PcpUserDashboard.tpl',
        'sectionTitle' => ts('Personal Campaign Pages'),
        'weight' => 40,
      );
      list($pcpBlock, $pcpInfo) = CRM_PCP_BAO_PCP::getPcpDashboardInfo($this->_contactId);
      
      //check this user has team pcp
      $relatedContact = array(); 
      $teamPcpCfId = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
      foreach ($pcpInfo as $pcpDetails) {
        $result     = civicrm_api('pcpteams', 'get', array('version' => 3, 'pcp_id' => $pcpDetails['pcpId']));
        $teamPcpIds  = array();
        if(isset($result['values'][$pcpDetails['pcpId']]["custom_{$teamPcpCfId}"])){
          $teamPcpIds[]  = $result['values'][$pcpDetails['pcpId']]["custom_{$teamPcpCfId}"];
        }
      }
      
      if(!empty($teamPcpIds)){
        foreach ($teamPcpIds as $teamPcpId) {
          $result     = civicrm_api('pcpteams', 'get', array('version' => 3, 'pcp_id' => $teamPcpId));
          $pageURl    = CRM_Utils_System::url('civicrm/pcp/info', "reset=1&id={$teamPcpId}&component=event");
          $action     = <<<ACTION
            <span>
              <a title="URL for this Page" class="action-item crm-hover-button" href="{$pageURl}">URL for this Page</a>
            </span>
ACTION;
         
          $teamPcpInfo  = array(
            'pageTitle' => CRM_Pcpteams_Utils::getPcpEventTitle($teamPcpId),
            'pcpId'     => $teamPcpId,
            'pcpTitle'  => $result['values'][$teamPcpId]['title'],
            'pcpStatus' => CRM_Core_OptionGroup::getLabel( 'pcp_status', $result['values'][$teamPcpId]['status_id']),
            'class'     => 'disabled',
            'action'    => $action,
          );
          
          $teamId = CRM_Pcpteams_Utils::getcontactIdbyPcpId($teamPcpId);
          $phone  = CRM_Core_BAO_Phone::allPhones($teamId, FALSE, NULL, array('is_primary' => 1));
          $teamContact = array(
            'name'   => CRM_Contact_BAO_Contact::displayName($teamId),
            'email'  => CRM_Contact_BAO_Contact::getPrimaryEmail($teamId),
            'phone'  => !empty($phone) ? $phone['phone'] : NULL,
          );
          array_push($relatedContact, $teamContact);
          array_push($pcpInfo, $teamPcpInfo);
        }
      }
      // $this->assign('pcpBlock', $pcpBlock);
      $this->assign('pcpInfo', $pcpInfo);
      
      //Contacts / Organization
      $dashboardElements[] = array(
        'class' => 'crm-dashboard-permissionedOrgs',
        'templatePath' => 'CRM/Pcpteams/Page/Dashboard/RelatedContact.tpl',
        'sectionTitle' => ts('Your Contacts / Organizations'),
        'weight' => 40,
      );

      $this->assign('relatedContact', $relatedContact);
    }
    
    // usort($dashboardElements, array('CRM_Utils_Sort', 'cmpFunc'));
    $this->assign('dashboardElements', $dashboardElements);

  }
  
  /**
   * perform actions and display for user dashboard
   *
   * @return void
   *
   * @access public
   */
  function run() {
    $this->preProcess();
    $this->buildUserDashBoard();
    return parent::run();
  }

}
