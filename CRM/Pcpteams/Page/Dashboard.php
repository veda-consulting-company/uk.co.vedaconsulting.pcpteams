<?php

require_once 'CRM/Core/Page.php';

class CRM_Pcpteams_Page_Dashboard extends CRM_Core_Page {

  function run() {
    //get Pcp Id from URL
    $state = NULL;
    $pcpId = CRM_Utils_Request::retrieve('id', 'Positive');
    $state = CRM_Utils_Request::retrieve('state', 'String');
    $userId= $pcpContactId = CRM_Pcpteams_Utils::getloggedInUserId();
    
    $pageTitle = empty($state) ? 'My Dashboard' : ucwords($state)." Dashboard ";
    CRM_Utils_System::setTitle( $pageTitle );
    
    // check the user is a team admin
    $checkUserIsTeamAdmin = CRM_Pcpteams_Utils::checkUserIsaTeamAdmin($userId);
    if(!empty($checkUserIsTeamAdmin)){
      $pcpContactId = $checkUserIsTeamAdmin['id'];
      $state        = $checkUserIsTeamAdmin['state'];
    }
    
    if (!$pcpId) {
      $result = civicrm_api('Pcpteams', 
        'getcontactpcp', 
        array(
          'contact_id' => $pcpContactId,
          'version'    => 3
        )
      );
      if (!empty($result['id'])) {
        $pcpId = $result['id'];
      }
    }
    
    
    $result = civicrm_api3('Contact', 'get', array(
        'sequential' => 1,
        'id' => $userId,
        ));
    if($result['is_error'] == 0) {
      $profilePicUrl = $result['values'][0]['image_URL'];
    }
    if(!empty($profilePicUrl)) {
      $this->assign('profilePicUrl', $profilePicUrl);
    }
    
    if (!$pcpId) {
      CRM_Core_Error::fatal(ts('Couldn\'t determine any PCP'));
    }
    
    // check the user has group
    $checkUserHasGroup = CRM_Pcpteams_Utils::checkOrUpdateUserPcpGroup($pcpId, 'get');
    if(!empty($checkUserHasGroup)){
      $pcpGroupId = $checkUserIsTeamAdmin['id'];
      $state        = $checkUserIsTeamAdmin['state'];
    }
    
    //FIXME: Validate the contact has permission to view / edit the PCP details (check with api)

    $joinTeamURl    = CRM_Utils_System::url('civicrm/pcp/team', 'reset=1&id='.$pcpId);
    $createTeamURl  = CRM_Utils_System::url('civicrm/pcp/team/create', 'reset=1&id='.$pcpId);
    $profilePicURl  = CRM_Utils_System::url('civicrm/pcp/profile', 'reset=1&id='.$pcpId);

    $this->assign('pcpId', $pcpId);
    $this->assign('createTeamUrl', $createTeamURl);
    $this->assign('joinTeamUrl', $joinTeamURl);
    $this->assign('profilePicURl', $profilePicURl);
    
    if(empty($state)){
      //FIXME : get the state name from api
      $state = 'Team';
    }
    $this->assign('path', ucwords($state));
    parent::run();
  }
}
