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
  static function checkORCreateTeamRelationship($iContactIdA, $iContactIdB, $custom = array(), $checkandCreate = FALSE, $action = 'join' ){
    if(empty($iContactIdA) || empty($iContactIdB)){
      $status = empty($iContactIdB) ? 'Team Contact is Missing' : 'Team Member Contact Id is Missing';
      CRM_Core_Session::setStatus($status);
    }
    $teamRelTypeName = CRM_Pcpteams_Constant::C_TEAM_RELATIONSHIP_TYPE;
    // When a new team is created
    if($action == 'create') {
      $teamRelTypeName = CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE;
    } 
    $relTypeId       = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', $teamRelTypeName, 'id', 'name_a_b');

    //check the Relationship Type Exists
    if(empty($relTypeId)){
      CRM_Core_Session::setStatus( t('Failed To create Relationship. Relationship Type (%1) does not exist.', array('%1' => $teamRelTypeName)) );
    }else{
      
      $aParams = array();
      //check the duplicates
      $aParams = array(
        'version'               => '3',
        'is_active'             => '1',
        'relationship_type_id'  => $relTypeId.'_a_b',
      );
      $bDuplicateFound = CRM_Contact_BAO_Relationship::checkDuplicateRelationship($aParams, $iContactIdA, $iContactIdB);

      if(!$bDuplicateFound && $checkandCreate){
        $aParams['contact_id_a'] = $iContactIdA;
        $aParams['contact_id_b'] = $iContactIdB;
        $aParams['relationship_type_id'] = $relTypeId;
        $aParams['is_active'] = $action == 'create' ? 1 : 0 ;
        if(!empty($custom)) {
          $aParams  = array_merge($aParams, $custom);
        }
        $createRelationship = civicrm_api3('Relationship', 'create', $aParams);
        if(!civicrm_error($createRelationship)){
          $teamName = self::getContactWithHyperlink($iContactIdB);
          CRM_Core_Session::setStatus(ts("Team contact has validated and successfully joined as {$teamRelTypeName} {$teamName}"), '', 'success');
        }
      }
    }

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
        $pcpAmounts[$pcps['id']] = CRM_PCP_BAO_PCP::thermoMeter($pcps['id']);
      }
    }
    $maxAmoutnRaisedPcp = array_search( max($pcpAmounts) , $pcpAmounts );
    $return['rank']     = $maxAmoutnRaisedPcp;
    $return['rankHolder']= $eventDetails['values'][$maxAmoutnRaisedPcp]['title'];
    return $return;
  }
  
  static function getPcpEventTitle($pcpId){
    if(empty($pcpId)){
      return null;
    }
    $pcpResult = civicrm_api('Pcpteams', 
       'get', 
       array(
         'pcp_id'     => $pcpId,
         'version'    => 3,
         'sequential' => 1,
       )
    );
    if(!civicrm_error($pcpResult)){
      $pageID         = $pcpResult['values'][0]['page_id'];
      $eventDetails   = CRM_Pcpteams_Utils::getEventDetailsbyEventId($pageID);
      return $eventDetails['title'];
    }
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

  // FIXME: pcp title should be combination of contact name and event name
  // similarly the description
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
        'title'           => $contactDisplayName.' : '.$eventDetails['title'],
        'intro_text'      => "Welcome to ".$contactDisplayName.'\'s PCP',
        'contact_id'      => $pcpContactId,
        'page_id'         => $componentPageId,
        'page_type'       => $component,
        'goal_amount'     => '0.00', //FIXME: Need to make sure the intial sample goal_amount, setting 0.00 now, user can update later on their manage page
      )
    );
    if(!civicrm_error($pcpResult) && $pcpResult['id']) {
      //create activity for pcp created.
      $ids          = array('target_contact_id' => $pcpContactId);
      $userId       = self::getloggedInUserId();
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
         WHERE `contact_type` = 'Organization' AND `contact_sub_type` = 'Team' AND `display_name` LIKE '%{$displayname}'";
    return CRM_Core_DAO::singleValueQuery($query);
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

    return $activityType['values'][$activityType['id']]['value'];
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
      case CRM_Pcpteams_Constant::C_AT_TEAM_INVITE:
        $isTeamAdmin = 0;
        if (isset($params['assignee_contact_id'])) {
          $targetName = CRM_Contact_BAO_Contact::displayName($params['assignee_contact_id']);
          $checkAdminParams = array(
            'version' => 3,
            'user_id' => $params['source_contact_id'],
            'team_contact_id' => $params['assignee_contact_id'],
          );
          $chkTeamAdmin= civicrm_api('Pcpteams', 'checkTeamAdmin', $checkAdminParams);
          $isTeamAdmin = $chkTeamAdmin['is_team_admin'];
        }
        
        $sourceName .= $isTeamAdmin ? ' ( Team Admin )' : ' ( Team Member )';
        $subject = 'Invite to Join Team';
        $details = 'Invited to Join Team '.$targetName. ' by '.$sourceName;
        break;
      case CRM_Pcpteams_Constant::C_AT_GROUP_JOIN:
      $subject = 'Joined to branch';
        $details = 'Joined to branch '.$targetName;
        break;
      case CRM_Pcpteams_Constant::C_AT_TRIBUTE_JOIN:
        $subject = 'Joined to Tribute contact';
        $details = 'Joined to Tribute '.$params['reason'].' of '.$targetName;
        unset($params['reason']);
        break;
      case CRM_Pcpteams_Constant::C_AT_PCP_CREATED:
        $subject = 'New PCP has created';
        $details = "New PCP has created";        
        break;
      case CRM_Pcpteams_Constant::C_AT_REQ_AUTHORISED:
        $subject = 'Team Request authorised';
        $details = "Member Join Team request has authorised";
        break;
      case CRM_Pcpteams_Constant::C_AT_REQ_DECLINED:
        $subject = 'Team Request rejected';
        $details = "Member Join Team request has declined";
        break;
      case CRM_Pcpteams_Constant::C_AT_REQ_MADE:
        $subject = 'Sent Team Request';
        $details = "Member Join Team request made by".$sourceName.' to '. $targetName;
        break;
      case CRM_Pcpteams_Constant::C_AT_LEAVE_TEAM:
        $subject = 'PCP Member Left Team';
        $details = "PCP Member Left Team ". $targetName;
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
  
  static function sendInviteEmail($message_template_id, $contact_id, $emailParams = array(), $teampcpId, $activityId ) {
    
    $mailParams = array();
    $contactParams = array();
    //create contact corresponding to each friend
    foreach ($emailParams['friend'] as $key => $details) {
      if ($details["first_name"]) {
        $contactParams[$key] = array(
          'first_name' => $details["first_name"],
          'last_name' => $details["last_name"],
          'contact_source' => ts('PCP Team Invite'),
          'email-Primary' => $details["email"],
        );

        $displayName = $details["first_name"] . " " . $details["last_name"];
        $mailParams['email'][$displayName] = $details["email"];
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
      $contact = CRM_Core_BAO_UFGroup::findContact($value, NULL, 'Individual');

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
    $mailParams['message'] = CRM_Utils_Array::value('suggested_message', $emailParams);
    $mailParams['messageTemplateID'] = $message_template_id;
    $mailParams['page_url'] = CRM_Utils_System::url('civicrm/pcp/manage', "reset=1&id={$teampcpId}", TRUE, NULL, FALSE, TRUE);
    self::sendMail($contact_id, $mailParams);
  }
  
  static function getPcpBlockId($eventId, $component = 'event') {
     if(empty($eventId)){
      return null;
    }
    $entity_table = CRM_PCP_BAO_PCP::getPcpEntityTable($component);
    $pcpBlock = new CRM_PCP_DAO_PCPBlock();
    $pcpBlock->entity_table = $entity_table;
    $pcpBlock->entity_id    = $eventId;
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
  
  static function hasPermission($pcpId, $loggedContactId, $action = CRM_Core_Permission::EDIT) {
    if(empty($pcpId)) {
      return NULL;
    }
    $pcpOwnerContactId  = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $pcpId, 'contact_id');
    $hasPermission      = FALSE;
    if(empty($loggedContactId)) {
      $loggedContactId = CRM_Pcpteams_Utils::getloggedInUserId();
    } 
    // Check the pcp page which he is looking is the owner of pcp, then allow 'edit' permission 
    if($pcpOwnerContactId == $loggedContactId) {
      $hasPermission = TRUE;
    } // Else if he is the memeber of the pcp , then allow 'view' permission
    else if ($action == CRM_Core_Permission::VIEW) { 
      // Find PCPs for this contact 
      $pcpQuery = "
        SELECT cps.id FROM civicrm_value_pcp_custom_set cps 
        INNER JOIN civicrm_pcp cp ON (cp.id = cps.entity_id)
        WHERE cps.team_pcp_id = %1 AND cp.contact_id = %2";
      $pcpQueryParams = array(
        1 => array($pcpId, 'Integer'),
        2 => array($loggedContactId, 'Integer'),
      );
      if(CRM_Core_DAO::singleValueQuery($pcpQuery, $pcpQueryParams)) {
          $hasPermission = TRUE;
      }
    }
    else {
        $query = "
          SELECT cr.id FROM civicrm_relationship cr
          INNER JOIN civicrm_relationship_type crt ON (crt.id = cr.relationship_type_id)
          WHERE cr.contact_id_a = %1 AND cr.contact_id_b = %2 AND cr.is_active = %3 AND crt.name_a_b = %4";

        $queryParams = array(
          1 => array($loggedContactId, 'Integer'),
          2 => array($pcpOwnerContactId, 'Integer'),
          3 => array(1, 'Integer'),
          4 => array(CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE, 'String'),
        );

        if(CRM_Core_DAO::singleValueQuery($query, $queryParams)) {
          $hasPermission = TRUE;
        }
      }
    return $hasPermission;
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
    foreach ($values['email'] as $displayName => $emailTo) {
      if ($emailTo) {
        // FIXME: factor the below out of the foreach loop
        CRM_Core_BAO_MessageTemplate::sendTemplate(
          array(
            'messageTemplateID' => $values['messageTemplateID'],
            'contactId' => $contactID,
            'tplParams' => array(
              'senderContactName' => $fromName,
              'pageURL' => $values['page_url'],
              'senderMessage' => $values['message']
            ),
            'from' => "$fromName <{$values['email_from']}>",
            'toName' => $displayName,
            'toEmail' => $emailTo,
            'replyTo' => $email,
          )
        );
      }
    }
  }

}
