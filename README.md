# uk.co.vedaconsulting.pcpteams

The extension has functionalities like:
- A journey for users to signup and create their page or team page or join an existing team.
- team admin being able to administer team page and its members.
- a pcp page with widgets / thermometer, for individual or team page
- a pcp dashboard for admin to manage from backend.
- notifications / activities

Its been a while, and not under active development.




Installation
-------------
Download / Clone PCP Teams extension from https://github.com/veda-consulting/uk.co.vedaconsulting.pcpteams into your CiviCRM custom extension directory. 

To install, navigate to

UI journey to CiviCRM extension page : Menu > Administer > System Settings > Manage Extensions

or 

Direct URL to CiviCRM extension page : http://YOUR_DOMAIN/civicrm/admin/extensions?reset=1

Click 'Refresh' button will display latest extensions from Custom Extension directory. Once extension has refreshed you can see the extension "PCP Team Management" in your extension list.

Click "Install" link to install this extension. 


Note : On installation process, this extensions creates default custom values,

1. Custom Group ('PCP Custom Set', 'PCP Relationship Set' )
2. Contact Sub Types ('Team', 'Branch, 'Corporate_Partner, 'In_Memory', 'In_Celebration')


Create Event 
-------------

Navigate to Menu > Events > New Event  or Using URL http://YOUR_DOMAIN/civicrm/event/add?reset=1&action=add

Create New Event with required values and click "Continue". Navigate to "Personal Campaigns" tab Click "Enable Personal Campaign Pages" for this event, filled up with required values and use "Pcp Supporter Profile" option from drop down for Supporter field. and also you can configure Personal Campaign Page link text (which would display on Event registration thank you page). Click "Save and Done".



Register for Event
-------------------

You can view list of Event in http://YOUR_DOMAIN/civicrm/event/manage?reset=1.

Click "Event Links" > "PCP Register for Events (EPI)" from action links.

this would take you to registration journey page with two buttons, to make sure you already have place in this event.
	1. 'No, I need to register' 
	2. 'Yes I do'

1. If you want register for this event click button 'No I need to Regsiter', then this would take to registration page for event. if you already registered with this event the journey would take you to PCP registration.
	
	Once you registered the Event, you can see link to promote PCP for this event. Click Personal Campaign Page link, would take you to PCP team set up journey page.



2. If you already registered, the jouney jump to Pcp team set up journey page.



PCP Team configuration journey
------------------------------

Are you supporting us as part of a team?
	
	Here we have 3 options 
	
	1. No, I am doing this event on my own
	2. Yes, I would like to create my own team
	3. Yes, I would like to join an existing team


	Option 1: 'I am doing this event on my own' :-
	----------------------------------------------
		This option would take you to your PCP Dashboard page, there you can see amount raised so far, and Buttons to create/join a PCP team.


	Option 2: 'I would like to create my own team' :-
	----------------------------------------------
		This option would take you to set up your new PCP team Page.

		Step 1. Enter Pcp Team Name  and Click Continue.
		Step 2. You can invite others to join this team or you can skip this step.

		Once you set up the Team, would take you to Team Dashboard, there you can see list of team members. Click "Invite Team Members" button to invite others to this team. This would send invitation email to the given email address (to send email please make sure CiviCRM SMTP is working).


	Option 3: 'I would like to join an existing team' :-
	--------------------------------------------------	
		This option would take you to join existing team. 

		Search the Team name using autocomplete field. if you can't find right team, you can see link in the above text "create your own team" to create one. 

		Find right team and Click "Continue". This would send request to team admin who can approve your request. and redirect to your dashboard.

		Here you can see the status of Team request and also you can use "withdraw request" button to cancel the your team request.


PCP Dashboard Page
------------------

In Dashboard, you can edit PCP target amount by inline link below the 'of target'. Click inline would allow you to amend target amount.

To donate from this dashboard, Click Donate button, which turns into inline edit amount field, Enter amount click OK to donate.