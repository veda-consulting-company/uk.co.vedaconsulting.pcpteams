<!-- ( Main Div ) -->
<div class="crm-form-block crm-search-form-block">
  
  <!-- ( HEADER ) -->
  <h2>{ts}Thast's great! Please enter the "{$tributeReason}" of Contact you are doing this with{/ts}</h2>
  <br>
  <div>
    <p> Start typing the name of the "{$tributeReason}" of Contact you are fundraising for and then select the appropriate name from those provided. if you can't ind the branch you wish to join please contact us on info@llr.org.uk<p>
  </div>
  <br>
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
