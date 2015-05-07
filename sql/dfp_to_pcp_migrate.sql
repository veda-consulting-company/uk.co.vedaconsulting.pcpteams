-- set vars
SELECT @drupal_db_name   := 'phing_vedaconsulting_llr_v1_drupal7';
SELECT @event_title1     := 'London Bikeathon 2015';
SELECT @contrib_page_id  := 10;
SELECT @pcp_notify_email := 'deepak@vedaconsulting.co.uk';

SELECT @event_id1 = id FROM civicrm_event where title = @event_title1;
SELECT @campaign_id1 = campaign_id FROM civicrm_event where title = @event_title1;

-- enable pcp for event
SELECT @supporter_profile_id := id from civicrm_uf_group where name = 'Pcp_Supporter_Profile';
INSERT INTO `civicrm_pcp_block` (`entity_table`, `entity_id`, `target_entity_type`, `target_entity_id`, `supporter_profile_id`, `is_approval_needed`, `is_tellfriend_enabled`, `tellfriend_limit`, `link_text`, `is_active`, `notify_email`) VALUES
('civicrm_event', @event_id1, 'contribute', @contrib_page_id, @supporter_profile_id, 0, 1, 5, 'Promote this donation with a personal campaign page', 1, @pcp_notify_email);
SELECT @pcp_block_id := LAST_INSERT_ID();

-- attach pcp to event, 2. set status to approved
UPDATE civicrm_pcp p 
INNER JOIN civicrm_pcp_campaign c on p.id = c.pcp_id
SET p.pcp_block_id = @pcp_block_id, p.status_id = 2
WHERE c.campaign_id = @campaign_id1 AND p.pcp_block_id = 0

-- update pcp to update page-text from that of drupal
UPDATE civicrm_pcp p 
INNER JOIN civicrm_pcp_campaign c ON p.id = c.pcp_id
INNER JOIN @drupal_db_name.field_data_body fd ON fd.entity_id = c.drupal_node_id AND fd.entity_type = 'node' AND fd.bundle = 'fundraising_page'
SET p.page_text = fd.body_value
WHERE (p.page_text IS NULL OR p.page_text = '') AND p.pcp_block_id = @pcp_block_id AND p.status_id = 2;

-- DS: FIXME need to migrate uploaded pics as well
