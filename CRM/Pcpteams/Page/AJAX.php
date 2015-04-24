<?php

/**
 * This class contains all contact related functions that are called using AJAX (jQuery)
 */

class CRM_Pcpteams_Page_AJAX {
  
  static function unsubscribeTeam(){
    $entity_id    = CRM_Utils_Type::escape($_POST['entity_id'], 'Integer');
    $team_pcp_id  = CRM_Utils_Type::escape($_POST['team_pcp_id'], 'Integer');
    // $teamPcpCfId  = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId(); 

    //check the hasPermission to view details
    if (!CRM_Pcpteams_Utils::hasPermission($entity_id, NULL, CRM_Core_Permission::EDIT)) {
      CRM_Core_Session::setStatus(ts("Sorry! You dont have right permission to Edit this page"));
      CRM_Utils_System::civiExit();
    }
    
    $params = array(
      'version'   => 3,
      'entity_id' => $entity_id,
      "team_pcp_id" => NULL, 
    );
    $updatedResult = civicrm_api3('pcpteams', 'customcreate', $params);
    if (!civicrm_error($updatedResult)) {
      echo 'updated';
    }
    else{
      echo $updatedResult['error_message'];
    }
    
    CRM_Utils_System::civiExit();
  }
  
  static function leaveTeam(){
    $user_id      = CRM_Utils_Type::escape($_POST['user_id'], 'Integer');
    $team_pcp_id  = CRM_Utils_Type::escape($_POST['team_pcp_id'], 'Integer');
    $params       = array(
      'version'     => '3',
      'user_id'     => $user_id,
      'team_pcp_id' => $team_pcp_id,
    );
    $result = civicrm_api('pcpteams', 'leaveTeam', $params);
      
    if($result){
      //create Activity - Join Team Request Authourised
      $actParams = array(
        'target_contact_id'=>  CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $team_pcp_id, 'contact_id'),
      );
      CRM_Pcpteams_Utils::createPcpActivity($actParams, CRM_Pcpteams_Constant::C_AT_LEAVE_TEAM);
      //end
      
      //send email once left the team
      $teamAdminId    = CRM_Pcpteams_Utils::getTeamAdmin($team_pcp_id);
      list($teamAdminName, $teamAdminEmail)  = CRM_Contact_BAO_Contact::getContactDetails($teamAdminId);
      $contactDetails = civicrm_api('Contact', 'get', array('version' => 3, 'sequential' => 1, 'id' => $user_id));
      $teamId         = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $team_pcp_id, 'contact_id');
      $teamName       = CRM_Contact_BAO_Contact::displayName($teamId);

      $emailParams =  array(
        'tplParams' => array(
          'teamAdminName' => $teamAdminName,
          'userFirstName' => $contactDetails['values'][0]['first_name'],
          'userlastName'  => $contactDetails['values'][0]['last_name'],
          'teamName'      => $teamName,
          'pageURL'       => CRM_Utils_System::url('civicrm/pcp/manage', "reset=1&id={$team_pcp_id}", TRUE, NULL, FALSE, TRUE),
        ),
        'email' => array(
          $teamAdminName => array(
            'first_name'    => $teamAdminName,
            'last_name'     => $teamAdminName,
            'email-Primary' => $teamAdminEmail,
            'display_name'  => $teamAdminName,
          )
        ),
        'valueName' => CRM_Pcpteams_Constant::C_MSG_TPL_LEAVE_TEAM,
        // 'email_from' => $fromEmail,
      );
      
      $sendEmail = CRM_Pcpteams_Utils::sendMail($user_id, $emailParams);
    
      
      echo 'updated'; //FIXME : Need to display proper response
    }
    
    CRM_Utils_System::civiExit();
  }
  
  static function inlineEditorAjax(){
    $eleId       = CRM_Utils_Type::escape($_POST['id'], 'String');
    $pcpId       = CRM_Utils_Type::escape($_POST['pcp_id'], 'Integer');
    $editedValue = CRM_Utils_Type::escape($_POST['value'], 'String');
    $columnfield = str_replace('pcp_', '', $eleId);
    
    //check the hasPermission to view details
    if (!CRM_Pcpteams_Utils::hasPermission($pcpId, NULL, CRM_Core_Permission::EDIT)) {
      CRM_Core_Session::setStatus(ts("Sorry! You dont have right permission to Edit this page"));
      CRM_Utils_System::civiExit();
    }
    
    $params      = array(
      'version' => 3,
      'id'      => $pcpId,
      $columnfield => trim($editedValue)
    );
    $result = civicrm_api('pcpteams', 'create', $params);
    echo $editedValue;
    CRM_Utils_System::civiExit();
  }
    
  static function approveTeamMember(){
    $entity_id      = CRM_Utils_Type::escape($_POST['entity_id'], 'Integer');
    $pcp_id         = CRM_Utils_Type::escape($_POST['pcp_id'], 'Integer');
    $team_pcp_id    = CRM_Utils_Type::escape($_POST['team_pcp_id'], 'Integer');
    // $teamPcpCfId    = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
    
    //check the hasPermission to view details
    if (!CRM_Pcpteams_Utils::hasPermission($team_pcp_id, NULL, CRM_Core_Permission::EDIT)) {
      CRM_Core_Session::setStatus(ts("Sorry! You dont have right permission to approve this member"));
      CRM_Utils_System::civiExit();
    }
      
      
    $params = array(
      'version'   => 3,
      'entity_id' => $pcp_id,
      "team_pcp_id" => $team_pcp_id,
    );
    $updatedResult = civicrm_api3('pcpteams', 'customcreate', $params);
    if (!civicrm_error($updatedResult)) {
      $result  = civicrm_api3('Relationship', 'delete', array(
        'sequential' => 1,
        'id'         => $entity_id,
      ));
      $contactID = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $pcp_id, 'contact_id');
      //create Activity - Join Team Request Authourised
      $actParams = array(
        'assignee_contact_id'=>  CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $team_pcp_id, 'contact_id'),
        'target_contact_id'  =>  $contactID,
      );
      CRM_Pcpteams_Utils::createPcpActivity($actParams, CRM_Pcpteams_Constant::C_AT_REQ_AUTHORISED);
      //end
      $teamMemberInfo = civicrm_api( 'pcpteams', 'getTeamMembersInfo', array(
          'version'  => 3, 
          'pcp_id'   => $pcp_id,
          'contact_id' => $contactID,
        )
      );
      $memberInfo = isset($teamMemberInfo['values'][0]) ? $teamMemberInfo['values'][0] : NULL;
      if(!$memberInfo){
        echo 'member not found';
        CRM_Utils_System::civiExit();
      }
      $html = "
      <div class=\"mem-row\">
        <div class=\"mem-body-row avatar\">
          <img width=\"35\" height=\"35\" src=\"{$memberInfo['image_url']}\">
        </div>
        <div class=\"mem-body-row name\">
              {$memberInfo['display_name']}
        </div> 
        <div class=\"mem-body-row pcp-progress\">
        <span>{$memberInfo['donations_count']} Donations</span>
        <div class=\"pcp-bar\">
          <div title=\"{$memberInfo['percentage']}%\" style=\"width: {$memberInfo['percentage']}%;\" class=\"pcp-bar-progress\">
          </div>
        </div>
        </div>
        <div class=\"mem-body-row raised\">
          {$memberInfo['amount_raised']}
        </div>
        <div class=\"mem-body-row donate\">
          <a href=\"{$memberInfo['donate_url']}\" class=\"btn-donate-small\">Donate</a>
        </div>
        <div class=\"clear\"></div>
      </div> 
      ";
      echo $html;
    }
    else{
      echo $updatedResult['error_message'];
    }
    CRM_Utils_System::civiExit();
  }  
  
  static function declineTeamMember(){
    $entity_id      = CRM_Utils_Type::escape($_POST['entity_id'], 'Integer');
    $op             = CRM_Utils_Type::escape($_POST['op'], 'String');
    $pcp_id         = CRM_Utils_Type::escape($_POST['pcp_id'], 'Integer');
    $team_pcp_id    = CRM_Utils_Type::escape($_POST['team_pcp_id'], 'Integer');
    $assigneeId     = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Relationship', $entity_id, 'contact_id_b');
    $targetId       = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Relationship', $entity_id, 'contact_id_a');

    //check user can decline this request
    if (!CRM_Pcpteams_Utils::hasPermission($team_pcp_id)) {
      CRM_Core_Session::setStatus(ts("Sorry! You dont have right permission to decline this member"));
      CRM_Utils_System::civiExit();
    }
    $teamAdminID        = CRM_Pcpteams_Utils::getTeamAdmin($team_pcp_id);

    $getUserPcpQuery    = "SELECT pcp_a_b FROM civicrm_value_pcp_relationship_set WHERE entity_id = {$entity_id}";
    $userPcpId          = CRM_Core_DAO::singleValueQuery($getUserPcpQuery);
    $updatedResult  = civicrm_api3('Relationship', 'delete', array(
      'sequential' => 1,
      'id'         => $entity_id,
      ));
    if(!civicrm_error($updatedResult)){
      //create Activity - Join Team Request Declined / withdraw
      $actParams = array(
        'assignee_contact_id'=>  $assigneeId,
        'target_contact_id'  =>  $targetId,
      );

      CRM_Pcpteams_Utils::createPcpActivity($actParams, CRM_Pcpteams_Constant::C_AT_REQ_DECLINED);
      list($userName, $userEmail)  = CRM_Contact_BAO_Contact::getContactDetails($targetId);
      $contactDetails = civicrm_api('Contact', 'get', array('version' => 3, 'sequential' => 1, 'id' => $targetId));

      $emailParams =  array(
        'tplParams' => array(
          'userFirstName' => $contactDetails['values'][0]['first_name'],
          'userLastName'  => $contactDetails['values'][0]['last_name'],
          'teamName'      => CRM_Contact_BAO_Contact::displayName($assigneeId),
          'pageURL'       => CRM_Utils_System::url('civicrm/pcp/manage', "reset=1&id={$userPcpId}", TRUE, NULL, FALSE, TRUE),
        ),
        'email' => array(
          $userName => array(
            'first_name'    => $contactDetails['values'][0]['first_name'],
            'last_name'     => $contactDetails['values'][0]['last_name'],
            'email-Primary' => $userEmail,
            'display_name'  => $userName,
          )
        ),
        'valueName' => CRM_Pcpteams_Constant::C_MSG_TPL_JOIN_REQ_DECLINE_TEAM,
        // 'email_from' => $fromEmail,
      );

      $sendEmail = CRM_Pcpteams_Utils::sendMail($teamAdminID, $emailParams);
      //end
      echo 'declined';
    }else{
      echo $updatedResult['error_message'];
    }
    CRM_Utils_System::civiExit();
  } 
  
  static function withdrawJoinRequest(){
    $entity_id      = CRM_Utils_Type::escape($_POST['entity_id'], 'Integer');
    $op             = CRM_Utils_Type::escape($_POST['op'], 'String');
    $pcp_id         = CRM_Utils_Type::escape($_POST['pcp_id'], 'Integer');
    $team_pcp_id    = CRM_Utils_Type::escape($_POST['team_pcp_id'], 'Integer');
    $targetId       = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Relationship', $entity_id, 'contact_id_b');
    $userID         = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Relationship', $entity_id, 'contact_id_a');

    //check user permission
    if (!CRM_Pcpteams_Utils::hasPermission($pcp_id, $userID)) {
      CRM_Core_Session::setStatus(ts("Sorry! You dont have right permission to withdraw this request"));
      CRM_Utils_System::civiExit();
    }
    $teamAdminID    = CRM_Pcpteams_Utils::getTeamAdmin($team_pcp_id);

    $getUserPcpQuery= "SELECT pcp_a_b FROM civicrm_value_pcp_relationship_set WHERE entity_id = {$entity_id}";
    $userPcpId      = CRM_Core_DAO::singleValueQuery($getUserPcpQuery);
    $updatedResult  = civicrm_api3('Relationship', 'delete', array(
      'sequential' => 1,
      'id'         => $entity_id,
      ));
    if(!civicrm_error($updatedResult)){
      //create Activity - Join Team Request withdraw
      $actParams = array(
        'assignee_contact_id'=>  $teamAdminID,
        'target_contact_id'  =>  $targetId,
      );
      //FIXME: Make sure the activity type., doesn't have seperate activity type for withdraw at the moment.
      CRM_Pcpteams_Utils::createPcpActivity($actParams, CRM_Pcpteams_Constant::C_AT_REQ_DECLINED);
      list($userName, $userEmail)  = CRM_Contact_BAO_Contact::getContactDetails($userID);
      $contactDetails = civicrm_api('Contact', 'get', array('version' => 3, 'sequential' => 1, 'id' => $userID));

      $emailParams =  array(
        'tplParams' => array(
          'userFirstName' => $contactDetails['values'][0]['first_name'],
          'userLastName'  => $contactDetails['values'][0]['last_name'],
          'teamName'      => CRM_Contact_BAO_Contact::displayName($targetId),
          'pageURL'       => CRM_Utils_System::url('civicrm/pcp/manage', "reset=1&id={$userPcpId}", TRUE, NULL, FALSE, TRUE),
        ),
        'email' => array(
          $userName => array(
            'first_name'    => $contactDetails['values'][0]['first_name'],
            'last_name'     => $contactDetails['values'][0]['last_name'],
            'email-Primary' => $userEmail,
            'display_name'  => $userName,
          )
        ),
        //FIXME: Make sure the message template., doesn't have seperate message template for withdraw at the moment.
        'valueName' => CRM_Pcpteams_Constant::C_MSG_TPL_JOIN_REQ_DECLINE_TEAM,
        // 'email_from' => $fromEmail,
      );
    
      $sendEmail = CRM_Pcpteams_Utils::sendMail($teamAdminID, $emailParams);
      //end
      echo 'declined';
    }else{
      echo $updatedResult['error_message'];
    }
    CRM_Utils_System::civiExit();
  }
  
  static function removeTeamMember() {
    $pcp_id         = CRM_Utils_Request::retrieve('pcp_id', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
    $team_pcp_id    = CRM_Utils_Request::retrieve('team_pcp_id', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
        
    // $teamPcpCfId    = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
    $params = array(
      'version'   => 3,
      'entity_id' => $pcp_id,
      "team_pcp_id" => NULL, 
    );
    $updatedResult = civicrm_api3('pcpteams', 'customcreate', $params);
    if(!civicrm_error($updatedResult)){
      echo 'removed';
    }else{
      echo $updatedResult['error_message'];
    }
    CRM_Utils_System::civiExit();
  }
  
  static function deactivateTeamMember() {
    $pcp_id         = CRM_Utils_Request::retrieve('pcp_id', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
    $team_pcp_id    = CRM_Utils_Request::retrieve('team_pcp_id', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
    
    //check the hasPermission to view details
    if (!CRM_Pcpteams_Utils::hasPermission($team_pcp_id, NULL, CRM_Core_Permission::EDIT)) {
      CRM_Core_Session::setStatus(ts("Sorry! You dont have right permission to de-activate this member"));
      CRM_Utils_System::civiExit();
    }
  
    // $teamPcpCfId    = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId(); 
    $params = array(
      'version'   => 3,
      'entity_id' => $pcp_id,
      "team_pcp_id" => NULL, 
    );
    $updatedResult = civicrm_api3('pcpteams', 'customcreate', $params);
    if(!civicrm_error($updatedResult)){
      echo 'deactivated';
    }else{
      echo $updatedResult['error_message'];
    }
    CRM_Utils_System::civiExit();
    
  }
  
  static function deleteTeamPcp() {
    $pcp_id         = CRM_Utils_Request::retrieve('pcp_id', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
    $team_pcp_id    = CRM_Utils_Request::retrieve('team_pcp_id', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
    $contactId      = CRM_Pcpteams_Utils::getcontactIdbyPcpId($pcp_id);
    $teamContactId  = CRM_Pcpteams_Utils::getcontactIdbyPcpId($team_pcp_id);
    
    $relTypeAdmin       = CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE;
    $adminRelTypeId     = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', $relTypeAdmin, 'id', 'name_a_b');
    $relationshipQuery  = "SELECT id FROM civicrm_relationship where contact_id_a = %1 AND contact_id_b = %2 AND relationship_type_id = %3";

    $queryParams = array(
      1 => array($contactId, 'Integer'),
      2 => array($teamContactId, 'Integer'),
      3 => array($adminRelTypeId, 'Integer'),
    );
      
    $relationship =  CRM_Core_DAO::singleValueQuery($relationshipQuery, $queryParams);
    if(!empty($relationship)) {
      $params = array(
        'version' => 3,
        'id'      => $team_pcp_id,
      );
      $result = civicrm_api('pcpteams', 'delete', $params);
      echo 'Deleted';
    } else {
      echo 'Sorry!! You are not team Admin';
    }
    CRM_Utils_System::civiExit();
  }
}

