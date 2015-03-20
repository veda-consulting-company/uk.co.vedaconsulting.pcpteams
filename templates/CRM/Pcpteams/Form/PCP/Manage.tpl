 <!-- ( PCP Title ) -->
<div id="pcp_title-{$pcpinfo.id}" class="crm-pcp-inline-edit" data-edit-params='{ldelim}"cid": "{$contactId}", "class_name": "CRM_Contact_Form_Inline_ContactInfo"{rdelim}'>
  {$pcpinfo.title}
</div>
<div class="clearfix"></div> 

<!-- ( Intro Text ) -->
<div id="pcp_intro_text-{$pcpinfo.id}" class="crm-pcp-inline-edit" data-edit-params='{ldelim}"cid": "{$contactId}", "class_name": "CRM_Contact_Form_Inline_ContactInfo"{rdelim}'>
  {$pcpinfo.intro_text}
</div>
<div class="clearfix"></div>
 
 <!-- ( Page Text ) -->
<div id="pcp_page_text-{$pcpinfo.id}" class="crm-pcp-inline-edit">
  {$pcpinfo.page_text}
</div>
<div class="clearfix"></div>

<!-- ( Goal Amount ) -->
<div id="pcp_goal_amount-{$pcpinfo.id}" class="crm-pcp-inline-edit" data-edit-params='{ldelim}"cid": "{$contactId}", "class_name": "CRM_Contact_Form_Inline_ContactInfo"{rdelim}'>
  {$pcpinfo.goal_amount}
</div>
<div class="clearfix"></div>


<!-- Congratulations block -->
<div class="crm-accordion-wrapper pcp-manage-block-info">
  <div class="crm-accordion-header">Event Name: {if $tplParams.event_title} {$tplParams.event_title} {/if}</div>
  <div class="crm-accordion-body pcp-manage-block-info-text crm-pcp-inline-edit">
    <strong>

      Congratulations, you are now signed up for {$tplParams.event_title}
    </strong>
    <br />
    <p>
      We have created this page to help you with your fundraising.

      Please take a few minutes to complete a couple of details below, you will need to add a fundraising

      target to give you something to aim for (aim high!) and write a little bit about yourself to encourage

      people to help you reach that target.

      If you want to do this event as a team or in memory of a loved one you can set that up below as well.
    </p>
    <br />
    <strong>
      Good Luck!!
    </strong>
  </div>
</div>

<!-- End Congratulations block -->
{literal}
<script type="text/javascript">
CRM.$(function($) {
 $(document).ready(function() {
    var apiUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='className=CRM_Pcpteams_Page_AJAX&fnName=inlineEditorAjax&snippet=6&json=1'}";{literal}
    $('.crm-pcp-inline-edit').editable(apiUrl, {
         //loadurl   : "http://cms45.loc/civicrm/ajax/inline?cid=202&class_name=CRM_Contact_Form_Inline_ContactInfo&snippet=6&reset=1",
         type      : 'textarea',
         cssclass  : 'crm-form-textarea',
         cancel    : 'Cancel',
         submit    : 'OK',
         height    : '100',
         indicator : '<img src="http://www.appelsiini.net/projects/jeditable/img/indicator.gif">',
         "callback"  : function( editedValue ){
            var editedId = cj(this).attr('id');
            cj(this).html(editedValue);
            // console.log( );
          }
    });
    $('.crm-pcp-inline-edit').mouseover(function(){
      $(this).css("background", "grey");
    });
    $('.crm-pcp-inline-edit').mouseout(function(){
      $(this).css("background", "white");
    });
 });
});
</script>
{/literal}
