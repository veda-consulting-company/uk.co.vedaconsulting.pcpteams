<?php

require_once 'pcpteams.civix.php';
require_once 'CRM/Pcpteams/Constant.php';

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
  $customDataXMLFile = $extensionDir  . '/xml/CustomGroupData.xml';
  $import = new CRM_Utils_Migrate_Import( );
  $import->run( $customDataXMLFile );
  
  //Create Contact Subtype
  $params = array('parent_id' => 3, 'is_active' => 1, 'is_reserved' => 0);
  foreach (array(
    CRM_Pcpteams_Constant::C_CONTACT_SUB_TYPE
    , CRM_Pcpteams_Constant::C_CONTACTTYPE_IN_MEM
    , CRM_Pcpteams_Constant::C_CONTACTTYPE_IN_CELEB
    , CRM_Pcpteams_Constant::C_CONTACTTYPE_BRANCH
    , CRM_Pcpteams_Constant::C_CONTACTTYPE_PARTNER
    ) as $subTypes) {
    if(!CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_ContactType', $subTypes, 'id', 'name')){
      $params['name']  = $subTypes;
      $params['label'] = str_replace('_', ' ', $subTypes);
      CRM_Contact_BAO_ContactType::add($params);
    }
  }
  
  //set foreignkey
  $sql = "ALTER TABLE `civicrm_value_pcp_custom_set`
  MODIFY `team_pcp_id` int(10) unsigned DEFAULT NULL,
  ADD CONSTRAINT `FK_civicrm_value_pcp_custom_set_team_pcp_id` FOREIGN KEY (`team_pcp_id`) REFERENCES `civicrm_pcp` (`id`) ON DELETE SET NULL";
  CRM_Core_DAO::executeQuery($sql);
  
  $messageHtmlSampleTeamInviteFile  = $extensionDir . '/message_templates/sample_team_invite.html';
  $messageHtml      = file_get_contents($messageHtmlSampleTeamInviteFile);
  $message_params = array(
    'sequential'  => 1,
    'version'     => 3,
    'msg_title'   => "Sample Team Invite Template",
    'msg_subject' => "Sample Team Invite",
    'is_default'  => 1,
    'msg_html'    => $messageHtml,
    'msg_text'    => 'sample team invite text',
  );
  $result = civicrm_api3('MessageTemplate', 'create', $message_params);

  return _pcpteams_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function pcpteams_civicrm_uninstall() {
  //Remove required data added when install extensions
  CRM_Core_DAO::executeQuery("
    DROP TABLE IF EXISTS civicrm_value_pcp_custom_set");
  CRM_Core_DAO::executeQuery("
    DELETE opv.* 
    FROM civicrm_option_value opv
    INNER JOIN civicrm_option_group og on opv.option_group_id = og.id
    where og.name = 'pcp_tribute'");
  CRM_Core_DAO::executeQuery("
    DELETE FROM civicrm_option_group where name = 'pcp_tribute'");
  CRM_Core_DAO::executeQuery("
    DELETE cf.* 
    FROM civicrm_custom_field cf
    INNER JOIN civicrm_custom_group cg on cf.custom_group_id = cg.id
    where cg.name = 'PCP_Custom_Set'");
  CRM_Core_DAO::executeQuery("
    DELETE FROM civicrm_custom_group where name = 'PCP_Custom_Set'");  
  CRM_Core_DAO::executeQuery("
    DELETE pb.* 
    FROM civicrm_pcp_block pb
    LEFT JOIN civicrm_pcp pcp on pb.id = pcp.pcp_block_id
    WHERE pcp.id IS NULL");  
  CRM_Core_DAO::executeQuery("
    DELETE uff.*
    FROM civicrm_uf_field uff
    LEFT JOIN civicrm_uf_group ufg on ufg.id = uff.uf_group_id
    WHERE ufg.name = 'PCP_Supporter_Profile'");  
  CRM_Core_DAO::executeQuery("
    DELETE ufj.*
    FROM civicrm_uf_join ufj
    LEFT JOIN civicrm_uf_group ufg on ufg.id = ufj.uf_group_id
    WHERE ufg.name = 'PCP_Supporter_Profile'");  
  CRM_Core_DAO::executeQuery("
    DELETE FROM civicrm_uf_group WHERE name = 'PCP_Supporter_Profile'");
  CRM_Core_DAO::executeQuery("
    DELETE msgt.*
    FROM civicrm_msg_template msgt WHERE msgt.msg_title = 'Sample Team Invite Template'"); 
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
    $query      = "SELECT pcp.contact_id, cs.tribute_contact_id 
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

  if($op == 'create' && $objectName == 'Participant') {
    $pcpBlockId = CRM_Pcpteams_Utils::getPcpBlockId($objectRef->event_id);
    if($pcpBlockId) {
      // Auto create default PCP
      CRM_Pcpteams_Utils::getPcpId($objectRef->event_id, 'event', TRUE, $objectRef->contact_id );
    }
  }
}

function pcpteams_civicrm_buildForm($formName, &$form) {
  if($formName == 'CRM_Event_Form_Registration_ThankYou') {
    $template              = CRM_Core_Smarty::singleton( );
    $beginHookFormElements = $template->get_template_vars();
    if($beginHookFormElements['pcpLink']) {
      $pageId = $form->getVar('_eventId');
      $supportURL  = CRM_Utils_System::url('civicrm/pcp/support', "reset=1&pageId={$pageId}&component=event&code=cpftq");
      $form->assign('pcpLink', $supportURL);
    }
  }
}


/**
 * Implementation of hook_civicrm_alterAPIPermissions
 * Copying the permissions same as entity = 'Contact'
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterAPIPermissions
 */
function pcpteams_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
    $permissions['pcpteams'] = array(
    'create' => array(
      'access CiviCRM',
    ),
    // managed by query object
    'get' => array(),
    'getContactlist' => array(
      'access CiviCRM',
      // 'access AJAX API',
    ),
    'getEventList' => array(
    'access CiviEvent',
    // 'access AJAX API',
    ),
  );
}
