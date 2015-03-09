<?php

/**
 * FIXME
 * Pcp Utils,
 * to call and use the methods in all pcp forms / pages.
 */

class  CRM_Pcpteams_Utils {
  //Constants
  CONST C_PCP_CUSTOM_GROUP_NAME = 'PCP_Custom_Set',
        C_CUSTOM_GROUP_EXTENDS	= 'PCP',
        C_TEAM_RELATIONSHIP_TYPE= 'Team Member of',
        C_TEAM_ADMIN_REL_TYPE   = 'Team Admin of',
        C_PCP_TYPE              = 'pcp_type_20150219182347',
        C_CF_BRANCH_PARTNER     = 'Branch_or_Partner',
        C_PCP_ID                = 7;
  /**
   * to get the logged in User Id
   */
  static function getloggedInUserId(){
    $session    = CRM_Core_Session::singleton( );
    $contactID  = $session->get('userID'        );
    return $contactID;
  }
  
  static function checkUserIsaTeamAdmin( $userId ){
    if(empty($userId)){
      return NULL;
    }
    require_once 'CRM/Contact/BAO/Relationship.php';
    $getUserRelationships = CRM_Contact_BAO_Relationship::getRelationship( $userId, CRM_Contact_BAO_Relationship::CURRENT);
    // Team Admin Relationship
    $relTypeAdmin   = self::C_TEAM_ADMIN_REL_TYPE;
    $adminRelTypeId = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', $relTypeAdmin, 'id', 'name_a_b');
    
    foreach ($getUserRelationships as $value) {
      //check the user is admin of team. return team id if found one
      if( $value['relationship_type_id'] == $adminRelTypeId ){
        return array('id' => $value['contact_id_b'], 'state' => 'Team');
      }
    }
    
    return null;
    
  }  
  static function checkOrUpdateUserPcpGroup( $pcpId, $action = 'get' ){
    if(empty($pcpId )){
      return NULL;
    }
    
    //get group Id from CustomGroup PCP_custom_set
    //CustomField name = 'Branch_or_partner'
    $cfId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', self::C_CF_BRANCH_PARTNER, 'id', 'name');
    if(!$cfId){
      return NULL;
    }
      
    $params = array(
      'version'   => 1,
      'entity_id' => $pcpId,
      'return.custom_'.$cfId => 1
    );
    
    return civicrm_api3('CustomValue', $action, $params);
    
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

  /**
   * To check the valid relationship is exists., Create If not Found one.
   */
  static function checkORCreateTeamRelationship($iContactIdA, $iContactIdB, $checkandCreate = FALSE ){
    if(empty($iContactIdA) || empty($iContactIdB)){
      $status = empty($iContactIdB) ? 'Team Contact is Missing' : 'Team Member Contact Id is Missing';
      CRM_Core_Session::setStatus($status);
    }

    $teamRelTypeName = self::C_TEAM_RELATIONSHIP_TYPE;
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
}
