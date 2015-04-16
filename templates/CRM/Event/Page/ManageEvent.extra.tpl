<div id="custom_pcp_links" class="custom_pcp_links" style="display:none;">
  <ul>
    <li>
      <a href="" class="action-item crm-hover-button crm-join-event" title="Register Participant">{ts}PCP register for event (EP1){/ts}</a>
    </li>
    <li>
      <a href="" class="action-item crm-hover-button crm-already-have-place" title="Register Participant">{ts}PCP already has a place (EP1A){/ts}</a>
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
        CRM.api3('Pcpteams', 'getpcpblock', {
          "sequential": 1,
          "entity_id": eventId
        }).done(function(result) {
          if (result['count']) {
            $('#panel_links_'+eventId).append(customlinkhtml);
            $('#panel_links_'+eventId).find('.crm-join-event').attr('href', joinUrl);
            $('#panel_links_'+eventId).find('.crm-already-have-place').attr('href', joinUrl+"&code=cpftq");
          }
        });
      });
    }
  });
</script>
{/literal}
