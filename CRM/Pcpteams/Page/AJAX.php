<?php

/**
 * This class contains all contact related functions that are called using AJAX (jQuery)
 */

class CRM_Pcpteams_Page_AJAX {
  
  static function getContactList($params){

    $where = null;
    if (!empty($params['contact_sub_type'])) {
      $contactSubType = CRM_Utils_Type::escape($params['contact_sub_type'], 'String');
      $where .= " AND contact_sub_type = '{$contactSubType}'";
    }
    
    //if get id from params
    if (!empty($params['id'])) {
      $where .= " AND id = " . (int) $params['id'];
    }
    
    //search name
    $name = $params['input'];
    $strSearch = "%$name%";
    if(isset($params['input'])){
      
      $where .= " AND display_name like '$strSearch'";
    }
    
    //query
    $query = "
      Select id, display_name, contact_type
      FROM civicrm_contact 
    ";
    
    //where clause
    if(!empty($where)){
      $query .= " WHERE (1) {$where}";
    }
    
    //LIMIT
    $query .= " LIMIT 0, 15";
    
    //execute query
    $dao = CRM_Core_DAO::executeQuery($query);
    while($dao->fetch()){
      $result['values'][] = array(
        'id'    =>  $dao->id,
        'label' =>  $dao->display_name,
        'icon_class' =>  $dao->contact_type,
      );
    }

    return $result;
  }
  
  static function unsubscribeTeam(){
    $entity_id    = CRM_Utils_Type::escape($_POST['entity_id'], 'Integer');
    $team_pcp_id  = CRM_Utils_Type::escape($_POST['team_pcp_id'], 'Integer');
    $teamPcpCfId  = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId(); 
    $params = array(
      'version'   => 3,
      'entity_id' => $entity_id,
      "custom_{$teamPcpCfId}" => NULL, 
    );
    $updatedResult = civicrm_api3('CustomValue', 'create', $params);
    if(!civicrm_error($updatedResult)){
      echo 'updated';
    }else{
      echo $updatedResult['error_message'];
    }
    
    CRM_Utils_System::civiExit();
  }
  
  static function leaveTeam(){
    $user_id      = CRM_Utils_Type::escape($_POST['user_id'], 'Integer');
    $team_pcp_id  = CRM_Utils_Type::escape($_POST['team_pcp_id'], 'Integer');
    $teamPcpCfId  = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId(); 
    $query = "
      Update civicrm_value_pcp_custom_set
      Set team_pcp_id = NULL
      Where team_pcp_id = {$team_pcp_id} AND entity_id IN ( SELECT id FROM civicrm_pcp WHERE contact_id = {$user_id} )
    ";
    $dao = CRM_Core_DAO::executeQuery($query);
    echo 'updated'; //FIXME : Need to display proper response
    
    CRM_Utils_System::civiExit();
  }
  
  static function inlineEditorAjax(){
    $eleId       = CRM_Utils_Type::escape($_POST['id'], 'String');
    $pcpId       = CRM_Utils_Type::escape($_POST['pcp_id'], 'Integer');
    $editedValue = CRM_Utils_Type::escape($_POST['value'], 'String');
    $columnfield = str_replace('pcp_', '', $eleId);

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
    $teamPcpCfId    = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
    $params = array(
      'version'   => 3,
      'entity_id' => $pcp_id,
      "custom_{$teamPcpCfId}" => $team_pcp_id,
    );
    $updatedResult = civicrm_api3('CustomValue', 'create', $params);
    if(!civicrm_error($updatedResult)){
    $result  = civicrm_api3('Relationship', 'delete', array(
      'sequential' => 1,
      'id'         => $entity_id,
      ));
      echo 'approved';
    }else{
      echo $updatedResult['error_message'];
    }
    CRM_Utils_System::civiExit();
  }  
  
  static function declineTeamMember(){
    $entity_id      = CRM_Utils_Type::escape($_POST['entity_id'], 'Integer');
    $updatedResult  = civicrm_api3('Relationship', 'delete', array(
      'sequential' => 1,
      'id'         => $entity_id,
      ));
    if(!civicrm_error($updatedResult)){
      echo 'declined';
    }else{
      echo $updatedResult['error_message'];
    }
    CRM_Utils_System::civiExit();
  } 
  
  static function removeTeamMember() {
    $pcp_id         = CRM_Utils_Request::retrieve('pcp_id', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
    $team_pcp_id    = CRM_Utils_Request::retrieve('team_pcp_id', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
    $teamPcpCfId    = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
    $params = array(
      'version'   => 3,
      'entity_id' => $pcp_id,
      "custom_{$teamPcpCfId}" => NULL, 
    );
    $updatedResult = civicrm_api3('CustomValue', 'create', $params);
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
    $teamPcpCfId    = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId(); 
    $params = array(
      'version'   => 3,
      'entity_id' => $pcp_id,
      "custom_{$teamPcpCfId}" => NULL, 
    );
    $updatedResult = civicrm_api3('CustomValue', 'create', $params);
    if(!civicrm_error($updatedResult)){
      echo 'deactivated';
    }else{
      echo $updatedResult['error_message'];
    }
    CRM_Utils_System::civiExit();
    
  }
  
  static function getEventList($params) {
  //Fixme: tidy up the codings
  //contact_sub_type
    $where = null;
    $params['sequential'] = 1;
    if (!empty($params['contact_sub_type'])) {
      $contactSubType = CRM_Utils_Type::escape($params['contact_sub_type'], 'String');
      
      $where .= " AND contact_sub_type = '{$contactSubType}'";
    }
    //if get id from params
    if (!empty($params['id'])) {
      $where .= " AND id = " . (int) $params['id'];
    }
    //search name
    $name = $params['input'];
    $strSearch = "%$name%";
    if(isset($params['input'])){
      
      $where .= " AND title like '$strSearch'";
    }
    //query
    $query = "
      Select id, title
      FROM civicrm_event
    ";
    //where clause
    if(!empty($where)){
      $query .= " WHERE (1) {$where}";
    }
    //LIMIT
    $query .= " LIMIT 0, 15";
    //execute query
    $dao = CRM_Core_DAO::executeQuery($query);
    while($dao->fetch()){
      $result[$dao->id] = array(
        'id'    =>  $dao->id,
        'label' =>  $dao->title,
      );
    }
    return $result;
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

