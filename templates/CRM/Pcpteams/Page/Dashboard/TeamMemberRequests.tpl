<div class="view-content">

  {if $teamRequestInfo}
    <div id="ltype">
      {strip}

      <table class="selector">
        <tr class="columnheader">
          <th>{ts}PCP Page{/ts}</th>
          <th>{ts}Contact Name{/ts}</th>
          <th>{ts}Contact Email{/ts}</th>
          <th>{ts}City{/ts}</th>
          <th>{ts}State{/ts}</th>
          <th>{ts}Country{/ts}</th>
          <th>{ts}Action{/ts}</th>
        </tr>

        {foreach from=$teamRequestInfo item=row key=entityId}
        <tr class="{cycle values='odd-row,even-row'}"  id="{$entityId}">
              <td class="bold">{$row.member_pcp_title}</td>
              <td>{$row.member_display_name}</td>
              <td>{$row.member_email}</td>
              <td>{$row.member_city}</td>
              <td>{$row.member_state_province_id}</td>
              <td>{$row.member_country}</td>
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
  function approveTeamMember(entityId, pcpId){
             var dataUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='snippet=4&className=CRM_Pcpteams_Page_AJAX&fnName=approveTeamMember' }"{literal};
             cj.ajax({ 
                url     : dataUrl,
                type    : 'post',
                data    : {entity_id : entityId, pcp_id : pcpId },
                success : function( data ) {
                    cj(document).ajaxStop(function() { location.reload(true); });
                }
             });
            
   
  }
  function declineTeamMember(entityId){
             var dataUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='snippet=4&className=CRM_Pcpteams_Page_AJAX&fnName=declineTeamMember' }"{literal};
             cj.ajax({ 
                url     : dataUrl,
                type    : 'post',
                data    : {entity_id : entityId},
                success : function( data ) {
                    cj(document).ajaxStop(function() { location.reload(true); });
                }
             });
            
   
  }
</script>
{/literal}