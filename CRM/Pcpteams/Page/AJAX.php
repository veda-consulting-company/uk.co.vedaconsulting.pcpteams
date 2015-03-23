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
    $entity_id    = $_POST['entity_id'];
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
    $entity_id = CRM_Utils_Request::retrieve('entity_id', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
    $pcp_id    = CRM_Utils_Request::retrieve('pcp_id', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
    $updatedResult  = civicrm_api3('Relationship', 'create', array(
      'sequential' => 1,
      'id'         => $entity_id,
      'is_active'  => 1,
      ));
      CRM_Core_Error::debug_var('$updatedResult', $updatedResult);
    if(!civicrm_error($updatedResult)){
      $result = civicrm_api('Pcpteams', 
        'getcontactpcp', 
        array(
          'contact_id' => $updatedResult['values'][0]['contact_id_b'],
          'version'    => 3
        )
      );
      if (!empty($result['id'])) {
        $teamPcpId = $result['id'];
      }
      $teamPcpCfId = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
      $params = array(
        'version'   => 3,
        'entity_id' => $pcp_id,
        "custom_{$teamPcpCfId}" => $teamPcpId,
      );
      $result = civicrm_api3('CustomValue', 'create', $params);
      echo 'approved';
    } else{
      echo $updatedResult['error_message'];
    }
    CRM_Utils_System::civiExit();
  }  
  
  static function declineTeamMember(){
    $entity_id      = $_POST['entity_id'];
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
    $entity_id      = CRM_Utils_Request::retrieve('entity_id', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
    $pcp_id         = CRM_Utils_Request::retrieve('pcp_id', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
    $team_pcp_id    = CRM_Utils_Request::retrieve('team_pcp_id', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
    $updateQuery    = "UPDATE `civicrm_value_pcp_custom_set` SET `team_pcp_id` = NULL WHERE entity_id = $pcp_id AND team_pcp_id = $team_pcp_id ";
    CRM_Core_DAO::executeQuery($updateQuery);
    $deleteActivity = "DELETE civicrm_activity WHERE id = $entity_id";
    CRM_Core_DAO::executeQuery($deleteActivity);
    echo 'Removed';
    CRM_Utils_System::civiExit();
  }
  
  static function deactivateTeamMember() {
    $entity_id      = CRM_Utils_Request::retrieve('entity_id', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
    $pcp_id         = CRM_Utils_Request::retrieve('pcp_id', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
    $team_pcp_id    = CRM_Utils_Request::retrieve('team_pcp_id', 'Positive', CRM_Core_DAO::$_nullObject, TRUE);
    $updateQuery    = "UPDATE `civicrm_value_pcp_custom_set` SET `team_pcp_id` = NULL WHERE entity_id = $pcp_id AND team_pcp_id = $team_pcp_id ";
    CRM_Core_DAO::executeQuery($updateQuery);
    $updatedResult  = civicrm_api3('Relationship', 'create', array(
      'sequential' => 1,
      'id'         => $entity_id,
      'is_active'  => 0,
      ));
    
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
}

