<div class="view-content">

  {if $relatedContact}
    <div id="ltype">
      {strip}

      <table class="selector">
        <tr class="columnheader">
          <th>{ts}Team{/ts}</th>
          <th>{ts}Member{/ts}</th>
          <th>{ts}Admin(Yes/No){/ts}</th>
          <th>{ts}Action{/ts}</th>
        </tr>

        {foreach from=$relatedContact item=row}
        <tr class="{cycle values='odd-row,even-row'}">
              <td class="bold">{$row.name}</td>
              <td>{ts}FIXME{/ts}</td>
              <td>{ts}FIXME{/ts}</td>
              <td>{ts}FIXME{/ts}</td>
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
