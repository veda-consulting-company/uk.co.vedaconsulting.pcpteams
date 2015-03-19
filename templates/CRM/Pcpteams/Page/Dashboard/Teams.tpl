<div class="view-content">

  {if $relatedContact}
    <div id="ltype">
      {strip}

      <table class="selector">
        <tr class="columnheader">
          <th>{ts}Name{/ts}</th>
          <th>{ts}PCP Title{/ts}</th>
          <th>{ts}Support of{/ts}</th>
          <th>{ts}Goal{/ts}</th>
          <th>{ts}Amount Raised{/ts}</th>
          <th>{ts}Action{/ts}</th>
          <th></th>
        </tr>

        {foreach from=$relatedContact item=row}
        <tr class="{cycle values='odd-row,even-row'}">
              <td class="bold">{$row.name}</td>
              <td>{ts}FIXME{/ts}</td>
              <td>{ts}FIXME{/ts}</td>
              <td>{ts}FIXME{/ts}</td>
              <td>{ts}FIXME{/ts}</td>
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
