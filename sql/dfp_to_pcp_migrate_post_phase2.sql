-- set vars
SELECT @drupal_db_name   := 'lalrorg_live_drupal7';
SELECT @contrib_page_id  := 20;
SELECT @pcp_notify_email := 'bvester@beatingbloodcancers.org.uk';

SELECT @supporter_profile_id := id from civicrm_uf_group where name = 'supporter_profile';

-- temporary table for holding all remaining campaigns & their events
CREATE TEMPORARY TABLE temp_dfp_pcp_migration (campaign_id int, camp_title varchar(128), event_id int, eve_title varchar(128), pcp_blk_id int) ENGINE=MyISAM;

-- so we have correct campaign assumption for rest of the queries
update civicrm_pcp_campaign set campaign_id = 3475 where pcp_id = 5465;

-- insert campaigns with exact events, where a campaign is associated with more than 1 event
INSERT INTO temp_dfp_pcp_migration (`campaign_id`, `camp_title`, `event_id`, `eve_title`) VALUES
(3366, 'Berkeley Construct',                      2744, 'Berkeley construct eventr (DFP migration)'),  
(3398, 'London Bikeathon 2015',                   2622, 'London Bikeathon 2015'),
(3399, 'Birmingham Bikeathon 2015',                2623, 'Birmingham Bikeathon 2015'),
(3475, 'Fireflies Tour - Charity donations 2015', 2698, 'The FireFlies Tour 2015'),
(3475, 'Fireflies Tour - Trading (Entry fees and corporate sponsorship) 2015', 2698, 'The FireFlies Tour 2015');

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
     WHERE (p.pcp_block_id = 0 OR p.page_type = 'contribute') AND tdfp.pcp_blk_id IS NOT NULL AND tdfp.event_id IS NOT NULL AND p.id IN (4902, 5026, 5465, 6571, 1427);

-- update pcp to update page-text from that of drupal
SET @s = CONCAT("UPDATE civicrm_pcp as p 
             INNER JOIN civicrm_pcp_campaign as c ON p.id = c.pcp_id
             INNER JOIN temp_dfp_pcp_migration tdfp on tdfp.campaign_id = c.campaign_id
             INNER JOIN ", @drupal_db_name, ".field_data_body as fd ON fd.entity_id = c.drupal_node_id AND fd.entity_type = 'node' AND fd.bundle = 'fundraising_page'
                    SET p.page_text = fd.body_value
                  WHERE (p.page_text IS NULL OR p.page_text = '') AND p.pcp_block_id = tdfp.pcp_blk_id AND p.status_id = 2 AND p.id IN (4902, 5026, 5465, 6571, 1427)");
PREPARE stmt1 FROM @s;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

-- update contact sub type for existing fundraising teams
    UPDATE civicrm_contact c
INNER JOIN civicrm_value_fundraising_team_data_130 t ON t.entity_id = c.id
INNER JOIN temp_dfp_pcp_migration tdfp ON tdfp.event_id = t.event_id_569
       SET c.contact_sub_type = REPLACE(c.contact_sub_type, 'Fundraising_Team', 'Team');

-- list team pcps and their member pcps including admins
SELECT @rel_type_id1 := id FROM civicrm_relationship_type where name_a_b = 'PCP Team Admin of';
SELECT @rel_type_id2 := id FROM civicrm_relationship_type where name_a_b = 'PCP Team Member of';
    SELECT mem.id as mem_pcp_id, rel.contact_id_a as mem_cid, IF(rel.relationship_type_id <> @rel_type_id1, 'PCP Team Member Of', 'PCP Team Admin Of') as relation, team.title, team.id as team_pcp_id, team.contact_id as team_cid, rel.is_active
      FROM civicrm_relationship rel 
INNER JOIN civicrm_value_fundraising_team_data_130 ft on ft.entity_id = rel.contact_id_b
INNER JOIN temp_dfp_pcp_migration tdfp ON tdfp.event_id = ft.event_id_569
INNER JOIN civicrm_pcp team on team.contact_id = rel.contact_id_b AND team.pcp_block_id = tdfp.pcp_blk_id AND team.page_id = tdfp.event_id AND team.page_type = 'event'
 LEFT JOIN civicrm_pcp mem on mem.contact_id = rel.contact_id_a AND mem.pcp_block_id = tdfp.pcp_blk_id AND mem.page_id = tdfp.event_id AND mem.page_type = 'event'
     WHERE rel.relationship_type_id IN (@rel_type_id1, @rel_type_id2) AND (mem.id IN (4902, 5026, 5465, 6571, 1427) OR team.id IN (4902, 5026, 5465, 6571, 1427))ORDER BY team.id;

