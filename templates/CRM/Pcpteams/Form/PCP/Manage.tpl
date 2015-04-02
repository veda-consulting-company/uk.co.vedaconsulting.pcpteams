<!-- header -->
<div class="crm-pcp-manage">

  <div class="pcp-panel">
    <!-- profile Image -->
    <div class="avatar-title-block">
        <div class="avatar">
          <img id="{$pcpinfo.image_id}" {if $is_edit_page} class="crm-pcp-inline-pic-edit" {/if} href="{$updateProfPic}" width="150" height="150" src="{$pcpinfo.image_url}">
        </div>
        <div id="pcp_title" class="title {if $is_edit_page}crm-pcp-inline-text-edit{/if}">{$pcpinfo.title}</div>
      <div class="clear"></div>
    </div>
    <div id="pcp-progress" class="pcp-progress">
      <span class="stat-num"><strong>{$pcpinfo.percentage}<i>%</i></strong></span>
      <div class="circle">
      </div>
    </div>
    <div class="stats">
      <div class="raised-total">
        <span class="amount">{$pcpinfo.amount_raised|crmMoney:$pcpInfo.currency}</span>
        <div class="raised"><span class="text">Raised so far</span></div>
      </div> 
      <div class="target">
        <span class="text">Of target</span>
        {* FIXME, Style should to take care of css*}
        <div>
          <div class="amount symbol">{$pcpinfo.currency_symbol}</div>
          <div id="pcp_goal_amount" class="amount {if $is_edit_page}crm-pcp-inline-text-edit{/if}">{$pcpinfo.goal_amount}</div>
        </div>
      </div> 
    </div>
    <div class="clear"></div>
  </div>
  <!-- End header-->

  <div class="pcp-messages">
  {if !empty($pcpStatus)}
  {foreach from=$pcpStatus item=pstatus}
    <div class="{$pstatus.type} pcp-message">
      <h3>{$pstatus.title}</h3>
      <p>{$pstatus.text}</p>
    </div>
  {/foreach}
  {/if}
  </div>

  <div class="pcp-body">
    <div class="totaliser-giveto-block">
      <div class="totaliser">
        <div class="colheader">
          Totaliser
        </div>
        <!-- BIO section -->
        <div id="pcp_intro_text" {if $is_edit_page}class="intro-text crm-pcp-inline-text-edit" data-edit-params='{ldelim}"cid": "{$contactId}", "class_name": "CRM_Contact_Form_Inline_ContactInfo"{rdelim}' {else} class="intro-text" {/if}>{$pcpinfo.intro_text}</div>
        <div id="pcp_page_text" class="page-text {if $is_edit_page}crm-pcp-inline-text-edit{/if}">{$pcpinfo.page_text}</div>
        <!-- BIO section ends -->
        <div class="team-section">
          {if $pcpinfo.team_pcp_id}
            <div class="avatar">
              <img width="75" height="75" src="{$teamPcpInfo.image_url}">
            </div>
            <div class="team-info">
              <h3><a href="{crmURL p="civicrm/pcp/manage" q="id=`$pcpinfo.team_pcp_id`"}">{$teamPcpInfo.title}</a></h3>
              <p>{$teamPcpInfo.intro_text}</p>
            </div>
            <div class="team-stats">
              <div class="raised-total">
                <span class="amount">{$teamPcpInfo.amount_raised|crmMoney:$teamPcpInfo.currency}</span>
                <div class="raised"><span class="text">Raised so far</span></div>
              </div>
              <div class="target">
                <span class="text">Of target</span>
                <div id="pcp_goal_amount" class="amount">{$teamPcpInfo.goal_amount|crmMoney:$teamPcpInfo.currency}</div>
              </div>
            </div>
          {elseif $pcpinfo.has_approval_pending}
            <h3>You have {$pcpinfo.has_approval_pending} Team Request waiting for approval.</h3>
            {foreach from=$pcpinfo.approval_pending item=pendingTeams }
              <div>
                <div class="avatar">
                  <img width="75" height="75" src="{$teamPcpInfo.image_url}">
                </div>
                <div class="team-info">
                  <h3><a href="{crmURL p="civicrm/pcp/manage" q="id=``"}">{$pendingTeams.teamPcpTitle}</a></h3>
                  <p>{$pendingTeams.pageTitle}</p>
                </div>
                <div class="team-stats">
                  <div class="raised-total">
                    <span class="amount">{$teamPcpInfo.amount_raised|crmMoney:$teamPcpInfo.currency}</span>
                    <div class="raised"><span class="text">Raised so far</span></div>
                  </div>
                  <div class="target">
                    <span class="text">Of target</span>
                    <div id="pcp_goal_amount" class="amount">{$pendingTeams.teamgoalAmount|crmMoney:$teamPcpInfo.currency}</div>
                  </div>
                </div>
                <div class="clear"></div>
              </div>
              <div class="pending-team-buttons">
                <a id="cancel-pending-btn" class="pcp-button pcp-btn-red" href="javascript:void(0)" onclick="deletePendingApproval({$pendingTeams.relationship_id});">{ts}Withdraw Request{/ts}</a>
              </div>
              <!--
              <div class="team-info">
                <div>{$pendingTeams.teamName}</div>
                <div>{$pendingTeams.teamPcpTitle}</div>
                <div>{$pendingTeams.pageTitle}</div>
                <div>{$pendingTeams.teamgoalAmount}</div>
                <div class="no-team-buttons">
                  <a id="cancel-pending-btn" class="pcp-button pcp-btn-red" href="javascript:void(0)" onclick="deletePendingApproval({$pendingTeams.relationship_id});">{ts}Scrap / Delete Request{/ts}</a>
                </div>
              </div>
              -->
            {/foreach}        
          {elseif $pcpinfo.is_teampage}
            <!-- <div class="invite-team-text">Invite people to the team</div> -->
            <div class="team-buttons">
              <a id="invite-team-btn" class="pcp-button pcp-btn-red crm-pcp-inline-team-edit" href="{$inviteTeamURl}">{ts}Invite Team Members{/ts}</a>
              <a id="leave-team-btn" class="pcp-button pcp-btn-red" href="javascript:void(0)" onclick="leaveTeam({$pcpinfo.id}, {$userId})">{ts}Leave Team{/ts}</a>
            </div>
          {else}
            <div class="no-team-buttons">
              <a id="create-team-btn" class="pcp-button pcp-btn-red crm-pcp-inline-team-edit" href="{$createTeamUrl}">{ts}Create a Team{/ts}</a>
              <a id="join-team-btn" class="pcp-button pcp-btn-red crm-pcp-inline-team-edit" href="{$joinTeamUrl}">{ts}Join a Team{/ts}</a>
            </div>
          {/if}
          <div class="clear"></div>
        </div>
      </div>
      <div class="givetoname">
        <div class="colheader">
          <div class="btn-donate">
            <a href="{$pcpinfo.donate_url}"><span id="donate_link_text" {if $is_edit_page}class="crm-pcp-inline-btn-edit"{/if}>Donate</span></a>
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
      <div id="member-req-block" class="member-req-block">
        <div class="mem-header">
          Team Member Requests
        </div>
        <div class="mem-body">
          {foreach from=$teamMemberRequestInfo item=memberInfo}
          <div class="mem-row" id="member_{$memberInfo.pcp_id}">
            <!--
            <div class="mem-body-row action">
              Remove link(admin)
            </div> -->
            <div class="mem-body-row avatar">
              <img width="35" height="35" src="{$memberInfo.image_url}">
            </div>
            <div class="mem-body-row name">
              {$memberInfo.display_name} 
              {if $memberInfo.is_team_admin}
                <br>
                <small> ( Team Admin ) </small>
              {/if}
            </div>
            <div class="mem-body-row progress">
              {$memberInfo.donations_count} Donations
            </div>
            <div class="mem-body-row raised">
              {$memberInfo.amount_raised|crmMoney}
            </div>
            <div class="mem-body-row donate">
              <a class="pcp-button pcp-btn-green" href="javascript:void(0)" onclick="approveTeamMember('{$memberInfo.relationship_id}','{$memberInfo.pcp_id}','{$memberInfo.team_pcp_id}');return false;">{ts}Approve{/ts}</a>
              <a class="pcp-button pcp-btn-red" href="javascript:void(0)" onclick="declineTeamMember('{$memberInfo.relationship_id}', '{$memberInfo.pcp_id}');return false;">{ts}Decline{/ts}</a>
            </div>
            <div class="clear"></div>
          </div>
          {/foreach}
          <div class="clear"></div>
        </div><!-- mem-body ends -->
        <div class="clear"></div>
      </div><!-- member-request block ends-->

      <div class="member-block">
        <div class="mem-header">
          Team Members
        </div>
        <div class="mem-body">
          {foreach from=$teamMemberInfo item=memberInfo}
          <div class="mem-row">
            <!--
            <div class="mem-body-row action">
              Remove link(admin)
            </div> -->
            <div class="mem-body-row avatar">
              <img width="35" height="35" src="{$memberInfo.image_url}">
            </div>
            <div class="mem-body-row name">
              {$memberInfo.display_name} 
              {if $memberInfo.is_team_admin}
                <br>
                <small> ( Team Admin ) </small>
              {/if}
            </div>
            <div class="mem-body-row pcp-progress">
              <span>{$memberInfo.donations_count} Donations</span>
              <div class="pcp-bar">
                <div class="pcp-bar-progress" style="width: {$memberInfo.percentage}%;" title="{$memberInfo.percentage}%">
                </div>
              </div>
            </div>
            <div class="mem-body-row raised">
              {$memberInfo.amount_raised|crmMoney}
            </div>
            <div class="mem-body-row donate">
              <a class="btn-donate-small" href="{$memberInfo.donate_url}">{ts}Donate{/ts}</a>
            </div>
            <div class="clear"></div>
          </div>
          {/foreach}
          <div class="clear"></div>
        </div><!-- mem-body ends -->
        <div class="clear"></div>
      </div><!-- member-block ends-->
    {/if}
  </div>

  <div class="clear"></div>
</div>
{* FIXME style display none should take care of css, Need to Discuss with DS to make general alert message *}
<div class="crm-pcp-alert-leave-team" style="display:none;">
  <p> Are you sure, want to leave from this team ?</p>
</div>
<div class="crm-pcp-alert-approve-request" style="display:none;">
  <p> Would you llike to Approve this request ?</p>
</div>
<div class="crm-pcp-alert-decline-request" style="display:none;">
  <p> Are you sure, want to Decline this request ?</p>
</div>
<div class="crm-pcp-alert-cancel-pending-request" style="display:none;">
  <p> Are you sure, want to delete this request ?</p>
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
    indicator : 'Saving..',
    callback  : function( editedValue ){
       var editedId = cj(this).attr('id');
       $(this).html(editedValue);
       $(this).css("background", "#F7F6F6");
       $(this).css("border", "none");
     }
  }
  // inline text edit
  $('.crm-pcp-inline-text-edit').editable(apiUrl, editparams);
  $('.crm-pcp-inline-text-edit').mouseover(function(){
    $(this).css("background", "#E5DEDE");
    $(this).css("border", "2px dashed #c4c4c4");
    $(this).css("border-radius", "10px");
  });
  $('.crm-pcp-inline-text-edit').mouseout(function(){
    $(this).css("background", "#F7F6F6");
    $(this).css("border", "none");
  });

  // inline text edit for buttons
  editparams['callback'] = function( editedValue ){
    var editedId = cj(this).attr('id');
    $(this).html(editedValue);
    $(this).css("background", "#e0001a");
    $(this).css("border", "none");
  }
  $('.crm-pcp-inline-btn-edit').editable(apiUrl, editparams);
  $('.crm-pcp-inline-btn-edit').mouseover(function(){
    $(this).css("background", "rgb(19, 18, 18)");
    $(this).css("border", "2px dashed #c4c4c4");
    $(this).css("border-radius", "10px");
  });
  $('.crm-pcp-inline-btn-edit').mouseout(function(){
    $(this).css("background", "#e0001a");
    $(this).css("border", "none");
  });

  // team member request block show/hide
  $('.member-req-block').hide();
  $('#showMemberRequests').on('click', function() {
    $('.member-req-block').show('slow');
    $(this).parent().parent().hide();
  });
  
  //inline Create, Invite and Join Team 
  $('.crm-pcp-inline-team-edit').on('click', function(ev){
    ev.preventDefault();
    var url   = cj(this).attr('href');
    var title = 'Join Team';
    if (url) {
      CRM.loadForm(url, {
        dialog: {width: 650, height: 'auto', title: title, show: 'drop', hide: "drop"}
      }).on('crmFormSuccess', function(e, data) {
        if (typeof(data.crmMessages) == 'object') {
          // swtich off civi's status popup loader
          $(document).off('ajaxSuccess');
          // use pcp's status message display method
          $.each(data.crmMessages, function(n, msg) {
           var pcpMessage = "<div class='pcp-info pcp-message'><h3>" + msg.title + "</h3><p>" + msg.text + "</p></div>";
            $('.pcp-messages').html('');
            $(pcpMessage).appendTo('.pcp-messages').show('slow');
          });
        }
      });
    }
  });
  
  // profile image inline edit
  $('.crm-pcp-inline-pic-edit').on('click', function(ev){
    var url = $(this).attr('href');
    var fileid = $(this).attr('id');
    if(fileid){
      url = url + '&fileid=' + fileid;
    }
    if (url) {
      CRM.loadForm(url, {
        dialog: {width: 500, height: 'auto', show: 'drop', hide: "drop"}
      }).on('crmFormSuccess', function(e, data) {
        console.log(data);
        $(document).ajaxStop(function() { 
          location.reload(true); 
          //DS FIXME: avoid loading of page with url below
          //$('.crm-pcp-inline-pic-edit').attr('src','/civicrm/file?reset=1&id=9&eid=50&time=' + new Date().getTime());
        });
      });
    }
  });
  $('.crm-pcp-inline-pic-edit').mouseover(function(){
    $(this).css("background", "#E5DEDE");
    $(this).css("border", "2px dashed #c4c4c4");
    $(this).css("border-radius", "10px");
  });
  $('.crm-pcp-inline-pic-edit').mouseout(function(){
    $(this).css("background", "#F7F6F6");
    $(this).css("border", "none");
  });

  // circular progress bar
  var circleVar = {/literal}{$pcpinfo.percentage};{literal}
  $('.circle').circleProgress({
    value: circleVar/100,
    size: 130,
    thickness: 15,
    lineCap: "round",
    fill: {
      gradient: ["#FF0000", "#e0001a"]
    },
  }).on('circle-animation-progress', function(event, progress) {
    if ((100 * progress) <= circleVar) {
      $('#pcp-progress').find('strong').html(parseInt(100 * progress) + '<i>%</i>');
    } else {
      $('#pcp-progress').find('strong').html(parseInt(circleVar) + '<i>%</i>');
    }
  });
});
function approveTeamMember(entityId, pcpId, teampcpId){
    cj(".crm-pcp-alert-approve-request").show();
    cj(".crm-pcp-alert-approve-request").dialog({
        title: "Approve Request",
        modal: true,
        resizable: true,
        bgiframe: true, 
        show: 'drop', 
        hide: 'drop',
        overlay: {
          opacity: 0.5,
          background: "black"
        },
        buttons: {
          "Yes": function() {
              var dataUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='snippet=4&className=CRM_Pcpteams_Page_AJAX&fnName=approveTeamMember' }"{literal};
              cj.ajax({ 
                 url     : dataUrl,
                 type    : 'post',
                 data    : {entity_id : entityId, pcp_id : pcpId, team_pcp_id: teampcpId },
                 success : function( data ) {
                  cj('#member_'+pcpId).remove();
                  cj('div.member-block > div.mem-body').append(data);
                 }
              });
            cj(this).dialog("destroy");
          },
          "No" : function() {
            cj(this).dialog("destroy");
          }
        }
    });
}
function declineTeamMember(entityId, pcpId){
    cj(".crm-pcp-alert-decline-request").show();
    cj(".crm-pcp-alert-decline-request").dialog({
        title: "Decline Request",
        modal: true,
        resizable: true,
        bgiframe: true,
        show: 'drop', 
        hide: 'drop',        
        overlay: {
          opacity: 0.5,
          background: "black"
        },
        buttons: {
          "Yes": function() {
              var dataUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='snippet=4&className=CRM_Pcpteams_Page_AJAX&fnName=declineTeamMember' }"{literal};
              var redirectUrl = window.location.href;
              redirectUrl = redirectUrl + '&op=decline';
              cj.ajax({ 
                 url     : dataUrl,
                 type    : 'post',
                 data    : {entity_id : entityId},
                 success : function( data ) {
                  cj('#member_'+pcpId).remove();
                 }
              });
            cj(this).dialog("destroy");
          },
          "No" : function() {
            cj(this).dialog("destroy");
          }
        }
    });
}
function leaveTeam(teampcpId, userId){
    cj(".crm-pcp-alert-leave-team").show();
    cj(".crm-pcp-alert-leave-team").dialog({
        title: "Leave Team",
        modal: true,
        resizable: true,
        bgiframe: true,
        show: 'drop', 
        hide: 'drop',        
        overlay: {
          opacity: 0.5,
          background: "black"
        },
        buttons: {
          "Yes": function() {
             var dataUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='snippet=4&className=CRM_Pcpteams_Page_AJAX&fnName=leaveTeam' }";{literal}
             var redirectUrl = {/literal}"{crmURL p='civicrm/pcp/dashboard' h=0 q='reset=1'}";{literal}
             cj.ajax({ 
                url     : dataUrl,
                type    : 'post',
                data    : {user_id : userId, team_pcp_id : teampcpId},
                success : function( data ) {
                  cj(document).ajaxStop(function() { 
                    location.href = redirectUrl; 
                  });
                }
             });
            cj(this).dialog("destroy");
          },
          "No" : function() {
            cj(this).dialog("destroy");
          }
        }
    });
 
}
function deletePendingApproval(entityId){
    cj(".crm-pcp-alert-cancel-pending-request").show();
    cj(".crm-pcp-alert-cancel-pending-request").dialog({
        title: "Decline Request",
        modal: true,
        resizable: true,
        bgiframe: true,
        show: 'drop', 
        hide: 'drop',        
        overlay: {
          opacity: 0.5,
          background: "black"
        },
        buttons: {
          "Yes": function() {
              var dataUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='snippet=4&className=CRM_Pcpteams_Page_AJAX&fnName=declineTeamMember' }"{literal};
              var redirectUrl = window.location.href;
              redirectUrl = redirectUrl + '&op=pending';
              cj.ajax({ 
                 url     : dataUrl,
                 type    : 'post',
                 data    : {entity_id : entityId},
                 success : function( data ) {
                  cj(document).ajaxStop(function() { 
                    location.href = redirectUrl; 
                  });
                 }
              });
            cj(this).dialog("destroy");
          },
          "No" : function() {
            cj(this).dialog("destroy");
          }
        }
    });
}
</script>
{/literal}
