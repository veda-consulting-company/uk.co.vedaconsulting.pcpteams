<!-- header -->
<div class="crm-pcp-manage">

  <div class="pcp-panel">
    <!-- profile Image -->
    <div class="avatar-title-block">
      <div class="avatar">
        {if $profilePicUrl}
          <img class="crm-pcp-inline-edit-pic" href="{$updateProfPic}"width="150" height="150" src="{$profilePicUrl}">
        {/if}
      </div>
      <div id="pcp_title" class="title crm-pcp-inline-edit">{$pcpinfo.title}</div>
      <div class="clear"></div>
    </div>
    <div class="stats">
      <div class="raised-total">
        <span class="amount">{$pcpinfo.amount_raised|crmMoney:$pcpInfo.currency}</span>
        <div class="raised"><span class="text">Raised so far</span></div>
      </div> 
      <div class="target">
        <span class="text">Of target</span>
        <div id="pcp_goal_amount" class="amount crm-pcp-inline-edit">{$pcpinfo.goal_amount|crmMoney:$pcpInfo.currency}</div>
      </div> 
    </div>
  </div>
  <!-- End header-->
 
  {if $no_donations} {* if $no-donations *}
  <div class="pcp-info pcp-message">
    <h3>Congratulations, you are now signed up for '{$pcpinfo.page_title}'</h3>
    <p>We have created this page to help you with your fundraising.
      Please take a few minutes to complete a couple of details below, you will need to add a fundraising
      target to give you something to aim for (aim high!) and write a little bit about yourself to encourage
      people to help you reach that target.
      If you want to do this event as a team or in memory of a loved one you can set that up below as well.
    </p>
  </div>
  {/if}

  <div class="pcp-body">
    <div class="totaliser-giveto-block">
      <div class="totaliser">
        <div class="colheader">
          Totaliser
        </div>
        <!-- BIO section -->
        <div id="pcp_intro_text" class="intro-text crm-pcp-inline-edit" data-edit-params='{ldelim}"cid": "{$contactId}", "class_name": "CRM_Contact_Form_Inline_ContactInfo"{rdelim}'>{$pcpinfo.intro_text}</div>
        <div id="pcp_page_text" class="page-text crm-pcp-inline-edit">{$pcpinfo.page_text}</div>
        <!-- BIO section ends -->
        <div class="team-section">
          {assign var="team_pcp_id" value=$pcpinfo.team_pcp_id}
          {if $pcpinfo.team_pcp_id}
            <div class="avatar">
              {if $teamProfilePic}
                <img width="75" height="75" src="{$teamProfilePic}">
              {/if}
            </div>
            <span class="team-name"><a href='{crmURL p="civicrm/pcp/manage" q="id=$team_pcp_id"}'>{$teamPcpInfo.title}</a></span>
            <span class="team-text">{$teamPcpInfo.intro_text}</span>
          {elseif $isa_team_page}
            {if $no_donations} {* if $no-donations *}
              <div class="invite-team-text">Invite people to the team</div>
              <div class="invite-team-buttons">
                <a id="invite-team-btn" class="button crm-pcp-inline-edit-team" href="{$inviteTeamURl}">{ts}Invite Team Members{/ts}</a>
              </div>
            {else}
              <span class="top-fund-text">Top team fundraisers.</span>
              <div class="top-fund-raisers">
                {if $topTeamDonationInfo}
                  {foreach from=$topTeamDonationInfo item=teamDonations}
                    <span class="fname">{$teamDonations.display_name}</span>
                  {/foreach}
                {else}
                  <span class="fname">No Donations recorded yet..</span>
                {/if}
              </div>
              <div class="top-fund-buttons">
                <a class="button crm-pcp-inline-edit-team" href="{$createTeamUrl}">{ts}Invite to Team{/ts}</a>
                <a class="button crm-pcp-inline-edit-team" href="{$joinTeamUrl}">{ts}See all Team{/ts}</a>
              </div>
            {/if}
          {else}
            <span class="no-team-text">Fundraise more, fundraise as a team Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius. Vestibulum viverra mi dictum odio scelerisque semper. Morbi</span>
            <p><strong> Fundraise more, fundraise as a team </strong></p><br>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius. Vestibulum viverra mi dictum odio scelerisque semper. Morbi</span>
            <div class="no-team-buttons">
              <a id="create-team-btn" class="button crm-pcp-inline-edit-team" href="{$createTeamUrl}">{ts}Create a Team{/ts}</a>
              <a id="join-team-btn" class="button crm-pcp-inline-edit-team" href="{$joinTeamUrl}">{ts}Join a Team{/ts}</a>
            </div>
          {/if}
          <div class="clear"></div>
        </div>
      </div>
      <div class="givetoname">
        <div class="colheader">
          <div class="btn-donate">
            <a href="/dfp/donate/59772/nojs"><span id="donate_link_text" class="crm-pcp-inline-btn-edit">Donate</span></a>
          </div>
        </div>
        <div class="rank">
          This Page is <strong>{$rankInfo.rank}<small>{$rankInfo.suffix}</small></strong> out of the <strong>{$rankInfo.pageCount}</strong> fundraisers taking part in event.
        </div>
        {foreach from=$donationInfo item=donations}
          <div class="top-donations">
            {$donations.display_name} has donated <strong> {$donations.total_amount|crmMoney} </strong>
          </div>
        {/foreach}
      </div>
      <div class="clear"></div>
    </div>

    {if $pcpinfo.is_teampage}
      <div class="member-block">
        <div class="mb-header">
          Team Members
        </div>
        <div class="mb-body">
          {foreach from=$teamMemberInfo item=memberInfo}
          <div class="mb-row">
            <div class="mb-body-row action">
              Remove link(admin)
            </div>
            <div class="mb-body-row name">
              {$memberInfo.member_contact_name} 
              {if $memberInfo.is_team_admin}
                <br>
                <small> ( Team Admin ) </small>
              {/if}
            </div>
            <div class="mb-body-row progress">
              FIXME count of Donations
            </div>
            <div class="mb-body-row raised">
              {$memberInfo.member_goal_amount|crmMoney}
            </div>
            <div class="mb-body-row donate">
              <input type="button" name="donate" value="donate"/>
            </div>
            <div class="clear"></div>
          </div>
          {/foreach}
          <div class="clear"></div>
        </div><!-- mb-body ends -->
        <div class="clear"></div>
      </div><!-- member-block ends-->
    {/if}
  </div>

  <div class="clear"></div>
</div>

{literal}
<script type="text/javascript">
CRM.$(function($) {
  var apiUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='className=CRM_Pcpteams_Page_AJAX&fnName=inlineEditorAjax&snippet=6&json=1'}";{literal}
  var editparams = {
    type      : 'text',
    //cssclass  : 'crm-form-textarea',
    //style     : 'inherit',
    cancel    : 'Cancel',
    submit    : 'OK',
    submitdata: {pcp_id: {/literal}{$pcpinfo.id}{literal}},
    tooltip   : 'Click to edit..',
    indicator : 'Saving..',//'<img src="http://www.appelsiini.net/projects/jeditable/img/indicator.gif">',
    callback  : function( editedValue ){
       var editedId = cj(this).attr('id');
       $(this).html(editedValue);
       $(this).css("background", "#F7F6F6");
       $(this).css("border", "none");
     }
  }
  $('.crm-pcp-inline-edit').editable(apiUrl, editparams);

  editparams['callback'] = function( editedValue ){
    var editedId = cj(this).attr('id');
    $(this).html(editedValue);
    $(this).css("background", "#e0001a");
    $(this).css("border", "none");
  }
  $('.crm-pcp-inline-btn-edit').editable(apiUrl, editparams);
  $('.crm-pcp-inline-edit').mouseover(function(){
    $(this).css("background", "#E5DEDE");
    $(this).css("border", "2px dashed #c4c4c4");
    $(this).css("border-radius", "10px");
  });
  $('.crm-pcp-inline-edit').mouseout(function(){
    $(this).css("background", "#F7F6F6");
    $(this).css("border", "none");
  });
  $('.crm-pcp-inline-btn-edit').mouseover(function(){
    $(this).css("background", "rgb(19, 18, 18)");
    $(this).css("border", "2px dashed #c4c4c4");
    $(this).css("border-radius", "10px");
  });
  $('.crm-pcp-inline-btn-edit').mouseout(function(){
    $(this).css("background", "#e0001a");
    $(this).css("border", "none");
  });
  
  //inline Create and Join Team 
  cj('.crm-pcp-inline-edit-team').on('click', function(ev){
    ev.preventDefault();
    var url = cj(this).attr('href');
    var id = cj(this).attr('id');
    var title = 'Join Team';
    if(id = 'create-team-btn'){
      title = 'Create Team';
    }
    if(id = 'invite-team-btn'){
      title = 'Invite Team';
    }
    if(url){
      CRM.loadForm(url, {dialog: {width: 650, height: 'auto', title: title}
                  }).on('crmFormSuccess', function(e, data) {});
    }// end if 
  });// end on click
  
  //inline Profile Image 
  cj('.crm-pcp-inline-edit-pic').on('click', function(ev){
    var url = cj(this).attr('href');
    if(url){
      CRM.loadForm(url, {dialog: {width: 500, height: 'auto'}
                  }).on('crmFormSuccess', function(e, data) {});
    }// end if 
  });// end on click
  $('.crm-pcp-inline-edit-pic').mouseover(function(){
    $(this).css("background", "#E5DEDE");
    $(this).css("border", "2px dashed #c4c4c4");
    $(this).css("border-radius", "10px");
  });
  $('.crm-pcp-inline-edit-pic').mouseout(function(){
    $(this).css("background", "#F7F6F6");
    $(this).css("border", "none");
  });
  
  
});
</script>
{/literal}
