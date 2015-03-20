<!-- header -->
<div class="crm-pcp-manage">

  <div class="pcp-panel">
    <!-- profile Image -->
    <div class="profile-avatar">
      {if $profilePicUrl}
        <img width="100" height="97" src="{$profilePicUrl}">
      {/if}
    </div>
    <div id="pcp_title-{$pcpinfo.id}" class="title crm-pcp-inline-edit">
      <h1>{$pcpinfo.title}</h1>
    </div>
    <div class="stats">
      <div class="raised-total">
        <span class="amount">{$pcpinfo.amount_raised}</span>
        <div class="raised"><span class="text">Raised so far</span></div>
      </div> 
      <div class="target">
        <span class="text">Of target</span>
        <div id="pcp_goal_amount-{$pcpinfo.id}" class="amount crm-pcp-inline-edit">{$pcpinfo.goal_amount}</span></div>
      </div> 
    </div>
  </div>
  <!-- End header-->
  
  <div class="pcp-body clearfix">
    <div class="totaliser clearfix">
      <div class="colheader">
        <h2>Totaliser</h2>
      </div>
      <div id="pcp_intro_text-{$pcpinfo.id}" class="crm-pcp-manage crm-pcp-inline-edit" data-edit-params='{ldelim}"cid": "{$contactId}", "class_name": "CRM_Contact_Form_Inline_ContactInfo"{rdelim}'>
        <p>{$pcpinfo.intro_text}</p>
      </div>
    
      <div id="pcp_page_text-{$pcpinfo.id}" class="crm-pcp-manage crm-pcp-inline-edit">
        <p>{$pcpinfo.page_text}</p>
      </div>
    </div>
    <div class="givetoname">
      <div class="colheader">
        <a href="/dfp/donate/59772/nojs" class="btn-donate">Donate</a>
      </div>
      <div class="rank">
        Name is #37 out of the 107 fundraisers taking part in event.
      </div>
      <div class="top-donations">
        Name has doanted £950
      </div>
      <div class="top-donations">
        Name has doanted £100
      </div>
      <div class="top-donations">
        Name has doanted £50
      </div>
    </div>
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
         //height    : '100',
         indicator : '<img src="http://www.appelsiini.net/projects/jeditable/img/indicator.gif">',
         "callback"  : function( editedValue ){
            var editedId = cj(this).attr('id');
            cj(this).html(editedValue);
            // console.log( );
          }
    });
    $('.crm-pcp-inline-edit').mouseover(function(){
      $(this).css("background", "#E5DEDE");
    });
    $('.crm-pcp-inline-edit').mouseout(function(){
      $(this).css("background", "#F7F6F6");
    });
 });
});
</script>
{/literal}
