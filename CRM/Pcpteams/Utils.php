<?php

/**
 * FIXME
 * Pcp Utils,
 * to call and use the methods in all pcp forms / pages.
 */
require_once 'CRM/Pcpteams/Constant.php';
class  CRM_Pcpteams_Utils {

  /**
   * to get the logged in User Id
   */
  static function getloggedInUserId(){
    return CRM_Core_Session::singleton()->get('userID');
  }

  // FIXME: convert this to API
  static function getPcpId($componentPageId, $component, $isCreatePCP = FALSE, $contactId = FALSE) {
    if(empty($contactId)) {
      $cid = CRM_Pcpteams_Utils::getloggedInUserId();
    } else {
      $cid = $contactId;
    }
    
    if ($cid) {
      $dao = new CRM_PCP_DAO_PCP();
      $dao->contact_id = $cid;
      $dao->page_id    = $componentPageId;
      $dao->page_type  = $component;
      if ($dao->find(TRUE)) {
        return $dao->id;
      }
      else if ($isCreatePCP) {
        return self::createDefaultPCP($cid, $componentPageId, $component);
      }
    } 
    return NULL;
  }
  
  // FIXME: 
  // 1. change function name to isUserTeamAdmin
  // 2. Ideally we need isUserTeamMember
  // 3. pure sql query
  // 4. Make this an API
  static function isUserTeamAdmin( $userId ){
    if(empty($userId)){
      return NULL;
    }
    require_once 'CRM/Contact/BAO/Relationship.php';
    $getUserRelationships = CRM_Contact_BAO_Relationship::getRelationship( $userId, CRM_Contact_BAO_Relationship::CURRENT);
    // Team Admin Relationship
    $relTypeAdmin   = CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE;
    $adminRelTypeId = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', $relTypeAdmin, 'id', 'name_a_b');
    
    foreach ($getUserRelationships as $value) {
      //check the user is admin of team. return team id if found one
      if( $value['relationship_type_id'] == $adminRelTypeId ){
        return array('id' => $value['contact_id_b'], 'state' => 'Team');
      }
    }
    
    return null;
  }  

  
  static function getContactWithHyperlink($id){
    if(empty($id)){
      return;
    }
    //FIXME: check permission to view contact
    $contactName = CRM_Contact_BAO_Contact::getContactDetails($id);
    $url = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid=%d');
    return sprintf("<a href =\"{$url}\">%s</a>", $id, $contactName['0']);
  }


  // FIXME:
  // 1. split it into get and create relationship apis
  // 2. We could have a wrapper on top of them as our own api if needed.
  /**
   * To check the valid relationship is exists., Create If not Found one.
   */
  static function createTeamRelationship($iContactIdA, $iContactIdB, $custom = array(), $action = 'join' ){
    $relationshipTypeName = CRM_Pcpteams_Constant::C_TEAM_RELATIONSHIP_TYPE;
    $is_active = 0;
    $skipTeamApproval = CRM_Pcpteams_Utils::getPcpTeamSettings(CRM_Pcpteams_Constant::C_SKIP_TEAM_APPROVAL);
    if ($skipTeamApproval) {
      $is_active = 1;
    }
    if($action == 'create') {
      // When a new team is created, we use admin relationship
      $relationshipTypeName = CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE;
      $is_active = 1;
    } 
    $relId = self::createRelationship($iContactIdA, $iContactIdB, $relationshipTypeName, $custom, $is_active);
    return array($relId, $is_active);
  }
  
  static function getcontactIdbyPcpId($id) {
    $id = CRM_Utils_Type::escape($id, 'Integer');
    $query = "SELECT contact_id FROM civicrm_pcp WHERE id = {$id}";
    return CRM_Core_DAO::singleValueQuery($query, CRM_Core_DAO::$_nullArray);
  }
  
  static function isaParticipantFor($eventId) {
    if (empty($eventId)) {
      return 0;
    } 
    $contactId = self::getloggedInUserId();

    $result = civicrm_api3('Participant', 'get', array('contact_id' => $contactId));
    if(!civicrm_error($result)) {
      foreach ($result['values'] as $key => $val) {
        if ($val['event_id'] == $eventId) {
          return $val['id'];
        }
      }
    }
    return 0;
  }

  static function getContributionDetailsByContributionPageId( $pageId ){
    if(empty($pageId)){
      return NULL;
    }
    
    $contributionParams = array(
      'version'     => 3,
      'sequential'  => 1,
      'contribution_page_id' => $pageId
    );
    
    $contributionAPI = civicrm_api3('Contribution', 'get', $contributionParams);
    if(civicrm_error($contributionAPI)){
      return NULL;
    }
    
    return $contributionAPI;  
  }
  
  static function getTeamPcpCustomFieldId(){
    return CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', CRM_Pcpteams_Constant::C_CF_TEAMPCPID, 'id', 'name');
  }
  
  static function getPcpTypeCustomFieldId(){
    return CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', CRM_Pcpteams_Constant::C_CF_PCP_TYPE, 'id', 'name');
  }  
  
  static function getBranchorPartnerCustomFieldId(){
    return CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', CRM_Pcpteams_Constant::C_CF_BRANCH_PARTNER, 'id', 'name');
  }
  
  static function getPcpTypeContactCustomFieldId(){
    return CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', CRM_Pcpteams_Constant::C_CF_PCP_TYPE_CONTACT, 'id', 'name');
  }
  
  static function getPcpABCustomFieldId(){
    return CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', CRM_Pcpteams_Constant::C_CF_PCPAB, 'id', 'name');
  }
  
  static function getPcpBACustomFieldId(){
    return CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', CRM_Pcpteams_Constant::C_CF_PCPBA, 'id', 'name');
  }
  
  static function getPcpCustomSetId(){
    return CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', CRM_Pcpteams_Constant::C_PCP_CUSTOM_GROUP_NAME, 'id', 'name');
  }
  
  static function getEventDetailsbyEventId( $id ){
    if(empty($id)){
      return NULL;
    }
    
    $params = array(
      'version' => 3,
      'id'      => $id,
    );
    
    $apiResult = civicrm_api3('Event', 'getsingle', $params);
    if(civicrm_error($apiResult)){
      return null;
    }
    
    return $apiResult;
  }
  
  static function canEditProfileImage( $pcpId, $pcpContactId ){
    if(empty($pcpId) || empty($pcpContactId)){
      return FALSE;
    }
    
    //check the User is logged in
    $userId = self::getloggedInUserId();
    if(!$userId){
      return FALSE;
    }
    
    //Check the user is Owner of the PCP
    if( $userId != $pcpContactId ){
      return FALSE;
    }
    
    //FIXME: If team user., Check the User is Team Admin
    return TRUE;
  }
  
  static function getEventPcps($eventId){
    if(empty($eventId)){
      return null;
    }
    
    $return = array();
    $eventDetails = civicrm_api('Pcpteams'
                            , 'getallpagesbyevent'
                            , array( 
                                'version'    => 3
                              , 'page_id'    => $eventId
                            )
    );
    
    $return['pcp_count']    = $eventDetails['count'];
    if($eventDetails['count'] > 0){
      foreach ($eventDetails['values'] as $pcps) {
        $pcpAmounts[$pcps['id']] = CRM_Pcpteams_BAO_PCP::thermoMeter($pcps['id']);
      }
    }
    $maxAmoutnRaisedPcp = array_search( max($pcpAmounts) , $pcpAmounts );
    $return['rank']     = $maxAmoutnRaisedPcp;
    $return['rankHolder']= $eventDetails['values'][$maxAmoutnRaisedPcp]['title'];
    return $return;
  }
  
  static function getPcpEventTitle($pcpId){
    if (empty($pcpId) ){
      return null;
    }
    $pageID = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $pcpId, 'page_id');
    if ($pageID) {
      $eventDetails   = CRM_Pcpteams_Utils::getEventDetailsbyEventId($pageID);
      return $eventDetails['title'];
    }
    return null;
  }
  
  static function getTeamAdmin($pcpId){
    if(empty($pcpId)){
      return null;
    }
    $teamContactID = CRM_Pcpteams_Utils::getcontactIdbyPcpId($pcpId);
    $teamAdminRelationshipTypeID  = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE, 'id', 'name_a_b');
    $relationshipResult = civicrm_api3('Relationship', 'get', array(
      'sequential' => 1,
      'relationship_type_id' => $teamAdminRelationshipTypeID,
      'contact_id_b' => $teamContactID,
      ));
    if(!civicrm_error($relationshipResult) && $relationshipResult['values']) {
      return $relationshipResult['values'][0]['contact_id_a'];
    }
  }

  static function createDefaultPcp($pcpContactId, $componentPageId, $component = 'event') {
    if (empty($pcpContactId) || empty($componentPageId)) {
      return FALSE;
    }
    $eventDetails  = CRM_Pcpteams_Utils::getEventDetailsbyEventId($componentPageId);
    $contactDisplayName = CRM_Contact_BAO_Contact::displayName($pcpContactId);
    $pcpResult = civicrm_api('Pcpteams', 
      'create', 
      array(
        'version'         => 3,
        'title'           => $contactDisplayName,
        'intro_text'      => "Welcome to ".$contactDisplayName.'\'s PCP',
        'contact_id'      => $pcpContactId,
        'page_id'         => $componentPageId,
        'page_type'       => $component,
        'goal_amount'     => '0.00', //FIXME: Need to make sure the initial sample goal_amount, setting 0.00 now, user can update later on their manage page
        'is_honor_roll' => 1,
      )
    );
    if(!civicrm_error($pcpResult) && $pcpResult['id']) {
      //create activity for pcp created.
      $ids          = array('target_contact_id' => $pcpContactId);
      $userId       = self::getloggedInUserId();
      
      $customDigFund= CRM_Core_BAO_CustomField::getCustomFieldID(CRM_Pcpteams_Constant::C_CF_DIGITAL_FUNDRAISING_PCP_ID, CRM_Pcpteams_Constant::C_CG_DIGITAL_FUNDRAISING);
      if ($customDigFund) {
        $ids["custom_{$customDigFund}"] = $pcpResult['id'];
      }
      if ($userId != $pcpContactId) {
        $ids['source_contact_id'] = $userId;
      }
      $activityName = $subject = CRM_Pcpteams_Constant::C_AT_PCP_CREATED;
      $desc         = 'New PCP has created';
      self::createPcpActivity($ids, $activityName);
      return $pcpResult['id'];
    }
    
    
    return NULL;
  }
  
  static function checkTeamExists($displayname) {
    if(empty($displayname)){
      return null;
    }
    $query = 
        "SELECT id FROM `civicrm_contact` 
         WHERE `contact_type` = 'Organization' AND `contact_sub_type` = 'Team' AND `organization_name` = %1";
    $queryParams = array( 1 => array( $displayname, 'String'));
    return CRM_Core_DAO::singleValueQuery($query, $queryParams);
  }
  
  static function getActivityTypeId ($activityname) {
    if(empty($activityname)){
      return null;
    }
    $optionGroupParams  = array('version' => '3'
                              ,'name' => CRM_Pcpteams_Constant::C_ACTIVITY_TYPE);
    $optionGroup        = civicrm_api('OptionGroup', 'Get', $optionGroupParams);

    $activityParams     = array('version' => '3'
                           ,'option_group_id' => $optionGroup['id']
                           ,'name' => $activityname);
    $activityType       = civicrm_api('OptionValue', 'get', $activityParams);

    return !empty($activityType['id']) ? $activityType['values'][$activityType['id']]['value'] : NULL;
  }
  
  static function createPcpActivity( $params, $activityname ){
    if(empty($activityname)){
      return null;
    }
    
    if (!isset($params['source_contact_id'])) {
      $params['source_contact_id'] = self::getloggedInUserId();
    }
    
    $sourceName = CRM_Contact_BAO_Contact::displayName($params['source_contact_id']);
    
    if (isset($params['target_contact_id'])) {
      $targetName = CRM_Contact_BAO_Contact::displayName($params['target_contact_id']);
    }
        
    //to handle to default values, subject and description for the activity type
    switch ($activityname) {
      case CRM_Pcpteams_Constant::C_AT_TEAM_CREATE:
        $subject = 'Team is created';
        $details = 'Team is created'.$targetName;
        break;
      case CRM_Pcpteams_Constant::C_AT_INVITATION_FROM_ADMIN:
        $sourceName .= ' ( Team Admin )' ;
        $subject = 'Team Member Invite to Join Team';
        $details = 'Invited to join team '.$targetName. ' by '.$sourceName;
        break;
      case CRM_Pcpteams_Constant::C_AT_INVITATION_FROM_MEMBER:
        $sourceName .= ' ( Team Member )';
        $subject = 'Team Member Invite to Join Team';
        $details = 'Invited to join team '.$targetName. ' by '.$sourceName;
        break;
      case CRM_Pcpteams_Constant::C_AT_GROUP_JOIN:
      $subject = 'Joined to branch';
        $details = 'Joined to branch '.$targetName;
        break;
      case CRM_Pcpteams_Constant::C_AT_TRIBUTE_JOIN:
        $subject = 'Joined to tribute contact';
        $details = 'Joined to tribute '.$params['reason'].' of '.$targetName;
        unset($params['reason']);
        break;
      case CRM_Pcpteams_Constant::C_AT_PCP_CREATED:
        $subject = 'New PCP has created';
        $details = "New PCP has created";        
        break;
      case CRM_Pcpteams_Constant::C_AT_REQ_AUTHORISED:
        $subject = 'Team request authorised';
        $details = "Member join team request has authorised";
        break;
      case CRM_Pcpteams_Constant::C_AT_REQ_DECLINED:
        $subject = 'Team request rejected';
        $details = "Member join team request has declined";
        break;
      case CRM_Pcpteams_Constant::C_AT_REQ_MADE:
        $subject = 'Team Member Requires Authorisation';
        $details = "Member join team request made by".$sourceName.' to '. $targetName;
        break;
      case CRM_Pcpteams_Constant::C_AT_LEAVE_TEAM:
        $subject = 'PCP member left team';
        $details = "PCP member left team ". $targetName;
        break;
      case CRM_Pcpteams_Constant::C_AT_SOFT_CREDIT:
        $subject = 'PCP ';
        $details = '';
        break;
      default:
        $subject = $activityname;
        $details = $activityname;
        break;
    }
    
    $activityTypeID = CRM_Pcpteams_Utils::getActivityTypeId($activityname);
    
    if($activityTypeID) {
      $activityParams = array(
        'activity_type_id'  => $activityTypeID,
        'subject'           => $subject,
        'details'           => $details,
        'activity_date_time'=> date( 'YmdHis' ),
        'status_id'         => 2, // status completed
        'version'           => 3,
      );
      $activityParams = array_merge($activityParams, $params);
      return civicrm_api( 'Activity','create', $activityParams );
    }
  }
  
  static function overrideLoginUrl(&$form) {
    $template              = CRM_Core_Smarty::singleton( );
    $beginHookFormElements = $template->get_template_vars();
    $loginURL              = $beginHookFormElements['loginURL'];
    if($loginURL) {
      $code  = $form->_code;
      $query = '';
      if($form->_tpId){
        $query  = "&tpId={$form->_tpId}";
        $code   = "cpftn";
      }
      if($code) {
        $query .= "&code={$code}";
      }
      $form->assign('loginURL', $loginURL.  urlencode($query));
    }
  }
  
  static function sendInviteEmail($message_template, $contact_id, $emailParams = array(), $teampcpId, $activityId ) {
    
    $mailParams = array();
    $contactParams = array();
    if (isset($emailParams['tplParams'])) {
      $mailParams['tplParams'] = $emailParams['tplParams'];
    }
    //create contact corresponding to each friend
    foreach ($emailParams['friend'] as $key => $details) {
      if ($details["first_name"]) {
        $displayName = $details["first_name"] . " " . $details["last_name"];
        $contactParams[$key] = array(
          'first_name'     => $details["first_name"],
          'last_name'      => $details["last_name"],
          'contact_source' => ts('PCP Team Invite'),
          'email-Primary'  => $details["email"],
          'display_name'   => $displayName,
        );
        $mailParams['email'][$displayName] = $contactParams[$key];
      }
    }
    
    if(empty($mailParams)) {
      return NULL;
    }
    
    $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
    $targetID   = CRM_Utils_Array::key('Activity Targets', $activityContacts);
      //friend contacts creation
    foreach ($contactParams as $key => $value) {
      //create contact only if it does not exits in db
      $value['email'] = $value['email-Primary'];
      $value['check_permission'] = FALSE;
      $contact = self::findContact($value, NULL, 'Individual');

      if (!$contact) {
        $contact = CRM_Contact_BAO_Contact::createProfileContact($value, CRM_Core_DAO::$_nullArray);
      }
       // attempt to save activity targets
      $targetParams = array(
        'activity_id' => $activityId,
        'contact_id'  => $contact,
        'record_type_id' => $targetID
      );

      // See if it already exists
      $activityContact = new CRM_Activity_DAO_ActivityContact();
      $activityContact->activity_id = $activityId;
      $activityContact->contact_id = $contact;
      $activityContact->find(TRUE);
      if (empty($activityContact->id)) {
        CRM_Activity_BAO_ActivityContact::create($targetParams);
      }
    }
    $mailParams['valueName'] = $message_template;
    
    return self::sendMail($contact_id, $mailParams);
  }
  
  static function getPcpBlockId($eventId, $component = 'event') {
     if(empty($eventId)){
      return null;
    }
    $entity_table = CRM_PCP_BAO_PCP::getPcpEntityTable($component);
    $pcpBlock = new CRM_PCP_DAO_PCPBlock();
    $pcpBlock->entity_table = $entity_table;
    $pcpBlock->entity_id    = $eventId;
    $pcpBlock->is_active = TRUE;
    $pcpBlock->find(TRUE);
    return $pcpBlock->id;
  }
  
  static function getPcpIdByContactAndEvent($eventId, $contactId, $component = 'event') {
     if(empty($eventId) || empty($contactId)){
      return null;
    }
    $dao = new CRM_PCP_DAO_PCP();
    $dao->contact_id  = $contactId; 
    $dao->page_id     = $eventId; 
    $dao->page_type   = $component;
    $dao->find(TRUE);
    return $dao->id;
  }
  
  static function hasPermission($pcpId = NULL, $contactId = NULL, $action = CRM_Core_Permission::EDIT, $teamPcpId = NULL) {
    if(empty($pcpId)) {
      if($contactId) {
        if ($action == CRM_Core_Permission::VIEW) { 
          // since get api is open now, we allow viewing member details
          return TRUE;
        } else {
          return ($contactId == CRM_Pcpteams_Utils::getloggedInUserId()) ? true : CRM_Contact_BAO_Contact_Permission::allow($contactId, $action);
        }
      }
      return FALSE;
    }
    $pcpOwnerContactId  = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $pcpId, 'contact_id');
    $hasPermission      = FALSE;
    if(empty($contactId)) {
      $contactId = CRM_Pcpteams_Utils::getloggedInUserId();
    } 
    // Check the pcp page which he is looking is the owner of pcp, then allow 'edit' permission 
    if($pcpOwnerContactId == $contactId) {
      return TRUE;
    } // Else if he is the memeber of the pcp , then allow 'view' permission
    else if ($action == CRM_Core_Permission::VIEW) { 
      // Since PCP get api is opened, as long as pcpId is available then allow view permission
      if ($pcpId) {
        return TRUE;
      }

      //CASE 1: IF logged in user is trying to view team member's pcp page
      //CASE 1A: get all team pcps for logged in user
      $getUserTeamQuery = "
        SELECT cps.team_pcp_id FROM civicrm_value_pcp_custom_set cps 
        INNER JOIN civicrm_pcp cp ON (cp.id = cps.entity_id)
        WHERE cp.contact_id = %1 AND cps.team_pcp_id IS NOT NULL
      ";
      $getUserTeamPcpDAO = CRM_Core_DAO::executeQuery($getUserTeamQuery, array( 1 => array($contactId, 'Integer')));
      $userTeamPcps = array();
      while ($getUserTeamPcpDAO->fetch()) {
        //CASE 2: IF logged in user is admin OR member of pcp being viewed
        if ($getUserTeamPcpDAO->team_pcp_id == $pcpId) {
          return TRUE;
        }
        $userTeamPcps[] = $getUserTeamPcpDAO->team_pcp_id;
      }

      //CASE 1B: IF pcp being viewed is related to team-pcp via custom teamp-pcp-id OR under approval relationship
      if (!empty($userTeamPcps)) {
        $userTeamPcpIds = implode(', ', $userTeamPcps);
        $memberQuery = "
          SELECT cp.id
          FROM civicrm_pcp cp
          LEFT JOIN civicrm_value_pcp_custom_set cpcs ON (cp.id = cpcs.entity_id)
          LEFT JOIN civicrm_value_pcp_relationship_set crcs ON (cp.id = crcs.pcp_a_b)
          WHERE (cpcs.entity_id = %1 AND cpcs.team_pcp_id IN ({$userTeamPcpIds})) OR ( crcs.pcp_a_b = %1 AND crcs.pcp_b_a IN ({$userTeamPcpIds}))
        ";
        $memberPcp = CRM_Core_DAO::singleValueQuery($memberQuery, array( 1 => array($pcpId, 'Integer')));
        if ($memberPcp) {
          return TRUE;
        }
      }
      
      //CASE 3: IF pcp being viewed has been requested to be joined by logged in user (under approval)
      $relQuery = "
        SELECT cr.id 
        FROM civicrm_relationship cr
        INNER JOIN civicrm_value_pcp_relationship_set crcs ON (cr.id = crcs.entity_id) 
        WHERE cr.contact_id_a = %1 AND cr.contact_id_b = %2 AND cr.relationship_type_id = %3 AND crcs.pcp_b_a = %4
      ";
      $relTypeId = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', CRM_Pcpteams_Constant::C_TEAM_RELATIONSHIP_TYPE, 'id', 'name_a_b');
      $relQueryParams = array(
        1 => array( $contactId, 'Integer'),
        2 => array( $pcpOwnerContactId, 'Integer'),
        3 => array( $relTypeId, 'Integer'),
        4 => array( $pcpId, 'Integer'),
      );
      if (CRM_Core_DAO::singleValueQuery($relQuery, $relQueryParams)) {
        return TRUE;
      }
      //CASE 4: if admin is trying to view the pcp
      if (CRM_Contact_BAO_Contact_Permission::allow($pcpOwnerContactId, CRM_Core_Permission::VIEW)) {
        return TRUE;
      }
    }
    else if ($action == CRM_Pcpteams_Constant::C_PERMISSION_MEMBER) { 
      if ($pcpId && $teamPcpId) {
        //check pcp custom set 
        $queryParams = array( 
          1 => array($pcpId, 'Integer'),
          2 => array($teamPcpId, 'Integer')
        );
        $query = "
          SELECT id FROM civicrm_value_pcp_custom_set 
          WHERE entity_id = %1 AND team_pcp_id = %2
        ";
        $teamMemberExists = CRM_Core_Dao::singleValueQuery($query, $queryParams);
        if ($teamMemberExists) {
          return TRUE;
        }
        
        
        //check pcp relationship custom set
        $query = "
        SELECT id FROM civicrm_value_pcp_relationship_set
        WHERE pcp_a_b = %1 AND pcp_b_a = %2
        ";
        $teamMemberExists = CRM_Core_Dao::singleValueQuery($query, $queryParams);
        if ($teamMemberExists) {
          return TRUE;
        }
      }
      // check if logged in user ($contactId) is a member of team pcp ($pcpId in this case)
      else if ($pcpId && $contactId) {
        $query = "
          SELECT cs.id FROM civicrm_value_pcp_custom_set cs
          INNER JOIN civicrm_pcp cp ON cp.id = cs.entity_id 
          INNER JOIN civicrm_contact cc ON cc.id = cp.contact_id
          WHERE cs.team_pcp_id = %1 AND cc.id = %2
        ";
        $queryParams = array( 
          1 => array($pcpId, 'Integer'),
          2 => array($contactId, 'Integer'),
        );
        $teamMemberExists = CRM_Core_Dao::executeQuery($query, $queryParams);
        if ($teamMemberExists->fetch()) {
          return TRUE;
        }
      }
      return FALSE;
        
    }
    else if ($action == CRM_Pcpteams_Constant::C_PERMISSION_TEAM_ADMIN) {
      if ($pcpId && $contactId) {
        $query = "
          SELECT cs.id FROM civicrm_value_pcp_custom_set cs
          INNER JOIN civicrm_pcp mp ON mp.id = cs.entity_id
          INNER JOIN civicrm_pcp tp ON tp.id = cs.team_pcp_id
          INNER JOIN civicrm_contact tc ON tc.id = tp.contact_id
          INNER JOIN civicrm_relationship cr ON cr.contact_id_b = tc.id
          INNER JOIN civicrm_relationship_type crt on crt.id = cr.relationship_type_id
          WHERE cs.entity_id = %1 AND cr.contact_id_a = %2 AND crt.name_a_b = %3";

         $queryParams = array( 
            1 => array($pcpId, 'Integer'),
            2 => array($contactId, 'Integer'),
            3 => array(CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE, 'String'),
          );
        if(CRM_Core_DAO::singleValueQuery($query, $queryParams)) {
          return TRUE;
        }
        if (CRM_Contact_BAO_Contact_Permission::allow($contactId, CRM_Core_Permission::EDIT)) {
          return TRUE;
        }
      }
    }
    else if ($action == CRM_Core_Permission::EDIT) {
      // A. if logged in user ($contactId) is owner of pcp ($pcpId) it should have returned true in the beginning.
      // B. at this point we checking if logged in user ($contactId) is admin for team-contact ($pcpOwnerContactId) of pcp ($pcpId)
      $query = "
        SELECT cr.id FROM civicrm_relationship cr
        INNER JOIN civicrm_relationship_type crt ON (crt.id = cr.relationship_type_id)
        WHERE cr.contact_id_a = %1 AND cr.contact_id_b = %2 AND cr.is_active = %3 AND crt.name_a_b = %4";

      $queryParams = array(
        1 => array($contactId, 'Integer'),
        2 => array($pcpOwnerContactId, 'Integer'),
        3 => array(1, 'Integer'),
        4 => array(CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE, 'String'),
      );

      if(CRM_Core_DAO::singleValueQuery($query, $queryParams)) {
        return TRUE;
      }
      if (CRM_Contact_BAO_Contact_Permission::allow($pcpOwnerContactId, CRM_Core_Permission::EDIT)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  static function getTeamAdminByTeamContactId($teamContactId) {
    if(empty($teamContactId)) {
      return NULL;
    }
    $query = "
      SELECT cc.display_name FROM civicrm_relationship cr 
      INNER JOIN civicrm_contact cc ON (cc.id = cr.contact_id_a)
      INNER JOIN civicrm_relationship_type crt ON (crt.id = cr.relationship_type_id)
      WHERE cr.contact_id_b = %1 AND crt.name_a_b = %2
    ";
    
    $queryParams = array(
       1 => array($teamContactId, 'Integer'),
       2 => array(CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE, 'String'),
    );
    
    return CRM_Core_DAO::singleValueQuery($query, $queryParams);
  }
    
  static function sendMail($contactID, &$values) {
    list($fromName, $email) = CRM_Contact_BAO_Contact::getContactDetails($contactID);
    // if no $fromName (only email collected from originating contact) - list returns single space
    if (trim($fromName) == '') {
      $fromName = $email;
    }

    // use contact email, CRM-4963
    if (empty($values['email_from'])) {
      $values['email_from'] = $email;
    }
    
    $tplParams = array();
    if (isset($values['tplParams'])) {
      $tplParams = $values['tplParams'];
    }
    $sent = FALSE;
    foreach ($values['email'] as $key => $emailDetails) {
      if ($emailDetails['email-Primary']) {
        // FIXME: factor the below out of the foreach loop
        $tplParams['inviteeFirstName'] = $emailDetails['first_name'];
        $tplParams['inviteeEmail'] = $emailDetails['email-Primary'];
        list($sent, $subject, $text, $html) = CRM_Core_BAO_MessageTemplate::sendTemplate(
          array(
            'groupName'         => CRM_Pcpteams_Constant::C_OG_MSG_TPL_WORKFLOW,
            'valueName'         => $values['valueName'],
            'contactId'         => $contactID,
            'tplParams'         => $tplParams,
            'from'              => "$fromName <{$values['email_from']}>",
            'toName'            => $emailDetails['display_name'],
            'toEmail'           => $emailDetails['email-Primary'],
            'replyTo'           => $email,
          )
        );
      }
    }
    return $sent ? TRUE : FALSE;
  }
  
  static function adjustTeamMemberTarget($teamPcpId, $memberPcpId = NULL) {
    if(empty($teamPcpId)) {
      return NULL;
    }
    $pcpType  = CRM_Pcpteams_Utils::checkPcpType($teamPcpId);
    $goalAmount = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $teamPcpId, 'goal_amount');
    if ($pcpType == CRM_Pcpteams_Constant::C_CONTACT_SUB_TYPE_TEAM) {
      if($memberPcpId) {
        $memGoalAmount = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $memberPcpId, 'goal_amount');
        if(empty($memGoalAmount)) {
          $params = array(
              'version'     => 3,
              'id'          => $memberPcpId,
              'goal_amount' => $goalAmount,
            );
          $result = civicrm_api('pcpteams', 'create', $params);
        }
      } else {
        $query = "
          UPDATE civicrm_pcp AS p1
          INNER JOIN civicrm_value_pcp_custom_set AS c ON p1.id = c.entity_id
          SET p1.goal_amount = %1
          WHERE (p1.goal_amount is NULL OR p1.goal_amount = 0) AND c.team_pcp_id = %2";
        
        $queryParams = array(
          1 => array($goalAmount, 'String'),
          2 => array($teamPcpId, 'Integer'),
        );
        CRM_Core_DAO::executeQuery($query, $queryParams);
      }
    }
  }
  
  static function checkPcpType($pcpId) {
    if(empty($pcpId)) {
      return NULL;
    }
    $aContactTypes   = CRM_Contact_BAO_Contact::getContactTypes(CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $pcpId, 'contact_id'));
    return in_array('Team', $aContactTypes) ? 'Team' : 'Indiviual';
    
  }
  
  static function adjustTeamTarget($pcpId) {
     if(empty($pcpId)) {
      return NULL;
    }
    $pcpType  = CRM_Pcpteams_Utils::checkPcpType($pcpId);
    // only for indiviual pcp
    if ($pcpType != CRM_Pcpteams_Constant::C_CONTACT_SUB_TYPE_TEAM) {
      $selectTeamPcpQuery = "SELECT team_pcp_id FROM civicrm_value_pcp_custom_set WHERE entity_id ={$pcpId}";
      $teamPcpId = CRM_Core_DAO::singleValueQuery($selectTeamPcpQuery);
      $isEdit = CRM_Pcpteams_Utils::hasPermission($teamPcpId, NULL, CRM_Core_Permission::EDIT);
      if($isEdit) {
        $query = "
          UPDATE civicrm_pcp p1 
          INNER JOIN civicrm_value_pcp_custom_set cs ON cs.team_pcp_id = p1.id
          INNER JOIN civicrm_pcp p2 ON p2.id = cs.entity_id
          SET p1.goal_amount = p2.goal_amount
          WHERE cs.entity_id = %1 AND (p1.goal_amount is NULL OR p1.goal_amount = 0) AND (p2.goal_amount IS NOT NULL OR p2.goal_amount <> 0)";

        $queryParams = array(
          1 => array($pcpId, 'String'),
        );
        CRM_Core_DAO::executeQuery($query, $queryParams);
      }
    }
  }
  
  static function reCreateRelationship($iContactIdA, $iContactIdB, $relationshipTypeName, $custom = array()){
    // Delete any old relationship on changing
    $query = "
      DELETE cr FROM civicrm_relationship cr
      INNER JOIN civicrm_relationship_type crt ON crt.id = cr.relationship_type_id";
    $where = " WHERE crt.name_a_b = %1 AND cr.contact_id_a = %2";
    $cfpcpab = CRM_Pcpteams_Utils::getPcpABCustomFieldId();
    if (CRM_Utils_Array::value('custom_'.$cfpcpab, $custom)) {
      $query .= " INNER JOIN civicrm_value_pcp_relationship_set crs ON crs.entity_id = cr.id";
      $where .= " AND crs.pcp_a_b = %3";
    }
    $sql = $query.$where;
    $queryParams = array (
      1 => array(CRM_Pcpteams_Constant::C_CORPORATE_REL_TYPE, 'String'),
      2 => array($iContactIdA, 'Int'),
      3 => array($custom['custom_'.$cfpcpab], 'Int'),
    );
    CRM_Core_DAO::executeQuery($sql, $queryParams);    
    // Create New Relationship against Team and Coporate
    self::createRelationship($iContactIdA, $iContactIdB, $relationshipTypeName, $custom);
  }
  
  static function createRelationship($iContactIdA, $iContactIdB, $relationshipTypeName, $custom = array(), $is_active = 1){
    if(empty($iContactIdA) || empty($iContactIdB)){
      $status = empty($iContactIdB) ? 'ContactIdB is Missing' : 'ContactIdA is Missing';
      CRM_Core_Error::debug_var('Input Details', $status);
      return FALSE;
    }
    $relTypeId = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', $relationshipTypeName, 'id', 'name_a_b');
    if ($relTypeId) {
      $aParams = array();
      //check the duplicates
      $aParams = array(
        'version'               => '3',
        'is_active'             => '1',
        'relationship_type_id'  => $relTypeId.'_a_b',
      );
      $bDuplicateFound = CRM_Contact_BAO_Relationship::checkDuplicateRelationship($aParams, $iContactIdA, $iContactIdB);
      if ($bDuplicateFound) {
        CRM_Core_Error::debug_log_message(ts('Relationship already exists.'));
        return TRUE;
      } else {
        $aParams['contact_id_a'] = $iContactIdA;
        $aParams['contact_id_b'] = $iContactIdB;
        $aParams['relationship_type_id'] = $relTypeId;
        $aParams['is_active'] = $is_active;
        if(!empty($custom)) {
          $aParams  = array_merge($aParams, $custom);
        }
        $createRelationship = civicrm_api3('Relationship', 'create', $aParams);
        if(!civicrm_error($createRelationship)){
          return $createRelationship['id'];
        }
      }
    } 
    return FALSE;
  }

  public static function getPcpTeamSettings($name = NULL) {
    $settingName = CRM_Pcpteams_Constant::PCPTEAM_SETTING_NAME;
    $settings    =  CRM_Core_BAO_Setting::getItem(NULL, $settingName, NULL, FALSE);
    if (!empty($settings)) {
      $settings = unserialize($settings);
    }

    if (!empty($name)) {
      return $settings[$name];
    }

    return $settings;
  }

  public static function setPcpTeamSettings($settingValues) {

    if (empty($settingValues)) {
      return; 
    }

    $settingName  = CRM_Pcpteams_Constant::PCPTEAM_SETTING_NAME;
    $settingValue = serialize($settingValues);

    return CRM_Core_BAO_Setting::setItem($settingValue, NULL, $settingName);
  }  

  /**
   * Searches for a contact in the db with similar attributes.
   *
   * @param array $params
   *   The list of values to be used in the where clause.
   * @param int $id
   *   The current contact id (hence excluded from matching).
   * @param string $contactType
   *
   * @return int|null
   *   contact_id if found, null otherwise
   */
  public static function findContact(&$params, $id = NULL, $contactType = 'Individual') {
    $dedupeParams = CRM_Dedupe_Finder::formatParams($params, $contactType);
    $dedupeParams['check_permission'] = CRM_Utils_Array::value('check_permission', $params, TRUE);
    $ids = CRM_Dedupe_Finder::dupesByParams($dedupeParams, $contactType, 'Supervised', array($id));
    if (!empty($ids)) {
      return implode(',', $ids);
    }
    else {
      return NULL;
    }
  }
}
