<div class="crm-form-block crm-search-form-block">
        <h2>{ts}Thanks, {$teamTitle} is now setup, you can now invite others to join you{/ts}</h2><br />
        <div class="crm-group tell_friend_form-group">
            <table class="form-layout-compressed">

                <tr>
                    <td class="right font-size12pt">{$form.from_name.label}&nbsp;&nbsp;</td>
                    <td class="font-size12pt">{$form.from_name.html} &lt;{$form.from_email.html}&gt;</td>
                </tr>
                <tr>
                  <td class="label font-size12pt">{$form.suggested_message.label}</td>
                  <td>{$form.suggested_message.html}</td>
                </tr>
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
                      </table>
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
        
