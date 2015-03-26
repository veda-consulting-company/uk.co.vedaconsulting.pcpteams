<?php

require_once 'CRM/Core/Page.php';

class CRM_Pcpteams_Form_PCP_Manage extends CRM_Core_Form {
  
  function preProcess(){
    CRM_Core_Resources::singleton()
      ->addStyleFile('uk.co.vedaconsulting.pcpteams', 'css/manage.css');

    $session = CRM_Core_Session::singleton();
    $this->_userID = $session->get('userID');
    if (!$this->_userID) {
      CRM_Core_Error::fatal(ts('You must be logged in to view this page.'));
    } 
    else {
      $pcpId = CRM_Utils_Request::retrieve('id', 'Positive', CRM_Core_DAO::$_nullArray, TRUE); 
      if(!CRM_Pcpteams_Utils::hasPermission($pcpId, $this->_userID, CRM_Core_Permission::VIEW)) {
        CRM_Core_Error::fatal(ts('You do not have permission to view this Page'));
      }
    }
  }

  function buildQuickForm() {
    //get params from URL
    $state = NULL;
    $pcpId = CRM_Utils_Request::retrieve('id', 'Positive', CRM_Core_DAO::$_nullArray, TRUE); 
    $state = CRM_Utils_Request::retrieve('state', 'String');
    $pcpContactId = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $pcpId, 'contact_id');
    
    $aContactTypes   = CRM_Contact_BAO_Contact::getContactTypes( $pcpContactId );
    if (in_array('Team', $aContactTypes) && self::checkTeamAdmin($pcpContactId,  $this->_userID)) {
      $pcpContactId = $this->_userID ;
    }
    
    if ($this->_userID != $pcpContactId) {
      CRM_Core_Error::fatal(ts('You do not have permission to view this Page'));
    }
    
    //Image URL
    $getPcpImgURl   = self::getPcpImageURl($pcpId);
    if(!empty($getPcpImgURl)) {
      $this->assign('profilePicUrl', $getPcpImgURl);
    }
    
    //Pcp Details
    $pcpDetails  = self::getPcpDetails($pcpId);
    $this->assign('pcpinfo', $pcpDetails);
    
    //check User is Team Admin
    if(!isset($pcpDetails['contact_id'])){
      $pcpDetails['contact_id']   = CRM_Pcpteams_Utils::getcontactIdbyPcpId($pcpId);
    }
    $isTeamAdmin = FALSE;
    if(isset($pcpDetails['team_pcp_id'])){
      $teamPcpcontactId = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $pcpDetails['team_pcp_id'], 'contact_id');
      $isTeamAdmin      = self::checkTeamAdmin($teamPcpcontactId,  $this->_userID);
    }
    $this->assign('isTeamAdmin', $isTeamAdmin);
    
    $aContactTypes   = CRM_Contact_BAO_Contact::getContactTypes( $pcpContactId );
    if (in_array('Team', $aContactTypes) && self::checkTeamAdmin($pcpContactId,  $this->_userID)) {
      $pcpContactId = $this->_userID ;
    }
    
    if (!$pcpDetails['contact_id']) {
      CRM_Core_Error::fatal(ts('Unable to Find Contact Record for this PCP. Please check the pcp id is valid...'));
    }
    
    //Fundraising Rank    
    //pcpId and Event (page) Id is required Field
    $aRankResult = civicrm_api('pcpteams', 'getRank', array(
      'version' => 3
      , 'sequential'  => 1
      , 'pcp_id'      => $pcpId
      , 'page_id'     => $pcpDetails['page_id']
      )
    );
    $this->assign('rankInfo', $aRankResult['values'][0]);

    //Top Donations    
    //pcpId and Event (page) Id is required Field
    $aDonationResult = civicrm_api('pcpteams', 'getAllDonations', array(
      'version' => 3
      , 'sequential'  => 1
      , 'pcp_id'      => $pcpId
      , 'page_id'     => $pcpDetails['page_id']
      , 'limit'       => 10
      )
    );
    $this->assign('donationInfo', $aDonationResult['values']);
    $no_donations = empty($aDonationResult['values']) ? TRUE : FALSE;
    $this->assign('no_donations', $no_donations);
    
    // Team Info, If exists
    $teamPcpInfo    = CRM_Core_DAO::$_nullArray;
    $teamProfilePic = NULL;
    if (isset($pcpDetails['team_pcp_id']) && !empty($pcpDetails['team_pcp_id'])) {
      $teamPcpInfo    = self::getPcpDetails($pcpDetails['team_pcp_id']);
      $teamProfilePic = self::getPcpImageURl($pcpDetails['team_pcp_id']);;
    }
    $this->assign('teamPcpInfo', $teamPcpInfo);
    $this->assign('teamProfilePic', $teamProfilePic);
      
    // check the contact Type
    $aContactTypes   = CRM_Contact_BAO_Contact::getContactTypes( $pcpDetails['contact_id'] );
    $isIndividualPcp = in_array('Individual', $aContactTypes) ? TRUE : FALSE;
    $isTeamPcp       = in_array('Team'      , $aContactTypes) ? TRUE : FALSE;
    $this->assign('isa_team_page', $isTeamPcp);
    if($isTeamPcp){
      $aTopTeamDonations = civicrm_api('pcpteams', 'getTopDonations', array(
        'version' => 3
        , 'sequential'  => 1
        , 'pcp_id'      => $pcpId
        , 'page_id'     => $pcpDetails['page_id']
        , 'limit'       => 3
        )
      );
      $this->assign('topTeamDonationInfo', $aTopTeamDonations['values']);
    }
    
    //team member info
    $teamMemberInfo = civicrm_api( 'pcpteams', 'getTeamMembersInfo', array(
        'version'  => 3, 
        'pcp_id'   => $pcpId,
      )
    );
    $this->assign('teamMemberInfo', isset($teamMemberInfo['values']) ? $teamMemberInfo['values'] : NULL);
      
    //set Page title
    if( $isIndividualPcp ){
      $state = 'Individual';
      $pageTitle = "My Personal Campaign Page : ". $pcpDetails['title'];
    }    
    
    if( $isTeamPcp ){
      $state     = 'Team';
      $pageTitle = "Team Campaign Page : ". $pcpDetails['title'];
    }    
    
    CRM_Utils_System::setTitle($pageTitle);
    
    //logged in User
    $userId         = CRM_Pcpteams_Utils::getloggedInUserId();
    
    //check the user can edit the profile image (boolean)
    $canEditProfile = CRM_Pcpteams_Utils::canEditProfileImage( $pcpId, $pcpDetails['contact_id'] );
    
    //Pcp layout button and URLs
    $joinTeamURl    = CRM_Utils_System::url('civicrm/pcp/inline/edit'     , "reset=1&id={$pcpId}&pageId={$pcpDetails['page_id']}&op=2&snippet=json");
    $createTeamURl  = CRM_Utils_System::url('civicrm/pcp/inline/edit'     , "reset=1&id={$pcpId}&pageId={$pcpDetails['page_id']}&op=1&snippet=json");
    $updateProfPic  = CRM_Utils_System::url('civicrm/pcp/inline/profile'  , "reset=1&id={$pcpId}&pageId={$pcpDetails['page_id']}&snippet=json");
    if($isTeamPcp){
      $inviteTeamURl= CRM_Utils_System::url('civicrm/pcp/inline/edit'     , "reset=1&id={$pcpId}&pageId={$pcpDetails['page_id']}&op=invite&snippet=json");
      $this->assign('inviteTeamURl' , $inviteTeamURl);
    }

    //assign values to tpl
    $this->assign('pcpId'         , $pcpId);
    $this->assign('createTeamUrl' , $createTeamURl);
    $this->assign('joinTeamUrl'   , $joinTeamURl);
    $this->assign('updateProfPic' , $updateProfPic);

    $honor = CRM_PCP_BAO_PCP::honorRoll($pcpId);
    $this->assign('honor', $honor);
    if(empty($state)){
      //FIXME : get the state name from api
      $state = 'Individual';
    }
    $this->assign('path', ucwords($state));
  }

  static function getPcpDetails($pcpId){
    if(empty($pcpId)){
      return NULL;
    }
    $result = civicrm_api('Pcpteams', 
        'get', 
        array(
          'pcp_id'     => $pcpId,
          'version'    => 3,
          'sequential' => 1,
        )
    );
    if(civicrm_error($result)){
      return NULL;
    }
    return $result['values'][0];
  }
  
  static function getPcpImageURl($pcpId){
    if(empty($pcpId)){
      return NULL;
    }
    
    $entityFile  = CRM_Core_BAO_File::getEntityFile('civicrm_pcp', $pcpId);
    if($entityFile){
      $fileInfo = reset($entityFile);
      $fileId   = $fileInfo['fileID'];
      $imageUrl = CRM_Utils_System::url('civicrm/file',"reset=1&id=$fileId&eid={$pcpId}"); 
      return $imageUrl;
    }
    $config = CRM_Core_Config::singleton();
    return CRM_Pcpteams_Constant::C_DEFAULT_PROFILE_PIC;
  }
  
  static function checkTeamAdmin($pcpContactId, $userId){
    if(empty($pcpContactId) || empty($userId)){
      return FALSE;  
    }
    $checkAdminParam= array(
      'version'           => 3
      , 'team_contact_id' => $pcpContactId
      , 'user_id'         => $userId
    );
    $checkTeamAdmin = civicrm_api('pcpteams', 'checkTeamAdmin', $checkAdminParam);
    return $checkTeamAdmin['is_team_admin'];
  }
}
