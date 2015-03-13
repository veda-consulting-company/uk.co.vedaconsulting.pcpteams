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

    // retrieve and store in controller session
    $eventId   = CRM_Utils_Request::retrieve('eventId', 'Positive', $controller);
    $teamPcpId = CRM_Utils_Request::retrieve('tpId', 'Positive', $controller);
    $workflowTeam = $controller->get('workflowTeam');

    if ($teamPcpId && !$workflowTeam) {
      $controller->set('workflowTeam', 'invite');
    }

    $step  = CRM_Utils_Request::retrieve('code', 'String', $controller);
    $pages = array(
      'cpfpa' => 'CRM_Pcpteams_Form_PCPAccount',
      'cpfp'  => 'CRM_Pcpteams_Form_PCP',
      'cpfed' => 'CRM_Pcpteams_Form_EventDetails',
      'cpfec' => 'CRM_Pcpteams_Form_EventConfirm',
      'cpftq' => 'CRM_Pcpteams_Form_TeamQuery',
      'cpftn' => 'CRM_Pcpteams_Form_TeamReact',
      'cpftc' => 'CRM_Pcpteams_Form_TeamConfirm',
      'cpftt' => 'CRM_Pcpteams_Form_TeamThankYou',
      'cpftw' => 'CRM_Pcpteams_Form_TeamWelcome',
      'cpfgq'  => 'CRM_Pcpteams_Form_GroupQuery',
      'cpfgj'  => 'CRM_Pcpteams_Form_GroupJoin',
      'cpftrq' => 'CRM_Pcpteams_Form_TributeQuery',
      'cpftrj' => 'CRM_Pcpteams_Form_TributeJoin',
    );
    if ($workflowTeam == 'invite') { // team invite
      unset($pages['cpftq']); // unset team query
      //$pages = $pages + array(
        //'cpfti' => 'CRM_Pcpteams_Form_TeamInvite',
      //);
    }
    if ($eventId && is_null($controller->get('participantId'))) {
      $participantId = CRM_Pcpteams_Utils::isaParticipantFor($eventId);
      // store in session so we not checking everytime
      $controller->set('participantId', $participantId);
    }

    // if no event or already registered, skip event pages
    if (!$eventId || $controller->get('participantId')) {
      unset($pages['cpfed'], $pages['cpfec']);
    }

    if (!$step) {
      // if no code, set it true, so we consider all pages
      $stepFound = true;
    } else {
      // otherwise set it to false, we consider all pages starting from the code
      $stepFound = false;
      if (!$session->get('userID')) {
        // if user not logged in, inject the account page anyway
        $this->_pages[$pages['cpfpa']] = NULL;
      }
    }

    foreach ($pages as $pCode => $page) {
      if ($pCode == $step) {
        $stepFound = true;
      }
      if ($stepFound) {
        $this->_pages[$page] = NULL;
      }
    }

    $this->addSequentialPages($this->_pages, $action);
  }
}
