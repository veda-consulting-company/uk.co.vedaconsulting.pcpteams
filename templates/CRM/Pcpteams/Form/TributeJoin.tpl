<!-- ( Main Div ) -->
<div class="crm-form-block crm-search-form-block">
  
  <!-- ( HEADER ) -->
  <h2>{ts}Thast's great! Please enter the "{$tributeReason}" of Contact you are doing this with{/ts}</h2>
  <br>
  <div>
    <p> {ts} Start typing the name of the "{$tributeReason}" of Contact you are fundraising for and then select the appropriate name from those provided. if you can't ind the branch you wish to join please contact us on info@llr.org.uk {/ts}<p>
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


{literal}
<script type="text/javascript">
      cj(document).ready(function () {  
       var tributeReason = {/literal}"{$tributeReason}";{literal}
       var tributeContact = {/literal}"{$tributeContact}";{literal}
        cj("#pcp_tribute_contact").select2({  
            placeholder: "Search "+tributeReason,  
            ajax: {
              url: CRM.url('civicrm/ajax/rest'),
              data: function (input, page_num) {
                var params = {};
                params.input = input;
                params.contact_sub_type = tributeContact;
                params.page_num = page_num;
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
                params.contact_sub_type = tributeContact;
                params.sequential = 1;
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
