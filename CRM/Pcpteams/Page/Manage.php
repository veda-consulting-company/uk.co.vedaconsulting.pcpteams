<?php

class CRM_Pcpteams_Page_Manage extends CRM_Core_Page {
  
  function run() {
    $this->preProcess();
    $this->buildView();
    parent::run();
  }

  function preProcess(){
    CRM_Core_Resources::singleton()
      ->addScriptFile('civicrm', 'packages/jquery/plugins/jquery.jeditable.min.js', CRM_Core_Resources::DEFAULT_WEIGHT, 'html-header')
      ->addScriptFile('civicrm', 'js/jquery/jquery.crmEditable.js', CRM_Core_Resources::DEFAULT_WEIGHT + 10, 'html-header')
      ->addScriptFile('uk.co.vedaconsulting.pcpteams', 'packages/jquery-circle-progress/dist/circle-progress.js', CRM_Core_Resources::DEFAULT_WEIGHT + 20, 'html-header')
      ->addStyleFile('uk.co.vedaconsulting.pcpteams', 'css/manage.css', CRM_Core_Resources::DEFAULT_WEIGHT + 1000, 'html-header')
      ->addScriptFile('civicrm', 'bower_components/ckeditor/ckeditor.js', 0, 'page-header');
    $session = CRM_Core_Session::singleton();
    $this->_userID = $session->get('userID');
    if (!$this->_userID) {
      CRM_Core_Error::fatal(ts('You must be logged in to view this page.'));
    } 
    else {
      $pcpId = CRM_Utils_Request::retrieve('id', 'Positive', CRM_Core_DAO::$_nullArray, TRUE); 
      if (!CRM_Pcpteams_Utils::hasPermission($pcpId, $this->_userID, CRM_Core_Permission::VIEW)) {
        CRM_Core_Error::fatal(ts('You do not have permission to view this Page.'));
      }
    }
    //set user can edit or view page.
    $isEdit = CRM_Pcpteams_Utils::hasPermission($pcpId, $this->_userID, CRM_Core_Permission::EDIT);
    $isMember = CRM_Pcpteams_Utils::hasPermission($pcpId, $this->_userID, CRM_Pcpteams_Constant::C_PERMISSION_MEMBER);
    $this->assign("is_edit_page", $isEdit);
    $this->_isEditPermission = $isEdit;
    $this->assign("is_member", $isMember);
    $this->assign('userId', $this->_userID);
  }

  function buildView() {
    //get params from URL
    $state = NULL;
    $pcpId = CRM_Utils_Request::retrieve('id', 'Positive', CRM_Core_DAO::$_nullArray, TRUE); 
    $state = CRM_Utils_Request::retrieve('state', 'String');
    $pcpContactId = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $pcpId, 'contact_id');
    $opBtn = CRM_Utils_Request::retrieve('op', 'String', CRM_Core_DAO::$_nullArray, FALSE, NULL, 'GET');
    
    //to set the status
    //FIXME: proper status message
    switch ($opBtn) {
      case 'join':
        $statusTitle = ts("Team Request Sent");
        $statusText  = ts('A notification has been sent to the team. Once approved, team should be visible on your page.');
        $this->setPcpStatus($statusText, $statusTitle, 'pcp-info');
        break;
      case 'create':
        $statusTitle = ts("New Team Created");
        $statusText  = ts("That's Great, You have successfully created the Team");
        $this->setPcpStatus($statusText, $statusTitle, 'pcp-info');
        break;
      case 'invite':
        $statusTitle = ts("Invite Team");
        $statusText  = ts("Invitation request(s) has been sent.");
        $this->setPcpStatus($statusText, $statusTitle, 'pcp-info');
        break;
      case 'approve':
        $statusTitle = ts("Team Member Request Approved");
        $statusText  = ts("Team member request has been approved.");
        $this->setPcpStatus($statusText, $statusTitle, 'pcp-info');
        break;
      case 'decline':
        $statusTitle = ts("Team Member Request Declined");
        $statusText  = ts("Team member request has been declined.");
        $this->setPcpStts(atus($statusText, $statusTitle, 'pcp-info'));
        break;
      case 'pending':
        $statusTitle = ts("Pending Request");
        $statusText  = ts("Pending Request has been cancelled.");
        $this->setPcpStatus($statusText, $statusTitle, 'pcp-info');
        break;        
      default:
        break;
    }
    
    $pcpDetails  = self::getPcpDetails($pcpId);
    $this->assign('pcpinfo', $pcpDetails);

    $teamPcpInfo = array();
    if (!empty($pcpDetails['team_pcp_id'])) {
      $teamPcpInfo = self::getPcpDetails($pcpDetails['team_pcp_id']);
    }
    $this->assign('teamPcpInfo', $teamPcpInfo);

    $pendingApprovalInfo = array();
    if (isset($pcpDetails['pending_team_pcp_id'])) {
      $pendingApprovalInfo = self::getPcpDetails($pcpDetails['pending_team_pcp_id']);
      //relationship Id., to withdraw pending request
      $pendingApprovalInfo['relationship_id'] = $pcpDetails['pending_team_relationship_id'];
    }
    $this->assign('pendingApprovalInfo', $pendingApprovalInfo);
    
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
    if (empty($aDonationResult['values']) && empty($pcpDetails['team_pcp_id']) && empty($pcpDetails['pending_team_pcp_id'])) {
      // if no donations, no team or team-requests, show a message
      $statusTitle = ts("Congratulations, you are now signed up for '%1'", array(1=>$pcpDetails['page_title']));
      $statusText  = ts('We have created this page to help you with your fundraising. Please take a few minutes to complete a couple of details below, you will need to add a fundraising target to give you something to aim for (aim high!) and write a little bit about yourself to encourage people to help you reach that target. If you want to do this event as a team or in memory of a loved one you can set that up below as well.');
      $this->setPcpStatus($statusText, $statusTitle, 'pcp-info');
    }
      
    //team member info
    $teamMemberInfo = civicrm_api( 'pcpteams', 'getTeamMembersInfo', array(
        'version'  => 3, 
        'pcp_id'   => $pcpId,
      )
    );
    $this->assign('teamMemberInfo', isset($teamMemberInfo['values']) ? $teamMemberInfo['values'] : NULL);
    
    // team member request info for admins (edit permission)
    if ($this->_isEditPermission) {
      $teamMemberRequestInfo = civicrm_api( 'pcpteams', 'getTeamRequestInfo', array(
        'version'     => 3, 
        'team_pcp_id' => $pcpId,
      ));
      $this->assign('teamMemberRequestInfo', isset($teamMemberRequestInfo['values']) ? $teamMemberRequestInfo['values'] : NULL);
      $this->assign('teamMemberRequestCount', $teamMemberRequestInfo['count']);
      if (!empty($teamMemberRequestInfo['values']) && $this->_isEditPermission) {
        $statusTitle = ts("New member request");
        $statusText  = ts("You have %1 new member request(s). Click <a id='showMemberRequests' class='pcp-button pcp-btn-red' href='#member-req-block'>here</a> to manage them.", array(1=>count($teamMemberRequestInfo['values'])));
        $this->setPcpStatus($statusText, $statusTitle, 'pcp-info');
      }
    }

    //set Page title
    $pageTitle = ts("Participant Page");
    if (!empty($pcpDetails['is_teampage'])) {
      $pageTitle = ts("Team Page");
    }
    if (!empty($pcpDetails['title'])) {
      $pageTitle = ts($pageTitle.": %1", array(1=>$pcpDetails['title']));
    }
    CRM_Utils_System::setTitle($pageTitle);
    
    //Pcp layout button and URLs
    //DS FIXME: these urls should be built in tpl
    $joinTeamURl    = CRM_Utils_System::url('civicrm/pcp/manage/team/edit'     , "reset=1&id={$pcpId}&pageId={$pcpDetails['page_id']}&op=2&snippet=json");
    $createTeamURl  = CRM_Utils_System::url('civicrm/pcp/manage/team/edit'     , "reset=1&id={$pcpId}&pageId={$pcpDetails['page_id']}&op=1&snippet=json");
    $updateProfPic  = CRM_Utils_System::url('civicrm/pcp/manage/profile/edit'  , "reset=1&id={$pcpId}&pageId={$pcpDetails['page_id']}&snippet=json");
    if ($pcpDetails['is_teampage']) {
      $inviteTeamURl= CRM_Utils_System::url('civicrm/pcp/manage/team/edit'     , "reset=1&id={$pcpId}&pageId={$pcpDetails['page_id']}&op=invite&snippet=json");
      $this->assign('inviteTeamURl' , $inviteTeamURl);
    }

    $this->assign('createTeamUrl' , $createTeamURl);
    $this->assign('joinTeamUrl'   , $joinTeamURl);
    $this->assign('updateProfPic' , $updateProfPic);

    // catch all status messages generated on pcp edit screen
    // and display in pcp style. We can use civi's no-pop up style, but for ajax
    // snippet that doesn't work anyway
    $allStatus = CRM_Core_Session::singleton()->getStatus(TRUE);
    if ($allStatus) {
      foreach ($allStatus as $status) {
        $this->setPcpStatus($status['text'], $status['title'], 'pcp-info');
      }
    }
  }

  static function getPcpDetails($pcpId){
    if (empty($pcpId)) {
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
    if (civicrm_error($result)) {
      return NULL;
    }
    return isset($result['values'][0]) ? $result['values'][0] : CRM_Core_DAO::$_nullArray;
  }

  function setPcpStatus($text, $title = '', $type = 'alert') {
    static $status = array();
    $status[] = array(
      'text'  => $text,
      'title' => $title,
      'type'  => $type,
    );
    $this->assign('pcpStatus', $status);
  }
}
