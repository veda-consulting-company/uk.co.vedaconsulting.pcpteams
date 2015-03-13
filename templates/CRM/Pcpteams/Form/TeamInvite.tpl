<div class="crm-block crm-form-block crm-team-invited-form-block">
    <h2>{ts}Is this the team you wish to join?{/ts}</h2>
    <table class="form-layout-compressed" >
        <tr>
          <td >
              {$teamTitle}</td>
        </tr>
        <tr>
          <td >{ts}Captain - {/ts}
           {$teamAdminDisplayName}
          </td>
        </tr>
        <tr>
          <td >{ts}Event - {/ts}
          {$eventTitle}</td>
        </tr>
        <tr class="crm-team-invited-form-block-teamType">
           <td class="label">{$form.teamOption.label}</td>
           <td>{$form.teamOption.html}</td>
        </tr>
    </table>
</div>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"} </div>
