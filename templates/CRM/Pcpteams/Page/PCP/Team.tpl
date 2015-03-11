<!-- .tpl file invoked: CRM\Pcpteams\Page\PCP\Team.tpl. Call via PCP.tpl -->

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
                            </div>
                            {if $tplParams.event_title}
                              </div><br /><br /><br />&nbsp;&nbsp;&nbsp;&nbsp;{ts}<h1>{$tplParams.event_title}</h1>{/ts}
                            {/if}
                        </td>
                    </tr>
                {/if}
                {if $updateProfPic}
                <tr>
                    <td>
                        <input type="button" name="profilepic" value="Upload Profile Pic" id="profilepic" onclick="parent.location='{$profilePicURl}'" />
                    </td>
                </tr>
                {/if}
            </table>
  </div>

</div>
<!-- End header-->

<div class="clearfix"></div>

<!-- Congratulations block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-info">
  <div class="crm-accordion-header">Event Name: {if $tplParams.event_title} {$tplParams.event_title} {/if}</div>
  <div class="crm-accordion-body pcp-dashboard-block-info-text">
    <strong>
      Congratulations, you Have now created a team taking part of {$tplParams.event_title}
    </strong>
    <br />
    <p>
      We have created this page to help you with your fundraising.

      Please take a few minutes to complete a couple of details below, you will need to add a fundraising

      target to give you something to aim for (aim high!) and write a little bit about yourself to encourage

      people to help you reach that target.

      If you want to do this event as a team or in memory of a loved one you can set that up below as well.
    </p>
    <br />
    <strong>
      Good Luck!!
    </strong>
  </div>
</div>
<!-- End Congratulations block -->

<!-- Totaliser block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-totaliser">
  <div class="crm-accordion-header"> Totaliser </div>
  <div class="crm-accordion-body pcp-dashboard-block-totaliser-text">
    <div class="pcp_totaliser darkbground">
      <p class="title_text">Totaliser</p>
    </div>
    <div class="pcp_block lightbground">
      <p class="title_thick">
        Tell us a bit about your team 
      </p>
      <p class="block_body">
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse interdum sapien sit amet sem

        varius. Vestibulum viverra mi dictum odio scelerisque semper. Morbi fermentum ut neque a mollis.

        Suspendisse volutpat erat purus, quis tincidunt justo molestie eget. Fusce purus nisi, aliquam nec
      </p>
      <p>
          <input type="button" name="givetoteamname" value="Give to team name" id="givetoname" onclick="parent.location='{$giveToTeamNameUrl}'" />
      </p>
      <p class="title_thick">
        Invite People to the team
      </p>
      <p>
        <input type="button" name="inviteteam" value="Invite team members" id="createteam" onclick="parent.location='{$inviteTeamUrl}'" />
      </p>
      <p class="title_thick">
        Top Team fundraisers
      </p>
        <p>
            <table class ="form-layout" >
                <tr>
                    <td>
                        <div id="crm-contact-thumbnail" style="float:left">
                                    <img width="100" height="97" src="{$fundRaiserPicUrl}">
                        </div>
                    </td>
                   <td>
                        <div id="crm-contact-thumbnail" style="float:left">
                                    <img width="100" height="97" src="{$fundRaiserPicUrl}">
                        </div>

                    </td>
                    <td>
                        <div id="crm-contact-thumbnail" style="float:left">
                                    <img width="100" height="97" src="{$fundRaiserPicUrl}">
                        </div>

                    </td>
                </tr>
                <tr>
                    <td style="float:left; ">
                        <input type="button" name="jointeam" value="Join a Team" id="jointeam" onclick="parent.location='{$joinTeamUrl}'" />
                        &nbsp;&nbsp;&nbsp;&nbsp; <input type="button" name="seeallteam" value="See all team" id="createteam" onclick="parent.location='{$seeallTeamUrl}'" />
                    </td>
                </tr>
            </table>
        </p>
    </div>
 
</div>
 </div>
<!-- End totaliser -->

<!-- Give to in mem block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-givetomemory">
          <div class="crm-accordion-header">{ts}Give to Memory{/ts}</div>
          <div class="crm-accordion-body pcp-dashboard-block-givetomemory-text">
              <p>
              Thank you for creating this page in memory of {$tplParams.in_memoryof}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <input type="button" name="givetomemory" value="Give in memory of " id="givetoname" onclick="parent.location='{$givetomemoryUrl}'" />
              </p>
              <p>
                  Please tell us a little about your story<br />
                  <input type="text" class = "crm-form-text" name="aboutmemtext">&nbsp;&nbsp;
  <input type="button" name="aboutmemsave" value="Save" id="aboutmemsave" onclick="parent.location='{$aboutmemSaveUrl}'">
              </p>
                <p class="title_thick">
                    <strong>
                        Inmem Bio/story
                    </strong>
                </p>
                <p class="block_body">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse interdum sapien sit amet sem

                    varius. Vestibulum viverra mi dictum odio scelerisque semper. Morbi fermentum ut neque a mollis.

                    Suspendisse volutpat erat purus, quis tincidunt justo molestie eget. Fusce purus nisi, aliquam nec<br />
                    <input type="button" name="readmore" value="Read more" id="aboutmemsave" onclick="parent.location='{$readMoreUrl}'">
                </p>
              
</div>
              
          </div>
<!-- End Give to in mem block -->


<!-- Donate to name block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-givetoteamname">
  <!-- Right side block -->
  <div class="crm-accordion-header"> Give to team name </div>
  <div class="crm-accordion-body pcp-dashboard-block-givetoteamname-text">
    <div class="lightbground">
      <input type="button" name="givetoteamname" value="Give to team name" id="givetoname" onclick="parent.location='{$giveToTeamNameUrl}'" />
    </div>
    
  </div>
</div>
<!-- End Donate to name block -->

<!-- blog block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-blog">
  <div class="crm-accordion-header"> Blog </div>
  <div class="crm-accordion-body">
    <p class="title_thick">
      Blog
    </p>
    <p class="block_body">
      Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse interdum

      sapien sit amet sem gravida, ut vestibulum ipsum varius. Vestibulum viverra

      mi dictum odio scelerisque semper. Morbi fermentum ut neque a mollis.

      Maecenas non quam magna. Suspendisse volutpat erat purus, quis tincidunt

      justo molestie eget. Fusce purus nisi, aliquam nec condimentum id, congue eu

      erat. Nam sit amet risus laoreet, maximus diam nec, cursus urna. Donec est
    </p>

  </div>
</div>
<!-- End Blog block -->

<!-- Upload Image-->
<div class="crm-accordion-wrapper pcp-dashboard-block-upload_image">
  <div class="crm-accordion-header"> Upload Image </div>
  <div class="crm-accordion-body">
    <BUTTON>
      Upload Image
    </BUTTON>
  </div>
</div>
<!-- End upload image -->


