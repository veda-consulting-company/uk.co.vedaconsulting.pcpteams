<div class="view-content">

  {if $teamMemberInfo}
    <div id="ltype">
      {strip}

      <table class="selector">
        <tr class="columnheader">
          <th>{ts}Team{/ts}</th>
          <th>{ts}Member{/ts}</th>
          <th>{ts}Admin(Yes/No){/ts}</th>
          <th>{ts}Action{/ts}</th>
        </tr>

        {foreach from=$teamMemberInfo item=row}
        <tr class="{cycle values='odd-row,even-row'}">
              <td class="bold">{$row.teamName}</td>
              <td class="bold">{$row.memberName}</td>
              <td class="bold">{$row.type}</td>
              <td class="bold">{$row.action}</td>
        </tr>
        {/foreach}
      </table>
    {/strip}
    </div>
  {else}
    <div class="messages status no-popup">
      <div class="icon inform-icon"></div>
      {ts}You have no new team membership requests yet.{/ts}
    </div>
  {/if}

</div>
{literal}
<script type="text/javascript">
    function removeTeamMember(pcpId, teampcpId){
        var dataUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='snippet=4&className=CRM_Pcpteams_Page_AJAX&fnName=removeTeamMember' }"{literal};
        cj.ajax({ 
            url     : dataUrl,
            type    : 'post',
            data    : {pcp_id : pcpId, team_pcp_id : teampcpId },
            success : function( data ) {
                cj(document).ajaxStop(function() { location.reload(true); });
            }
        });
    }
    function deactivateTeamMember(pcpId, teampcpId){
        var dataUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='snippet=4&className=CRM_Pcpteams_Page_AJAX&fnName=deactivateTeamMember' }"{literal};
        cj.ajax({ 
           url     : dataUrl,
           type    : 'post',
           data    : {pcp_id : pcpId, team_pcp_id : teampcpId },
           success : function( data ) {
              cj(document).ajaxStop(function() { location.reload(true); });
           }
        });
    }
</script>
{/literal}
