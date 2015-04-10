{* HEADER *}
  {if $defaultImageUrl}
  <div class="avatar">
    <span> <strong> Current Profile Image </strong> </span>
  </div>
  <div class="avatar">
    <img src="{$defaultImageUrl}">
  </div>
  <div class="clear"></div>
  <br>
  {/if}
  <div class="crm-section">
    <div class="label">{$form.image_URL.label}</div>
    <div class="content">{$form.image_URL.html}</div>
    <div class="clear"></div>
  </div>

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
