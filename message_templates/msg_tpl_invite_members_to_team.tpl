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

<p>
  <strong>
    Hi {$inviteeFirstName},
  </strong>

  <strong>{$userName}</strong> has invited you to partake in <strong>{$eventName}</strong> and become a member of team <strong>{$teamName}</strong> and help fight blood cancer with leukaemia and lymphoma research.

  To sign up simply click the link below to register for the event and start your fundraising.

  To help us keep track of you please use <strong>{$inviteeEmail}</strong> when you register.

  We hope you take the plunge! Love the LLR fundraising team
</p>

</body>
</html>
