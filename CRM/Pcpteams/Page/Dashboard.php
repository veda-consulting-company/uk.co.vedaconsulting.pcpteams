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
    
    $phone          = CRM_Core_BAO_Phone::allPhones($contactId, FALSE, NULL, array('is_primary' => 1));
    $contactSubType = CRM_Contact_BAO_Contact::getContactSubType($contactId);
    $contactType    = CRM_Contact_BAO_ContactType::getLabel($contactSubType[0]);
    $gid            = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'PCP_Supporter_Profile', 'id', 'name');
    // $updateURL      = CRM_Utils_System::url('civicrm/profile/edit', "reset=1&gid=$gid&cid=$contactId");
    $return         = array(
      'name'  => CRM_Contact_BAO_Contact::displayName($contactId), 
      'type'  => $contactType, 
      'email' => CRM_Contact_BAO_Contact::getPrimaryEmail($contactId),
      'phone' => !empty($phone) && isset($phone['phone']) ? $phone['phone'] : NULL,
      // 'action'=> "<a href=$updateURL>Update Contact Information</a>",
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
      $dashboardElements[] = array(
        'class' => 'crm-dashboard-pcp',
        'templatePath' => 'CRM/Pcpteams/Page/Dashboard/List.tpl',
        'sectionTitle' => ts('Personal Campaign Pages'),
        'weight' => 40,
      );
      
      $result = civicrm_api( 'pcpteams', 'getPcpDashboardInfo', array(
          'version'   => 3, 
          'contact_id'=> $this->_contactId,
        )
      );
      
      $pcpInfo = $relatedContact = array();
      if(!civicrm_error($result)){
        $pcpInfo = $result['values'];
      }
      foreach ($pcpInfo as $pcpId => $pcpDetails) {
        $teamId     = $pcpDetails['teamPcpid']  ? CRM_Pcpteams_Utils::getcontactIdbyPcpId($pcpDetails['teamPcpid']) : NULL;
        $orgId      = $pcpDetails['org_id']     ? $pcpDetails['org_id']     : NULL;
        $tribute_id = $pcpDetails['tribute_id'] ? $pcpDetails['tribute_id'] : NULL;
        $relatedContact[$teamId]                   = self::relatedContactInfo($teamId);
        $relatedContact[$pcpDetails['org_id']]     = self::relatedContactInfo($orgId);
        $relatedContact[$pcpDetails['tribute_id']] = self::relatedContactInfo($tribute_id);
      }

      $this->assign('pcpInfo', $pcpInfo);
      
      //Contacts / Organization
      $dashboardElements[] = array(
        'class' => 'crm-dashboard-permissionedOrgs',
        'templatePath' => 'CRM/Pcpteams/Page/Dashboard/RelatedContact.tpl',
        'sectionTitle' => ts('Your Contacts / Organizations'),
        'weight' => 40,
      );

      $this->assign('relatedContact', array_filter($relatedContact));
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
