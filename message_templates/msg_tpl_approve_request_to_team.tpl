<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <title></title>
</head>
<body>

{capture assign=headerStyle}colspan="2" style="text-align: left; padding: 4px; border-bottom: 1px solid #999; background-color: #eee;"{/capture}
{capture assign=labelStyle }style="padding: 4px; border-bottom: 1px solid #999; background-color: #f7f7f7;"{/capture}
{capture assign=valueStyle }style="padding: 4px; border-bottom: 1px solid #999;"{/capture}

<p><strong>{$userFirstName}</strong></p>

<p>Your request to join <strong> {$teamName}</strong> for <strong>{$eventName}</strong>  has been approved. You should now see yourself listed under team members on the <strong> {$teamName}</strong> fundraising page</p>

<p><a href="{$pageURL}">{ts}link to team page{/ts}</a></p>

<p>Thanks</p>

<p>The LLR Fundraising Team</p>


</body>
</html>
