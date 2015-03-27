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

  $pcpID = CRM_Utils_Array::value('id', $params);
  $pcp   = CRM_PCP_BAO_PCP::add($params, FALSE);

  if (!$pcpID) {
    // We 'll need to call create hook. Civi doesn't do it for PCP.  
    CRM_Utils_Hook::post('create', 'PCP', $pcp->id, $pcp);
  }

  $values = array();
   _civicrm_api3_object_to_array_unique_fields($pcp, $values[$pcp->id]);
  return civicrm_api3_create_success($values, $params, 'Pcpteams', 'create');
}
function _civicrm_api3_pcpteams_create_spec(&$params) {
  $params['title']['api.required'] = 1;
  $params['contact_id']['api.required'] = 1;
}

function civicrm_api3_pcpteams_get($params) {
  $dao = new CRM_PCP_DAO_PCP();
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
      $teamAdmin        = CRM_Pcpteams_Utils::getTeamAdminByTeamContactId($dao->id);
      $teamAdminString  = $teamAdmin ? ' Admin is: '.$teamAdmin : ' Admin is: unknown';
      $result[$dao->id] = array(
        'id'    =>  $dao->id,
        'label' =>  $dao->display_name.$teamAdminString,
        'icon_class' =>  $dao->contact_type,
      );
    }

  return civicrm_api3_create_success($result, $params, 'pcpteams');
}

function civicrm_api3_pcpteams_getPcpDashboardInfo($params) {
  $dao = new CRM_PCP_DAO_PCP();
  $dao->contact_id = $params['contact_id']; 
  $dao->is_active  = $params['is_active']; 
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

    $result[$pcpId]['pcpId']         = $pcpId;
    $result[$pcpId]['goal_amount']   = $pcpResult['values'][0]['goal_amount'];
    $result[$pcpId]['amount_raised'] = $pcpResult['values'][0]['amount_raised'];
    $result[$pcpId]['page_title']    = $pcpResult['values'][0]['page_title'];
    $result[$pcpId]['isTeamExist']   = $value['isTeamExist'] = $pcpResult['values'][0]['is_teampage'];
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
  $manageURL  = CRM_Utils_System::url('civicrm/pcp/manage', "id={$params['id']}"); 
  $pageURL    = CRM_Utils_System::url('civicrm/pcp/page', "reset=1&component=event&id={$params['id']}"); 
  $updateURL  = CRM_Utils_System::url('civicrm/pcp/info', "action=browse&component=event&id={$params['id']}"); 
  $disableURL = CRM_Utils_System::url('civicrm/pcp'     , "action={$active}&reset=1&component=event&id={$params['id']}"); 
  $deleteURL  = CRM_Utils_System::url('civicrm/pcp'     , "action=delete&reset=1&component=event&id={$params['id']}");
  $active     = ucwords($active);
  
  if(empty($params['is_active'])) {
    $action     = "
    <span>
      <a href=\"{$editURL}\" class=\"action-item crm-hover-button\" title='Configure' >Edit Page</a>
      <a href=\"{$pageURL}\" class=\"action-item crm-hover-button\" title='URL for this Page' >View Page</a>
    </span>
    <span class='btn-slide crm-hover-button'>more
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
      <a href=\"{$editURL}\" class=\"action-item crm-hover-button\" title='Configure' >Edit Page</a>
      <a href=\"{$manageURL}\" class=\"action-item crm-hover-button\" title='Manage' >Manage</a>
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
      INNER JOIN civicrm_contact cc ON ( cc.id = cp.contact_id )
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
      <a href=\"{$editURL}\" class=\"action-item crm-hover-button\" title='Configure' >Edit Page</a>
      <a href=\"{$manageURL}\" class=\"action-item crm-hover-button\" title='Manage' >Manage</a>
      <a href=\"{$pageURL}\" class=\"action-item crm-hover-button\" title='URL for this Page' >View Page</a>
      <a href='javascript:void(0)' class=\"action-item crm-hover-button\" title='Delete' onclick = 'deleteTeamPcp({$entityId},{$teamPcpId});'>Delete</a>
    </span>";
  }
  
  //FIXME : check User permission and return action based on permission
  $action     = $span."
    <span class='btn-slide crm-hover-button'>more
      <ul class='panel'>
        <li>
          <a href='javascript:void(0)' class=\"action-item crm-hover-button\" title='Join Team' onclick='unsubscribeTeam({$entityId}, {$teamPcpId});'>Unscbscribe from this Team</a>
        </li>        
      </ul>
    </span>
  ";

  return $action;
}

function civicrm_api3_pcpteams_getMyPendingTeam($params) {
  $result= CRM_Core_DAO::$_nullArray;
  $contact_id_a = $params['contact_id']; 
  $relTypeId    = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType',  CRM_Pcpteams_Constant::C_TEAM_RELATIONSHIP_TYPE, 'id', 'name_a_b');
  $teamQuery    = "SELECT `contact_id_b` as team_contact_id FROM `civicrm_relationship` WHERE `contact_id_a` = %1 AND `relationship_type_id` = %2 AND `is_active` = 0";
  $teamQueryParams  = array(1 => array($contact_id_a, 'Int'), 2 => array($relTypeId, 'Int'));
  $teamDao          = CRM_Core_DAO::executeQuery($teamQuery, $teamQueryParams);
  $teamContactIds = array();
  while($teamDao->fetch()) {
    $teamContactIds[] = $teamDao->team_contact_id;
  }
  $sTeamIds = implode(', ', array_filter($teamContactIds));
  if(!empty($sTeamIds)) {
    $query = "
      SELECT  ce.title    as page_title 
      , cp.id             as pcp_id  
      , cp.contact_id     as pcp_contact_id  
      , cc.display_name   as team_name  
      , cp.title          as pcp_title  
      , cp.goal_amount    as pcp_goal_amount  
      FROM civicrm_pcp cp 
      INNER JOIN civicrm_event ce ON ce.id = cp.page_id
      INNER JOIN civicrm_contact cc ON ( cc.id = cp.contact_id )
      WHERE cp.contact_id IN ( $sTeamIds )
    ";
    $dao = CRM_Core_DAO::executeQuery($query);
    while($dao->fetch()){
      $pcpResult = civicrm_api('pcpteams', 'get', array(
          'version' => 3,
          'sequential'  => 1,
          'pcp_id'      => $dao->pcp_id
        )
      );
      $result[] = array(
        'teamName'      => $dao->team_name,
        'teamPcpTitle'  => $dao->pcp_title,
        'pageTitle'     => $dao->page_title,
        'teamgoalAmount'=> $dao->pcp_goal_amount,
        'amount_raised' => $pcpResult['values'][0]['amount_raised'],
        'teamPcpId'     => $dao->pcp_id,
        'contactId'     => $dao->pcp_contact_id,
      );
    }
  }
  return civicrm_api3_create_success($result, $params);
}

function civicrm_api3_pcpteams_getTeamRequest($params) {
  $result= CRM_Core_DAO::$_nullArray;
  // Get Team Admin Contact Ids for this contact
  $getUserRelationships = CRM_Contact_BAO_Relationship::getRelationship( $params['contact_id'], CRM_Contact_BAO_Relationship::CURRENT);
  // Team Admin Relationship
  $relTypeAdmin   = CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE;
  $adminRelTypeId = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', $relTypeAdmin, 'id', 'name_a_b');
  $teamAdminContactIds = array();
  foreach ($getUserRelationships as $value) {
    if( $value['relationship_type_id'] == $adminRelTypeId ){
      $teamAdminContactIds[] = $value['contact_id_b'];
    }
  }
  
  // Get Team Member Contact Ids for these teams with is_active = 0
  $steamAdminContactIds = implode(', ', array_filter($teamAdminContactIds));
  if (!empty($steamAdminContactIds)) {
    $relTypeId            = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType',  CRM_Pcpteams_Constant::C_TEAM_RELATIONSHIP_TYPE, 'id', 'name_a_b');
    $teamMemberQuery      = "SELECT `contact_id_a` as team_member_contact_id, id as relationship_id FROM `civicrm_relationship` WHERE `is_active` = 0 AND `relationship_type_id` = $relTypeId AND `contact_id_b` IN ( $steamAdminContactIds)";
    $teamMemberQueryDao   = CRM_Core_DAO::executeQuery($teamMemberQuery);
    $teamMemberContactIds = array();
    while($teamMemberQueryDao->fetch()) {
      $customRelationQuery = "SELECT pcp_a_b as source_pcp_id, pcp_b_a as destination_pcp_id FROM civicrm_value_pcp_relationship_set WHERE entity_id = {$teamMemberQueryDao->relationship_id}";
      $customDao = CRM_Core_DAO::executeQuery($customRelationQuery);
      $customDao->fetch();
      $teamMemberDetails[] = array('source_pcp_id' => $customDao->source_pcp_id, 'destination_pcp_id' => $customDao->destination_pcp_id, 'relationship_id' => $teamMemberQueryDao->relationship_id);
    }

    if(!empty($teamMemberDetails)) {
      foreach($teamMemberDetails as $teamMember) {
        $query = "
          SELECT  ce.title    as page_title 
          , cp.id             as pcp_id  
          , cp.contact_id     as pcp_contact_id  
          , cc.display_name   as display_name  
          , cp.title          as pcp_title  
          , cp.goal_amount    as pcp_goal_amount  
          , cl.email          as email  
          , ca.city           as city
          , ca.state_province_id    as state  
          , ca.country_id    as country 
          FROM civicrm_pcp cp
          INNER JOIN civicrm_event ce ON ce.id = cp.page_id
          INNER JOIN civicrm_contact cc ON ( cc.id = cp.contact_id )
          INNER JOIN civicrm_email cl ON ( cl.contact_id = cp.contact_id )
          INNER JOIN civicrm_address ca ON ( ca.contact_id = cp.contact_id )
          WHERE cp.id = {$teamMember['source_pcp_id']}
        ";
        $dao = CRM_Core_DAO::executeQuery($query);
        $dao->fetch();
        $teamPcpResult = civicrm_api('Pcpteams', 'get', array('version' => 3, 'sequential' => 1, 'pcp_id' => $teamMember['destination_pcp_id']));
        $result[$dao->pcp_id] = array(
          'member_display_name' => $dao->display_name,
          'member_email'        => $dao->email,
          'member_city'         => $dao->city,
          'member_country'      => $dao->country,
          'member_state_province_id'  => $dao->state_province_id,
          'member_pcp_id'             => $dao->pcp_id,
          'member_pcp_title'          => $dao->pcp_title,
          'member_page_title'         => $dao->page_title,
          'contactId'                 => $dao->pcp_contact_id,
          'team_display_name'         => CRM_Contact_BAO_Contact::displayName($teamPcpResult['values'][0]['contact_id']),
          'team_pcp_title'            => $teamPcpResult['values'][0]['title'],
          'action'                    => _getTeamRequestActionLink($teamMember['relationship_id'], $dao->pcp_id, $teamPcpResult['id']),
        );
      }
    }
  }
  return civicrm_api3_create_success($result, $params);
  
}

function _getTeamRequestActionLink($relationshipId, $pcpId, $teampcpId ){
  $action     = "
    <span>
      <a href='javascript:void(0)' class=\"action-item crm-hover-button\" title='Approve Team Member' onclick='approveTeamMember({$relationshipId},{$pcpId},{$teampcpId});'>Approve</a>
      <a href='javascript:void(0)' class=\"action-item crm-hover-button\" title='Decline Team Member' onclick='declineTeamMember({$relationshipId});'>Decline</a>
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
    
function civicrm_api3_pcpteams_getTeamMembers($params) {
  $result= CRM_Core_DAO::$_nullArray;
  $getUserRelationships = CRM_Contact_BAO_Relationship::getRelationship( $params['contact_id'], CRM_Contact_BAO_Relationship::CURRENT);
  // Team Admin Relationship
  $relTypeAdmin   = CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE;
  $adminRelTypeId = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', $relTypeAdmin, 'id', 'name_a_b');
  $teamAdminContactIds = array();
  foreach ($getUserRelationships as $value) {
    if( $value['relationship_type_id'] == $adminRelTypeId ){
      $teamAdminContactIds[] = $value['contact_id_b'];
    }
  }
  if(!empty($teamAdminContactIds)) {
    foreach ($teamAdminContactIds as $teamAdminContact) {
      $teamPcpResult = civicrm_api('Pcpteams', 'getcontactpcp', 
          array(
            'contact_id' => $teamAdminContact,
            'version'    => 3
          )
      );
      if (!empty($teamPcpResult['values'])) {
        foreach($teamPcpResult['values'] as $value) {
          $query            = "SELECT team_pcp_id, entity_id FROM civicrm_value_pcp_custom_set where team_pcp_id = {$value['id']}";
          $contactPcpIdsDao = CRM_Core_DAO::executeQuery($query);
          while($contactPcpIdsDao->fetch()) {
            $myContactId = CRM_Pcpteams_Utils::getcontactIdbyPcpId($contactPcpIdsDao->entity_id);
            // Don't show if it is team admin
            if($myContactId == $params['contact_id']) {
              continue;
            }
            $result[$contactPcpIdsDao->team_pcp_id] = array(
              'my_pcp_id'  => $contactPcpIdsDao->entity_id,
              'team_pcp_id'=> $contactPcpIdsDao->team_pcp_id,
              'memberName' => CRM_Contact_BAO_Contact::displayName(CRM_Pcpteams_Utils::getcontactIdbyPcpId($contactPcpIdsDao->entity_id)),
              'type'       => 'No',
              'teamName'   => CRM_Contact_BAO_Contact::displayName(CRM_Pcpteams_Utils::getcontactIdbyPcpId($contactPcpIdsDao->team_pcp_id)),
              'action'     => _getTeamMemberActionLink(5, $contactPcpIdsDao->entity_id, $contactPcpIdsDao->team_pcp_id),

            );
          }
        }
      }
    }
  }
  return civicrm_api3_create_success($result, $params);
}

function _civicrm_api3_pcpteams_getTeamMemberPCPIds($params){
  
  if (isset($params['teams'])) {
    $teamPcpId = implode(', ', $params['teams']);
    $selectClause = "entity_id as pcp_id";
    $whereClause  = "team_pcp_id IN ( {$teamPcpId} )";
  } else {
    
    if(empty($params['pcp_id'])){
      return CRM_Core_DAO::$_nullArray;
    }
    
    $params['contact_id'] = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $params['pcp_id'], 'contact_id');
    
    $contactSubType = CRM_Contact_BAO_Contact::getContactTypes($params['contact_id']);
    if (in_array(CRM_Pcpteams_Constant::C_CONTACT_SUB_TYPE, $contactSubType)) {
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
  
  $teamMemberPcpIds = _civicrm_api3_pcpteams_getTeamMemberPCPIds($params);
  
  if(empty($teamMemberPcpIds)){
     return CRM_Core_DAO::$_nullArray; 
  }
  $teamMemberPcpIds   = implode(', ', $teamMemberPcpIds);
  $relTypeAdmin       = CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE;
  $adminRelTypeId     = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', $relTypeAdmin, 'id', 'name_a_b');

  $query = "
    SELECT cp.id        as member_pcp_id
      , cp.page_id      as page_id
      , cp.contact_id   as member_contact_id
      , cp.goal_amount  as member_goal_amount
      , cc.display_name as member_contact_name
      , CASE 
        WHEN cr.relationship_type_id = {$adminRelTypeId} THEN '1' ELSE '0'
        END             as is_team_admin
    FROM civicrm_pcp cp
    LEFT JOIN civicrm_value_pcp_custom_set cpcs ON (cpcs.entity_id = cp.id)
    LEFT JOIN civicrm_contact cc ON (cc.id = cp.contact_id)
    LEFT JOIN civicrm_relationship cr ON (cr.contact_id_a = cp.contact_id AND cr.is_active)
    where cp.id IN ( $teamMemberPcpIds )
  ";
  $dao = CRM_Core_DAO::executeQuery($query);
  while ($dao->fetch()) {
    $members[$dao->member_pcp_id] = $dao->toArray();
  }
  //amount raised and total donations count
  foreach ($members as $memberPcpId => $values) {
    $getAllDonations            = civicrm_api3_pcpteams_getAllDonations(array('page_id' => $values['page_id'], 'pcp_id' => $values['member_pcp_id']));
    $values ['donations_count'] = $getAllDonations['count'];
    $values ['contact_id'] = $values['member_contact_id']; //to avoid notice message while call method getMoreInfo
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
      <a href='javascript:void(0)' class=\"action-item crm-hover-button\" title='De-activate' onclick='deactivateTeamMember({{$myPcpId},{$teamPcpId});'>De-activate</a>
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

  return civicrm_api3_create_success($result, $params, 'pcpteams');

}


function civicrm_api3_pcpteams_getRank($params) {
  $dao = new CRM_PCP_DAO_PCP();
  $dao->page_id = $params['page_id'];
  
  //dao result
  $result = @_civicrm_api3_dao_to_array($dao);
  $pcpAmounts = array();
  foreach ($result as $pcps) {
    $pcpAmounts[$pcps['id']] = CRM_PCP_BAO_PCP::thermoMeter($pcps['id']);
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
    INNER JOIN civicrm_contact cc on (cc.id = cct.contact_id)
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
    INNER JOIN civicrm_contact cc on (cc.id = cct.contact_id)
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


function civicrm_api3_pcpteams_getRaisedSoFar($params) {
  $result = CRM_Core_DAO::$_nullArray;
  $query  = "
    SELECT cc.total_amount FROM `civicrm_contribution_soft` cs
    INNER JOIN civicrm_contribution cc on (cc.id = cs.`contribution_id`)
    INNER JOIN civicrm_pcp_block cpb on (cpb.target_entity_id = cc.contribution_page_id)
    WHERE cpb.entity_id = %1 AND cs.`pcp_id` = %2 GROUP BY cs.`contribution_id`
  ";
  $queryParams = array(
    1 => array($params['page_id'], 'Integer'),
    2 => array($params['pcp_id'], 'Integer'),
  );
  $dao = CRM_Core_DAO::executeQuery($query, $queryParams);
  $result[$params['pcp_id']] = 0;
  while ($dao->fetch()) {
    $result[$params['pcp_id']] += $dao->total_amount;
  }

  return civicrm_api3_create_success($result, $params);
}

function _civicrm_api3_pcpteams_getRaisedSoFar_spec(&$params) {
  $params['page_id']['api.required'] = 1;
  $params['pcp_id']['api.required'] = 1;
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
    $pcpBlockId         = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCP', $pcpId, 'pcp_block_id', 'id');
    $contributionPageId = CRM_Core_DAO::getFieldValue('CRM_PCP_DAO_PCPBlock', $pcpBlockId, 'target_entity_id', 'id');
    $donateUrl          = CRM_Utils_System::url('civicrm/contribute/transact', 'id='.$contributionPageId.'&pcpId='.$pcpId.'&reset=1');
    
    $aContactTypes   = CRM_Contact_BAO_Contact::getContactTypes( $pcpValues['contact_id'] );
    $isTeamPcp       = in_array('Team'      , $aContactTypes) ? TRUE : FALSE;
     $params[$pcpId]['page_title']       = CRM_Core_DAO::getFieldValue('CRM_Event_DAO_Event', $pcpValues['page_id'], 'title');
     $params[$pcpId]['amount_raised']    = civicrm_api3_pcpteams_getAmountRaised($pcpId);
     $params[$pcpId]['image_url']        = $imageUrl ? $imageUrl : CRM_Pcpteams_Constant::C_DEFAULT_PROFILE_PIC;
     $params[$pcpId]['image_id']         = $fileId;
     $params[$pcpId]['donate_url']       = $donateUrl;
     $params[$pcpId]['is_teampage']      = $isTeamPcp;
  }
}

function civicrm_api3_pcpteams_getAmountRaised($pcpId) {
    $query = "
    SELECT SUM(cc.total_amount) as total
    FROM civicrm_pcp_block cpb
    INNER JOIN civicrm_contribution cc on (cc.contribution_page_id = cpb.target_entity_id )
    WHERE cc.id IN ( SELECT contribution_id FROM civicrm_contribution_soft WHERE pcp_id = %1)
    AND cc.contribution_status_id =1 AND cc.is_test = 0";

    $params = array(1 => array($pcpId, 'Integer'));
    $amountRaised = CRM_Core_DAO::singleValueQuery($query, $params);
    return $amountRaised ? $amountRaised : '0.00';
}

function civicrm_api3_pcpteams_getTeamRequestInfo($params) {
  $result= CRM_Core_DAO::$_nullArray;
  $query = " 
    SELECT crs.pcp_a_b, cc.display_name, cp.page_id, cr.id FROM civicrm_value_pcp_relationship_set crs
    INNER JOIN civicrm_relationship cr ON (cr.id = crs.entity_id)
    INNER JOIN civicrm_pcp cp ON (cp.id = crs.pcp_a_b)
    INNER JOIN civicrm_contact cc ON (cr.contact_id_a = cc.id)
    WHERE crs.pcp_b_a = %1";
  
  $queryParams = array(
    1 => array($params['team_pcp_id'], 'Integer'),
  );
  
  $dao = CRM_Core_Dao::executeQuery($query, $queryParams);
  while($dao->fetch()) {
    $memberPcpResult = civicrm_api('Pcpteams', 'get', array('version' => 3, 'sequential' => 1, 'pcp_id' => $dao->pcp_a_b));
    $getAllDonations = civicrm_api3_pcpteams_getAllDonations(array('page_id' => $dao->page_id, 'pcp_id' => $dao->pcp_a_b));
    $result[$dao->pcp_a_b] = array(
      'member_display_name'       => $dao->display_name,
      'member_pcp_id'             => $dao->pcp_a_b,
      'amount_raised'             => $memberPcpResult['values'][0]['amount_raised'],
      'donations_count'           => $getAllDonations['count'],
      'image_url'                 => $memberPcpResult['values'][0]['image_url'] ? $memberPcpResult['values'][0]['image_url'] : CRM_Pcpteams_Constant::C_DEFAULT_PROFILE_PIC,
      'image_id'                  => $memberPcpResult['values'][0]['image_id'],
      'team_pcp_id'               => $params['team_pcp_id'],
      'relationship_id'           => $dao->id
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
