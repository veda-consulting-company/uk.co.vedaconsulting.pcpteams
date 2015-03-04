<!-- ( Main Div ) -->
<div class="crm-form-block crm-search-form-block">

  <!-- ( HEADER ) -->

  <div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="top"}
  </div>

  <div class="help">
    {ts}Fixme.. This is test help text. {/ts}
  </div>
  <!-- ( FIELD (AUTOMATIC LAYOUT) ) -->

  {foreach from=$elementNames item=elementName}
    <div class="crm-section">
      <div class="label">{$form.$elementName.label}</div>
      <div class="content">
      {if $elementName eq 'deceased_date'}
        {include file="CRM/common/jcalendar.tpl" elementName=deceased_date}
      {else}
        {$form.$elementName.html}
      {/if}
      </div>
      <div class="clear"></div>
    </div>
  {/foreach}

  <!-- ( FOOTER ) -->
  <div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
