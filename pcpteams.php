<?php

require_once 'pcpteams.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function pcpteams_civicrm_config(&$config) {
  _pcpteams_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function pcpteams_civicrm_xmlMenu(&$files) {
  _pcpteams_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function pcpteams_civicrm_install() {
  //create custom group from xml file 
  // Create OptionGroup, OptionValues, RelationshipType, CustomGroup and CustomFields
  $extensionDir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
  $customDataXMLFile = $extensionDir  . 'CustomGroupData.xml';
  require_once 'CRM/Utils/Migrate/Import.php';
  $import = new CRM_Utils_Migrate_Import( );
  $import->run( $customDataXMLFile );
  
  //Create Contact Subtype
  $params = array('parent_id' => 3, 'is_active' => 1, 'is_reserved' => 0);
  require_once 'CRM/Contact/BAO/ContactType.php';
  foreach (array('Team'
                , 'In_Memory'
                , 'In_Celebration'
                , 'Branch'
                , 'Corporate_Partner'
                ) as $subTypes) {
    if(!CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_ContactType', $subTypes, 'id', 'name')){
      $params['name']  = $subTypes;
      $params['label'] = str_replace('_', ' ', $subTypes);
      CRM_Contact_BAO_ContactType::add($params);
    }
  }

  return _pcpteams_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function pcpteams_civicrm_uninstall() {
  return _pcpteams_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function pcpteams_civicrm_enable() {
  return _pcpteams_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function pcpteams_civicrm_disable() {
  return _pcpteams_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function pcpteams_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _pcpteams_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function pcpteams_civicrm_managed(&$entities) {
  return _pcpteams_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function pcpteams_civicrm_caseTypes(&$caseTypes) {
  _pcpteams_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function pcpteams_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _pcpteams_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

// create soft credit for team contact
function pcpteams_civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
  if ($objectName == 'ContributionSoft' && $op == 'create' && $objectRef->pcp_id) {
    $query      = "SELECT pcp.contact_id, cs.pcp_type_contact 
      FROM civicrm_value_pcp_custom_set cs
      INNER JOIN civicrm_pcp pcp ON cs.team_pcp_id = pcp.id 
      WHERE cs.entity_id = %1";
    $dao = CRM_Core_DAO::executeQuery($query, array(1 => array($objectRef->pcp_id, 'Integer')) );
    $dao->fetch();
    
    if ($dao->contact_id) {
      $newSoft = clone $objectRef;
      $newSoft->contact_id = $dao->contact_id;
      $newSoft->pcp_personal_note = "Created From Hook";
      unset($newSoft->id);
      $newSoft->save();
    }

    if ($dao->pcp_type_contact) {
      $newSoft = clone $objectRef;
      $newSoft->contact_id = $dao->pcp_type_contact;
      $newSoft->pcp_personal_note = "Created From Hook";
      unset($newSoft->id);
      $newSoft->save();
    }
  }
}

function pcpteams_civicrm_navigationMenu(&$params){
  $maxId                = max(array_keys($params));
  $dashboardID          = $maxId+1;
  $individualID         = $maxId+2;
  $teamID               = $maxId+3;
  $groupID              = $maxId+4;
  $tributeID            = $maxId+5;
  $params[$dashboardID] = array(
          'attributes' => array(
          'label'     => ts('Go to Pages'),
          'name'      => 'Go to Pages',
          'url'       => NULL,
          'active'    => 1,
          'parentID'  => Null,
          'operator'  => NULL,
          'navID'     => $dashboardID,
          'permission'=> 'access CiviCRM',
        ),
  );
  //My Dashboard
  $params[$dashboardID]['child'][$individualID]= array(
                            'attributes' => array (
                                              'label'      => 'My Dashboard',
                                              'name'       => 'My Dashboard',
                                              'url'        => 'civicrm/pcp/dashboard?reset=1',
                                              'permission' => 'access CiviCRM',
                                              'operator'   => null,
                                              'separator'  => null,
                                              'parentID'   => $dashboardID,
                                              'navID'      => $individualID,
                                              'active'     => 1
                                              )
                          );  
  //TEAM Dashboard
  $params[$dashboardID]['child'][$teamID]= array(
                            'attributes' => array (
                                              'label'      => 'Team',
                                              'name'       => 'Team',
                                              'url'        => 'civicrm/pcp/dashboard?reset=1&state=team',
                                              'permission' => 'access CiviCRM',
                                              'operator'   => null,
                                              'separator'  => null,
                                              'parentID'   => $dashboardID,
                                              'navID'      => $teamID,
                                              'active'     => 1
                                              )
                          );
  //Group Dashboard
  $params[$dashboardID]['child'][$groupID]= array(
                            'attributes' => array (
                                              'label'      => 'Group',
                                              'name'       => 'Group',
                                              'url'        => 'civicrm/pcp/dashboard?reset=1&state=group',
                                              'permission' => 'access CiviCRM',
                                              'operator'   => null,
                                              'separator'  => null,
                                              'parentID'   => $dashboardID,
                                              'navID'      => $groupID,
                                              'active'     => 1
                                              )
                          );
  //Tribute Dashboard
  $params[$dashboardID]['child'][$tributeID]= array(
                            'attributes' => array (
                                              'label'      => 'Tribute',
                                              'name'       => 'Tribute',
                                              'url'        => 'civicrm/pcp/dashboard?reset=1&state=tribute',
                                              'permission' => 'access CiviCRM',
                                              'operator'   => null,
                                              'separator'  => null,
                                              'parentID'   => $dashboardID,
                                              'navID'      => $tributeID,
                                              'active'     => 1
                                              )
                          );
  }

