<!-- ( Main Div ) -->
<div class="crm-form-block crm-search-form-block">
        <h2>{ts}That's great! Please select the name of the team you wish to join{/ts}</h2><br />
        {if $snippet neq 'json'}
        <span class="bold">{ts}Start typing the name
            of the team you wish to join and then select the correct name. 
            If you can't find the team you are looking for, please contact the team captain. 
            Alternatively you can <a class='crm-link' href='{crmURL p="civicrm/pcp/support" q="reset=1&pageId=$component_page_id&component=event&code=cpftn&option=1"}'>{ts}create your own team{/ts}</a>. If you need  further assistance, <a href="#webform-client-form-6">{ts}contact us{/ts}</a>{/ts}.</span>
        {/if}
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

{literal}
<script type="text/javascript">
      cj(document).ready(function () {
        var eventId = {/literal}{$component_page_id};{literal}
        cj("#pcp_team_contact").select2({  
            placeholder: "Search Team",  
            ajax: {
              url: CRM.url('civicrm/ajax/rest'),
              data: function (input, page_num) {
                var params = {};
                params.input = input;
                params.contact_sub_type = 'Team';
                params.page_num = page_num;
                params.event_id = eventId;
                return {
                  entity: 'pcpteams',
                  action: 'getContactlist',
                  json: JSON.stringify(params)
                };
              },
              results: function(data) {
                return {more: data.more_results, results: data.values || []};
              }
            },
            minimumInputLength: 1,
            formatResult: CRM.utils.formatSelect2Result,
            formatSelection: function(row) {
              return row.label;
            },
            escapeMarkup: function (m) {return m;},
            initSelection: function($el, callback) {
               val = $el.val();
               var params = {};
                params.id = val;
               CRM.api3('pcpteams', 'getContactlist', params).done(function(result) {
                callback(result.values[0]);
                // Trigger change (store data to avoid an infinite loop of lookups)
                // $el.data('entity-value', result.values).trigger('change');
              });
            }
        });  
    }); 
</script>
{/literal}
