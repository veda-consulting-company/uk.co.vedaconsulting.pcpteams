<div class="crm-block crm-form-block crm-team-invited-form-block">
    <h2>{ts}Is this the team you wish to join?{/ts}</h2>
    <div id="team-type-query" class="form-item">
        <table class="form-layout-compressed">
          <tr class="crm-team-invited-form-block-teamDetails">
            <td class="label">{$form.description.label}</td>
            <td>{$form.description.value}</td>
         </tr>
         <tr class="crm-team-invited-form-block-teamType">
            <td class="label">{$form.teamOption.label}</td>
            <td>{$form.teamOption.html}</td>
         </tr>
        </table>
    </div>
</div>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"} </div>
