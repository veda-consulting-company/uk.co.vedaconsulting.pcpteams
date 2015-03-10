<?php

require_once 'CRM/Core/Page.php';

class CRM_Pcpteams_Page_PCP extends CRM_Core_Page {
  
  static function getPcpIdbyContactId( $pcpContactId ){
    if(empty($pcpContactId)){
      return NULL;
    }
    $result = civicrm_api('Pcpteams', 
        'getcontactpcp', 
        array(
          'contact_id' => $pcpContactId,
          'version'    => 3,
        )
    );
    
    if (!empty($result['id'])) {
      return $result['id'];
    }
    
    return NULL;
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
    return NULL;
  }
  
  function run() {
    //get params from URL
    $state = NULL;
    $pcpId = CRM_Utils_Request::retrieve('id', 'Positive'); //make required
    $state = CRM_Utils_Request::retrieve('state', 'String');
    
    //FIXME : this condition once the user logged in 
    $userId= CRM_Pcpteams_Utils::getloggedInUserId();
    if (!$pcpId && !empty($userId)) {
      $pcpId = self::getPcpIdbyContactId($userId);
    }
    
    //FATAL ERROR : if pcp id not found.
    if (!$pcpId) {
      CRM_Core_Error::fatal(ts('Couldn\'t determine any PCP'));
    }
    
    
    //Image URL
    $getPcpImgURl   = self::getPcpImageURl($pcpId);
    if(!empty($getPcpImgURl)) {
      $this->assign('profilePicUrl', $getPcpImgURl);
    }
    
    //get contact Id by pcp Id
    $pcpDetails  = self::getPcpDetails($pcpId);
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
      CRM_Utils_System::setTitle( ts('My Personal Campaign Page') );
    }    
    //End Individual
    
    // contact Type Team
    if( $isTeamPcp ){
      $state     = 'Team';
      $pageTitle = "Team Campaign Page : ". CRM_Contact_BAO_Contact::displayName( $pcpDetails['contact_id'] );
      CRM_Utils_System::setTitle($pageTitle);
    }    
    //End Team
    
    
    //Pcp layout button and URLs
    $joinTeamURl    = CRM_Utils_System::url('civicrm/pcp/team', 'reset=1&id='.$pcpId);
    $createTeamURl  = CRM_Utils_System::url('civicrm/pcp/team/create', 'reset=1&id='.$pcpId);
    $profilePicURl  = CRM_Utils_System::url('civicrm/pcp/profile', 'reset=1&id='.$pcpId);
    $branchURl      = CRM_Utils_System::url('civicrm/pcp/branchorpartner', 'reset=1&id='.$pcpId);
    
    //assign values to tpl
    $this->assign('pcpId', $pcpId);
    $this->assign('createTeamUrl', $createTeamURl);
    $this->assign('joinTeamUrl', $joinTeamURl);
    $this->assign('profilePicURl', $profilePicURl);
    $this->assign('branchURl', $branchURl);
    
    if(empty($state)){
      //FIXME : get the state name from api
      $state = 'Individual';
    }
    $this->assign('path', ucwords($state));
    parent::run();
  }
}
