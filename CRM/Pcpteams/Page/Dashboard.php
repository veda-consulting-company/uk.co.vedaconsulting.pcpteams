<?php

require_once 'CRM/Core/Page.php';

class CRM_Pcpteams_Page_Dashboard extends CRM_Core_Page {
  function getloggedInUserId(){
    $session    = CRM_Core_Session::singleton( );
    $contactID  = $session->get('userID'        );
    return $contactID;
  }
  

  function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(ts('Dashboard'));


    parent::run();
  }
}
