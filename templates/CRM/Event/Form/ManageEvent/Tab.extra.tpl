<div id="custom_pcp_links">
  <ul>
    <li>
      <a href="" class="crm-join-event" title="Register Participant">{ts}PCP register for event (EP1){/ts}</a>
    </li>
    <li>
      <a href="" class="crm-already-have-place" title="Register Participant">{ts}PCP already has a place (EP1A){/ts}</a>
    </li>
  </ul>
</div>
{literal}
<script type="text/javascript">
  CRM.$(function($) {
    if ($('#crm-event-links-list').length) {
        var eventId     = {/literal}{$id}{literal};
        var joinUrl     = {/literal}"{crmURL p='civicrm/pcp/support' h=0 q='reset=1&component=event&pageId=' }"{literal}+eventId;

        $('#custom_pcp_links ul').find('.crm-join-event').attr('href', joinUrl);
        $('#custom_pcp_links ul').find('.crm-already-have-place').attr('href', joinUrl+"&code=cpftq");
        $('#crm-event-links-list ul').append($('#custom_pcp_links ul > li'));
    }
  });
</script>
{/literal}
