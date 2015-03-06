<!-- .tpl file invoked: CRM\Pcpteams\Page\Dashboard\Tribute.tpl. Call via Dashboard.tpl -->

<!-- header -->
<div class="pcp-dashboard-header">

	<!-- profile Image -->
	<div class="pcp-dashboard-header-profile-pic inline-display">
            <table >
                <tr>
                    <td>
                        <input type="button" name="profilepic" value="Edit/Upload Pic" id="profilepic" onclick="parent.location='{$profilePicURl}'" /><br />
                    </td>
                    <td>
                        <br />&nbsp;&nbsp;&nbsp;&nbsp;{ts}<h1>in memory of name</h1>{/ts}
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
            <input type="button" name="giveinmemof" value="Give in memory of" id="giveinmemof" onclick="parent.location='{$profilePicURl}'" /><br />
	</div>
</div>
<!-- End totaliser -->

<!-- Inmem Bio block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-inmembio">
	<!-- Right side block -->
	<div class="crm-accordion-header"> In Mem Bio/story </div>
	<div class="crm-accordion-body pcp-dashboard-block-inmembio-text">
		<div class="lightbground">
			<p class="title_text">In Mem Bio/story</p>
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
<!-- End Inmem Bio name block -->


<!-- contentfilter block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-contentfilter">
	<!-- Right side block -->
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
                        <input id="donations" type="checkbox" value="1" name="donations">&nbsp;&nbsp;{ts}Donations{/ts}
                    </td>
                    <td>
                        <input id="photos" type="checkbox" value="1" name="photos">&nbsp;&nbsp;{ts}Photos{/ts}
                    </td>
                    <td>
                        <input type="button" name="update" value="update" id="update" onclick="parent.location='{$profilePicURl}'" /><br />
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

<!-- donation block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-donation">
    <div class="crm-accordion-header">Donations</div>
	<div class="crm-accordion-body">
	</div>
</div>
<!-- End donation block -->

<!-- Photo-->
<div class="crm-accordion-wrapper pcp-dashboard-block-photo">
    <div class="crm-accordion-header">Photos </div>
	<div class="crm-accordion-body">
	</div>
</div>
<!-- End Photo -->