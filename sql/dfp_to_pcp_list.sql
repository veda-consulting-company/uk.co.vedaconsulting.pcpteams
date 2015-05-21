SELECT @event_title := 'Birmingham Bikeathon 2015';

SELECT @event_id := id FROM civicrm_event where title = @event_title  COLLATE utf8_unicode_ci;
SELECT @pcp_block_id := pb.id FROM civicrm_pcp_block pb WHERE  pb.entity_table = 'civicrm_event' and pb.entity_id = @event_id;

-- list non migrated pcps
SELECT count(*) as Credits, p.id as 'PCP ID', p.title as 'PCP Title', url.alias as 'DFP URL', fdh.field_dfp_header_fid as image_id
FROM civicrm_pcp p 
INNER JOIN civicrm_pcp_campaign c on p.id = c.pcp_id 
INNER JOIN civicrm_campaign camp on camp.id = c.campaign_id 
INNER JOIN civicrm_event e on camp.id = e.campaign_id
INNER JOIN phing_vedaconsulting_llr_v1_drupal7.url_alias url on url.source = CONCAT('node/', c.drupal_node_id)
INNER JOIN civicrm_contribution_soft sc on p.id = sc.pcp_id
 LEFT JOIN phing_vedaconsulting_llr_v1_drupal7.field_data_field_dfp_header fdh on fdh.entity_id = c.drupal_node_id AND fdh.entity_type = 'node' AND fdh.bundle='fundraising_page'
WHERE e.id = @event_id AND p.pcp_block_id = 0 
GROUP BY p.id ORDER BY Credits desc;

-- Query to list =>  Number of Soft Credits | PCP ID | PCP Title | DFP URL  | image_id |
SELECT count(*) as Credits, p.id as 'PCP ID', p.title as 'PCP Title', url.alias as 'DFP URL', fdh.field_dfp_header_fid as image_id
FROM civicrm_pcp p 
INNER JOIN civicrm_pcp_campaign c on p.id = c.pcp_id 
INNER JOIN civicrm_campaign camp on camp.id = c.campaign_id 
INNER JOIN civicrm_event e on camp.id = e.campaign_id
INNER JOIN phing_vedaconsulting_llr_v1_drupal7.url_alias url on url.source = CONCAT('node/', c.drupal_node_id)
INNER JOIN civicrm_contribution_soft sc on p.id = sc.pcp_id
 LEFT JOIN phing_vedaconsulting_llr_v1_drupal7.field_data_field_dfp_header fdh on fdh.entity_id = c.drupal_node_id AND fdh.entity_type = 'node' AND fdh.bundle='fundraising_page'
WHERE e.id = @event_id
GROUP BY p.id ORDER BY Credits desc;

-- list team pcps and their member pcps including admins
SELECT @rel_type_id1 := id FROM civicrm_relationship_type where name_a_b = 'PCP Team Admin of';
SELECT @rel_type_id2 := id FROM civicrm_relationship_type where name_a_b = 'PCP Team Member of';
SELECT team.id as team_pcp_id, team.contact_id as team_cid, mem.id as mem_pcp_id, mem.contact_id as mem_cid, team.title  
FROM civicrm_relationship rel 
INNER JOIN civicrm_pcp mem on mem.contact_id = rel.contact_id_a AND mem.pcp_block_id = @pcp_block_id AND mem.page_id = @event_id AND mem.page_type = 'event'
INNER JOIN civicrm_pcp team on team.contact_id = rel.contact_id_b AND team.pcp_block_id = @pcp_block_id AND team.page_id = @event_id AND team.page_type = 'event'
INNER JOIN civicrm_value_fundraising_team_data_130 ft on ft.entity_id = rel.contact_id_b
WHERE rel.relationship_type_id IN (@rel_type_id1, @rel_type_id2) AND ft.event_id_569 = @event_id;
