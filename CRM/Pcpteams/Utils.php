<?php

/**
 * FIXME
 * Pcp Utils, 
 * to call and use the methods in all pcp forms / pages.
 */

class  CRM_Pcpteams_Utils {
	//Constants
	CONST C_PCP_CUSTOM_GROUP_NAME = 'PCP_Custom_Set',
				C_CUSTOM_GROUP_EXTENDS	= 'PCP';
	/**
	 * to get the logged in User Id
	 */
	function getloggedInUserId(){
    $session    = CRM_Core_Session::singleton( );
    $contactID  = $session->get('userID'        );
    return $contactID;
  }

}
