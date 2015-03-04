<?php

require_once 'CRM/Core/Page.php';

class CRM_Pcpteams_Page_Dashboard extends CRM_Core_Page {

  function run() {
    // Page title
    CRM_Utils_System::setTitle(ts('Dashboard'));

    $pcpId = $createTeamURl = $joinTeamURl = "";

    //get Pcp Id from URL
    $pcpId = CRM_Utils_Request::retrieve('id', 'Positive');

    //FIXME: Validate the contact has permission to view / edit the PCP details 

    //set Create Team and Join Team button URL
    if(!empty($pcpId)){
		$joinTeamURl 	= CRM_Utils_System::url('civicrm/pcp/team', 'reset=1&id='.$pcpId);
		$createTeamURl 	= CRM_Utils_System::url('civicrm/pcp/team/create', 'reset=1&id='.$pcpId);
    }


    //assign values to template
    $this->assign('pcpId', $pcpId);
    $this->assign('createTeamUrl', $createTeamURl);
    $this->assign('joinTeamUrl', $joinTeamURl);

    parent::run();
  }
}
