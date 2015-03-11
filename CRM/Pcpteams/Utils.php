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
    $session    = CRM_Core_Session::singleton( );
    $contactID  = $session->get('userID'        );
    return $contactID;
  }

  // FIXME: 
  // 1. change function name to isUserTeamAdmin
  // 2. Ideally we need isUserTeamMember
  // 3. pure sql query
  // 4. Make this an API
  static function checkUserIsaTeamAdmin( $userId ){
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

  // FIXME:
  // 1. split function into two - 
  // A. civicrm_api('pcpteams', get, $params) - this already exist
  // B. civicrm_api('pcpteams', create, $params) 
  // 2. change all places to use the api instead of this function
  static function checkOrUpdateUserPcpGroup( $pcpId, $action = 'get', $params = array() ){
    if(empty($pcpId )){
      return NULL;
    }
    
    //get group Id from CustomGroup PCP_custom_set
    //CustomField name = 'Branch_or_partner'
    $cfId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', CRM_Pcpteams_Constant::C_CF_BRANCH_PARTNER, 'id', 'name');
    if($params['cfId']){
      $cfId = $params['cfId'];
    }
    
    if(!$cfId){
      return NULL;
    }
      
    $customParams = array(
      'version'   => 1,
      'entity_id' => $pcpId,
    );
    
    if($action == 'get') {
      $customParams['return.custom_'.$cfId] = 1;
    }
    
    if($action == 'create' ) {
      $customParams['custom_'.$cfId]  = $params['value'];
      
    }
    if(isset($params['id'])) {
      $customParams['id'] = $params['id'];
    }

    return civicrm_api3('CustomValue', $action, $customParams);
    
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
  // 1. Ideally we should do a pushUserContext() for dashboard and get rid of this one.
  static function pcpRedirectUrl($pageName, $qParams = array()){
    $url = CRM_Utils_System::url('civicrm/pcp/dashboard', 'reset=1');
    if($pageName){
      $url = CRM_Utils_System::url("civicrm/pcp/{$pageName}", 'reset=1');
    }

    if($qParams){
      foreach ($qParams as $key => $value) {
        $url .= sprintf("&%s=%s", $key, $value);
      }
    }

    CRM_Utils_System::redirect($url);
  }

  // FIXME:
  // 1. split it into get and create relationship apis
  // 2. We could have a wrapper on top of them as our own api if needed.
  /**
   * To check the valid relationship is exists., Create If not Found one.
   */
  static function checkORCreateTeamRelationship($iContactIdA, $iContactIdB, $checkandCreate = FALSE ){
    if(empty($iContactIdA) || empty($iContactIdB)){
      $status = empty($iContactIdB) ? 'Team Contact is Missing' : 'Team Member Contact Id is Missing';
      CRM_Core_Session::setStatus($status);
    }

    $teamRelTypeName = CRM_Pcpteams_Constant::C_TEAM_RELATIONSHIP_TYPE;
    $relTypeId       = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', $teamRelTypeName, 'id', 'name_a_b');

    //check the Relationship Type Exists
    if(empty($relTypeId)){
      CRM_Core_Session::setStatus( t('Failed To create Relationship. Relationship Type (%1) does not exist.', array('%1' => $teamRelTypeName)) );
    }else{

      //check the duplicates
      $aParams = array(
        'version'               => '3',
        'is_active'             => '1',
        'relationship_type_id'  => $relTypeId,
      );
      $bDuplicateFound = CRM_Contact_BAO_Relationship::checkDuplicateRelationship($aParams, $iContactIdA, $iContactIdB);

      if(!$bDuplicateFound && $checkandCreate){
        $aParams['contact_id_a'] = $iContactIdA;
        $aParams['contact_id_b'] = $iContactIdB;

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
}
