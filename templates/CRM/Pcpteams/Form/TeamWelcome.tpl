<div class="crm-form-block crm-search-form-block">
        <h2>{ts}Welcome to 'Team name'!{/ts}</h2><br />
        <span class="bold">{ts}You are now a member of 'Team name', don't forget to share your team page later to encourage your friends to get involved
            {/ts}</span>
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
        
