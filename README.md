# PCP Teams

## Use cases

- You have a participation fundraising event such as 5k Run for the Cure, Holidays of Giving, etc.
- People wish to raise money as a team, group or company such as: "Johnson's Joggers" or "St. Marks Hospital"
- Each team will have a leader that can manage who is on their team 
- Each team member will have a separate page but have their efforts contribute to the total fundraising of their team
- Rather than pay high transaction % fees to a SaaS or license fees software company for these features, you wish to provide team fundraising through low-cost open-source CiviCRM without a redundant step to import your data later

## Features

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


## Configuration

- http://YOUR_DOMAIN/civicrm/pcpteams/setting?reset=1 allows you to turn notification on/off and other settings
- Edit the colors by customizing `css/manage.css` and `templates/CRM/Pcpteams/Page/Manage.tpl` within the extension itself

## Usage

### Creating a new event

1. Navigate to **Events > New Event**.
1. Create a new event with the required values and click "Continue".
1. Click on the **Personal Campaigns** tab, and click **Enable Personal Campaign Pages** for this event.
1. For "Supporter Profile", choose "PCP Supporter Profile".
1. Fill in the other fields as necessary and click **Save**.

### Registering for an event

1. Navigate to **Events > Manage Events**.
1. Locate an event for which you have configured personal campaign pages.
1. Click **Event Links > PCP Register for Events (EPI)** from action links.
1. This will take you to a "registration journey" page with two buttons, to make sure you already have place in this event.
    - "No, I need to register" 
    - "Yes I do"

    Note: is is a configurable setting to skip this dialogue in your workflow if you want people to register for the event and create their PCP at the same time.

1. If you want to register for this event click **No, I need to Register**. Then this will take you to the registration page for the event. If you already registered with this event the journey will take you to PCP registration. Once you registered the event, you can see link to promote PCP for this event. Click the **Personal Campaign Page** link to go to the PCP team setup journey page.

1. If you already registered, the journey jumps to the "PCP Team Setup" journey page.  

### PCP Team configuration journey

Are you supporting us as part of a team?

Here we have the following 3 options...

#### Option 1: 'I am doing this event on my own'

Configure your own PCP page, with 'amount raised', and button to create/join a PCP team later if you choose.

#### Option 2: 'I would like to create my own team'

This option would take you to set up your new PCP team page with the following steps:

1. Enter PCP Team Name and Click Continue.
1. You can invite others to join this team or you can skip this step and do it later.
1. After setting up the team, you will arrive at the Team Dashboard where you can see list of team members.
1. Click **Invite Team Members** to invite others to this team. This will send an invitation email to the given email address. (Make sure [outbound email](https://docs.civicrm.org/user/en/latest/advanced-configuration/email-system-configuration/) is working.)

Note: this option creates a PCP page for the team itself as well as a PCP page for you the individual.

#### Option 3: 'I would like to join an existing team'

This option would take you to join existing team. 

1. Search the team name you wish to join.
    * If you wish you can also still click to **create your own team** at this point. 

1. Find right team and click **Continue**.
    * This will send request to team admin who can approve your request (if required) and redirect you to your dashboard.

1. At the dashboard you can see the status of your team request and can click **withdraw request** to cancel it.

### PCP Dashboard Page

In the dashboard, you can edit various fields with "inline editing". 

For instance, click the link below 'of target'. Clicking willing allow you to amend the target (aka 'goal') amount inline. The same is true of the description and title of your page.

To donate from this dashboard, click the **Donate** button, which turns into an inline edit amount field. Enter an amount, and click **OK** to donate.

### PCP Teams List

You can use the CiviCRM API or Drupal Views to publish a list of active individual PCPs and team PCPs.  Note that all teams have a CiviCRM contact subtype of "Team".
