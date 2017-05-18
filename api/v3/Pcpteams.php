<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.5                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2014                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 * File for the CiviCRM APIv3 group functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_pcpteams
 * @copyright CiviCRM LLC (c) 2004-2014
 */

function civicrm_api3_pcpteams_create($params) {

  // since we are allowing html input from the user
  // we also need to purify it, so lets clean it up
  // $params['pcp_title']      = $pcp['title'];
  // $params['pcp_contact_id'] = $pcp['contact_id'];
  $htmlFields = array( 'intro_text', 'page_text', 'title' );
  foreach ( $htmlFields as $field ) {
    if ( ! empty($params[$field]) ) {
      $params[$field] = CRM_Utils_String::purifyHTML($params[$field]);
    }
  }
  $entity_table = CRM_PCP_BAO_PCP::getPcpEntityTable($params['page_type']);
  $pcpBlock = new CRM_PCP_DAO_PCPBlock();
  $pcpBlock->entity_table = $entity_table;
  $pcpBlock->entity_id = $params['page_id'];
  $pcpBlock->find(TRUE);
  $params['pcp_block_id'] = $pcpBlock->id;
  $params['goal_amount']  = CRM_Utils_Rule::cleanMoney($params['goal_amount']);

  // 1 -> waiting review
  // 2 -> active / approved (default for now)
  $params['status_id'] = CRM_Utils_Array::value('status_id', $params, 2);

  // active by default for now
  $params['is_active'] = CRM_Utils_Array::value('is_active', $params, 1);

  $pcp = CRM_Pcpteams_BAO_PCP::create($params, FALSE);
  
  //Custom Set
  $customFields = CRM_Core_BAO_CustomField::getFields('PCP', FALSE, FALSE,
    NULL, NULL, TRUE
  );
  $isCustomValueSet = FALSE;
  foreach ($customFields as $fieldID => $fieldValue) {
    list($tableName, $columnName, $cgId) = CRM_Core_BAO_CustomField::getTableColumnGroup($fieldID);
    if (!empty($params[$columnName]) || !empty($params["custom_{$fieldID}"])) {
      $isCustomValueSet = TRUE;
      //FIXME: to find out the custom value exists, set -1 as default now
      $params["custom_{$fieldID}_-1"] = !empty($params[$columnName]) ? $params[$columnName] : $params["custom_{$fieldID}"];
    }
  }
  if ($isCustomValueSet) {
    $params['custom'] = CRM_Core_BAO_CustomField::postProcess($params,
      $customFields,
      $pcp->id,
      'PCP'
    );
    CRM_Core_BAO_CustomValueTable::store($params['custom'], 'civicrm_pcp', $pcp->id);
  }
  //end custom set

  $values = array();
  @_civicrm_api3_object_to_array_unique_fields($pcp, $values[$pcp->id]);
  return civicrm_api3_create_success($values, $params, 'Pcpteams', 'create');
}
function _civicrm_api3_pcpteams_create_spec(&$params) {
  $params['title']['api.required'] = 1;
  $params['contact_id']['api.required'] = 1;
}

function civicrm_api3_pcpteams_get($params) {
  $permParams = array(
    'pcp_id' => $params['pcp_id'],
    'team_pcp_id' => isset($params['team_pcp_id']) ? $params['team_pcp_id'] : NULL,
  );
  if (!_civicrm_pcpteams_permission_check($permParams, CRM_Core_Permission::VIEW)
    // && !_civicrm_pcpteams_permission_check($permParams, CRM_Pcpteams_Constant::C_PERMISSION_MEMBER)
    ) {
    return civicrm_api3_create_error('insufficient permission to view this record');
  }
  
  $dao = new CRM_PCP_DAO_PCP();
  $result = CRM_Core_DAO::$_nullArray;
  
 
  // FIXME: need to enforce type check
  $dao->id = $params['pcp_id']; // type check done by getfields
  $dao->pcp_id = $dao->id;

  //'@' this suppress the notice message, because
  // _civicrm_api_get_entity_name_from_dao returns the entity as 'p_c_p_id' which is undefined in PCP DAO fields
  //ref:  _civicrm_api_get_entity_name_from_dao($bao); in api/v3/utils.php (801)
  $result = @_civicrm_api3_dao_to_array($dao);
  
  //this dao_to_array suppressing the contact_id because 
  //the field_name check fails 'pcp_contact_id' == 'contact_id' in _civicrm_api3_dao_to_array()
  $result[$dao->id]['contact_id']    = $dao->contact_id;
  
  // Append custom info
  // Note: This should ideally be done in _civicrm_api3_dao_to_array, but since PCP is not one of 
  // recongnized entity in core, we can append it seprately for now.
  _civicrm_api3_pcpteams_custom_get($result);
  
  //custom data with customfield names, to avoid reusing the custom field id everytime
  _civicrm_api3_pcpteams_getCustomData($result);
  
  //append custom data page_tile, amount_raised_sofar, is_teampage, image_url, donate_url
  _civicrm_api3_pcpteams_getMoreInfo($result);
  
  return civicrm_api3_create_success($result, $params);
}
function _civicrm_api3_pcpteams_get_spec(&$params) {
  $params['pcp_id']['api.required'] = 1;
}

function civicrm_api3_pcpteams_delete($params) {
  //check permission to delete 
  $permParams = array(
    'pcp_id' => $params['id'],
  );
  if (!_civicrm_pcpteams_permission_check($permParams, CRM_Core_Permission::EDIT)) {
    return civicrm_api3_create_error('insufficient permission to delete this record');
  }
  
  $result = array();
  
  CRM_PCP_BAO_PCP::deleteById($params['id']);
  return civicrm_api3_create_success($result, $params);
}
function _civicrm_api3_pcpteams_delete_spec(&$params) {
  $params['id']['api.required'] = 1;
}

/*
  FIXME: using getfields removes the required check
function civicrm_api3_pcpteams_getfields($params) {
  return civicrm_api3_create_success(array(
    'pcp_id' => array(
      'name'     => 'pcp_id',
      'type'     => 1,
    ),
    'contact_id' => array(
      'name'     => 'contact_id',
      'type'     => 1,
      'required' => 1,
    ),
  ));
}
 */

function _civicrm_api3_pcpteams_custom_get(&$params) {
  foreach ($params as $rid => $rval) {
    _civicrm_api3_custom_data_get($params[$rid], FALSE, 'PCP', $rid);
    // FIXME: we should at some point replace "custom_xy_" with column-names
  }
}

function civicrm_api3_pcpteams_getpcpblock($params) {
  $dao = new CRM_PCP_DAO_PCPBlock();
  // FIXME: need to enforce type check
  $dao->entity_id = $params['entity_id']; // type check done by getfields
  $result         = _civicrm_api3_dao_to_array($dao);

  return civicrm_api3_create_success($result, $params);
}
function _civicrm_api3_pcpteams_getpcpblock_spec(&$params) {
  $params['entity_id']['api.required'] = 1;
}

function civicrm_api3_pcpteams_getallpagesbyevent($params) {
  $dao = new CRM_PCP_DAO_PCP();
  $dao->page_id = $params['page_id'];
  
  //'@' this suppress the notice message, because
  // _civicrm_api_get_entity_name_from_dao returns the entity as 'p_c_p_id' which is undefined in PCP DAO fields
  //ref:  _civicrm_api_get_entity_name_from_dao($bao); in api/v3/utils.php (801)
  $result = @_civicrm_api3_dao_to_array($dao);
  return civicrm_api3_create_success($result, $params);
}
function _civicrm_api3_pcpteams_getallpagesbyevent_spec(&$params) {
  $params['page_id']['api.required'] = 1;
}

function civicrm_api3_pcpteams_getContactList($params) {
  //Fixme: tidy up the codings
  //contact_sub_type
    $where = null;
    $params['sequential'] = 1;
    if (!empty($params['contact_sub_type'])) {
      $contactSubType = CRM_Utils_Type::escape($params['contact_sub_type'], 'String');
      
      $where .= " AND cc.contact_sub_type = '{$contactSubType}'";
    }
    
    //if get id from params
    if (!empty($params['id'])) {
      $where .= " AND id = " . (int) $params['id'];
    }
    
    //search name
    $name = $params['input'];
    $strSearch = "%$name%";
    if(isset($params['input'])){
      
      $where .= " AND cc.display_name like '$strSearch'";
    }
    
    if(isset($params['event_id'])) {
      $adminRelTypeName = CRM_Utils_Type::escape(CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE, 'String');
      $where .= " AND crt.name_a_b = '{$adminRelTypeName}' AND cp.page_id = " . (int) $params['event_id'];
    }
    //query
    $query = "
      Select cc.id, cc.display_name, cc.contact_type
      FROM civicrm_contact cc
    ";
     
    if (isset($params['contact_sub_type']) && $params['contact_sub_type'] == 'Team') {
      $query = "
        SELECT cc.id, CONCAT(cc.display_name, ' ( Admin: ', admin.display_name, ' )') as display_name, cc.contact_type
        FROM civicrm_contact cc
        INNER JOIN civicrm_pcp cp ON cp.contact_id = cc.id
        INNER JOIN civicrm_relationship cr ON cc.id = cr.contact_id_b
        INNER JOIN civicrm_contact admin ON admin.id = cr.contact_id_a
        INNER JOIN civicrm_relationship_type crt ON crt.id = cr.relationship_type_id
      ";
    }
    
    //where clause
    if(!empty($where)){
      $query .= " WHERE (1) AND cc.is_deleted = 0 {$where}";
    }
    
    $teamListLimit = CRM_Pcpteams_Utils::getPcpTeamSettings(CRM_Pcpteams_Constant::C_TEAM_LIST_LIMIT);
    // if constant is set to anything other than zero, apply limit
    if ($teamListLimit) {
      $query .= " LIMIT " . $teamListLimit;
    }
    //execute query
    CRM_Core_Error::debug_var('getcontactlist $query', $query);
    $dao = CRM_Core_DAO::executeQuery($query);
    while($dao->fetch()){
      $result[$dao->id] = array(
        'id'    =>  $dao->id,
        'label' =>  $dao->display_name,
        'icon_class' =>  $dao->contact_type,
      );
    }

  return civicrm_api3_create_success($result, $params, 'pcpteams');
}
// Get all pcp related info for the contact id
function civicrm_api3_pcpteams_getContactPcp($params) {
  $permParams = array(
    'contact_id' => $params['contact_id'],
  );
  if (!_civicrm_pcpteams_permission_check($permParams, CRM_Core_Permission::VIEW)) {
    return civicrm_api3_create_error('insufficient permission to view this record');
  }
  
  $dao = new CRM_PCP_DAO_PCP();
  $dao->contact_id = $params['contact_id']; 
  $dao->is_active  = (isset($params['is_active'])) ? $params['is_active'] : NULL; 
  $result = @_civicrm_api3_dao_to_array($dao);
  _civicrm_api3_pcpteams_custom_get($result);
  
  $pcpDashboardValues = array();
  foreach ($result as $pcpId => $value) {
    $pcpResult = civicrm_api('pcpteams', 'get', array(
      'version'     => 3,
      'sequential'  => 1,
      'pcp_id'      => $pcpId
      )
    );

    $result[$pcpId]                  = $pcpResult['values'][0];
    $result[$pcpId]['pcpId']         = $pcpId;
    $result[$pcpId]['goal_amount']   = $pcpResult['values'][0]['goal_amount'];
    $result[$pcpId]['amount_raised'] = $pcpResult['values'][0]['amount_raised'];
    $result[$pcpId]['page_title']    = $pcpResult['values'][0]['page_title'];
    $result[$pcpId]['isTeamExist']   = $value['isTeamExist'] = $pcpResult['values'][0]['is_teampage'];
    $result[$pcpId]['action']        = _getPcpDashboardActionLink($value);
    $result[$pcpId]['page_url']      = _civicrm_api3_pcpteams_getDigitalPageUrl($pcpId);
  }

  return civicrm_api3_create_success($result, $params);
}

function _civicrm_api3_pcpteams_getContactPcp_spec(&$params) {
  $params['contact_id']['api.required'] = 1;
}

function _getPcpDashboardActionLink($params){
  $active     = $params['is_active'] ? 'disable' : 'enable';
  $updateLabel= $params['isTeamExist'] ? 'Change' : 'Join';
  
  //action URLs
  $manageURL  = CRM_Utils_System::url('civicrm/pcp/manage', "id={$params['id']}"); 
  $pageURL    = CRM_Utils_System::url('civicrm/pcp/page', "reset=1&component=event&id={$params['id']}"); 
  $updateURL  = CRM_Utils_System::url('civicrm/pcp/info', "action=browse&component=event&id={$params['id']}"); 
  $disableURL = CRM_Utils_System::url('civicrm/pcp'     , "action={$active}&reset=1&component=event&id={$params['id']}"); 
  $deleteURL  = CRM_Utils_System::url('civicrm/pcp'     , "action=delete&reset=1&component=event&id={$params['id']}");
  $active     = ucwords($active);
  
  if(empty($params['is_active'])) {
    $action     = "
    <span>
      <a href=\"{$disableURL}\" class=\"action-item crm-hover-button\" title=\"$active\" >{$active}</a>
    </span>
    <span class='btn-slide crm-hover-button' style='display: none;'>more
      <ul class='panel'>
        <li>
          <a href=\"{$disableURL}\" class=\"action-item crm-hover-button\" title=\"$active\" >{$active}</a>
        </li>
      </ul>
    </span>
  ";
    return $action;
  } 
  //create and join team URLs
  $joinTeamURl    = CRM_Utils_System::url('civicrm/pcp/support', "reset=1&pageId={$params['page_id']}&component=event&code=cpftn");
  $createTeamURl  = CRM_Utils_System::url('civicrm/pcp/support', "reset=1&pageId={$params['page_id']}&component=event&code=cpftn&option=1");
  
  //FIXME : check User permission and return action based on permission
  $action     = "
    <span>
      <a href=\"{$manageURL}\" class=\"action-item crm-hover-button\" title='Manage' >Manage</a>
    </span>
    <span class='btn-slide crm-hover-button' style='display: none;'>more
      <ul class='panel'>
        <li>
          <a href=\"{$joinTeamURl}\" class=\"action-item crm-hover-button\" title='{$updateLabel} Team' >{$updateLabel} Team</a>
        </li>        

        <li>
          <a href=\"{$createTeamURl}\" class=\"action-item crm-hover-button\" title='Create Team' >Create a New Team</a>
        </li>
     
        <li>
          <a href=\"{$updateURL}\" class=\"action-item crm-hover-button\" title='Update Contact Information' >Update Contact Information</a>
        </li>

        <li>
          <a href=\"{$disableURL}\" class=\"action-item crm-hover-button\" title=\"$active\" >{$active}</a>
        </li>
        <li>
          <a href=\"{$deleteURL}\" class=\"action-item crm-hover-button small-popup\" title='Delete' onclick = \"return confirm('Are you sure you want to delete this Personal Campaign Page?\nThis action cannot be undone.');\">Delete</a>
        </li>
      </ul>
    </span>
  ";

  return $action;
}

function civicrm_api3_pcpteams_getMyTeamInfo($params) {
  
  //check the contact id is the logged in userId
  $permParams = array(
    'contact_id' => $params['contact_id'],
  );
  if (!_civicrm_pcpteams_permission_check($permParams, CRM_Core_Permission::VIEW)) {
    return civicrm_api3_create_error('insufficient permission to view this record');
  }
  
  $dao = new CRM_PCP_DAO_PCP();
  $dao->contact_id = $params['contact_id']; 
  $result = @_civicrm_api3_dao_to_array($dao);
  _civicrm_api3_pcpteams_custom_get($result);
  _civicrm_api3_pcpteams_getCustomData($result);
  
  $cfTeamPcpId = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
  $pcpDashboardValues = $teamIds = array();
  foreach ($result as $pcpId => $value) {
    if(isset($value['team_pcp_id'])){
      $teamIds[$pcpId] = $value['team_pcp_id'];
    } 
  }
  if(!empty($teamIds)){
    $sTeamIds = implode(', ', array_filter($teamIds));
    $query = "
      SELECT  ce.title    as page_title 
      , cp.id             as pcp_id  
      , cp.contact_id     as pcp_contact_id  
      , cc.display_name   as team_name  
      , cp.title          as pcp_title  
      , cp.goal_amount    as pcp_goal_amount  
      , cp.page_id        as page_id
      FROM civicrm_pcp cp 
      INNER JOIN civicrm_event ce ON ce.id = cp.page_id
      INNER JOIN civicrm_contact cc ON ( cc.id = cp.contact_id AND cc.is_deleted = 0 )
      INNER JOIN civicrm_value_pcp_custom_set cpcs ON ( cpcs.team_pcp_id = cp.id )
      WHERE cp.id IN ( $sTeamIds )
    ";
    $dao = CRM_Core_DAO::executeQuery($query);
    while($dao->fetch()){
      $myPcpId = array_search($dao->pcp_id, $teamIds); 
      $relTypeAdmin       = CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE;
      $adminRelTypeId     = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', $relTypeAdmin, 'id', 'name_a_b');
      $relationshipQuery  = "SELECT id FROM civicrm_relationship where contact_id_a = %1 AND contact_id_b = %2 AND relationship_type_id = %3";
         
      $queryParams = array(
        1 => array($params['contact_id'], 'Integer'),
        2 => array($dao->pcp_contact_id, 'Integer'),
        3 => array($adminRelTypeId, 'Integer'),
      );
      $relationship =  CRM_Core_DAO::singleValueQuery($relationshipQuery, $queryParams);
      $role         = $relationship ? 'Admin' : 'Member';
      
      $pcpResult = civicrm_api('pcpteams', 'get', array(
        'version' => 3
        , 'sequential'  => 1
        , 'pcp_id'      => $dao->pcp_id
        )
      );
     
      $teamResult[$myPcpId] = array(
        'teamName'      => $dao->team_name,
        'my_pcp_id'     => $myPcpId,
        'my_pcp_title'  => $pcpResult['values'][0]['page_title'],
        'teamPcpTitle'  => $dao->pcp_title,
        'pageTitle'     => $dao->page_title,
        'teamgoalAmount'=> $dao->pcp_goal_amount,
        'amount_raised' => $pcpResult['values'][0]['amount_raised'],
        'teamPcpId'     => $dao->pcp_id,
        'contactId'     => $dao->pcp_contact_id,
        'action'        => _getTeamInfoActionLink($myPcpId, $dao->pcp_id, $role),
        'role'          => $role,
      );
    }
    return civicrm_api3_create_success($teamResult, $params);
  }
  
}

function _civicrm_api3_pcpteams_getMyTeamInfo_spec(&$params) {
  $params['contact_id']['api.required'] = 1;
}

function _getTeamInfoActionLink($entityId, $teamPcpId, $role){
  
  //action URLs
  $pageURL    = CRM_Utils_System::url('civicrm/pcp/page', "reset=1&component=event&id={$teamPcpId}"); 
  $span = "
    <span>
      <a href=\"{$pageURL}\" class=\"action-item crm-hover-button\" title='URL for this Page' >View Page</a>
    </span>";
  if($role == 'Admin') {
    $editURL    = CRM_Utils_System::url('civicrm/pcp/info', "action=update&component=event&id={$teamPcpId}"); 
    $manageURL  = CRM_Utils_System::url('civicrm/pcp/manage', "id={$teamPcpId}"); 
    $span = " <span>
      <a href=\"{$manageURL}\" class=\"action-item crm-hover-button\" title='Manage' >Manage</a>
    </span>";
  }
  
  //FIXME : check User permission and return action based on permission
  $action     = $span."
    <span class='btn-slide crm-hover-button'>more
      <ul class='panel'>
        <li>
          <a href='javascript:void(0)' class=\"action-item crm-hover-button\" title='Leave Team' onclick='unsubscribeTeam({$entityId}, {$teamPcpId});'>Leave Team</a>
        </li>        
      </ul>
    </span>
  ";

  return $action;
}

function civicrm_api3_pcpteams_getMyPendingTeam($params) {
  $result= CRM_Core_DAO::$_nullArray;
  
  // $checkPermission = TRUE;
  // if (isset($params['checkPermission'])) {
  //   $checkPermission = $params['checkPermission'];
  // }
  //check the contact id is the logged in userId
  $permParams = array(
    'contact_id' => $params['contact_id'],
  );
  if (!_civicrm_pcpteams_permission_check($permParams, CRM_Core_Permission::VIEW)) {
    return civicrm_api3_create_error('insufficient permission to view this record');
  }
  
  $contact_id_a = $params['contact_id']; 
  $relTypeId    = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType',  CRM_Pcpteams_Constant::C_TEAM_RELATIONSHIP_TYPE, 'id', 'name_a_b');
  $teamQueryParams  = array(1 => array($contact_id_a, 'Int'), 2 => array($relTypeId, 'Int'));

  if(!empty($contact_id_a) && !empty($relTypeId)) {
    $query = "
      SELECT  ce.title    as page_title 
      , cp.id             as pcp_id  
      , cp.contact_id     as pcp_contact_id  
      , cc.display_name   as team_name  
      , cp.title          as pcp_title  
      , cp.goal_amount    as pcp_goal_amount
      , cr.id             as relationship_id 
      FROM civicrm_pcp cp 
      INNER JOIN civicrm_event ce ON ce.id = cp.page_id
      INNER JOIN civicrm_contact cc ON ( cc.id = cp.contact_id AND cc.is_deleted = 0 )
      INNER JOIN civicrm_relationship cr ON ( cr.contact_id_b = cp.contact_id AND cr.relationship_type_id = %2 )
      WHERE cr.contact_id_a = %1 AND cr.is_active = 0
    ";
    $dao = CRM_Core_DAO::executeQuery($query, $teamQueryParams);
    while($dao->fetch()){
      $result[] = array(
        'teamName'      => $dao->team_name,
        'teamPcpTitle'  => $dao->pcp_title,
        'pageTitle'     => $dao->page_title,
        'teamgoalAmount'=> $dao->pcp_goal_amount,
        'amount_raised' => civicrm_api3_pcpteams_getAmountRaised(array('pcp_id' => $dao->pcp_id, 'version' => 3)),
        'teamPcpId'     => $dao->pcp_id,
        'contactId'     => $dao->pcp_contact_id,
        'relationship_id'=> $dao->relationship_id,
      );
    }
  }
  return civicrm_api3_create_success($result, $params);
}

function civicrm_api3_pcpteams_getTeamRequest($params) {
  $result= CRM_Core_DAO::$_nullArray;
  
  //check the contact id is the logged in userId
  $permParams = array(
    'contact_id' => $params['contact_id'],
  );
  if (!_civicrm_pcpteams_permission_check($permParams, CRM_Core_Permission::VIEW)) {
    return civicrm_api3_create_error('insufficient permission to view this record');
  }

  $query = "
    SELECT  
      ce.title            as page_title, 
      mpcp.id             as pcp_id,  
      rmem.id             as rel_id,  
      mem.id              as pcp_contact_id,  
      mem.display_name    as display_name,  
      team.display_name   as team_display_name,  
      mpcp.title          as pcp_title,  
      tpcp.id             as team_pcp_id,  
      tpcp.title          as team_pcp_title,  
      mpcp.goal_amount    as pcp_goal_amount,  
      meme.email          as email,  
      mema.city           as city,
      mems.name           as state,  
      mema.name           as country
    FROM
      civicrm_relationship radmin
      INNER JOIN civicrm_relationship_type rat ON rat.id = radmin.relationship_type_id
      INNER JOIN civicrm_relationship rmem ON (radmin.contact_id_b = rmem.contact_id_b AND rmem.is_active = 0)
      INNER JOIN civicrm_relationship_type rmt ON rmt.id = rmem.relationship_type_id
      INNER JOIN civicrm_contact mem ON mem.id = rmem.contact_id_a
      INNER JOIN civicrm_contact team ON team.id = rmem.contact_id_b
      INNER JOIN civicrm_value_pcp_relationship_set rset ON rset.entity_id = rmem.id
      INNER JOIN civicrm_pcp mpcp ON mpcp.id = rset.pcp_a_b
      INNER JOIN civicrm_event ce ON ce.id = mpcp.page_id AND mpcp.page_type = 'event'
      INNER JOIN civicrm_pcp tpcp ON tpcp.id = rset.pcp_b_a
      LEFT JOIN civicrm_email meme ON ( meme.contact_id = mem.id )
      LEFT JOIN civicrm_address mema ON ( mema.contact_id = mem.id )
      LEFT JOIN civicrm_country memc ON ( mema.country_id = memc.id )
      LEFT JOIN civicrm_state_province mems ON ( mema.state_province_id = mems.id )
    WHERE radmin.contact_id_a = %1 AND rat.name_a_b = %2 AND rmt.name_a_b = %3";
  
  $queryParams = array( 
    1 => array($params['contact_id'], 'Integer'),
    2 => array(CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE, 'String'),
    3 => array(CRM_Pcpteams_Constant::C_TEAM_RELATIONSHIP_TYPE, 'String'),
  );
    
  $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
  while ($dao->fetch()) {
    $result[$dao->pcp_id] = array(
      'member_display_name' => $dao->display_name,
      'member_email'        => $dao->email,
      'member_city'         => $dao->city,
      'member_country'      => $dao->country,
      'member_state_province_id'  => $dao->state ,
      'member_pcp_id'             => $dao->pcp_id,
      'member_pcp_title'          => $dao->pcp_title,
      'member_page_title'         => $dao->page_title,
      'contactId'                 => $dao->pcp_contact_id,
      'team_display_name'         => $dao->team_display_name,
      'team_pcp_title'            => $dao->team_pcp_title,
      'action'                    => _getTeamRequestActionLink($dao->rel_id, $dao->pcp_id, $dao->team_pcp_id),
    );
  }
  return civicrm_api3_create_success($result, $params);
}

function _getTeamRequestActionLink($relationshipId, $pcpId, $teampcpId ){
  $action     = "
    <span>
      <a href='javascript:void(0)' class=\"action-item crm-hover-button\" title='Approve Team Member' onclick='approveTeamMember({$relationshipId},{$pcpId},{$teampcpId});'>Approve</a>
      <a href='javascript:void(0)' class=\"action-item crm-hover-button\" title='Decline Team Member' onclick='declineTeamMember({$relationshipId}, {$pcpId}, {$teampcpId});'>Decline</a>
    </span>
    ";
  return $action;
}

function _civicrm_api3_pcpteams_getCustomData(&$params) {
  $result = array();
  foreach ($params as $pcpId => $pcpValues) {
    foreach ($pcpValues as $fieldName => $values) {
      $explodeFieldName = explode('_', $fieldName);
      if($explodeFieldName[0] == 'custom'){
        $column_name = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', $explodeFieldName[1], 'column_name');
        $params[$pcpId][$column_name] = $values;
        if($column_name == 'team_pcp_id'){
          $params[$pcpId]['team_pcp_name'] = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $values, 'title' );
        }elseif (isset($explodeFieldName[2]) && $explodeFieldName[2] == 'id') {
          $column_name = str_replace('id', 'name', $column_name);
          $params[$pcpId][$column_name] = $pcpValues['custom_'.$explodeFieldName[1]];
        }else{
          $column_name .= '_label';
          $ogId  = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', $explodeFieldName[1], 'option_group_id');
          $ovDao = new CRM_Core_DAO_OptionValue;
          $ovDao->option_group_id = $ogId;
          $ovDao->value = $values;
          $ovDao->find(TRUE);
          $params[$pcpId][$column_name] = $ovDao->label;
        }
      }
    }
  }
}
// Get team members for logged in user(team admin)  
function civicrm_api3_pcpteams_getTeamMembers($params) {
  $result= CRM_Core_DAO::$_nullArray;
  
  //check the contact id is the logged in userId
  $permParams = array(
    'contact_id' => $params['contact_id'],
  );
  if (!_civicrm_pcpteams_permission_check($permParams, CRM_Core_Permission::VIEW)) {
    return civicrm_api3_create_error('insufficient permission to view this record');
  }
  
  $query = "
    SELECT 
    mp.id as member_pcp_id,
    tp.id as team_pcp_id,
    mc.id as member_contact_id,
    mc.display_name as member_display_name,
    tc.display_name as team_display_name
    FROM civicrm_value_pcp_custom_set cs
    INNER JOIN civicrm_pcp mp ON mp.id = cs.entity_id
    INNER JOIN civicrm_contact mc ON mc.id = mp.contact_id
    INNER JOIN civicrm_pcp tp ON tp.id = cs.team_pcp_id
    INNER JOIN civicrm_contact tc ON tc.id = tp.contact_id
    INNER JOIN civicrm_relationship cr ON cr.contact_id_b = tc.id
    INNER JOIN civicrm_relationship_type crt on crt.id = cr.relationship_type_id
    WHERE cr.contact_id_a = %1 AND crt.name_a_b = %2 AND mc.id != cr.contact_id_a";
  
  $queryParams = array( 
    1 => array($params['contact_id'], 'Integer'),
    2 => array(CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE, 'String'),
  );
  
  $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
  while ($dao->fetch()) {
    $result[$dao->member_pcp_id] = array(
      'my_pcp_id'  => $dao->member_pcp_id,
      'team_pcp_id'=> $dao->team_pcp_id,
      'member_id'  => $dao->member_contact_id,
      'memberName' => $dao->member_display_name,
      'type'       => 'No',
      'teamName'   => $dao->team_display_name,
      'action'     => _getTeamMemberActionLink(5, $dao->member_pcp_id, $dao->team_pcp_id),
    );
  }
  return civicrm_api3_create_success($result, $params);
}

function _civicrm_api3_pcpteams_getTeamMemberPCPIds($params){
  
  if (isset($params['teams'])) {
    $teamPcpId = implode(', ', $params['teams']);
    $selectClause = "entity_id as pcp_id";
    $whereClause  = "team_pcp_id IN ( {$teamPcpId} )";
    if (empty($teamPcpId)) {
      return array();
    }
  } else {
    
    if(empty($params['pcp_id'])){
      return CRM_Core_DAO::$_nullArray;
    }
    
    $params['contact_id'] = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $params['pcp_id'], 'contact_id');
    
    $contactSubType = CRM_Contact_BAO_Contact::getContactTypes($params['contact_id']);
    if (in_array(CRM_Pcpteams_Constant::C_CONTACT_SUB_TYPE_TEAM, $contactSubType)) {
      $teamPcpId = $params['pcp_id'];
      $selectClause = "entity_id as pcp_id";
      $whereClause  = "team_pcp_id IN ( {$teamPcpId} )";
    } elseif (in_array('Individual', $contactSubType)) {
      $selectClause = "team_pcp_id as pcp_id";
      $whereClause  = "entity_id = {$params['pcp_id']}";
    }
    
  }

  $query = "
    SELECT {$selectClause} 
    FROM civicrm_value_pcp_custom_set 
    WHERE {$whereClause}
  ";
  $dao = CRM_Core_DAO::executeQuery($query);
  $ids = array();
  while ($dao->fetch()) {
    $ids[] = $dao->pcp_id;
  }
  if (!isset($params['teams']) && in_array('Individual', $contactSubType)){
    $params['teams'] = $ids;
    $ids = _civicrm_api3_pcpteams_getTeamMemberPCPIds($params);
  }
  
  return $ids;
}

function civicrm_api3_pcpteams_getTeamMembersInfo($params){
  $result= CRM_Core_DAO::$_nullArray;
  //check the hasPermission to view details
  $permParams = array(
    'pcp_id' => $params['pcp_id'],
  );
  if (!_civicrm_pcpteams_permission_check($permParams, CRM_Core_Permission::VIEW)) {
    return civicrm_api3_create_error('insufficient permission to view this record');
  }
  
  $teamMemberPcpIds = _civicrm_api3_pcpteams_getTeamMemberPCPIds($params);
  
  if(empty($teamMemberPcpIds)){
     return CRM_Core_DAO::$_nullArray; 
  }
  $teamMemberPcpIds   = implode(', ', $teamMemberPcpIds);
  $relTypeAdmin       = CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE;
  $adminRelTypeId     = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', $relTypeAdmin, 'id', 'name_a_b');
  $teamPcpContactId   = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $params['pcp_id'], 'contact_id');
  $where              = NULL;
  if (isset($params['contact_id'])) {
    $where = " AND cp.contact_id = {$params['contact_id']}";
  }
  $query = "
    SELECT cp.id        as pcp_id
      , cp.page_id      as page_id
      , cp.contact_id   as contact_id
      , cp.goal_amount  as goal_amount
      , cc.display_name as display_name
      , cpcs.team_pcp_id as team_pcp_id
      , CASE 
        WHEN cr.relationship_type_id = {$adminRelTypeId} AND cr.contact_id_b = {$teamPcpContactId} THEN '1' ELSE '0'
        END             as is_team_admin
    FROM civicrm_pcp cp
    LEFT JOIN civicrm_value_pcp_custom_set cpcs ON (cpcs.entity_id = cp.id)
    LEFT JOIN civicrm_contact cc ON (cc.id = cp.contact_id AND cc.is_deleted = 0)
    LEFT JOIN civicrm_relationship cr ON (cr.contact_id_a = cp.contact_id AND cr.is_active = 1)
    where cp.id IN ( $teamMemberPcpIds ) {$where}
  ";
  $dao = CRM_Core_DAO::executeQuery($query);
  while ($dao->fetch()) {
    $members[$dao->pcp_id] = $dao->toArray();
  }
  //amount raised and total donations count
  foreach ($members as $memberPcpId => $values) {
    $getAllDonations            = civicrm_api3_pcpteams_getAllDonations(array('page_id' => $values['page_id'], 'pcp_id' => $values['pcp_id'], 'team_pcp_id' => $values['team_pcp_id']));
    $values ['donations_count'] = $getAllDonations['count'];
    $result[$memberPcpId]  = $values;
  }
  //donation URL and more info
  _civicrm_api3_pcpteams_getMoreInfo($result);
  
  //Sort By Amount Raised
  _civicrm_api3_pcpteams_sortby_amount_raised($result);
  
  return civicrm_api3_create_success($result, $params);
}

function _civicrm_api3_pcpteams_getTeamMembersInfo_spec(&$params) {
  $params['pcp_id']['api.required'] = 1;
}

function _getTeamMemberActionLink($activityId, $myPcpId, $teamPcpId){
  $action     = "
    <span>
      <a href='javascript:void(0)' class=\"action-item crm-hover-button\" title='Remove From Team' onclick='removeTeamMember({$myPcpId},{$teamPcpId} );'>Remove</a>
    </span>
    ";
  return $action;
}

function civicrm_api3_pcpteams_getEventList($params) {
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
      
      $where .= " AND title like '$strSearch' AND is_active = 1";
      // find only events ending in the future
      $endDate = date('YmdHis');
      $currentandfuture .= "
        AND ( `end_date` >= {$endDate} OR
          (
            ( end_date IS NULL OR end_date = '' ) AND start_date >= {$endDate}
          )
        )";
      $where .= $currentandfuture;
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

  return civicrm_api3_create_success($result, $params, 'pcpteams');

}


function civicrm_api3_pcpteams_getRank($params) {
  $result= CRM_Core_DAO::$_nullArray;
  //check the hasPermission to view details
  $permParams = array(
    'pcp_id' => $params['pcp_id'],
  );
  if (!_civicrm_pcpteams_permission_check($permParams, CRM_Core_Permission::VIEW)) {
    return civicrm_api3_create_error('insufficient permission to view this record');
  }
  
  $dao = new CRM_PCP_DAO_PCP();
  $dao->page_id = $params['page_id'];
  
  //dao result
  $result = @_civicrm_api3_dao_to_array($dao);
  $pcpAmounts = array();
  foreach ($result as $pcps) {
    $pcpAmounts[$pcps['id']] = civicrm_api3_pcpteams_getAmountRaised(array('pcp_id' => $pcps['id'], 'version' => 3));
  }
  //remove pcps doesn't have donations
  arsort($pcpAmounts);
  
  //get count the pages has donations 
  $hasDonations = count(array_filter($pcpAmounts));
  
  //check this pcp has some donations
  if ($pcpAmounts[$params['pcp_id']] == null) {
    $rank = $hasDonations + 1;
  }else{
    $rank = array_search($params['pcp_id'], array_keys($pcpAmounts)) + 1;
  }
  
  switch ($rank % 10) {
    // Handle 1st, 2nd, 3rd
    case 1:  $suffix = 'st'; break;
    case 2:  $suffix = 'nd'; break;
    case 3:  $suffix = 'rd'; break;
    default: $suffix = 'th'; break;
  }
  
  $rankResult[$params['page_id']]['rank']         = $rank;
  $rankResult[$params['page_id']]['suffix']       = $suffix;
  $rankResult[$params['page_id']]['pcp_id']       = $params['pcp_id'];
  $rankResult[$params['page_id']]['rankHolder']   = $result[$params['pcp_id']]['title'];
  $rankResult[$params['page_id']]['pageCount']    = count($result);
  $rankResult[$params['page_id']]['hasDonations'] = $hasDonations;
  $rankResult[$params['page_id']]['noDonations']  = count($result) - $hasDonations ;
      
  $result = $rankResult;
  return civicrm_api3_create_success($result, $params);
}

function _civicrm_api3_pcpteams_getRank_spec(&$params) {
  $params['page_id']['api.required'] = 1;
  $params['pcp_id']['api.required'] = 1;
}

function civicrm_api3_pcpteams_getAllDonations($params) {
  $limit = NULL;
  $result= CRM_Core_DAO::$_nullArray;
  //check the hasPermission to view details
  $permParams = array(
    'pcp_id' => $params['pcp_id'],
    'team_pcp_id' => isset($params['team_pcp_id']) ? $params['team_pcp_id'] : NULL,
  );
  if (!_civicrm_pcpteams_permission_check($permParams, CRM_Core_Permission::VIEW) 
    // && !_civicrm_pcpteams_permission_check($permParams, CRM_Pcpteams_Constant::C_PERMISSION_MEMBER)
    ) {
    return civicrm_api3_create_error('insufficient permission to view this record');
  }
  if (isset($params['limit']) && CRM_Utils_Type::escape($params['limit'], 'Integer')) {
    $limit = "LIMIT 0, {$params['limit']}";
  }
  
  $query = "
    SELECT cpb.target_entity_id 
    , cct.id            as contribution_id
    , cct.contact_id    as contact_id
    , cct.total_amount  as total_amount
    , cct.receive_date  as receive_date
    , cc.display_name   as display_name
    FROM civicrm_pcp_block cpb
    INNER JOIN civicrm_contribution cct on (cct.contribution_page_id = cpb.target_entity_id )
    INNER JOIN civicrm_contact cc on (cc.id = cct.contact_id AND cc.is_deleted = 0)
    WHERE cpb.entity_id = %1 AND cct.id IN ( SELECT contribution_id FROM civicrm_contribution_soft WHERE pcp_id = %2)
    ORDER BY cct.receive_date DESC
    {$limit}
  ";
  
  $queryParams = array(
    1 => array($params['page_id'], 'Integer'),
    2 => array($params['pcp_id'], 'Integer'),
  );
  $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
  while ($dao->fetch()) {
    $result[$dao->contribution_id] = $dao->toArray();
  }

  return civicrm_api3_create_success($result, $params);
}

function _civicrm_api3_pcpteams_getAllDonations_spec(&$params) {
  $params['page_id']['api.required'] = 1;
  $params['pcp_id']['api.required'] = 1;
}

function civicrm_api3_pcpteams_getTopDonations($params) {
  $result= CRM_Core_DAO::$_nullArray;
  
  //check the hasPermission to view details
  $permParams = array(
    'pcp_id' => $params['pcp_id'],
  );
  if (!_civicrm_pcpteams_permission_check($permParams, CRM_Core_Permission::VIEW)) {
    return civicrm_api3_create_error('insufficient permission to view this record');
  }
  
  if($params['limit']){
    $limit = "LIMIT 0, {$params['limit']}";
  }else{
    $limit = "LIMIT 0, 5";
  }
  
  $query = "
    SELECT cpb.target_entity_id
    , cct.id as contribution_id
    , cct.contact_id as contact_id
    , cct.total_amount as total_amount
    , cc.display_name as display_name
    FROM civicrm_pcp_block cpb
    INNER JOIN civicrm_contribution cct on (cct.contribution_page_id = cpb.target_entity_id )
    INNER JOIN civicrm_contact cc on (cc.id = cct.contact_id AND cc.is_deleted = 0)
    WHERE cpb.entity_id = %1 AND cct.id IN ( SELECT contribution_id FROM civicrm_contribution_soft WHERE pcp_id = %2)
    ORDER BY cct.total_amount DESC
    {$limit}
  ";
  $queryParams = array(
    1 => array($params['page_id'], 'Integer'),
    2 => array($params['pcp_id'], 'Integer'),
  );
  $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
  while ($dao->fetch()) {
    $result[$dao->contribution_id] = $dao->toArray();
  }

  return civicrm_api3_create_success($result, $params);
}

function _civicrm_api3_pcpteams_getTopDonations_spec(&$params) {
  $params['page_id']['api.required'] = 1;
  $params['pcp_id']['api.required'] = 1;
}

function civicrm_api3_pcpteams_checkTeamAdmin($params) {
  $teamRelTypeName = CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE;
  $relTypeId       = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', $teamRelTypeName, 'id', 'name_a_b');
  $reltionships    = CRM_Contact_BAO_Relationship::getRelationship( $params['user_id'], CRM_Contact_BAO_Relationship::CURRENT);
  $result['user_id']         = $params['user_id'];
  $result['is_team_admin']   = 0;
  $result['team_contact_id'] = $params['team_contact_id'];
  foreach ($reltionships as $relId => $relValue) {
    if ($relTypeId == $relValue['relationship_type_id'] 
        && $params['team_contact_id'] == $relValue['contact_id_b']
        && $relValue['is_active'] 
        ) {
      $result['relationship_id']  = $relValue['id'];
      $result['is_team_admin']    = 1;
    }
  }
  return $result;
}

function _civicrm_api3_pcpteams_checkTeamAdmin_spec(&$params) {
  $params['user_id']['api.required'] = 1;
  $params['team_contact_id']['api.required'] = 1;
}


function _civicrm_api3_pcpteams_getMoreInfo(&$params) {
  foreach ($params as $pcpId => $pcpValues) {
    $entityFile   = CRM_Core_BAO_File::getEntityFile('civicrm_pcp', $pcpId);
    $imageUrl = "";
    $fileId   = NULL;
    if($entityFile){
      $fileInfo = reset($entityFile);
      $fileId   = $fileInfo['fileID'];
      $imageUrl = CRM_Utils_System::url('civicrm/file',"reset=1&id=$fileId&eid={$pcpId}"); 
    }
    $pcpBlockId = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $pcpId, 'pcp_block_id', 'id');
    
    $contributionPageId = 1;
    if ($pcpBlockId) {
      $contributionPageId = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCPBlock', $pcpBlockId, 'target_entity_id', 'id');
    }
    $donateUrl  = CRM_Utils_System::url('civicrm/contribute/transact', 'id='.$contributionPageId.'&pcpId='.$pcpId.'&reset=1');
    
    $aContactTypes   = CRM_Contact_BAO_Contact::getContactTypes( $pcpValues['contact_id'] );
    $isTeamPcp       = in_array('Team'      , $aContactTypes) ? TRUE : FALSE;
     $params[$pcpId]['page_title']       = CRM_Core_DAO::getFieldValue('CRM_Event_DAO_Event', $pcpValues['page_id'], 'title');
     $params[$pcpId]['amount_raised']    = civicrm_api3_pcpteams_getAmountRaised(array('pcp_id' => $pcpId, 'version' => 3));
     $params[$pcpId]['image_url']        = $imageUrl ? $imageUrl : CRM_Core_Resources::singleton()->getUrl('uk.co.vedaconsulting.pcpteams', CRM_Pcpteams_Constant::C_DEFAULT_PROFILE_PIC);
     $params[$pcpId]['image_id']         = $fileId;
     $params[$pcpId]['donate_url']       = $donateUrl;
     $params[$pcpId]['is_teampage']      = $isTeamPcp;
     $params[$pcpId]['contact_name'] = CRM_Contact_BAO_Contact::displayName($pcpValues['contact_id']);

    //calculate percentage
    $percentage   = 0;
    if (!empty($pcpValues['goal_amount']) && ($pcpValues['goal_amount'] > 0)){
      $percentage = number_format(($params[$pcpId]['amount_raised'] / $params[$pcpId]['goal_amount']) * 100);
    }
    
    if (isset($pcpValues['currency'])) {
     $params[$pcpId]['currency_symbol']  = CRM_Core_DAO::getFieldValue('CRM_Financial_DAO_Currency', $pcpValues['currency'], 'symbol', 'name');
    }
    $params[$pcpId]['percentage']       = $percentage;
    
    // check the user has pending request
    $pendingDetails = civicrm_api3_pcpteams_getMyPendingTeam(array('contact_id' => $pcpValues['contact_id']));
    $params[$pcpId]['pending_team_pcp_id']    = isset($pendingDetails['values'][0]) ? $pendingDetails['values'][0]['teamPcpId'] : NULL;
    $params[$pcpId]['pending_team_relationship_id']    = isset($pendingDetails['values'][0]) ? $pendingDetails['values'][0]['relationship_id'] : NULL;
  
  }
}

function civicrm_api3_pcpteams_getAmountRaised($params) {
    $pcpId = $params['pcp_id'];

    $query = "
      SELECT SUM(cs.amount) as total
      FROM civicrm_contribution_soft cs
      LEFT JOIN civicrm_value_pcp_custom_set cscv ON ( cscv.entity_id = cs.pcp_id )
      WHERE cs.contact_id = ( SELECT contact_id FROM civicrm_pcp WHERE id = %1 ) AND (cscv.team_pcp_id = %1 OR cs.pcp_id = %1)
    ";

    $params = array(1 => array($pcpId, 'Integer'));

    $amountRaised = CRM_Core_DAO::singleValueQuery($query, $params);
    return $amountRaised ? $amountRaised : '0.00';
}
function _civicrm_api3_pcpteams_getAmountRaised_spec(&$params) {
  $params['pcp_id']['api.required'] = 1;
}

function civicrm_api3_pcpteams_getTeamRequestInfo($params) {
  $result= CRM_Core_DAO::$_nullArray;
  //check the hasPermission to view details
  $permParams = array(
    'team_pcp_id' => $params['team_pcp_id'],
  );
  if (!_civicrm_pcpteams_permission_check($permParams, CRM_Core_Permission::VIEW)) {
    return civicrm_api3_create_error('insufficient permission to view this record');
  }
  
  $query = " 
    SELECT crs.pcp_a_b, cc.display_name, cp.page_id, cr.id FROM civicrm_value_pcp_relationship_set crs
    INNER JOIN civicrm_relationship cr ON (cr.id = crs.entity_id AND cr.is_active = 0)
    INNER JOIN civicrm_pcp cp ON (cp.id = crs.pcp_a_b)
    INNER JOIN civicrm_contact cc ON (cr.contact_id_a = cc.id AND cc.is_deleted = 0)
    WHERE crs.pcp_b_a = %1";
  
  $queryParams = array(
    1 => array($params['team_pcp_id'], 'Integer'),
  );
  
  $dao = CRM_Core_Dao::executeQuery($query, $queryParams);
  while($dao->fetch()) {
    $memberPcpResult = civicrm_api('Pcpteams', 'get', array('version' => 3, 'sequential' => 1, 'pcp_id' => $dao->pcp_a_b, 'team_pcp_id' => $params['team_pcp_id'] ));
    $getAllDonations = civicrm_api3_pcpteams_getAllDonations(array('page_id' => $dao->page_id, 'pcp_id' => $dao->pcp_a_b, 'team_pcp_id' => $params['team_pcp_id'] ));
    $result[$dao->pcp_a_b] = array(
      'display_name'       => $dao->display_name,
      'pcp_id'             => $dao->pcp_a_b,
      'amount_raised'      => $memberPcpResult['values'][0]['amount_raised'],
      'donations_count'    => $getAllDonations['count'],
      'image_url'          => $memberPcpResult['values'][0]['image_url'] ? $memberPcpResult['values'][0]['image_url'] : CRM_Core_Resources::singleton()->getUrl('uk.co.vedaconsulting.pcpteams', CRM_Pcpteams_Constant::C_DEFAULT_PROFILE_PIC),
      'image_id'           => $memberPcpResult['values'][0]['image_id'],
      'team_pcp_id'        => $params['team_pcp_id'],
      'relationship_id'    => $dao->id
    );
  }
  return civicrm_api3_create_success($result, $params);
}

function _civicrm_api3_pcpteams_sortby_amount_raised(&$result){
  //FIXME: to manage ASC, DESC by params. sort by Highest amount raiser by default now
  //array sort by amount raised
  $amountRaised = array();
  foreach ($result as $key => $row)
  {
      $amountRaised[$key]  = $row['amount_raised'];
      $donationCount[$key] = $row['donations_count'];
  }
  array_multisort($amountRaised, SORT_DESC, $donationCount, SORT_DESC, $result);
}

function civicrm_api3_pcpteams_leaveTeam($params) {
  
  //check the hasPermission to view details
  $result = CRM_Core_DAO::$_nullArray;
  if (!CRM_Pcpteams_Utils::hasPermission($params['team_pcp_id'], NULL, CRM_Core_Permission::VIEW)) {
    CRM_Core_Session::setStatus(ts("Sorry! You dont have right permission to leave this team..."));
    return civicrm_api3_create_success($result, $params);
  }
  
  // $teamPcpCfId  = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
  $team_pcp_id  = $params['team_pcp_id']; 
  $user_id      = $params['user_id']; 
  $query = "
    Update civicrm_value_pcp_custom_set
    Set team_pcp_id = NULL
    Where team_pcp_id = {$team_pcp_id} AND entity_id IN ( SELECT id FROM civicrm_pcp WHERE contact_id = {$user_id} )
  ";
  $dao = CRM_Core_DAO::executeQuery($query);
  // Delete relatoinship so that it can be joined after leave
  $deleteQuery = "
    DELETE cr FROM civicrm_relationship cr
    INNER JOIN civicrm_value_pcp_relationship_set crs ON crs.entity_id = cr.id
    INNER JOIN civicrm_pcp cp ON cp.id = crs.pcp_a_b
    INNER JOIN civicrm_contact cc ON cc.id = cp.contact_id
    WHERE crs.pcp_b_a = %1 AND cp.contact_id = %2";
  $deleteQueryParams = array(
    1 => array($team_pcp_id, 'Int'),
    2 => array($user_id, 'Int')
  );
  CRM_Core_DAO::executeQuery($deleteQuery, $deleteQueryParams);
  $teamPcpTitle = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $team_pcp_id, 'title');
  CRM_Core_Session::setStatus(ts("Unsubscribe from {$teamPcpTitle}"), NULL, 'success');
  return TRUE;
}
function _civicrm_api3_pcpteams_leaveTeam_spec(&$params) {
  $params['user_id']['api.required'] = 1;
  $params['team_pcp_id']['api.required'] = 1;
}

function _civicrm_pcpteams_permission_check($params, $action = CRM_Core_Permission::VIEW){
  $reqFieldFound = FALSE;
  
  //check the params should have any one of these values.
  foreach (array('pcp_id', 'team_pcp_id', 'contact_id') as $value) {
    if (array_key_exists($value, $params)) {
      $reqFieldFound = TRUE;
    }
  }
  if (!$reqFieldFound) {
    return FALSE;
  }
  
  $contactId = isset($params['contact_id']) ? $params['contact_id'] : NULL ;
  
  if ($action == CRM_Pcpteams_Constant::C_PERMISSION_MEMBER) {
    return CRM_Pcpteams_Utils::hasPermission($params['pcp_id'], $contactId, $action, $params['team_pcp_id']);
  }
  
  //pcp id permission check 
  if (isset($params['pcp_id'])) {
    return CRM_Pcpteams_Utils::hasPermission($params['pcp_id'], $contactId, $action);
  }
  
  //team pcp id permission check 
  if (isset($params['team_pcp_id'])) {
    return CRM_Pcpteams_Utils::hasPermission($params['team_pcp_id'], $contactId, $action);
  }
  
  //check with contact_id 
  if (isset($contactId)) {
    return CRM_Pcpteams_Utils::hasPermission(NULL, $contactId, $action);
  }
  
  return FALSE;
}


function civicrm_api3_pcpteams_customcreate($params) {
  $customParams     = array();
  $isEditPermission = CRM_Pcpteams_Utils::hasPermission($params['entity_id'], NULL, CRM_Core_Permission::EDIT);

  foreach ($params as $key => $value) {
    if ($key && !in_array($key, array('entity_id', 'version'))) {
      $customFieldId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', $key, 'id', 'column_name');
      if (!$customFieldId) {
        continue;
      }

      $skipTeamApproval = CRM_Pcpteams_Utils::getPcpTeamSettings(CRM_Pcpteams_Constant::C_SKIP_TEAM_APPROVAL);
      if ($key == 'team_pcp_id' && !$skipTeamApproval) {
        if ($value) {
          // With Approval Mode switched On - we don't want pcp-owners to control / update setting of team_pcp_id.
          // Lets make sure its the admin who is doing it by checking if logged in user has edit permission on team_pcp_id ($value here)
          if(!CRM_Pcpteams_Utils::hasPermission($value, NULL, CRM_Core_Permission::EDIT)) { 
            continue;
          }
        } else if (!(CRM_Pcpteams_Utils::hasPermission($params['entity_id'], NULL, CRM_Pcpteams_Constant::C_PERMISSION_TEAM_ADMIN) || $isEditPermission)) {
          // this is the case when somebody is setting team_pcp_id to NULL 
          // if the logged in user is (A) admin for pcp ($params['entity_id']) being updated OR (B) owner of pcp being updated,  
          // we allow it to unset
          continue;
        }
      } else if (!$isEditPermission) {
        continue;
      }
      $customParams["custom_{$customFieldId}"] = $value;
    }
  }
  if (empty($customParams)) {
    return civicrm_api3_create_error('insufficient permission to edit this record');
  }

  $customParams['version']   = 3;
  $customParams['entity_id'] = $params['entity_id'];

  return civicrm_api3('CustomValue', 'create', $customParams);
}

function _civicrm_api3_pcpteams_customcreate_spec(&$params) {
  $params['entity_id']['api.required'] = 1;
}

function _civicrm_api3_pcpteams_getDigitalPageUrl($pcpId) {
  if (empty($pcpId)) {
    return NULL;
  }
  
  //check custom group exists
  $cgId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', CRM_Pcpteams_Constant::C_CG_DIGITAL_FUNDRAISING, 'id', 'name');
  if (!$cgId) {
    CRM_Core_Error::debug_log_message(ts("Custom Group %1 does not exist", array( 1 => CRM_Pcpteams_Constant::C_CG_DIGITAL_FUNDRAISING)));
    return NULL;
  }
  
  $tableName    = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', $cgId, 'table_name');
  $columnPcpId  = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', CRM_Pcpteams_Constant::C_CF_DIGITAL_FUNDRAISING_PCP_ID, 'column_name', 'name');
  $columnPageUrl= CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomField', CRM_Pcpteams_Constant::C_CF_DIGITAL_FUNDRAISING_DFP_URL, 'column_name', 'name');
  
  if (!$tableName || !$columnPcpId || !$columnPageUrl) {
    return NULL;
  }
  $ogId  = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', CRM_Pcpteams_Constant::C_ACTIVITY_TYPE, 'id', 'name');
  $query = "
    SELECT cdfp.{$columnPageUrl}
    FROM {$tableName} cdfp
    WHERE cdfp.{$columnPcpId} = %1
  ";
  
  $queryParams = array(
    1 => array($pcpId, 'Integer'),
  );
  
  $pageurl = CRM_Core_DAO::singleValueQuery($query, $queryParams);
  
  return $pageurl ? $pageurl : NULL;
}
