<table class="dashboard-elements">
  {if $showGroup}
    <tr class="crm-dashboard-groups">
      <td>
        <div class="header-dark">
          {ts}Your Group(s){/ts}
        </div>
        {include file="CRM/Contact/Page/View/UserDashBoard/GroupContact.tpl"}

      </td>
    </tr>
  {/if}

  {foreach from=$dashboardElements item=element}
    <tr{if isset($element.class)} class="{$element.class}"{/if}>
      <td>
        <div class="header-dark">{$element.sectionTitle}</div>
        {include file=$element.templatePath}
      </td>
    </tr>
  {/foreach}
</table>
