<?php

/**
 * FIXME
 * Pcp Utils,
 * to call and use the methods in all pcp forms / pages.
 */

class  CRM_Pcpteams_Utils {
  //Constants
  CONST C_PCP_CUSTOM_GROUP_NAME = 'PCP_Custom_Set',
        C_CUSTOM_GROUP_EXTENDS	= 'PCP',
        C_PCP_TYPE              = 'pcp_type_20150219182347',
        C_PCP_ID                = 7;
  /**
   * to get the logged in User Id
   */
  static function getloggedInUserId(){
    $session    = CRM_Core_Session::singleton( );
    $contactID  = $session->get('userID'        );
    return $contactID;
  }
  
}
