<!-- header -->
<div class="crm-pcp-manage">

  <div class="pcp-panel">
    <!-- profile Image -->
    <div class="avatar-title-block">
        <div class="avatar">
          <img id="{$pcpinfo.image_id}" {if $is_edit_page} class="crm-pcp-inline-pic-edit" {/if} href="{$updateProfPic}" width="150" height="150" src="{$pcpinfo.image_url}">
        </div>
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
        <div class="raised"><span class="text">{ts}Raised so far{/ts}</span></div>
      </div> 
      <div class="target">
        <span class="text">{ts}Of target{/ts}</span>
        <div>
          <div class="amount symbol">{$pcpinfo.currency_symbol}</div>
          <div id="pcp_goal_amount" class="amount {if $is_edit_page}crm-pcp-inline-text-edit{/if}" data-placeholder="Goal Amount">{$pcpinfo.goal_amount}</div>
        </div>
      </div> 
    </div>
    <div id="pcp_title" class="title {if $is_edit_page}crm-pcp-inline-text-edit{/if}" data-placeholder="Page Title">{$pcpinfo.title}</div>
    <div class="clear"></div>
  </div>
  <!-- End header-->

  <div class="pcp-messages">
  {if !empty($pcpStatus)}
  {foreach from=$pcpStatus item=pstatus}
    <div class="{$pstatus.type} pcp-message">
      <h3>{ts}{$pstatus.title}{/ts}</h3>
      <p>{ts}{$pstatus.text}{/ts}</p>
    </div>
  {/foreach}
  {/if}
  </div>

  <div class="pcp-body">
    <div class="totaliser-giveto-block">
      <div class="totaliser">
        <div class="colheader">
          {ts}Totalizer{/ts}
        </div>
        <!-- BIO section -->
        <div id="pcp_intro_text" {if $is_edit_page}class="intro-text .crm-pcp-inline-text-edit" contenteditable="true" data-edit-params='{ldelim}"cid": "{$contactId}", "class_name": "CRM_Contact_Form_Inline_ContactInfo"{rdelim}' {else} class="intro-text" {/if} data-placeholder="Intro Text">{$pcpinfo.intro_text}</div>
        <div id="pcp_page_text" class="page-text {if $is_edit_page}crm-pcp-inline-text-edit{/if}" data-placeholder="Page Description">{$pcpinfo.page_text}</div>
        <br>
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
                <div class="raised"><span class="text">{ts}Raised so far{/ts}</span></div>
              </div>
              <div class="target">
                <span class="text">{ts}Of target{/ts}</span>
                <div id="pcp_goal_amount" class="amount">{$teamPcpInfo.goal_amount|crmMoney:$teamPcpInfo.currency}</div>
              </div>
            </div>
          {elseif $pcpinfo.pending_team_pcp_id}
            <h3>{ts}(You have Team Request waiting for approval){/ts}</h3>
              <div class="waiting-approval">
                <div class="avatar">
                  <img width="75" height="75" src="{$pendingApprovalInfo.image_url}">
                </div>
                <div class="team-info">
                  <h3>{$pendingApprovalInfo.title}</h3>
                  <p>{$pendingApprovalInfo.intro_text}</p>
                </div>
                <div class="team-stats">
                  <div class="raised-total">
                    <span class="amount">{$pendingApprovalInfo.amount_raised|crmMoney:$pendingApprovalInfo.currency}</span>
                    <div class="raised"><span class="text">{ts}Raised so far{/ts}</span></div>
                  </div>
                  <div class="target">
                    <span class="text">{ts}Of target{/ts}</span>
                    <div id="pcp_goal_amount" class="amount">{$pendingApprovalInfo.goal_amount|crmMoney:$pendingApprovalInfo.currency}</div>
                  </div>
                </div>
                <div class="clear"></div>
              </div>
              <div class="pending-team-buttons">
                <a class="pcp-button pcp-btn-red crm-pcp-alert-cancel-pending-request" href="javascript:void(0)" data-entity-id={$pendingApprovalInfo.relationship_id} data-pcp-id={$pcpinfo.pcp_id} data-teampcp-id={$pcpinfo.pending_team_pcp_id}>{ts}Withdraw Request{/ts}</a>
              </div>
          {elseif $pcpinfo.is_teampage && $is_member}
            <!-- <div class="invite-team-text">Invite people to the team</div> -->
            <div class="team-buttons">
              <a class="pcp-button pcp-btn-brown crm-pcp-inline-team-edit" href="{$inviteTeamURl}">{ts}Invite Team Members{/ts}</a>
              {if !$is_edit_page}
              <a class="pcp-button pcp-btn-brown crm-pcp-alert-leave-team" href="javascript:void(0)" data-user-id={$userId} data-teampcp-id={$pcpinfo.id}>{ts}Leave Team{/ts}</a>
              {/if}
            </div>
          {else}
              {if !$pcpinfo.is_teampage }
                <div class="no-team-buttons">
                  <a id="create-team-btn" class="pcp-button pcp-btn-brown crm-pcp-inline-team-edit" href="{$createTeamUrl}">{ts}Create a Team{/ts}</a>
                  <a id="join-team-btn" class="pcp-button pcp-btn-brown crm-pcp-inline-team-edit" href="{$joinTeamUrl}">{ts}Join a Team{/ts}</a>
                </div>
            {/if}
          {/if}
          <div class="clear"></div>
        </div>
      </div>
      <div class="givetoname">
        <div class="colheader">
          <div class="btn-donate">
            <a href="{$pcpinfo.donate_url}"><span id="donate_link_text" {if $is_edit_page}class="crm-pcp-inline-btn-edit"{/if} data-placeholder="name of he button">{ts}Donate{/ts}</span></a>
          </div>
        </div>
        {if !empty($donationInfo)}
          <div class="rank">This Page is <strong>{$rankInfo.rank}<small>{$rankInfo.suffix}</small></strong> out of the <strong>{$rankInfo.pageCount}</strong> fundraisers taking part in event.</div>
        {/if}
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
          {ts}Team Member Requests{/ts}
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
              <a href="{crmURL p="civicrm/pcp/manage" q="id=`$memberInfo.pcp_id`"}">{$memberInfo.display_name}</a>  
              {if $memberInfo.is_team_admin}
                <br>
                <small> {ts}( Team Admin ){/ts} </small>
              {/if}
            </div>
            <div class="mem-body-row progress">
              {$memberInfo.donations_count} Donations
            </div>
            <div class="mem-body-row raised">
              {$memberInfo.amount_raised|crmMoney}
            </div>
            <div class="mem-body-row donate">
              <a class="pcp-button pcp-btn-brown crm-pcp-alert-approve-request" href="javascript:void(0)" data-entity-id={$memberInfo.relationship_id} data-pcp-id={$memberInfo.pcp_id} data-teampcp-id={$memberInfo.team_pcp_id}>{ts}Approve{/ts}</a>
              <a class="pcp-button pcp-btn-brown crm-pcp-alert-decline-request" href="javascript:void(0)" data-entity-id={$memberInfo.relationship_id} data-pcp-id={$memberInfo.pcp_id} data-teampcp-id={$memberInfo.team_pcp_id}>{ts}Decline{/ts}</a>
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
          {ts}Team Members{/ts}
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
              <a href="{crmURL p="civicrm/pcp/manage" q="id=`$memberInfo.pcp_id`"}">{$memberInfo.display_name}</a> 
              {if $memberInfo.is_team_admin}
                <br>
                <small> {ts}( Team Admin ){/ts} </small>
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
    tooltip   : ts('Click to edit..'),
    indicator : ts('Saving..'),
    callback  : function( editedValue ){
       var editedId = cj(this).attr('id');
       $(this).html(editedValue);
       $(this).css("background", "#F7F6F6");
       $(this).css("border", "none");
     }
  }

  // inline text edit
  //#3515 Now we display editable field placholder of each
  $('.crm-pcp-inline-text-edit').each(function(){
    editparams['placeholder'] = ts('Click to edit ') + $(this).attr('data-placeholder');
    editparams['tooltip'] = ts('Click to edit ') + $(this).attr('data-placeholder');
    $(this).editable(apiUrl, editparams);
  });
  // $('.crm-pcp-inline-text-edit').editable(apiUrl, editparams);
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
  //#3515 Now we display editable field placholder of each
  $('.crm-pcp-inline-btn-edit').each(function(){
    editparams['placeholder'] = ts('Click to edit ') + $(this).attr('data-placeholder');
    editparams['tooltip'] = ts('Click to edit ') + $(this).attr('data-placeholder');
    $(this).editable(apiUrl, editparams);
  });  
  // $('.crm-pcp-inline-btn-edit').editable(apiUrl, editparams);
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

  $(".crm-pcp-alert-approve-request").on('click', function(ev) {
    var $el = $(this);
    CRM.confirm({
      title: ts('{/literal}{ts escape="js"}Approve Request{/ts}{literal}'),
      message: ts('{/literal}{ts escape="js"}Approve new team member?{/ts}{literal}'),
      options: {{/literal}yes: '{ts escape="js"}Yes{/ts}', no: '{ts escape="js"}No{/ts}'{literal}},
    }).on('crmConfirm:yes', function() {
      var postUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='snippet=4&className=CRM_Pcpteams_Page_AJAX&fnName=approveTeamMember'}"{literal};
      var request = $.post(postUrl, {entity_id : $el.data('entityId'), pcp_id : $el.data('pcpId'), team_pcp_id: $el.data('teampcpId')});
      request.done(function(data) {
        setPcpMessage('Member Request Approved', 'Member Request Approved');
        $('div.member-block > div.mem-body').append(data);
        $el.closest('.mem-row').remove();
        if ($(".member-req-block > .mem-body > div").length <= 1) {
          $(".member-req-block").hide('slow');
        }
      });
    });
  });

  $(".crm-pcp-alert-decline-request").on('click', function(ev) {
    var $el = $(this);
    CRM.confirm({
      title: ts('{/literal}{ts escape="js"}Decline Request{/ts}{literal}'),
      message: ts('{/literal}{ts escape="js"}Are you sure you want to decline this request?{/ts}{literal}'),
      options: {{/literal}yes: '{ts escape="js"}Yes{/ts}', no: '{ts escape="js"}No{/ts}'{literal}},
    }).on('crmConfirm:yes', function() {
      var postUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='snippet=4&className=CRM_Pcpteams_Page_AJAX&fnName=declineTeamMember'}"{literal};
      var request = $.post(postUrl, {entity_id : $el.data('entityId'), pcp_id : $el.data('pcpId'), team_pcp_id: $el.data('teampcpId')});
      request.done(function(data) {
        setPcpMessage('Member Request Declined', 'Member Request Declined');
        $el.closest('.mem-row').remove();
        if ($(".member-req-block > .mem-body > div").length <= 1) {
          $(".member-req-block").hide('slow');
        }
      });
    });
  });

  $(".crm-pcp-alert-leave-team").on('click', function(ev) {
    var $el = $(this);
    CRM.confirm({
      title: ts('{/literal}{ts escape="js"}Leave Team{/ts}{literal}'),
      message: ts('{/literal}{ts escape="js"}Do you really want to leave the team?{/ts}{literal}'),
      options: {{/literal}yes: '{ts escape="js"}Yes{/ts}', no: '{ts escape="js"}No{/ts}'{literal}},
    }).on('crmConfirm:yes', function() {
      var postUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='snippet=4&className=CRM_Pcpteams_Page_AJAX&fnName=leaveTeam'}";{literal}
      var request = $.post(postUrl, {user_id : $el.data('userId'), team_pcp_id : $el.data('teampcpId')});
      request.done(function(data) {
        setPcpMessage('Left The Team', 'You no longer part of this team, and this page may no longer be visible to you.');
      });
    });
  });

  $(".crm-pcp-alert-cancel-pending-request").on('click', function(ev) {
    var $el = $(this);
    CRM.confirm({
      title: ts('{/literal}{ts escape="js"}Cancel Join Request{/ts}{literal}'),
      message: ts('{/literal}{ts escape="js"}Are you sure you want to withdraw your request to join this team?{/ts}{literal}'),
      options: {{/literal}yes: '{ts escape="js"}Yes{/ts}', no: '{ts escape="js"}No{/ts}'{literal}},
    }).on('crmConfirm:yes', function() {
      var postUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='snippet=4&className=CRM_Pcpteams_Page_AJAX&fnName=withdrawJoinRequest' }"{literal};
      var request = $.post(postUrl, {entity_id : $el.data('entityId'), pcp_id : $el.data('pcpId'), team_pcp_id: $el.data('teampcpId')});
      request.done(function(data) {
        setPcpMessage('Join Request Cancelled', 'Your join request to the team has been cancelled.');
        $el.closest('.team-section').remove();
      });
    });
  });

  function setPcpMessage(title, text) {
    var pcpMessage = "<div class='pcp-info pcp-message'>";
    if (title) {
      pcpMessage = pcpMessage + "<h3>" + title + "</h3>";
    }
    if (text) {
      pcpMessage = pcpMessage + "<p>" + text + "</p>";
    }
    $('.pcp-messages').html('');
    $(pcpMessage).appendTo('.pcp-messages').show('slow');
  }
});
</script>
{/literal}
