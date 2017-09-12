# uk.co.vedaconsulting.pcpteams

## Use Cases

- You have a participation fundraising event such as 5k Run for the Cure, Holidays of Giving, etc.
- People wish to raise money as a team, group or company such as: "Johnson's Joggers" or "St. Marks Hospital"
- Each team will have a leader that can manage who is on their team 
- Each team member will have a separate page but have their efforts contribute to the total fundraising of their team
- Rather than pay high transaction % fees to a SaaS or license fees software company for these features, you wish to provide team fundraising through low-cost open-source CiviCRM without a redundant step to import your data later

The extension has functionalities like:

- A journey for users to signup for events and then create: their solo fundraising page, team, or join an existing team
- Team admin being able to administer team page and its members, with optional approval process, or enable auto-approval
- A redesigned PCP page with widgets, thermometer and inline editing
- A PCP dashboard for admin to manage from backend
- Notifications and activities
- Improved appearance of PCP pages

![Fundraising Page](https://raw.githubusercontent.com/Stoob/uk.co.vedaconsulting.pcpteams/5c00b922c525894193fcae2c7561720c506fb0b5/pcp.png "PCP Teams Fundraising Page")

## Installation

Download / Clone PCP Teams extension from https://github.com/veda-consulting/uk.co.vedaconsulting.pcpteams into your CiviCRM custom extension directory. 

To install, either navigate to:

* UI journey to CiviCRM extension page : Menu > Administer > System Settings > Extensions or 
* Direct URL to CiviCRM extension page : http://YOUR_DOMAIN/civicrm/admin/extensions?reset=1

Note: Click 'Refresh' button to display latest extensions from Custom Extension directory if you do not see the extension.

Click "Install" link to install this extension. 

Note: On installation process, this extensions creates default custom values,

1. Custom Group ('PCP Custom Set', 'PCP Relationship Set' )
2. Contact Sub Types ('Team', 'Branch, 'Corporate_Partner, 'In_Memory', 'In_Celebration')


## Settings

- http://YOUR_DOMAIN/civicrm/pcpteams/setting?reset=1 allows you to turn Notification on/off and other settings
- Edit the colors by customizing `css/manage.css` and `templates/CRM/Pcpteams/Page/Manage.tpl` within the extension itself


## Create Event

Navigate to Menu > Events > New Event  or Using URL http://YOUR_DOMAIN/civicrm/event/add?reset=1&action=add

Create New Event with required values and click "Continue". Navigate to "Personal Campaigns" tab Click "Enable Personal Campaign Pages" for this event, filled up with required values and use "Pcp Supporter Profile" option from drop down for Supporter field. and also you can configure Personal Campaign Page link text (which would display on Event registration thank you page). Click "Save and Done".


## Register for Event

You can view list of Event in http://YOUR_DOMAIN/civicrm/event/manage?reset=1.

Click "Event Links" > "PCP Register for Events (EPI)" from action links.

this would take you to registration journey page with two buttons, to make sure you already have place in this event.
1. 'No, I need to register' 
2. 'Yes I do'

Note: is is a configurable setting to skip this dialogue in your workflow if you want people to register for the event and create their PCP at the same time.

1. If you want register for this event click button 'No I need to Regsiter', then this would take to registration page for event if you already registered with this event the journey would take you to PCP registration.  Once you registered the Event, you can see link to promote PCP for this event. Click Personal Campaign Page link, would take you to PCP team set up journey page.

2. If you already registered, the jouney jump to PCP team set up journey page.  


## PCP Team configuration journey

Are you supporting us as part of a team?

Here we have 3 options 

1. No, I am doing this event on my own
2. Yes, I would like to create my own team
3. Yes, I would like to join an existing team

### Option 1: 'I am doing this event on my own'

Configure your own PCP page, with 'amount raised', and button to create/join a PCP team later if you choose.

### Option 2: 'I would like to create my own team'

This option would take you to set up your new PCP team Page.

Step 1. Enter Pcp Team Name and Click Continue.
Step 2. You can invite others to join this team or you can skip this step and do it later.

Once you set up the Team, would take you to Team Dashboard, there you can see list of team members. Click "Invite Team Members" button to invite others to this team. This would send invitation email to the given email address (to send email please make sure CiviCRM SMTP is working).

Note: this option creates a PCP page for the Team itself as well as a PCP page for you the individual.

### Option 3: 'I would like to join an existing team'

This option would take you to join existing team. 

Search the Team name you wish to join.  If you wish you can also still click to "create your own team" at this point. 

Find right team and Click "Continue". This would send request to team admin who can approve your request, if approval is a requirement and redirect you to your dashboard.

Here you can see the status of Team request and also you can use "withdraw request" button to cancel the your team request.


## PCP Dashboard Page

In Dashboard, you can edit various fields with "inline editing". 

For instance, click the link below 'of target'. Click inline would allow you to amend target (aka 'goal') amount.  The same is true of the description and title of your page.

To donate from this dashboard, Click Donate button, which turns into inline edit amount field, Enter amount click OK to donate.


## PCP Teams List

You can use the CiviCRM API or Drupal Views to publish a list of active individual PCPs and team PCPs.  Note that all teams have a CiviCRM Contact Subtype of "Team".
