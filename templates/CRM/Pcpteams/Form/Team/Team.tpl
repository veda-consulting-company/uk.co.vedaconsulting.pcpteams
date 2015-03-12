<!-- ( Main Div ) -->
<div class="crm-form-block crm-search-form-block">
	
	<!-- ( HEADER ) -->

	<div class="crm-submit-buttons">
	{include file="CRM/common/formButtons.tpl" location="top"}
	</div>

	<div class="help">
		{ts}Fixme.. This is test help text. Please select the Pcp team. {/ts}
	</div>
	<!-- ( FIELD (AUTOMATIC LAYOUT) ) -->

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
