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
    
    $session = CRM_Core_Session::singleton();
    $session->pushUserContext(CRM_Utils_System::url('civicrm/pcp/dashboard', 'reset=1'));
  }
  
  static function relatedContactInfo($contactId){
    $return = array();
    if(empty($contactId)){
      return $return;
    }
    
    $phone          = CRM_Core_BAO_Phone::allPhones($contactId, TRUE, NULL, array('is_primary' => 1));
    $contactSubType = CRM_Contact_BAO_Contact::getContactSubType($contactId);
    $contactType    = CRM_Contact_BAO_ContactType::getLabel($contactSubType[0]);
    $gid            = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'PCP_Supporter_Profile', 'id', 'name');
    $updateURL      = CRM_Utils_System::url('civicrm/profile/edit', "reset=1&gid=$gid&cid=$contactId");
    // $updateURL      =CRM_Utils_System::url('civicrm/pcp/support', "action=browse&component=event&id={$params['pcpId']}");
    $return         = array(
      'name'  => CRM_Contact_BAO_Contact::displayName($contactId), 
      'type'  => $contactType, 
      'email' => CRM_Contact_BAO_Contact::getPrimaryEmail($contactId),
      'phone' => !empty($phone) && isset($phone[1]['phone']) ? $phone[1]['phone'] : NULL,
      'action'=> "<a href=$updateURL>Update Contact Information</a>",
    );
    
    return $return;
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
      //My Personal campaign pages
      $dashboardElements[] = array(
        'class' => 'crm-dashboard-pcp',
        'templatePath' => 'CRM/Pcpteams/Page/Dashboard/Pages.tpl',
        'sectionTitle' => ts('My Personal Campaign Pages'),
        'weight' => 40,
      );
      
      $pcpInfo = civicrm_api( 'pcpteams', 'getPcpDashboardInfo', array(
          'version'   => 3, 
          'contact_id'=> $this->_contactId,
        )
      );
      $this->assign('pcpInfo', isset($pcpInfo['values']) ? $pcpInfo['values'] : NULL);
      
      //My Teams
      $dashboardElements[] = array(
        'class' => 'crm-dashboard-permissionedOrgs',
        'templatePath' => 'CRM/Pcpteams/Page/Dashboard/Teams.tpl',
        'sectionTitle' => ts('My Teams'),
        'weight' => 40,
      );
      $teamInfo = civicrm_api( 'pcpteams', 'getMyTeamInfo', array(
          'version'   => 3, 
          'contact_id'=> $this->_contactId,
        )
      );
      $this->assign('teamInfo', isset($teamInfo['values']) ? $teamInfo['values'] : NULL);
      
      //My Pending Team Requests
      $dashboardElements[] = array(
        'class' => 'crm-dashboard-permissionedteamreq',
        'templatePath' => 'CRM/Pcpteams/Page/Dashboard/TeamRequests.tpl',
        'sectionTitle' => ts('My Pending Team Requests'),
        'weight' => 41,
      );
      
      //New Team Member Requests
      $dashboardElements[] = array(
        'class' => 'crm-dashboard-permissionednewteamreq',
        'templatePath' => 'CRM/Pcpteams/Page/Dashboard/TeamMemberRequests.tpl',
        'sectionTitle' => ts('New Team Member Requests'),
        'weight' => 42,
      );
      
      //In Active Pages
      $dashboardElements[] = array(
        'class' => 'crm-dashboard-permissionedinactive',
        'templatePath' => 'CRM/Pcpteams/Page/Dashboard/PagesDisabled.tpl',
        'sectionTitle' => ts('In Active Pages'),
        'weight' => 43,
      );
      
      //Team Members
      $dashboardElements[] = array(
        'class' => 'crm-dashboard-permissionedteammembers',
        'templatePath' => 'CRM/Pcpteams/Page/Dashboard/TeamMembers.tpl',
        'sectionTitle' => ts('Team Members'),
        'weight' => 44,
      );

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
