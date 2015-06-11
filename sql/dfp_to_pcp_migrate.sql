-- set vars
SELECT @drupal_db_name   := 'phing_vedaconsulting_llr_v1_drupal7';
SELECT @contrib_page_id  := 20;
SELECT @pcp_notify_email := 'bvester@beatingbloodcancers.org.uk';

SELECT @supporter_profile_id := id from civicrm_uf_group where name = 'supporter_profile';

-- temporary table for holding all remaining campaigns & their events
CREATE TEMPORARY TABLE temp_dfp_pcp_migration (campaign_id int primary key, camp_title varchar(128), event_id int, eve_title varchar(128), pcp_blk_id int) ENGINE=MyISAM;

-- insert campaigns with exact events, where a campaign is associated with more than 1 event
INSERT INTO temp_dfp_pcp_migration (`campaign_id`, `camp_title`, `event_id`, `eve_title`) VALUES
(68, 'Company - Charity of the Year',          2731, 'Umbrella Charity of the Year event (DFP migration)'),
(2562, 'Challenge Events',                     2319, 'Challenge Events'),
(2806, 'A Christmas Cocktal 2011 - Donations', 2378, 'fake event'),
(3221, 'Regional Events',                      2732, 'Umbrella Regional Events (DFP migration)'),
(3247, 'London to Paris 2014',                 2473, 'London to Paris 2014'),
(3249, 'Blenheim Triathlon 2014',              2476, 'Blenheim Triathlon 2014'),
(3259, 'Aberdeen Walk 2013',                   2378, 'fake event'),
(3341, 'Run to the Beat 10k 2014',             2565, 'Run to the Beat 10k 2014'),
(3361, 'Edinburgh Marathon Festival 2015',     2592, 'Edinburgh Marathon Festival 2015'),
(3368, 'Regional Runs',                        2649, 'Cardiff Half Marathon 2015'),
(3371, 'London to Paris 2015',                 2604, 'London to Paris 2015'),
(3430, 'Southend Bikeathon 2015',              2690, 'Southend Bikeathon 2015'),
(3437, 'Yorkshire Marathon 2015',              2652, 'Yorkshire Marathon 2015');

-- insert campaigns with one events
INSERT INTO temp_dfp_pcp_migration
     SELECT camp.id, camp.title, e.id, e.title, NULL 
       FROM civicrm_campaign camp  
 INNER JOIN civicrm_event e on camp.id = e.campaign_id  
      WHERE camp.id IN (SELECT DISTINCT c.campaign_id from civicrm_pcp_campaign c) AND  e.title NOT IN ('Birmingham Bikeathon 2015', 'London Bikeathon 2015')
   GROUP BY camp.id 
     HAVING count(*) <= 1;

-- enable pcp (pcp blocks) for event (only if not already enabled)
INSERT INTO `civicrm_pcp_block` (`entity_table`, `entity_id`, `target_entity_type`, `target_entity_id`, `supporter_profile_id`, `is_approval_needed`, `is_tellfriend_enabled`, `tellfriend_limit`, `link_text`, `is_active`, `notify_email`)
     SELECT 'civicrm_event' as etable, tdfp.event_id as eid, 'contribute' as tet, @contrib_page_id as tei, @supporter_profile_id as spi, 
             0 as ian, 1 as ite, 5 as tl, 'Promote this donation with a personal campaign page' as lt, 1 as ia, @pcp_notify_email as pne 
       FROM temp_dfp_pcp_migration tdfp
  LEFT JOIN civicrm_pcp_block pb ON pb.entity_table = 'civicrm_event' AND pb.entity_id = tdfp.event_id AND pb.target_entity_type = 'contribute' AND 
            pb.target_entity_id = @contrib_page_id AND pb.supporter_profile_id = @supporter_profile_id
      WHERE pb.id IS NULL;

-- update pcp_blk_id in temp table based on event
   UPDATE temp_dfp_pcp_migration tdfp
LEFT JOIN civicrm_pcp_block pb ON pb.entity_table = 'civicrm_event' AND pb.entity_id = tdfp.event_id AND pb.target_entity_type = 'contribute' AND pb.target_entity_id = @contrib_page_id AND pb.supporter_profile_id = @supporter_profile_id
      SET tdfp.pcp_blk_id = pb.id 
    WHERE tdfp.pcp_blk_id IS NULL;

-- 1. attach pcp to event, 2. set status to approved, 3. set honor roll
-- DS: note we not changing old page_id = 10 and page_type = contribute, to page_id = 20
    UPDATE civicrm_pcp as p
INNER JOIN civicrm_pcp_campaign pc on p.id = pc.pcp_id
INNER JOIN temp_dfp_pcp_migration tdfp on tdfp.campaign_id = pc.campaign_id
       SET p.pcp_block_id = tdfp.pcp_blk_id, p.status_id = 2, page_id = tdfp.event_id, page_type = 'event', is_honor_roll = 1
     WHERE p.pcp_block_id = 0 AND tdfp.pcp_blk_id IS NOT NULL AND tdfp.event_id IS NOT NULL;

-- update pcp to update page-text from that of drupal
SET @s = CONCAT("UPDATE civicrm_pcp as p 
             INNER JOIN civicrm_pcp_campaign as c ON p.id = c.pcp_id
             INNER JOIN temp_dfp_pcp_migration tdfp on tdfp.campaign_id = c.campaign_id
             INNER JOIN ", @drupal_db_name, ".field_data_body as fd ON fd.entity_id = c.drupal_node_id AND fd.entity_type = 'node' AND fd.bundle = 'fundraising_page'
                    SET p.page_text = fd.body_value
                  WHERE (p.page_text IS NULL OR p.page_text = '') AND p.pcp_block_id = tdfp.pcp_blk_id AND p.status_id = 2");
PREPARE stmt1 FROM @s;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

-- update contact sub type for existing fundraising teams
    UPDATE civicrm_contact c
INNER JOIN civicrm_value_fundraising_team_data_130 t ON t.entity_id = c.id
INNER JOIN temp_dfp_pcp_migration tdfp ON tdfp.event_id = t.event_id_569
       SET c.contact_sub_type = REPLACE(c.contact_sub_type, 'Fundraising_Team', 'Team');

-- convert 'team leader is' relationships to 'pcp team admin of'
SELECT @rel_type_id := id FROM civicrm_relationship_type where name_a_b = 'PCP Team Admin of';
SELECT @old_rel_type_id := id FROM civicrm_relationship_type where name_a_b = 'Team Leader is';
    UPDATE civicrm_relationship r1
INNER JOIN civicrm_value_fundraising_team_data_130 team ON r1.contact_id_a = team.entity_id
INNER JOIN temp_dfp_pcp_migration tdfp ON tdfp.event_id = team.event_id_569
INNER JOIN civicrm_relationship oldr ON r1.id = oldr.id
       SET r1.contact_id_a = oldr.contact_id_b, r1.contact_id_b = oldr.contact_id_a, r1.relationship_type_id = @rel_type_id
     WHERE r1.relationship_type_id = @old_rel_type_id;

-- convert 'team member is' relationships to 'pcp team member of'
SELECT @rel_type_id := id FROM civicrm_relationship_type where name_a_b = 'PCP Team Member of';
SELECT @old_rel_type_id := id FROM civicrm_relationship_type where name_a_b = 'Team Member is';
    UPDATE civicrm_relationship r1
INNER JOIN civicrm_value_fundraising_team_data_130 team ON r1.contact_id_a = team.entity_id
INNER JOIN temp_dfp_pcp_migration tdfp ON tdfp.event_id = team.event_id_569
INNER JOIN civicrm_relationship oldr ON r1.id = oldr.id
       SET r1.contact_id_a = oldr.contact_id_b, r1.contact_id_b = oldr.contact_id_a, r1.relationship_type_id = @rel_type_id
     WHERE r1.relationship_type_id = @old_rel_type_id;

-- insert new pcps for teams
INSERT INTO civicrm_pcp (contact_id, status_id, title, intro_text, page_id, page_type, pcp_block_id, is_honor_roll, is_active)
     SELECT team.entity_id, 2, cc.display_name, cc.display_name, tdfp.event_id, 'event', tdfp.pcp_blk_id, 1, 1
       FROM civicrm_value_fundraising_team_data_130 team
 INNER JOIN civicrm_contact cc ON cc.id = team.entity_id
 INNER JOIN temp_dfp_pcp_migration tdfp ON tdfp.event_id = team.event_id_569
  LEFT JOIN civicrm_pcp p ON p.contact_id = team.entity_id AND p.pcp_block_id = tdfp.pcp_blk_id AND p.page_id = tdfp.event_id AND page_type = 'event'
      WHERE p.id IS NULL;

-- team member relationship ids
SELECT @rel_type_id1 := id FROM civicrm_relationship_type where name_a_b = 'PCP Team Admin of';
SELECT @rel_type_id2 := id FROM civicrm_relationship_type where name_a_b = 'PCP Team Member of';

-- update team member pcp(s) with team_pcp_id
INSERT INTO civicrm_value_pcp_custom_set (entity_id, team_pcp_id)
     SELECT mem.id, team.id
       FROM civicrm_relationship rel
 INNER JOIN civicrm_value_fundraising_team_data_130 ft on ft.entity_id = rel.contact_id_b
 INNER JOIN temp_dfp_pcp_migration tdfp ON tdfp.event_id = ft.event_id_569
 INNER JOIN civicrm_pcp mem on mem.contact_id = rel.contact_id_a AND mem.pcp_block_id = tdfp.pcp_blk_id AND mem.page_id = tdfp.event_id AND mem.page_type = 'event'
 INNER JOIN civicrm_pcp team on team.contact_id = rel.contact_id_b AND team.pcp_block_id = tdfp.pcp_blk_id AND team.page_id = tdfp.event_id AND team.page_type = 'event'
 INNER JOIN civicrm_contact cb on cb.id = rel.contact_id_b AND cb.is_deleted != 1
  LEFT JOIN civicrm_value_pcp_custom_set cs ON cs.entity_id = mem.id AND cs.team_pcp_id = team.id
      WHERE rel.relationship_type_id IN (@rel_type_id1, @rel_type_id2) AND cs.id IS NULL 
   GROUP BY mem.id, team.id;

-- fill relationship custom fields so we know the exact pcps the relationship is for
INSERT INTO civicrm_value_pcp_relationship_set (entity_id, pcp_a_b, pcp_b_a) 
     SELECT rel.id, mem.id as pcp_a_b, team.id as pcp_b_a
       FROM civicrm_relationship rel
 INNER JOIN civicrm_value_fundraising_team_data_130 ft on ft.entity_id = rel.contact_id_b
 INNER JOIN temp_dfp_pcp_migration tdfp ON tdfp.event_id = ft.event_id_569
 INNER JOIN civicrm_pcp mem on mem.contact_id = rel.contact_id_a AND mem.pcp_block_id = tdfp.pcp_blk_id AND mem.page_id = tdfp.event_id AND mem.page_type = 'event'
 INNER JOIN civicrm_pcp team on team.contact_id = rel.contact_id_b AND team.pcp_block_id = tdfp.pcp_blk_id AND team.page_id = tdfp.event_id AND team.page_type = 'event'
 INNER JOIN civicrm_contact cb on cb.id = rel.contact_id_b AND cb.is_deleted != 1
  LEFT JOIN civicrm_value_pcp_relationship_set rs ON rs.entity_id = rel.id 
      WHERE rel.relationship_type_id IN (@rel_type_id1, @rel_type_id2) AND rs.id IS NULL
      ON DUPLICATE KEY UPDATE pcp_a_b = VALUES(pcp_a_b), pcp_b_a = VALUES(pcp_b_a);

-- if team members don't have a pcp, update their relationships to in-active
    UPDATE civicrm_relationship rel 
INNER JOIN civicrm_value_fundraising_team_data_130 ft on ft.entity_id = rel.contact_id_b
INNER JOIN temp_dfp_pcp_migration tdfp ON tdfp.event_id = ft.event_id_569
INNER JOIN civicrm_pcp team on team.contact_id = rel.contact_id_b AND team.pcp_block_id = tdfp.pcp_blk_id AND team.page_id = tdfp.event_id AND team.page_type = 'event'
 LEFT JOIN civicrm_pcp mem on mem.contact_id = rel.contact_id_a AND mem.pcp_block_id = tdfp.pcp_blk_id AND mem.page_id = tdfp.event_id AND mem.page_type = 'event'
       SET rel.is_active = 0
     WHERE rel.relationship_type_id IN (@rel_type_id1, @rel_type_id2) AND mem.id IS NULL;

-- soft credit team pcp(s) based on member pcp(s)
INSERT INTO civicrm_contribution_soft (contribution_id, contact_id, amount, currency, pcp_id, pcp_display_in_roll, pcp_roll_nickname, pcp_personal_note, soft_credit_type_id) 
     SELECT cs.contribution_id, team.contact_id, cs.amount, cs.currency, mem.id, cs.pcp_display_in_roll, cs.pcp_roll_nickname, cs.pcp_personal_note, cs.soft_credit_type_id
       FROM civicrm_relationship rel 
 INNER JOIN civicrm_value_fundraising_team_data_130 ft on ft.entity_id = rel.contact_id_b
 INNER JOIN temp_dfp_pcp_migration tdfp ON tdfp.event_id = ft.event_id_569
 INNER JOIN civicrm_pcp mem on mem.contact_id = rel.contact_id_a AND mem.pcp_block_id = tdfp.pcp_blk_id AND mem.page_id = tdfp.event_id AND mem.page_type = 'event'
 INNER JOIN civicrm_pcp team on team.contact_id = rel.contact_id_b AND team.pcp_block_id = tdfp.pcp_blk_id AND team.page_id = tdfp.event_id AND team.page_type = 'event'
 INNER JOIN civicrm_contribution_soft cs on cs.pcp_id = mem.id
  LEFT JOIN civicrm_contribution_soft tcs on tcs.pcp_id = mem.id AND tcs.contribution_id = cs.contribution_id AND tcs.contact_id = team.contact_id
      WHERE rel.relationship_type_id IN (@rel_type_id1, @rel_type_id2) AND tcs.id IS NULL;

-- list team pcps and their member pcps including admins
    SELECT mem.id as mem_pcp_id, rel.contact_id_a as mem_cid, IF(rel.relationship_type_id <> @rel_type_id1, 'PCP Team Member Of', 'PCP Team Admin Of') as relation, team.title, team.id as team_pcp_id, team.contact_id as team_cid, rel.is_active
      FROM civicrm_relationship rel 
INNER JOIN civicrm_value_fundraising_team_data_130 ft on ft.entity_id = rel.contact_id_b
INNER JOIN temp_dfp_pcp_migration tdfp ON tdfp.event_id = ft.event_id_569
INNER JOIN civicrm_pcp team on team.contact_id = rel.contact_id_b AND team.pcp_block_id = tdfp.pcp_blk_id AND team.page_id = tdfp.event_id AND team.page_type = 'event'
 LEFT JOIN civicrm_pcp mem on mem.contact_id = rel.contact_id_a AND mem.pcp_block_id = tdfp.pcp_blk_id AND mem.page_id = tdfp.event_id AND mem.page_type = 'event'
     WHERE rel.relationship_type_id IN (@rel_type_id1, @rel_type_id2) ORDER BY team.id;

-- DS: FIXME need to migrate uploaded pics as well
