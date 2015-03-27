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
        CRM_Core_Error::fatal(ts('You do not have permission to view this Page.'));
      }
    }
  }

  function buildQuickForm() {
    //get params from URL
    $state = NULL;
    $pcpId = CRM_Utils_Request::retrieve('id', 'Positive', CRM_Core_DAO::$_nullArray, TRUE); 
    $state = CRM_Utils_Request::retrieve('state', 'String');
    $pcpContactId = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $pcpId, 'contact_id');
    
    $pcpDetails  = self::getPcpDetails($pcpId);
    $this->assign('pcpinfo', $pcpDetails);

    $teamPcpInfo = array();
    if (!empty($pcpDetails['team_pcp_id'])) {
      $teamPcpInfo = self::getPcpDetails($pcpDetails['team_pcp_id']);
    }
    $this->assign('teamPcpInfo', $teamPcpInfo);
    
    //Fundraising Rank    
    $aRankResult = civicrm_api('pcpteams', 'getRank', array(
      'version' => 3
      , 'sequential'  => 1
      , 'pcp_id'      => $pcpId
      , 'page_id'     => $pcpDetails['page_id']
      )
    );
    $this->assign('rankInfo', $aRankResult['values'][0]);

    //Top Donations    
    $aDonationResult = civicrm_api('pcpteams', 'getAllDonations', array(
      'version' => 3
      , 'sequential'  => 1
      , 'pcp_id'      => $pcpId
      , 'page_id'     => $pcpDetails['page_id']
      , 'limit'       => 10
      )
    );
    $this->assign('donationInfo', $aDonationResult['values']);
      
    //team member info
    $teamMemberInfo = civicrm_api( 'pcpteams', 'getTeamMembersInfo', array(
        'version'  => 3, 
        'pcp_id'   => $pcpId,
      )
    );
    $this->assign('teamMemberInfo', isset($teamMemberInfo['values']) ? $teamMemberInfo['values'] : NULL);
    
     //team member request info
    $teamMemberRequestInfo = civicrm_api( 'pcpteams', 'getTeamRequestInfo', array(
        'version'     => 3, 
        'team_pcp_id' => $pcpId,
      )
    );
    $this->assign('teamMemberRequestInfo', isset($teamMemberRequestInfo['values']) ? $teamMemberRequestInfo['values'] : NULL);

    //set Page title
    $pageTitle = "Individual Page : ". $pcpDetails['title'];
    if (!empty($pcpDetails['is_teampage'])) {
      $pageTitle = "Team Page : ". $pcpDetails['title'];
    }    
    CRM_Utils_System::setTitle($pageTitle);
    
    //Pcp layout button and URLs
    //DS FIXME: these urls should be built in tpl
    $joinTeamURl    = CRM_Utils_System::url('civicrm/pcp/inline/edit'     , "reset=1&id={$pcpId}&pageId={$pcpDetails['page_id']}&op=2&snippet=json");
    $createTeamURl  = CRM_Utils_System::url('civicrm/pcp/inline/edit'     , "reset=1&id={$pcpId}&pageId={$pcpDetails['page_id']}&op=1&snippet=json");
    $updateProfPic  = CRM_Utils_System::url('civicrm/pcp/inline/profile'  , "reset=1&id={$pcpId}&pageId={$pcpDetails['page_id']}&snippet=json");
    if($pcpDetails['is_teampage']){
      $inviteTeamURl= CRM_Utils_System::url('civicrm/pcp/inline/edit'     , "reset=1&id={$pcpId}&pageId={$pcpDetails['page_id']}&op=invite&snippet=json");
      $this->assign('inviteTeamURl' , $inviteTeamURl);
    }

    $this->assign('createTeamUrl' , $createTeamURl);
    $this->assign('joinTeamUrl'   , $joinTeamURl);
    $this->assign('updateProfPic' , $updateProfPic);
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
}
