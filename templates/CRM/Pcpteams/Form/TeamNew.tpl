<!-- ( Main Div ) -->
<div class="crm-form-block crm-search-form-block">
    <h2>{ts}That's great! Please enter your team name{/ts}<br /></h2>

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
  <span class="bold"><a class='button' href="{$skipURL}">{ts}Skip team setup{/ts}</a></span>
  </div>
</div>

