<?php

require_once 'CRM/Core/Page.php';

class CRM_Pcpteams_Form_PCP_Manage extends CRM_Core_Form {
  
  function preProcess(){
    CRM_Core_Resources::singleton()
      ->addStyleFile('uk.co.vedaconsulting.pcpteams', 'css/manage.css');

    $session = CRM_Core_Session::singleton();
    $userID = $session->get('userID');
    if (!$userID) {
      CRM_Core_Error::fatal(ts('You must be logged in to view this page.'));
    }    
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
  
  
  function buildQuickForm() {
    //get params from URL
    $state = NULL;
    $pcpId = CRM_Utils_Request::retrieve('id', 'Positive', CRM_Core_DAO::$_nullArray, TRUE); 
    $state = CRM_Utils_Request::retrieve('state', 'String');
    
    //Image URL
    $getPcpImgURl   = self::getPcpImageURl($pcpId);
    if(!empty($getPcpImgURl)) {
      $this->assign('profilePicUrl', $getPcpImgURl);
    }
    
    //Pcp Details
    $pcpDetails  = self::getPcpDetails($pcpId);
    $amountRaised= CRM_PCP_BAO_PCP::thermoMeter($pcpId);
    if($amountRaised){
      $pcpDetails['amount_raised'] =  CRM_Utils_Money::format($amountRaised);
    }else{
      $pcpDetails['amount_raised'] =  CRM_Utils_Money::format('0.00');
    }
    $this->assign('pcpinfo', $pcpDetails);
    if(!isset($pcpDetails['contact_id'])){
      $pcpDetails['contact_id']   = CRM_Pcpteams_Utils::getcontactIdbyPcpId($pcpId);
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
    $updateProfPic  = CRM_Utils_System::url('civicrm/pcp/profile'         , 'reset=1&id='.$pcpId);

    //assign values to tpl
    $this->assign('pcpId'         , $pcpId);
    $this->assign('createTeamUrl' , $createTeamURl);
    $this->assign('joinTeamUrl'   , $joinTeamURl);
    $this->assign('updateProfPic' , $canEditProfile ? $updateProfPic : NULL);

    $honor = CRM_PCP_BAO_PCP::honorRoll($pcpId);
    $this->assign('honor', $honor);
    if(empty($state)){
      //FIXME : get the state name from api
      $state = 'Individual';
    }
    $this->assign('path', ucwords($state));
  }
}
