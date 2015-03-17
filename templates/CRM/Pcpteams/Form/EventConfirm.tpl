<!-- ( Main Div ) -->
<div class="crm-form-block crm-search-form-block">
	
	<!-- ( HEADER ) -->
	 <br>
	 <h2>{ts}Thanks for choosing to Support us for '{$eventDetails.title}'{/ts}</h2>
	 <br>
	 
	 <div>
	 	<p>
	 		Please Confirm  that you have already claimed plae in the event
	 	</p>
	 </div>
	<!-- ( FIELD (AUTOMATIC LAYOUT) ) -->

	{foreach from=$elementNames item=elementName}
	  <div class="crm-section">
	    <div class="label">{$form.$elementName.label}</div>
	    <div class="content">{$form.$elementName.html}</div>
	    <div class="clear"></div>
	  </div>
	{/foreach}
	
	<br>
	<br>
	<br>
	<!-- ( FOOTER ) -->
	<div class="crm-submit-buttons">
	{include file="CRM/common/formButtons.tpl" location="bottom"}
	</div>
</div>
