<div class="view-content">

  {if $relatedContact}
    <div id="ltype">
      {strip}

      <table class="selector">
        <tr class="columnheader">
          <th>{ts}Contact Name{/ts}</th>
          <th>{ts}Type{/ts}</th>
          <th>{ts}E-Mail{/ts}</th>
          <th>{ts}Phone{/ts}</th>
          <!-- <th></th> -->
        </tr>

        {foreach from=$relatedContact item=row}
        <tr class="{cycle values='odd-row,even-row'}">
              <td class="bold">{$row.name}</td>
              <td>{$row.type}</td>
              <td>{$row.email}</td>
              <td>{$row.phone}</td>
              <!-- <td>{$row.action}</td> -->
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
