<div class="view-content">

  {if $teamPendingInfo}
    <div id="ltype">
      {strip}

      <table class="selector">
        <tr class="columnheader">
          <th>{ts}Name{/ts}</th>
          <th>{ts}PCP Title{/ts}</th>
          <th>{ts}Support of{/ts}</th>
          <th>{ts}Status{/ts}</th>
        </tr>

        {foreach from=$teamPendingInfo item=row}
        <tr class="{cycle values='odd-row,even-row'}">
              <td class="bold">{$row.teamName}</td>
              <td>
              {$row.teamPcpTitle}
              </td>
              <td>{$row.pageTitle}</td>
              <td>{ts}Pending Approval{/ts}</td>
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
