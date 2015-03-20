<div class="view-content">

  {if $pcpInfo}
    <div id="ltype">
      {strip}

      <table class="selector">
        <tr class="columnheader">
          <th>{ts}Pcp Title{/ts}</th>
          <th>{ts}Support of{/ts}</th>
          <th>{ts}Goal{/ts}</th>
          <th>{ts}Amount Raised{/ts}</th>
          <th>{ts}Action{/ts}</th>
        </tr>

        {foreach from=$pcpInfo item=row}
        <tr class="{cycle values="odd-row,even-row"} {$row.class}">
              <td class="bold"><a href="{crmURL p='civicrm/pcp/info' q="reset=1&id=`$row.pcpId`" a=1}" title="{ts}Preview your Personal Campaign Page{/ts}">{$row.title}</a></td>
              <td>{$row.page_title}</td>
              <td align="right">{$row.goal_amount}</td>
              <td align="right">{$row.amount_raised}</td>
              <td>{$row.action|replace:'xx':$row.pcpId}</td>
        </tr>
        {/foreach}
      </table>
    {/strip}
    </div>
  {else}
    <div class="messages status no-popup">
      <div class="icon inform-icon"></div>
      {ts}You do not have any active Personal Campaign pages.{/ts}
    </div>
  {/if}

</div>
