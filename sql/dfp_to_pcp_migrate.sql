-- set vars
SELECT @drupal_db_name   := 'phing_vedaconsulting_llr_v1_drupal7';
SELECT @event_title      := 'Birmingham Bikeathon 2015';
SELECT @contrib_page_id  := 10;
SELECT @pcp_notify_email := 'deepak@vedaconsulting.co.uk';

SELECT @event_id := id FROM civicrm_event where title = @event_title  COLLATE utf8_unicode_ci;
SELECT @campaign_id := campaign_id FROM civicrm_event where title = @event_title  COLLATE utf8_unicode_ci;

-- enable pcp for event (only if not already enabled)
SELECT @supporter_profile_id := id from civicrm_uf_group where name = 'supporter_profile';
INSERT INTO `civicrm_pcp_block` (`entity_table`, `entity_id`, `target_entity_type`, `target_entity_id`, `supporter_profile_id`, `is_approval_needed`, `is_tellfriend_enabled`, `tellfriend_limit`, `link_text`, `is_active`, `notify_email`)
SELECT * from (SELECT 'civicrm_event' as etable, @event_id as eid, 'contribute' as tet, @contrib_page_id as tei, @supporter_profile_id as spi, 0 as ian, 1 as ite, 5 as tl, 'Promote this donation with a personal campaign page' as lt, 1 as ia, @pcp_notify_email as pne) as tmp WHERE NOT EXISTS (SELECT pb.id FROM civicrm_pcp_block pb  WHERE  pb.entity_table = 'civicrm_event' and pb.entity_id = @event_id) LIMIT 1;
SELECT @pcp_block_id := pb.id FROM civicrm_pcp_block pb WHERE  pb.entity_table = 'civicrm_event' and pb.entity_id = @event_id;

-- attach pcp to event, 2. set status to approved, 3. set honor roll
UPDATE civicrm_pcp as p
INNER JOIN civicrm_pcp_campaign as c on p.id = c.pcp_id
SET p.pcp_block_id = @pcp_block_id, p.status_id = 2, page_id = @event_id, page_type = 'event', is_honor_roll = 1
WHERE c.campaign_id = @campaign_id AND p.pcp_block_id = 0;

-- update pcp to update page-text from that of drupal
SET @s = CONCAT("UPDATE civicrm_pcp as p 
INNER JOIN civicrm_pcp_campaign as c ON p.id = c.pcp_id
INNER JOIN ", @drupal_db_name, ".field_data_body as fd ON fd.entity_id = c.drupal_node_id AND fd.entity_type = 'node' AND fd.bundle = 'fundraising_page'
SET p.page_text = fd.body_value
WHERE (p.page_text IS NULL OR p.page_text = '') AND p.pcp_block_id = @pcp_block_id AND p.status_id = 2");
PREPARE stmt1 FROM @s;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

-- update contact sub type for existing fundraising teams
UPDATE civicrm_contact c
INNER JOIN civicrm_value_fundraising_team_data_130 t on t.entity_id = c.id
SET c.contact_sub_type = REPLACE(c.contact_sub_type, 'Fundraising_Team', 'Team')
WHERE t.event_id_569 = @event_id;

-- convert 'team leader is' relationships to 'pcp team admin of'
SELECT @rel_type_id := id FROM civicrm_relationship_type where name_a_b = 'PCP Team Admin of';
SELECT @old_rel_type_id := id FROM civicrm_relationship_type where name_a_b = 'Team Leader is';
UPDATE civicrm_relationship r1
INNER JOIN civicrm_value_fundraising_team_data_130 team ON r1.contact_id_a = team.entity_id
INNER JOIN civicrm_relationship oldr ON r1.id = oldr.id
SET r1.contact_id_a=oldr.contact_id_b, r1.contact_id_b=oldr.contact_id_a, r1.relationship_type_id = @rel_type_id
WHERE r1.relationship_type_id = @old_rel_type_id AND team.event_id_569 = @event_id;

-- convert 'team member is' relationships to 'pcp team member of'
SELECT @rel_type_id := id FROM civicrm_relationship_type where name_a_b = 'PCP Team Member of';
SELECT @old_rel_type_id := id FROM civicrm_relationship_type where name_a_b = 'Team Member is';
UPDATE civicrm_relationship r1
INNER JOIN civicrm_value_fundraising_team_data_130 team ON r1.contact_id_a = team.entity_id
INNER JOIN civicrm_relationship oldr ON r1.id = oldr.id
SET r1.contact_id_a=oldr.contact_id_b, r1.contact_id_b=oldr.contact_id_a, r1.relationship_type_id = @rel_type_id
WHERE r1.relationship_type_id = @old_rel_type_id AND team.event_id_569 = @event_id;

-- insert new pcps for teams
INSERT INTO civicrm_pcp (contact_id, status_id, title, intro_text, page_id, page_type, pcp_block_id, is_honor_roll, is_active)
SELECT team.entity_id, 2, CONCAT(cc.display_name, ' PCP'), CONCAT(cc.display_name, ' PCP'), @event_id, 'event', @pcp_block_id, 1, 1
FROM civicrm_value_fundraising_team_data_130 team
INNER JOIN civicrm_contact cc ON cc.id = team.entity_id
LEFT JOIN civicrm_pcp p ON p.contact_id = team.entity_id AND p.page_id = @event_id AND page_type = 'event'
WHERE team.event_id_569 = @event_id AND p.id IS NULL;

-- update team member pcp(s) with team_pcp_id
SELECT @rel_type_id := id FROM civicrm_relationship_type where name_a_b = 'PCP Team Member of';
INSERT INTO civicrm_value_pcp_custom_set (entity_id, team_pcp_id)
SELECT mem.id, team.id
FROM civicrm_relationship rel
INNER JOIN civicrm_pcp mem on mem.contact_id = rel.contact_id_a
INNER JOIN civicrm_pcp team on team.contact_id = rel.contact_id_b
INNER JOIN civicrm_value_fundraising_team_data_130 ft on ft.entity_id = rel.contact_id_b
LEFT JOIN civicrm_value_pcp_custom_set cs ON cs.entity_id = mem.id AND cs.team_pcp_id = team.id
WHERE rel.relationship_type_id = @rel_type_id AND ft.event_id_569 = @event_id AND cs.id IS NULL;

-- DS: FIXME need to migrate uploaded pics as well
