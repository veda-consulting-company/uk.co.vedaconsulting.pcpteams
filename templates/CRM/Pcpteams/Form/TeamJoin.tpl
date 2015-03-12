<!-- ( Main Div ) -->
<div class="crm-form-block crm-search-form-block">
        <h2>{ts}That's great! Please select the name of the team you wish to join{/ts}</h2><br />
        <span class="bold">{ts}Start typing the name
            of the team you wish to join and then select the appropriate name from those provided.
            if you can't find the team you wish to join please contact the team captain of visit the
            team page and join from there{/ts}</span>
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
