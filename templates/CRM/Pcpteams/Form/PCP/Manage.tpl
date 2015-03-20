<!-- header -->
<div class="pcp-manage-header">
  <!-- profile Image -->
  <div class="pcp-manage-header-profile-pic inline-display">
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

<div id="pcp_intro_text" class="crm-pcp-inline-edit" data-edit-params='{ldelim}"cid": "{$contactId}", "class_name": "CRM_Contact_Form_Inline_ContactInfo"{rdelim}'>
  {$pcpinfo.intro_text}
</div>
<div class="clearfix"></div>
<div id="pcp_page_text" class="crm-pcp-inline-edit">
  {$pcpinfo.page_text}
</div>


<!-- Congratulations block -->
<div class="crm-accordion-wrapper pcp-manage-block-info">
  <div class="crm-accordion-header">Event Name: {if $tplParams.event_title} {$tplParams.event_title} {/if}</div>
  <div class="crm-accordion-body pcp-manage-block-info-text crm-pcp-inline-edit">
    <strong>

      Congratulations, you are now signed up for {$tplParams.event_title}
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
{literal}
<script type="text/javascript">
CRM.$(function($) {
 $(document).ready(function() {
    $('.crm-pcp-inline-edit').editable('http://www.example.com/save.php', {
         //loadurl   : "http://cms45.loc/civicrm/ajax/inline?cid=202&class_name=CRM_Contact_Form_Inline_ContactInfo&snippet=6&reset=1",
         type      : 'textarea',
         cssclass  : 'crm-form-textarea',
         cancel    : 'Cancel',
         submit    : 'OK',
         indicator : '<img src="http://www.appelsiini.net/projects/jeditable/img/indicator.gif">',
         tooltip   : 'Click to edit...',
    });
    $('.crm-pcp-inline-edit').mouseover(function(){
      $(this).css("background", "grey");
    });
    $('.crm-pcp-inline-edit').mouseout(function(){
      $(this).css("background", "white");
    });
 });
});
</script>
{/literal}
