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

  $pcp = CRM_PCP_BAO_PCP::add($params, FALSE);
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
   
  $result[$dao->id]['amount_raised'] = CRM_PCP_BAO_PCP::thermoMeter($params['pcp_id']);
  
  // Append custom info
  // Note: This should ideally be done in _civicrm_api3_dao_to_array, but since PCP is not one of 
  // recongnized entity in core, we can append it seprately for now.
  _civicrm_api3_pcpteams_custom_get($result);
  
  //custom data with customfield names, to avoid reusing the custom field id everytime
  _civicrm_api3_pcpteams_getCustomData($result);

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
  $dao->is_active  = $params['is_active']; 
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
        <li>
          <a href=\"{$deleteURL}\" class=\"action-item crm-hover-button small-popup\" title='Delete' onclick = \"return confirm('Are you sure you want to delete this Personal Campaign Page?\nThis action cannot be undone.');\">Delete</a>
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

function civicrm_api3_pcpteams_getMyPendingTeam($params) {
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
    WHERE cp.contact_id IN ( $sTeamIds )
  ";
  $dao = CRM_Core_DAO::executeQuery($query);
  while($dao->fetch()){
    $teamPendingResult[] = array(
      'teamName'      => $dao->team_name,
      'teamPcpTitle'  => $dao->pcp_title,
      'pageTitle'     => $dao->page_title,
      'teamgoalAmount'=> CRM_Utils_Money::format($dao->pcp_goal_amount),
      'amount_raised' => CRM_Utils_Money::format(CRM_PCP_BAO_PCP::thermoMeter($dao->pcp_id)),
      'teamPcpId'     => $dao->pcp_id,
      'contactId'     => $dao->pcp_contact_id,
    );
  }
  $result = $teamPendingResult;
  return civicrm_api3_create_success($result, $params);
}

function civicrm_api3_pcpteams_getTeamRequest($params) {
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
    $relTypeId            = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType',  CRM_Pcpteams_Constant::C_TEAM_RELATIONSHIP_TYPE, 'id', 'name_a_b');
    $teamMemberQuery      = "SELECT `contact_id_a` as team_member_contact_id, id as activity_id FROM `civicrm_relationship` WHERE `is_active` = 0 AND `relationship_type_id` = $relTypeId AND `contact_id_b` IN ( $steamAdminContactIds)";
    $teamMemberQueryDao   = CRM_Core_DAO::executeQuery($teamMemberQuery);
    $teamMemberContactIds = array();
    while($teamMemberQueryDao->fetch()) {
      $teamMemberContactIds[$teamMemberQueryDao->activity_id] = $teamMemberQueryDao->team_member_contact_id;
    }
    $steamMemberContactIds = implode(', ', array_filter($teamMemberContactIds));
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
      LEFT JOIN civicrm_event ce ON ce.id = cp.page_id
      LEFT JOIN civicrm_contact cc ON ( cc.id = cp.contact_id )
      LEFT JOIN civicrm_email cl ON ( cl.contact_id = cp.contact_id )
      LEFT JOIN civicrm_address ca ON ( ca.contact_id = cp.contact_id )
      WHERE cp.contact_id IN ( $steamMemberContactIds )
    ";
    $dao = CRM_Core_DAO::executeQuery($query);
    while($dao->fetch()){
      $teamRequest[$dao->pcp_id] = array(
        'member_display_name' => $dao->display_name,
        'member_email'        => $dao->email,
        'member_city'         => $dao->city,
        'member_country'      => $dao->country,
        'member_state_province_id'  => $dao->state_province_id,
        'member_pcp_id'             => $dao->pcp_id,
        'member_pcp_title'          => $dao->pcp_title,
        'member_page_title'         => $dao->page_title,
        'contactId'                 => $dao->pcp_contact_id,
        'action'                    => _getTeamRequestActionLink(array_search($dao->pcp_contact_id, $teamMemberContactIds), $dao->pcp_id),
      );
    }
  $result = $teamRequest;
  return civicrm_api3_create_success($result, $params);
  
}

function _getTeamRequestActionLink($activityId, $pcpId ){
  $action     = "
    <span>
      <a href='javascript:void(0)' class=\"action-item crm-hover-button\" title='Approve Team Member' onclick='approveTeamMember({$activityId},{$pcpId});'>Approve</a>
      <a href='javascript:void(0)' class=\"action-item crm-hover-button\" title='Decline Team Member' onclick='declineTeamMember({$entityId});'>Decline</a>
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
  // Get the pcpIds
  $result = civicrm_api('Pcpteams', 'getcontactpcp', 
      array(
        'contact_id' => $params['contact_id'],
        'version'    => 3
      )
  );
  if (!empty($result['values'])) {
    foreach($result['values'] as $value) {
      $contactPcpIds[] = $value['id'];
    }
    $scontactPcpIds = implode(', ', $contactPcpIds);
    $query           = "SELECT team_pcp_id, entity_id FROM civicrm_value_pcp_custom_set where entity_id IN ($scontactPcpIds)";
    $teamPcpIdsDao   = CRM_Core_DAO::executeQuery($query);
    // Get team pcpIds
    $myPcp = array();
    while($teamPcpIdsDao->fetch()) {
      $teamPcpContactIds[] = CRM_Pcpteams_Utils::getcontactIdbyPcpId($teamPcpIdsDao->team_pcp_id);
      $myPcp[$teamPcpIdsDao->entity_id]= $teamPcpIdsDao->team_pcp_id;
    }
    //print_r($teamAdminContactIds);
    //die();
    $validTeamContactIds  = array_intersect($teamPcpContactIds, $teamAdminContactIds);
    $svalidTeamContactIds = implode(', ', $validTeamContactIds);
    $findQuery = "SELECT cpcs.team_pcp_id as team_pcp_id, cp_a.id as my_pcp_id, cr.id as activity_id, cr.`contact_id_a` as member, cr.contact_id_b as team, cr.`relationship_type_id` as rel_type_id
                  FROM civicrm_pcp cp
                  LEFT JOIN civicrm_value_pcp_custom_set cpcs on (cpcs.team_pcp_id = cp.id)
                  LEFT JOIN civicrm_pcp cp_a on (cp_a.id = cpcs.entity_id)
                  LEFT JOIN civicrm_relationship cr on (cr.contact_id_a = cp_a.contact_id AND cr.contact_id_b = cp.contact_id AND cr.is_active=1)
                  WHERE cp.contact_id IN ($svalidTeamContactIds)";
   
    $findQueryDao = CRM_Core_DAO::executeQuery($findQuery);
    while($findQueryDao->fetch()) {
      if(CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', $findQueryDao->rel_type_id, 'name_a_b', 'id') == CRM_Pcpteams_Constant::C_TEAM_ADMIN_REL_TYPE) {
        continue;
      }
      $memberDetails[] = array(
        'my_pcp_id'  => $findQueryDao->my_pcp_id,
        'team_pcp_id'=> $findQueryDao->team_pcp_id,
        'memberName' => CRM_Contact_BAO_Contact::displayName($findQueryDao->member),
        'type'       => 'No',
        'teamName'   => CRM_Contact_BAO_Contact::displayName($findQueryDao->team),
        'action'     => _getTeamMemberActionLink($findQueryDao->activity_id, $findQueryDao->my_pcp_id, $findQueryDao->team_pcp_id),

      );
    }
  }
  $result = $memberDetails;
  return civicrm_api3_create_success($result, $params);
}

function _getTeamMemberActionLink($activityId, $myPcpId, $teamPcpId){
  $action     = "
    <span>
      <a href='javascript:void(0)' class=\"action-item crm-hover-button\" title='Remove From Team' onclick='removeTeamMember({$activityId},{$myPcpId},{$teamPcpId} );'>Remove</a>
      <a href='javascript:void(0)' class=\"action-item crm-hover-button\" title='De-activate' onclick='deactivateTeamMember({$activityId}, {$myPcpId},{$teamPcpId});'>De-activate</a>
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
