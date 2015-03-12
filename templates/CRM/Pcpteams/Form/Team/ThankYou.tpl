<div class="crm-form-block crm-search-form-block">
        <h2>{ts}Good luck 'Team name'!{/ts}</h2><br />
        <span class="bold">{ts}Your team has been created
            and we have emailed your team mates asking them to join you.
            You will receive and email when each of them accepts your invite
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
