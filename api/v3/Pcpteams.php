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
  $result = array();
  return civicrm_api3_create_success($result, $params);
}
function _civicrm_api3_pcpteams_create_spec(&$params) {
  $params['title']['api.required'] = 1;
}

function civicrm_api3_pcpteams_get($params) {
  $dao = new CRM_PCP_DAO_PCP();
  // FIXME: need to enforce type check
  $dao->id = $params['pcp_id']; // type check done by getfields
  $result = _civicrm_api3_dao_to_array($dao);

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
  $result = _civicrm_api3_dao_to_array($dao);

  // Append custom info
  // Note: This should ideally be done in _civicrm_api3_dao_to_array, but since PCP is not one of 
  // recongnized entity in core, we can append it seprately for now.
  _civicrm_api3_pcpteams_custom_get($result);

  return civicrm_api3_create_success($result, $params);
}
function _civicrm_api3_pcpteams_getcontactpcp_spec(&$params) {
  $params['contact_id']['api.required'] = 1;
}

function _civicrm_api3_pcpteams_custom_get(&$params) {
  foreach ($params as $rid => $rval) {
    _civicrm_api3_custom_data_get($params[$rid], 'PCP', $rid);
    // FIXME: we should at some point replace "custom_xy_" with column-names
  }
}
