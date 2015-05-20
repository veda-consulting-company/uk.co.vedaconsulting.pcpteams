<div class="crm-form-block crm-search-form-block">
        {if $snippet eq 'json'}
        <h2>{ts}Invite others to join you{/ts}</h2><br />
        {else}
        {* workflow eq 1 is team create and 2 is team join *}
        <h2>{ts}Thanks {if $workflow eq 1} , {$teamTitle} is now set up. {elseif $workflow eq 2} for joining {$teamTitle}.{/if} You can now invite others to join you.{/ts}</h2><br />
        {/if}
        <div class="crm-group tell_friend_form-group">
            <table class="form-layout-compressed">

                <tr>
                    <td class="right font-size12pt">{$form.from_name.label}&nbsp;&nbsp;</td>
                    <td class="font-size12pt">{$form.from_name.html} &lt;{$form.from_email.html}&gt;</td>
                </tr>
                <!-- <tr>
                  <td class="label font-size12pt">{$form.suggested_message.label}</td>
                  <td>{$form.suggested_message.html}</td>
                </tr> -->
                <tr>
                  <td></td>
                  <td>
                  <fieldset class="crm-group tell_friend_emails-group">
                      <legend>{ts}Send to these Friend(s){/ts}</legend>
                      <table>
                        <tr class="columnheader">
                          <td>{ts}First Name{/ts}</td>
                          <td>{ts}Last Name{/ts}</td>
                          <td>{ts}Email Address{/ts}</td>
                        </tr>
                        {section name=mail start=1 loop=$mailLimit}
                        {assign var=idx  value=$smarty.section.mail.index}
                        <tr>
                          <td class="even-row">{$form.friend.$idx.first_name.html}</td>
                          <td class="even-row">{$form.friend.$idx.last_name.html}</td>
                          <td class="even-row">{$form.friend.$idx.email.html}</td>
                        </tr>
                        {/section}
                        <table id="additional-contacts-sec" style="display:none">
                            {section name=mail start=6 loop=11}
                            {assign var=idx  value=$smarty.section.mail.index}
                            <tr>
                              <td class="even-row">{$form.friend.$idx.first_name.html}</td>
                              <td class="even-row">{$form.friend.$idx.last_name.html}</td>
                              <td class="even-row">{$form.friend.$idx.email.html}</td>
                            </tr>
                            {/section}
                        </table>
                      </table>
                        <span id="add-more-contacts-link" title="{ts}click to add more{/ts}"><a class="crm-hover-button action-item add-more-inline" href="javascript:void(0)" onclick="showAdditional();">{ts}Add More{/ts}</a></span>
                  </fieldset>
                  </td>
                </tr>
            </table>
        </div>
		
	<!-- ( FOOTER ) -->
	<div class="crm-submit-buttons">
	{include file="CRM/common/formButtons.tpl" location="bottom"}
	</div>
</div>
        
{literal}
   <script type="text/javascript">
      function showAdditional(){
          cj('#additional-contacts-sec').show();
          cj('#add-more-contacts-link').hide();
      }
   </script>
{/literal}
        
