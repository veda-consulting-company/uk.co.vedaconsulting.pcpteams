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
  
  /**
   * To get all Params needed to Display the Individual Pcp
   */
  static function getIndividualPcpParams($pcpDetails){
    $return = array();
    if(empty(CRM_Utils_Array::value('id', $pcpDetails))){
      return $return;
    }
    
    //Step 1: Intially set the page state is New Page., ie., No Team, No In Memory and No Donations
    $return['page_state'] = 'new';
    
    //Step 2:check this Page has some Donations
    $pcpBlockDetails = civicrm_api('Pcpteams', 'getpcpblock', array('entity_id' => $pcpDetails['page_id'], 'version' => 3, 'sequential' => 1));
    if(!civicrm_error($pcpBlockDetails)){
      $targetEntityId = $pcpBlockDetails['values'][0]['target_entity_id'];
      $contriAPI      = CRM_Pcpteams_Utils::getContributionDetailsByContributionPageId($targetEntityId);
      if($contriAPI['count'] > 0){
        $return['page_state'] = 'donations';
      }
    }
    
    //Step 3:check this Page has Team pcp id ., (check in custom set)
    $teamPcpCfId = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
    $teamExist   = FALSE;
    if(isset($pcpDetails['custom_'.$teamPcpCfId])){
      $return['page_state'] = 'team';
      $teamExist = TRUE;
    }
    
    //Step 4:check this Page has tribute in Memory ( check in custom set)
    $pcpTypeCfId = CRM_Pcpteams_Utils::getPcpTypeCustomFieldId();
    $pcpTypeCf   = civicrm_api3('CustomField', 'getsingle', array('version' => 3, 'id' => $pcpTypeCfId));
    $ovInMem     = civicrm_api3('OptionValue', 'getsingle', array('version' => 3, 'option_group_id' => $pcpTypeCf['option_group_id'], 'name' => CRM_Pcpteams_Constant::C_CF_IN_MEMORY));
    $inMemExist  = FALSE;
    if(isset($pcpDetails['custom_'.$pcpTypeCfId]) && $pcpDetails['custom_'.$pcpTypeCfId] == $ovInMem['value']){
      $return['page_state'] = 'in_mem';
      $inMemExist  = TRUE;
    }
    
    //Step 5:check this Page has Team and tribute in Memory ( check in custom set )
    if($teamExist && $inMemExist){
      $return['page_state'] = 'both';
    }
    
    return $return;
  }
  
  /**
   * To get all Params needed to Display the Team Pcp
   */
  static function getTeamPcpParams($pcpDetails){
    $return = array();
    if(empty(CRM_Utils_Array::value('id', $pcpDetails))){
      return $return;
    }
  }
  
  
  function run() {
    //get params from URL
    $state = NULL;
    $store = array();
    $pcpId = CRM_Utils_Request::retrieve('id', 'Positive', $store, TRUE); 
    $state = CRM_Utils_Request::retrieve('state', 'String');
    
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
      $tplParams = self::getIndividualPcpParams($pcpDetails);
    }    
    //End Individual
    
    // contact Type Team
    if( $isTeamPcp ){
      $state     = 'Team';
      $pageTitle = "Team Campaign Page : ". CRM_Contact_BAO_Contact::displayName( $pcpDetails['contact_id'] );
      CRM_Utils_System::setTitle($pageTitle);
      $tplParams = self::getTeamPcpParams($pcpDetails);
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
    $honor = CRM_PCP_BAO_PCP::honorRoll($pcpId);
    $this->assign('honor', $honor);
    if(empty($state)){
      //FIXME : get the state name from api
      $state = 'Individual';
    }
    $this->assign('path', ucwords($state));
    parent::run();
  }
}
