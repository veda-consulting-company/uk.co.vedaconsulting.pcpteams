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
    return $config->extensionsURL.'/'.CRM_Pcpteams_Constant::C_DEFAULT_PROFILE_PIC;
  }
  
  /**
   * To get all Params needed to Display the Team Pcp
   */
  static function getTeamPcpParams($pcpDetails){
    $return = array();
    if (empty($pcpDetails['id'])) {
      return $return;
    }
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
    
    //get contact Id by pcp Id
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
    
    // check the contact Type
    $aContactTypes   = CRM_Contact_BAO_Contact::getContactTypes( $pcpDetails['contact_id'] );
    $isIndividualPcp = in_array('Individual', $aContactTypes) ? TRUE : FALSE;
    $isTeamPcp       = in_array('Team'      , $aContactTypes) ? TRUE : FALSE;
    
    // contact Type Individual
    if( $isIndividualPcp ){
      $state = 'Individual';
      $pageTitle = "My Personal Campaign Page : ". $pcpDetails['title'];
      // $tplParams = self::getIndividualPcpParams($pcpDetails);
    }    
    //End Individual
    
    // contact Type Team
    if( $isTeamPcp ){
      $state     = 'Team';
      $pageTitle = "Team Campaign Page : ". $pcpDetails['title'];
      $tplParams = self::getTeamPcpParams($pcpDetails);
    }    
    //End Team
    
    //set Page title
    CRM_Utils_System::setTitle($pageTitle);
    
    //logged in User
    $userId         = CRM_Pcpteams_Utils::getloggedInUserId();
    
    //check the user can edit the profile image (boolean)
    $canEditProfile = CRM_Pcpteams_Utils::canEditProfileImage( $pcpId, $pcpDetails['contact_id'] );
    
    //EventTitle 
    $tplParams['event_title'] = NULL;
    if($pcpDetails['page_type'] == 'event') {
      $eventDetails   = CRM_Pcpteams_Utils::getEventDetailsbyEventId( $pcpDetails['page_id']);
      $eventTitleLink = CRM_Utils_System::url('civicrm/event/info', "reset=1&id={$eventDetails['id']}");
      $tplParams['event_title'] = $eventDetails['title'];
    } 
    
    //fundraiser Name 
    $fundraiserName = CRM_Contact_BAO_Contact::displayName($pcpDetails['contact_id']);
    
    //title of the page
    $tplParams['fundraiser']    = $fundraiserName;
    $tplParams['title_of_page'] = $fundraiserName ." does <a href={$eventTitleLink}>".$tplParams['event_title']."</a>";
    
    //totaliser
    $targetAmount = CRM_Utils_Money::format($pcpDetails['goal_amount'], $pcpDetails['currency']);
    //FIXME : calculating the soft credits are all contribution for this page.
    $amountRaised = CRM_PCP_BAO_PCP::thermoMeter($pcpId);
    $amountRaised = CRM_Utils_Money::format($amountRaised, $pcpDetails['currency']);
    $tplParams['totaliser']     = "Target Amount : ".$targetAmount." Amount Raised : ".$amountRaised;
    $tplParams['target_amount'] = $targetAmount;
    $tplParams['amount_raised'] = $amountRaised;
    
    //donate to URL 
    // $tplParams['donate_to_url'] = CRM_Utils_System::url('civicrm/contribute/transact', "reset=1&id={$tplParams['target_entity_id']}&pcpId={$pcpId}");
    
    //Biography
    
    //Fundraising Rank    
    $eventPcps = CRM_Pcpteams_Utils::getEventPcps($pcpDetails['page_id']);
    $tplParams['rankHolder']    = $eventPcps['rankHolder'];
    $tplParams['eventPcpCount'] = $eventPcps['pcp_count'];
    
    //Pcp layout button and URLs
    $joinTeamURl    = CRM_Utils_System::url('civicrm/pcp/support', 'reset=1&id='.$pcpId . '&code=cpftn');
    $createTeamURl  = CRM_Utils_System::url('civicrm/pcp/support', 'reset=1&id='.$pcpId);
    $updateProfPic  = CRM_Utils_System::url('civicrm/pcp/profile', 'reset=1&id='.$pcpId);
    $branchURl      = CRM_Utils_System::url('civicrm/pcp/branchorpartner', 'reset=1&id='.$pcpId);
    
    //assign values to tpl
    $this->assign('pcpId', $pcpId);
    $this->assign('createTeamUrl', $createTeamURl);
    $this->assign('joinTeamUrl', $joinTeamURl);
    $this->assign('updateProfPic', $canEditProfile ? $updateProfPic : NULL);
    $this->assign('branchURl', $branchURl);
    $this->assign('tplParams', $tplParams);
    $honor = CRM_PCP_BAO_PCP::honorRoll($pcpId);
    $this->assign('honor', $honor);
    if(empty($state)){
      //FIXME : get the state name from api
      $state = 'Individual';
    }
    $this->assign('path', ucwords($state));
  }
}
