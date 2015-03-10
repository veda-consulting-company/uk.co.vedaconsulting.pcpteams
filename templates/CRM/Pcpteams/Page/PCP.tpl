<!-- .tpl file invoked: CRM\Pcpteams\Page\Dashboard.tpl. Call via form.tpl if we have a form in the page. -->
<!-- include CSS file -->
{if $config->extensionsURL}
<!-- <link rel="stylesheet" type="text/css" href="{$config->extensionsURL}/uk.co.vedaconsulting.pcpteams/css/style.css" /> -->
{/if}

{if $path}
<!-- include tpl based on the user state -->
{include file="CRM/Pcpteams/Page/PCP/$path.tpl"}
{/if}

