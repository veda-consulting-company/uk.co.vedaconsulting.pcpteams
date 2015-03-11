<!-- .tpl file invoked: CRM\Pcpteams\Page\Dashboard\Team.tpl. Call via Dashboard.tpl -->

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
                              </div><br /><br /><br />&nbsp;&nbsp;&nbsp;&nbsp;{ts}<h1>{$tplParams.title_of_page}</h1>{/ts}
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

      Congratulations, you are now signed up for {$tplParams.eventTitle}
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
      <p class="title_text"><strong>{$tplParams.totaliser}</strong></p>
    </div>
    <div class="pcp_block lightbground">
      {if $tplParams.page_state eq 'new'}
      <p class="title_thick">
        A bit Information about you 
      </p>
      <p class="block_body">
        This is sample text for Testing New Individual Even sign up page 7.0
      </p>
      {elseif $tplParams.page_state eq 'donations'}
      <p class="title_thick">
        Bio
      </p>
      <p class="block_body">
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse interdum sapien sit amet sem

        varius. Vestibulum viverra mi dictum odio scelerisque semper. Morbi fermentum ut neque a mollis.

        Suspendisse volutpat erat purus, quis tincidunt justo molestie eget. Fusce purus nisi, aliquam nec
      </p>
      {/if}
    </div>

    <!--Fundraiser update block -->
    <div class="pcp_fundrasier">
      <p class="title_thick">
        fundraise more, fundraise as a team
      </p>
      <p>


        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse interdum sapien sit amet sem

        varius. Vestibulum viverra mi dictum odio scelerisque semper. Morbi fermentum ut neque a mollis.

        Suspendisse volutpat erat purus, quis tincidunt justo molestie eget. Fusce purus nisi, aliquam nec

      </p>
      {if $createTeamUrl}
      <input type="button" name="createteam" value="Create a Team" id="createteam" onclick="parent.location='{$createTeamUrl}'" />
      {/if}
      {if $joinTeamUrl}
      <input type="button" name="jointeam" value="Join a Team" id="jointeam" onclick="parent.location='{$joinTeamUrl}'" />
      {/if}
      
    </div>
      <div class="description">
          
          
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

<!-- Give to name block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-givetoname">
    <div class="crm-accordion-header">{ts}Give to {$tplParams.fundraiser}{/ts}</div>
    <div class="crm-accordion-body pcp-dashboard-block-givetoname-text">
        <input type="button" name="givetoname" value="Give to {$tplParams.fundraiser}" id="givetoname" onclick="parent.location='{$tplParams.donate_to_url}'" />
    </div>
</div>

<!-- End Give to name block -->
      <!--Donations block -->
      <div class="crm-accordion-wrapper pcp-dashboard-block-donations">
          <div class="crm-accordion-header">{ts}Donations{/ts}</div>
          <div class="crm-accordion-body pcp-dashboard-block-donations-text">
              
                <div>
                      Name is <strong>{$tplParams.rankHolder}</strong> out 

                      of the {$tplParams.eventPcpCount} 

                      fundraisers taking

                      part in event
                </div>                
                <div class="honor_roll">
                    <marquee behavior="scroll" direction="up" id="pcp_roll"  scrolldelay="200" bgcolor="#fafafa">
                      {foreach from = $honor item = v}
                      <div class="pcp_honor_roll_entry">
                          <div class="pcp-honor_roll-nickname">{$v.nickname}</div>
                          <div class="pcp-honor_roll-total_amount">{$v.total_amount}</div>
                          <div class="pcp-honor_roll-personal_note">{$v.personal_note}</div>
                      </div>
                      {/foreach}
                    </marquee>
                </div>

          </div>
      </div>
 <!--End Donations block -->
 
 <!-- in memory block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-inmemory">
  <!-- Right side block -->
  <div class="crm-accordion-header">{ts}In Memory Name{/ts}</div>
  <div class="crm-accordion-body pcp-dashboard-block-inmemory-text">
    <div class="lightbground">
      <p class="title_text">{ts}In Memory Name{/ts}</p>
    </div>
    <div class="block_body block_body_alignment darkbground">
      <p>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse interdum sapien sit amet sem

        varius. Vestibulum viverra mi dictum odio scelerisque semper. Morbi fermentum ut neque a mollis.

        Suspendisse volutpat erat purus, quis tincidunt justo molestie eget. Fusce purus nisi, aliquam nec
      </p>
      <div style="clear: both;"></div>
    </div>
  </div>
</div>
    
    <!-- team block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-team">
  <!-- Right side block -->
  <div class="crm-accordion-header">{ts}Team name{/ts}</div>
  <div class="crm-accordion-body pcp-dashboard-block-team-text">
    
    <div class="block_body block_body_alignment darkbground">
      <p>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse interdum sapien sit amet sem

        varius. Vestibulum viverra mi dictum odio scelerisque semper. Morbi fermentum ut neque a mollis.

        Suspendisse volutpat erat purus, quis tincidunt justo molestie eget. Fusce purus nisi, aliquam nec
      </p>
      <div style="clear: both;"></div>
    </div>
  </div>
</div>
    
    
<!-- End team block -->
<!-- team and in memory block -->
<div class="crm-accordion-wrapper pcp-dashboard-block-inmemoryandteam">
  <!-- Right side block -->
  <div class="crm-accordion-header">{ts}Team and In Memory{/ts}</div>
  <div class="crm-accordion-body pcp-dashboard-block-inmemoryandteam-text">
    <table class ="form-layout" >
                    <tr>
                        <td>
                            <div id="crm-contact-thumbnail" style="float:left">
                                        <img width="100" height="97" src="{$profilePicUrl}">
                            </div>
                        </td>
                       <td>
                            <div id="crm-contact-thumbnail" style="float:left">
                                        <img width="100" height="97" src="{$profilePicUrl}">
                            </div>
                                       
                        </td>
                    </tr>
                    <tr>
                        <td style="float:left; ">
                            {ts}InMemoryofname{/ts}
                        </td>
                        <td style="float:right;">
                            {ts}Team Name{/ts}
                        </td>
                    </tr>
            </table>
  </div>
</div>
    
    
<!-- End team and in memory block -->
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


