<!-- .tpl file invoked: CRM\Pcpteams\Page\Dashboard\Group.tpl. Call via form.tpl if we have a form in the page. -->
<!-- include CSS file -->
<!-- need to fix the extension URL config->extensionurl -->
<!-- <link rel="stylesheet" type="text/css" href="/civicrm_custom/extensions/uk.co.vedaconsulting.pcpteams/css/style.css" /> -->

<!-- header -->
<div class="pcp-dashboard-header">

	<!-- profile Image -->
	<div class="pcp-dashboard-header-profile-pic inline-display">
            <table >
                {if $profilePicUrl}
                    <tr>
                        <td>
                            <div id="crm-contact-thumbnail" style="float:left">
                                <div class="crm-contact_image crm-contact_image-block">
                                    <a class="crm-image-popup" href="{$profilePicUrl}">
                                        <img width="100" height="97" src="{$profilePicUrl}">
                                    </a>
                                </div>
                            </div><br /><br /><br />&nbsp;&nbsp;&nbsp;&nbsp;{ts}<h1>{$groupTitle}</h1>{/ts}
                        </td>
                    </tr>
                {/if}
                <tr>
                    <td>
                        <input type="button" name="profilepic" value="Edit Pic" id="profilepic" onclick="parent.location='{$profilePicURl}'" />
                    </td>
                </tr>
            </table>
	</div>

</div>
<!-- End header-->

<div class="clearfix"></div>

<!-- Totaliser block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-totaliser">
	<div class="crm-accordion-header"> Totaliser </div>
	<div class="crm-accordion-body pcp-dashboard-block-totaliser-text">
		<div class="pcp_totaliser darkbground">
			<p class="title_text">Totaliser</p>
		</div>
	</div>
</div>
<!-- End totaliser -->

<!-- Group Bio block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-groupbio">
	<!-- Right side block -->
	<div class="crm-accordion-header"> Group Bio </div>
	<div class="crm-accordion-body pcp-dashboard-block-groupbio-text">
		<div class="lightbground">
			<p class="title_text">Group Bio</p>
		</div>
		<div class="block_body block_body_alignment darkbground">
			<p>
				Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse interdum sapien sit amet sem

				varius. Vestibulum viverra mi dictum odio scelerisque semper. Morbi fermentum ut neque a mollis.

				Suspendisse volutpat erat purus, quis tincidunt justo molestie eget. Fusce purus nisi, aliquam nec
			</p>
		</div>
	</div>
</div>
<!-- End Group Bio name block -->

<!-- in memory block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-donatetoname">
  <!-- Right side block -->
  <div class="crm-accordion-header">{ts}In Memory Name{/ts}</div>
  <div class="crm-accordion-body pcp-dashboard-block-donatetoname-text">
    <div class="lightbground">
      <p class="title_text">{ts}In Memory Name{/ts}</p>
    </div>
    <div class="block_body block_body_alignment darkbground">
      <p>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse interdum sapien sit amet sem

        varius. Vestibulum viverra mi dictum odio scelerisque semper. Morbi fermentum ut neque a mollis.

        Suspendisse volutpat erat purus, quis tincidunt justo molestie eget. Fusce purus nisi, aliquam nec
      </p>
      <a href="{crmURL p="civicrm/pcp/reason" q="reset=1" a=1}" title="{ts}In Memory Name{/ts}" class="button">{ts}In Memory Name{/ts}</a>
      <div style="clear: both;"></div>
    </div>
  </div>
</div>
<!-- End in memory block -->

<!-- Your contact block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-yourcontact">
	<!-- Right side block -->
	<div class="crm-accordion-header"> Your contact </div>
	<div class="crm-accordion-body pcp-dashboard-block-yourcontact-text">
		<div class="lightbground">
			<p class="title_text">Your contact</p>
		</div>
		<div class="block_body block_body_alignment darkbground">
			<p>
				Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse interdum sapien sit amet sem

				varius. Vestibulum viverra mi dictum odio scelerisque semper. Morbi fermentum ut neque a mollis.

				Suspendisse volutpat erat purus, quis tincidunt justo molestie eget. Fusce purus nisi, aliquam nec
			</p>
		</div>
	</div>
</div>
<!-- End Your contact block -->

<!-- promotedevents block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-promotedevents">
	<!-- Right side block -->
	<div class="crm-accordion-header">Promoted Events  </div>
	<div class="crm-accordion-body pcp-dashboard-block-promotedevents-text">
		<div class="lightbground">
			<p class="title_text">Promoted Events</p>
		</div>
		<div class="block_body block_body_alignment darkbground">
			<p>
				Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse interdum sapien sit amet sem

				varius. Vestibulum viverra mi dictum odio scelerisque semper. Morbi fermentum ut neque a mollis.

				Suspendisse volutpat erat purus, quis tincidunt justo molestie eget. Fusce purus nisi, aliquam nec
			</p>
		</div>
	</div>
</div>
<!-- End promotedevents block -->

<!-- contentfilter block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-contentfilter">
	<!-- Right side block -->
        <div class="crm-accordion-header">Content Filters </div>
	<div class="crm-accordion-body pcp-dashboard-block-contentfilter-text">
            <table>
                <tr>
                    <td>
                        <input id="all" type="checkbox" value="1" name="all">&nbsp;&nbsp;{ts}All{/ts}
                    </td>
                    <td>
                        <input id="blogs" type="checkbox" value="1" name="blogs">&nbsp;&nbsp;{ts}Blogs{/ts}
                    </td>
                    <td>
                        <input id="events" type="checkbox" value="1" name="events">&nbsp;&nbsp;{ts}Events{/ts}
                    </td>
                    <td>
                        <input id="photos" class="crm-form-checkbox" type="checkbox" value="1" name="photos">&nbsp;&nbsp;{ts}Photos{/ts}
                    </td>
                </tr>
            </table>
	</div>
</div>
<!-- End contentsfilter block -->
<!-- blog block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-blog">
    <div class="crm-accordion-header">Blogs</div>
	<div class="crm-accordion-body">
	</div>
</div>
<!-- End Blog block -->

<!-- event block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-event">
    <div class="crm-accordion-header">Events</div>
	<div class="crm-accordion-body">
	</div>
</div>
<!-- End event block -->

<!-- Photo-->
<div class="crm-accordion-wrapper pcp-dashboard-block-photo">
    <div class="crm-accordion-header">Photos </div>
	<div class="crm-accordion-body">
	</div>
</div>
<!-- End Photo -->
