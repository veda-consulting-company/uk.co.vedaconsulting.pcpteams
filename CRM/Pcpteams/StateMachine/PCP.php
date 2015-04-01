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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2014
 * $Id$
 *
 */

/**
 * State machine for managing different states of the Import process.
 *
 */
class CRM_Pcpteams_StateMachine_PCP extends CRM_Core_StateMachine {

  /**
   * class constructor
   *
   * @param object $controller
   * @param \const|int $action
   *
   * @internal param \CRM_Contact_Import_Controller $object
   * @return \CRM_PCP_StateMachine_PCP CRM_Contact_Import_StateMachine
   */
  function __construct($controller, $action = CRM_Core_Action::NONE) {
    parent::__construct($controller, $action);

    $session = CRM_Core_Session::singleton();
    $session->set('singleForm', FALSE);

    $pages = array(
      // Note: we might want PCPAccount if other users of extension want the extension to care of user accounting
      //'cpfpa'  => 'CRM_Pcpteams_Form_PCPAccount', 
      'cpfeq'  => 'CRM_Pcpteams_Form_EventQuery',
      'cpfer'  => 'CRM_Pcpteams_Form_EventReact',
      'cpfec'  => 'CRM_Pcpteams_Form_EventConfirm',
      'cpfere' => 'CRM_Pcpteams_Form_EventRegister',
      'cpftq'  => 'CRM_Pcpteams_Form_TeamQuery',
      'cpftn'  => 'CRM_Pcpteams_Form_TeamReact',
      'cpftc'  => 'CRM_Pcpteams_Form_TeamConfirm',
      'cpftt'  => 'CRM_Pcpteams_Form_TeamThankYou',
      'cpfgq'  => 'CRM_Pcpteams_Form_GroupQuery',
      'cpfgj'  => 'CRM_Pcpteams_Form_GroupJoin',
      'cpftrq' => 'CRM_Pcpteams_Form_TributeQuery',
      'cpftrj' => 'CRM_Pcpteams_Form_TributeJoin',
    );

    $step      = CRM_Utils_Request::retrieve('code', 'String', $controller);
    $pcpId     = CRM_Utils_Request::retrieve('id', 'Positive', $controller);
    $pageId    = CRM_Utils_Request::retrieve('pageId', 'Positive', $controller);
    $component = CRM_Utils_Request::retrieve('component', 'String', $controller);
    $teamPcpId = CRM_Utils_Request::retrieve('tpId', 'Positive', $controller);
    $workflowTeam    = $controller->get('workflowTeam');

    // DS: for now we skipping branch and tribute screens. We might enable them back later.
    $controller->set('workflowGroup', 'skip'); // remove me later
    $controller->set('workflowTribute', 'skip'); // remove me later

    $workflowGroup   = $controller->get('workflowGroup');
    $workflowTribute = $controller->get('workflowTribute');

    // check if contact is already registered
    // get pcp id
    if ('event' == $controller->get('component') && $pageId) {
      $eventId = $pageId;
      if (is_null($controller->get('participantId'))) {
        $participantId = CRM_Pcpteams_Utils::isaParticipantFor($eventId);
        // store in session so we not checking everytime
        $controller->set('participantId', $participantId);
      }
      if (!$pcpId) {
        $pcpId = CRM_Pcpteams_Utils::getPcpId($pageId, $component, TRUE);
        $controller->set('id', $pcpId); // in PCPAccount.php this gets retrieved & set as page_id
        $controller->set('page_id', $pcpId); // set it anyway
      }
    }

    // if team pages need skipping
    // FIXME: we 'll need to keep pcp info laoded ad stored in static cache? 
    // so we not making this check everytime
    if ($controller->get('page_id') && 
      (empty($workflowTeam) || empty($workflowGroup) || empty($workflowTribute))) 
    {
      $result = civicrm_api(
        'Pcpteams', 
        'get', 
        array(
          'version'    => 3, 
          'sequential' => 1, 
          'pcp_id'     => $controller->get('page_id')
        )
      );
      if (empty($workflowTeam)) {
        $cfid = CRM_Pcpteams_Utils::getTeamPcpCustomFieldId();
        if (!empty($result['values'][0]["custom_{$cfid}"])) {
          $controller->set('workflowTeam', 'skip');
        }
      }
      if (empty($workflowGroup)) {
        $cfid = CRM_Pcpteams_Utils::getBranchorPartnerCustomFieldId();
        if (!empty($result['values'][0]["custom_{$cfid}"])) {
          $controller->set('workflowGroup', 'skip');
        }
      }
      if (empty($workflowTribute)) {
        $cfid = CRM_Pcpteams_Utils::getPcpTypeContactCustomFieldId();
        if (!empty($result['values'][0]["custom_{$cfid}"])) {
          $controller->set('workflowTribute', 'skip');
        }
      }
    }

    // if need jumping to invite page
    if (!$workflowTeam) {
      if (empty($teamPcpId)) {
        $teamPcpId = CRM_Core_Session::singleton()->get('pcpteams_tpid');
      }
      if ($teamPcpId) {
        $controller->set('tpId', $teamPcpId);
        $controller->set('workflowTeam', 'invite');
      }
    }

    // unset pages per workflow
    if ('invite' == $controller->get('workflowTeam')) { // team invite
      unset($pages['cpftc'],$pages['cpftt']);
    }
    if ('skip' == $controller->get('workflowTeam')) {
      // unset all team pages
      unset($pages['cpftq'],$pages['cpftn'],$pages['cpftc'],$pages['cpftt']);
    }
    if ('skip' == $controller->get('workflowGroup')) {
      // unset all group pages
      unset($pages['cpfgq'],$pages['cpfgj']);
    }
    if ('skip' == $controller->get('workflowTribute')) {
      // unset all group pages
      unset($pages['cpftrq'],$pages['cpftrj']);
    }

    // if no event or already registered, skip event pages
    if (!$eventId || $controller->get('participantId')) {
      unset($pages['cpfec'], $pages['cpfere']);
    }
    if ('cpfeq' != $step) {
      unset($pages['cpfeq'], $pages['cpfer']);
    }

    if (!$step) {
      // if no code, set it true, so we consider all pages
      $stepFound = true;
    } else {
      // otherwise set it to false, we consider all pages starting from the code
      $stepFound = false;
      // DS: we now using drupal's account page
      //if (!$session->get('userID')) {
      // if user not logged in, inject the account page anyway
      //$this->_pages[$pages['cpfpa']] = NULL;
      //}
    }

    foreach ($pages as $pCode => $page) {
      if ($pCode == $step) {
        $stepFound = true;
      }
      if ($stepFound) {
        $this->_pages[$page] = NULL;
      }
    }

    if (empty($this->_pages)) {
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/pcp/manage', 'id='.$controller->get('page_id')));
    }
    $this->addSequentialPages($this->_pages, $action);
  }
}
