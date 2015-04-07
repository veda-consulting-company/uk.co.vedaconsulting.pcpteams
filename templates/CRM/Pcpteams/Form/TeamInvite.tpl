<div class="crm-block crm-form-block crm-team-invited-form-block">
  <h2>{ts}Is this the team you wish to join?{/ts}</h2>
  <div id="help">
    <table>
      <tr>
        <td>{$teamTitle}</td>
      </tr>
      <tr>
        <td >{ts}Captain{/ts} - {$teamAdminDisplayName}
        </td>
      </tr>
      <tr>
        <td >{ts}Event{/ts} - {$eventTitle}</td>
      </tr>
    </table>
  </div>
</div>
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
  <a class='button' href='{crmURL p="civicrm/pcp/support" q="code=cpfgq&qfKey=$qfKey"}'>&nbsp;{ts}No{/ts}&nbsp;</a>
</div>
