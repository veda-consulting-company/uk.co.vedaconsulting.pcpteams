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
}
