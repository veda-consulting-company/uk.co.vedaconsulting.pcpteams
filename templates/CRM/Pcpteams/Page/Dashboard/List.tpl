<div class="view-content">

  {if $pcpInfo}
    <div id="ltype">
      {strip}

      <table class="selector">
        <tr class="columnheader">
          <th>{ts}Your Page{/ts}</th>
          <th>{ts}In Support of{/ts}</th>
          <th>{ts}Goal Amount{/ts}</th>
          <th>{ts}Campaign Ends{/ts}</th>
          <th>{ts}Status{/ts}</th>
          <th>{ts}Is Active{/ts}</th>
          <th></th>
        </tr>

        {foreach from=$pcpInfo item=row}
        <tr class="{cycle values="odd-row,even-row"} {$row.class}">
              <td class="bold"><a href="{crmURL p='civicrm/pcp/info' q="reset=1&id=`$row.pcpId`" a=1}" title="{ts}Preview your Personal Campaign Page{/ts}">{$row.pcpTitle}</a></td>
              <td>{$row.pageTitle}</td>
              <td>{$row.goalAmount}</td>
              <td>{if $row.end_date}{$row.end_date|truncate:10:''|crmDate}{else}({ts}ongoing{/ts}){/if}</td>
              <td>{$row.pcpStatus}</td>
              <td>{$row.isActive}</td>
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
