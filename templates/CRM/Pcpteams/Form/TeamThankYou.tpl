<div class="crm-form-block crm-search-form-block">
    <span>{$team_thank_message}</span>
    {foreach from=$elementNames item=elementName}
      <div class="crm-section">
        <div class="label">{$form.$elementName.label}</div>
        <div class="content">{$form.$elementName.html}</div>
        <div class="clear"></div>
      </div>
    {/foreach}

    <!-- ( FOOTER ) -->
    <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
</div>
