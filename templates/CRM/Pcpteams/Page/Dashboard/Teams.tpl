<div class="view-content">

  {if $teamInfo}
    <div id="ltype">
      {strip}

      <table class="selector" id="team_info">
        <tr class="columnheader">
          <th>{ts}Name{/ts}</th>
          <th>{ts}My PCP Title{/ts}</th>
          <th>{ts}Team PCP Title{/ts}</th>
          <th>{ts}Support of{/ts}</th>
          <th>{ts}Goal{/ts}</th>
          <th>{ts}Amount Raised{/ts}</th>
          <th>{ts}Action{/ts}</th>
        </tr>

        {foreach from=$teamInfo item=row key=entityId}
        <tr class="{cycle values='odd-row,even-row'}" id="{$entityId}">
              <td class="bold">{$row.teamName}</td>
              <td>{$row.my_pcp_title}</td>
              <td>
              {$row.teamPcpTitle}
                <div id="{$entityId}_alert" class='alert_message' style="display:none;">
                  <p> Are you sure want to unsubscribe from Team - {$row.teamPcpTitle} </p>
                </div>
              </td>
              <td>{$row.pageTitle}</td>
              <td align="right">{$row.teamgoalAmount}</td>
              <td align="right">{$row.amount_raised}</td>
              <td>{$row.action}</td>
        </tr>
        {/foreach}
      </table>
    {/strip}
    </div>
  {else}
    <div class="messages status no-popup">
      <div class="icon inform-icon"></div>
      {ts}You do not have any Team or organization related to {/ts}
    </div>
  {/if}

</div>

{literal}
<script type="text/javascript">
  function unsubscribeTeam(entityId, teampcpId){
    cj("#"+entityId+"_alert").show();
    cj("#"+entityId+"_alert").dialog({
        title: "Unsubscribe from Team",
        modal: true,
        resizable: true,
        bgiframe: true,
        overlay: {
          opacity: 0.5,
          background: "black"
        },
        buttons: {
          "Ok": function() {
             var dataUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='snippet=4&className=CRM_Pcpteams_Page_AJAX&fnName=unsubscribeTeam' }"{literal};
             cj.ajax({ 
                url     : dataUrl,
                type    : 'post',
                data    : {entity_id : entityId, team_pcp_id : teampcpId},
                success : function( data ) {
                    cj(document).ajaxStop(function() { location.reload(true); });
                    cj('#team_info').find('tr#'+entityId).remove();
                }
             });
            cj(this).dialog("destroy");
            },
          "Cancel" : function(){
            cj(this).dialog("destroy");
            cj("#"+entityId+"_alert").hide();
            }

          }
    });
 
  }
</script>
{/literal}
