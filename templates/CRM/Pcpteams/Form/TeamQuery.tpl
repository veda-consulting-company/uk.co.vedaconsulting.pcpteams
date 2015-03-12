<div class="crm-block crm-form-block crm-team-query-form-block">
    <h2>{ts}Are you supporting us as part of a team?{/ts}</h2>
    <div id="team-type-query" class="form-item">
        <table class="form-layout-compressed">
         <tr class="crm-team-query-form-block-teamType">
            <td class="label">{$form.teamOption.label}</td>
            <td>{$form.teamOption.html}</td>
         </tr>
        </table>
    </div>
</div>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"} </div>