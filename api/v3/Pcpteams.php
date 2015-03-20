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
  $params['title']      = $params['pcp_title'];
  $params['intro_text'] = $params['pcp_intro_text'];
  $params['contact_id'] = $params['pcp_contact_id'];
  $params['page_id']    = $params['page_id'];
  $params['page_type']  = $params['page_type'];

  // since we are allowing html input from the user
  // we also need to purify it, so lets clean it up
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

  $pcp = CRM_PCP_BAO_PCP::add($params, FALSE);
  $values = array();
   _civicrm_api3_object_to_array_unique_fields($pcp, $values[$pcp->id]);
  return civicrm_api3_create_success($values, $params, 'Pcpteams', 'create');
}
function _civicrm_api3_pcpteams_create_spec(&$params) {
  $params['pcp_title']['api.required'] = 1;
  $params['pcp_contact_id']['api.required'] = 1;
}

function civicrm_api3_pcpteams_get($params) {
  $dao = new CRM_PCP_DAO_PCP();
  // FIXME: need to enforce type check
  $dao->id = $params['pcp_id']; // type check done by getfields
  //'@' this suppress the notice message, because
  // _civicrm_api_get_entity_name_from_dao returns the entity as 'p_c_p_id' which is undefined in PCP DAO fields
  //ref:  _civicrm_api_get_entity_name_from_dao($bao); in api/v3/utils.php (801)
  $result = @_civicrm_api3_dao_to_array($dao);

  // Append custom info
  // Note: This should ideally be done in _civicrm_api3_dao_to_array, but since PCP is not one of 
  // recongnized entity in core, we can append it seprately for now.
  _civicrm_api3_pcpteams_custom_get($result);

  return civicrm_api3_create_success($result, $params);
}
function _civicrm_api3_pcpteams_get_spec(&$params) {
  $params['pcp_id']['api.required'] = 1;
}

function civicrm_api3_pcpteams_delete($params) {
  $result = array();
  return civicrm_api3_create_success($result, $params);
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

function civicrm_api3_pcpteams_getcontactpcp($params) {
  $dao = new CRM_PCP_DAO_PCP();
  // FIXME: need to enforce type check
  $dao->contact_id = $params['contact_id']; // type check done by getfields
  
  //'@' this suppress the notice message, because
  // _civicrm_api_get_entity_name_from_dao returns the entity as 'p_c_p_id' which is undefined in PCP DAO fields
  //ref:  _civicrm_api_get_entity_name_from_dao($bao); in api/v3/utils.php (801)
  $result = @_civicrm_api3_dao_to_array($dao);

  // Append custom info
  // Note: This should ideally be done in _civicrm_api3_dao_to_array, but since PCP is not one of 
  // recongnized entity in core, we can append it seprately for now.
  _civicrm_api3_pcpteams_custom_get($result);

  return civicrm_api3_create_success($result, $params);
}

function _civicrm_api3_pcpteams_getcontactpcp_spec(&$params) {
  $params['contact_id']['api.required'] = 1;
}

function civicrm_api3_pcpteams_getstate($params) {
  $dao = new CRM_PCP_DAO_PCP();
  // FIXME: need to enforce type check
  $dao->contact_id = $params['contact_id']; // type check done by getfields
  
  //'@' this suppress the notice message, because
  // _civicrm_api_get_entity_name_from_dao returns the entity as 'p_c_p_id' which is undefined in PCP DAO fields
  //ref:  _civicrm_api_get_entity_name_from_dao($bao); in api/v3/utils.php (801)
  $result = @_civicrm_api3_dao_to_array($dao);
  _civicrm_api3_pcpteams_custom_get($result);
  _civicrm_api3_pcpteams_getstate_output($result);
  return civicrm_api3_create_success($result, $params);
}

function _civicrm_api3_pcpteams_getstate_spec(&$params) {
  $params['contact_id']['api.required'] = 1;
}

function _civicrm_api3_pcpteams_getstate_output(&$result){
  $result = array_shift($result);

  if(isset($result['id'])){
    $result['state'][] = 'individual';
  }
  
  require_once 'CRM/Pcpteams/Utils.php';
  $cfTeamPcpId = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
  $cforgId     = CRM_Pcpteams_Utils::getBranchorPartnerCustomFieldId();
  if(isset($result['custom_'.$cfTeamPcpId])){
    $result['state'][] = 'team';
  }
  
  if(isset($result['custom_'.$cforgId])){
    $result['state'][] = 'group';
  }
}

function _civicrm_api3_pcpteams_custom_get(&$params) {
  foreach ($params as $rid => $rval) {
    _civicrm_api3_custom_data_get($params[$rid], 'PCP', $rid);
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
      $result[$dao->id] = array(
        'id'    =>  $dao->id,
        'label' =>  $dao->display_name,
        'icon_class' =>  $dao->contact_type,
      );
    }

  return civicrm_api3_create_success($result, $params, 'pcpteams');
}

function civicrm_api3_pcpteams_getPcpDashboardInfo($params) {
  $dao = new CRM_PCP_DAO_PCP();
  $dao->contact_id = $params['contact_id']; 
  $result = @_civicrm_api3_dao_to_array($dao);
  _civicrm_api3_pcpteams_custom_get($result);
  
  $cfTeamPcpId = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
  $pcpDashboardValues = array();
  foreach ($result as $pcpId => $value) {
    $isTeamExist                     = isset($value['custom_'.$cfTeamPcpId]) ? $value['custom_'.$cfTeamPcpId] : 0;
    $result[$pcpId]['amount_raised'] = CRM_Utils_Money::format(CRM_PCP_BAO_PCP::thermoMeter($pcpId));
    $result[$pcpId]['goal_amount']   = CRM_Utils_Money::format($value['goal_amount']);
    $result[$pcpId]['page_title']    = CRM_Core_DAO::getFieldValue('CRM_Event_DAO_Event', $value['page_id'], 'title');
    $result[$pcpId]['isTeamExist']   = $value['isTeamExist'] = $isTeamExist;
    $result[$pcpId]['action']        = _getPcpDashboardActionLink($value);
  }

  return civicrm_api3_create_success($result, $params);
}

function _civicrm_api3_pcpteams_getPcpDashboardInfo_spec(&$params) {
  $params['contact_id']['api.required'] = 1;
}

function _getPcpDashboardActionLink($params){
  $active     = $params['is_active'] ? 'disable' : 'enable';
  $updateLabel= $params['isTeamExist'] ? 'Change' : 'Join';
  
  //action URLs
  $editURL    = CRM_Utils_System::url('civicrm/pcp/info', "action=update&component=event&id={$params['id']}"); 
  $pageURL    = CRM_Utils_System::url('civicrm/pcp/page', "reset=1&component=event&id={$params['id']}"); 
  $updateURL  = CRM_Utils_System::url('civicrm/pcp/info', "action=browse&component=event&id={$params['id']}"); 
  $disableURL = CRM_Utils_System::url('civicrm/pcp'     , "action={$active}&reset=1&component=event&id={$params['id']}"); 
  $deleteURL  = CRM_Utils_System::url('civicrm/pcp'     , "action=delete&reset=1&component=event&id={$params['id']}");
  $active     = ucwords($active);
  
  //create and join team URLs
  $joinTeamURl    = CRM_Utils_System::url('civicrm/pcp/support', "reset=1&pageId={$params['page_id']}&component=event&code=cpftn");
  $createTeamURl  = CRM_Utils_System::url('civicrm/pcp/support', "reset=1&pageId={$params['page_id']}&component=event&code=cpftn&option=1");
  
  //FIXME : check User permission and return action based on permission
  $action     = "
    <span>
      <a href=\"{$editURL}\" class=\"action-item crm-hover-button\" title='Configure' >Edit Page</a>
      <a href=\"{$pageURL}\" class=\"action-item crm-hover-button\" title='URL for this Page' >View Page</a>
    </span>
    <span class='btn-slide crm-hover-button'>more
      <ul class='panel'>
        <li>
          <a href=\"{$joinTeamURl}\" class=\"action-item crm-hover-button\" title='Join Team' >{$updateLabel} Team</a>
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
  $dao = new CRM_PCP_DAO_PCP();
  $dao->contact_id = $params['contact_id']; 
  $result = @_civicrm_api3_dao_to_array($dao);
  _civicrm_api3_pcpteams_custom_get($result);
  
  $cfTeamPcpId = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
  $pcpDashboardValues = $teamIds = array();
  foreach ($result as $pcpId => $value) {
    if(isset($value['custom_'.$cfTeamPcpId])){
      $teamIds[$pcpId] = $value['custom_'.$cfTeamPcpId];
    } 
  }
  if(empty($teamIds)){
    return civicrm_api3_create_success(CRM_Core_DAO::$_nullObject, $params);
  }
    
  $sTeamIds = implode(', ', array_filter($teamIds));
  $query = "
    SELECT  ce.title    as page_title 
    , cp.id             as pcp_id  
    , cp.contact_id     as pcp_contact_id  
    , cc.display_name   as team_name  
    , cp.title          as pcp_title  
    , cp.goal_amount    as pcp_goal_amount  
    FROM civicrm_pcp cp 
    LEFT JOIN civicrm_event ce ON ce.id = cp.page_id
    LEFT JOIN civicrm_contact cc ON ( cc.id = cp.contact_id )
    LEFT JOIN civicrm_value_pcp_custom_set cpcs ON ( cpcs.entity_id = cp.id )
    WHERE cp.id IN ( $sTeamIds )
  ";
  $dao = CRM_Core_DAO::executeQuery($query);
  while($dao->fetch()){
    $myPcpId = array_search($dao->pcp_id, $teamIds); 
    $teamResult[$myPcpId] = array(
      'teamName'      => $dao->team_name,
      'my_pcp_id'     => $myPcpId,
      'my_pcp_title'  => CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $myPcpId, 'title'),
      'teamPcpTitle'  => $dao->pcp_title,
      'pageTitle'     => $dao->page_title,
      'teamgoalAmount'=> CRM_Utils_Money::format($dao->pcp_goal_amount),
      'amount_raised' => CRM_Utils_Money::format(CRM_PCP_BAO_PCP::thermoMeter($dao->pcp_id)),
      'teamPcpId'     => $dao->pcp_id,
      'contactId'     => $dao->pcp_contact_id,
      'action'        => _getTeamInfoActionLink($myPcpId, $dao->pcp_id, $cfTeamPcpId),
    );
  }
  $result = $teamResult;
  
  return civicrm_api3_create_success($result, $params);
}

function _civicrm_api3_pcpteams_getMyTeamInfo_spec(&$params) {
  $params['contact_id']['api.required'] = 1;
}

function _getTeamInfoActionLink($entityId, $teamPcpId, $cfTeamPcpId){
  
  //action URLs
  $pageURL    = CRM_Utils_System::url('civicrm/pcp/page', "reset=1&component=event&id={$teamPcpId}"); 
  
  //FIXME : check User permission and return action based on permission
  $action     = "
    <span>
      <a href=\"{$pageURL}\" class=\"action-item crm-hover-button\" title='URL for this Page' >View Page</a>
    </span>
    <span class='btn-slide crm-hover-button'>more
      <ul class='panel'>
        <li>
          <a href='javascript:void(0)' class=\"action-item crm-hover-button\" title='Join Team' onclick='unsubscribeTeam({$entityId});'>Unscbscribe from this Team</a>
        </li>        
      </ul>
    </span>
  ";

  return $action;
}
