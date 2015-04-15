<div id="custom_pcp_links" class="custom_pcp_links">
  <ul>
    <li>
      <a href="" class="action-item crm-hover-button crm-join-event" title="Register Participant">Join / Register Event </a>
    </li>
    <li>
      <a href="" class="action-item crm-hover-button crm-already-have-place" title="Register Participant">Already have a place</a>
    </li>
  </ul>
</div>
{literal}
<script type="text/javascript">
  CRM.$(function($) {
    if ($('.crm-event-links').length) {
      $('.crm-event-links').each(function () {
        var panelULId   = $(this).find('.panel').attr('id');
        var splittedId  = panelULId.split("_");
        var eventId     = splittedId[2];
        var joinUrl     = {/literal}"{crmURL p='civicrm/pcp/support' h=0 q='reset=1&component=event&pageId=' }"{literal}+eventId;
        var customlinkhtml  = $('.custom_pcp_links ul > li').clone().attr('id', 'custom_pcp_links_'+eventId);
        var isPcpConfigured = CRM.api3('Pcpteams', 'getpcpblock', {
          "sequential": 1,
          "entity_id": eventId
        }).done(function(result) {
          return result.count;
        });

        if (isPcpConfigured) {
          $('#custom_pcp_links_'+eventId).find('.crm-join-event').attr('href', joinUrl);
          $('#custom_pcp_links_'+eventId).find('.crm-already-have-place').attr('href', joinUrl+"&code=cpftq");
          $(this).find('.panel').append(customlinkhtml);
        }
      });
    }
  });
</script>
{/literal}
