<!-- ( Main Div ) -->
<div class="crm-form-block crm-search-form-block">
	
	<!-- ( HEADER ) -->
	 <br>
	 <h2>{ts}Please select a event you want to register for{/ts}</h2>
	 <br>
	 
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

{literal}
<script type="text/javascript">
    cj(document).ready(function () {  
        cj("#event_id").select2({  
            placeholder: "Search Event",  
            ajax: {
              url: CRM.url('civicrm/ajax/rest'),
              data: function (input, page_num) {
                var params = {};
                params.input = input;
                params.page_num = page_num;
                return {
                  entity: 'pcpteams',
                  action: 'getEventList',
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
               CRM.api3('pcpteams', 'getEventList', params).done(function(result) {
                callback(result.values[0]);
                // Trigger change (store data to avoid an infinite loop of lookups)
                // $el.data('entity-value', result.values).trigger('change');
              });
            }
        });  
    }); 
</script>
{/literal}