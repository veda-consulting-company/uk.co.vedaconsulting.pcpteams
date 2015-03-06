<!-- ( Main Div ) -->
<div class="crm-form-block crm-search-form-block">
	<div class="crm-submit-buttons">
	{include file="CRM/common/formButtons.tpl" location="top"}
	</div>
          <table class="image_URL-section form-layout-compressed">
            <tr>
                <td>
                    {$form.image_URL.label}&nbsp;&nbsp;{help id="id-upload-image" file="CRM/Contact/Form/Contact.hlp"}<br />
                    {$form.image_URL.html|crmAddClass:twenty}
                    
                </td>
            </tr>
            
            {if $profilepic}
                <tr>
                    <td>
                        <div id="crm-contact-thumbnail">
                            <div class="crm-contact_image crm-contact_image-block">
                                <a class="crm-image-popup" href="{$profilePicUrl}">
                                    <img width="100" height="21" src="{$profilePicUrl}">
                                </a>
                            </div>
                        </div>
                        
                    </td>
                </tr>
            {/if}
            
          </table>
		
	<!-- ( FOOTER ) -->
	<div class="crm-submit-buttons">
	{include file="CRM/common/formButtons.tpl" location="bottom"}
	</div>
</div>
