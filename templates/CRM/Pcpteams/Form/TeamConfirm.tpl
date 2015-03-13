<div class="crm-form-block crm-search-form-block">
        <h2>{ts}Thanks, {$teamTitle} is now setup, you can now invite others to join you{/ts}</h2><br />
        <span class="bold">{ts}Enter your team mates email addresses
            and we will let them know about 'Team name', enter as many email addresses as you like seperating each by a comma
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
        
