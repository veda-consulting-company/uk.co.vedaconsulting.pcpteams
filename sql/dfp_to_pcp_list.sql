-- Query to list =>  Number of Soft Credits | PCP ID | PCP Title | DFP URL  | image_id |
SELECT count(*) as Credits, p.id as 'PCP ID', p.title as 'PCP Title', url.alias as 'DFP URL', fdh.field_dfp_header_fid as image_id
FROM civicrm_pcp p 
INNER JOIN civicrm_pcp_campaign c on p.id = c.pcp_id 
INNER JOIN civicrm_campaign camp on camp.id = c.campaign_id 
INNER JOIN civicrm_event e on camp.id = e.campaign_id
INNER JOIN phing_vedaconsulting_llr_v1_drupal7.url_alias url on url.source = CONCAT('node/', c.drupal_node_id)
INNER JOIN civicrm_contribution_soft sc on p.id = sc.pcp_id
 LEFT JOIN phing_vedaconsulting_llr_v1_drupal7.field_data_field_dfp_header fdh on fdh.entity_id = c.drupal_node_id AND fdh.entity_type = 'node' AND fdh.bundle='fundraising_page'
WHERE e.title IN ("London Bikeathon 2015", "Birmingham Bikeathon 2015", "london to Paris 2015")
GROUP BY p.id ORDER BY Credits desc;

-- list non migrated pcps
SELECT count(*) as Credits, p.id as 'PCP ID', p.title as 'PCP Title', url.alias as 'DFP URL', fdh.field_dfp_header_fid as image_id
FROM civicrm_pcp p 
INNER JOIN civicrm_pcp_campaign c on p.id = c.pcp_id 
INNER JOIN civicrm_campaign camp on camp.id = c.campaign_id 
INNER JOIN civicrm_event e on camp.id = e.campaign_id
INNER JOIN phing_vedaconsulting_llr_v1_drupal7.url_alias url on url.source = CONCAT('node/', c.drupal_node_id)
INNER JOIN civicrm_contribution_soft sc on p.id = sc.pcp_id
 LEFT JOIN phing_vedaconsulting_llr_v1_drupal7.field_data_field_dfp_header fdh on fdh.entity_id = c.drupal_node_id AND fdh.entity_type = 'node' AND fdh.bundle='fundraising_page'
WHERE e.title IN ("London Bikeathon 2015", "Birmingham Bikeathon 2015") AND p.pcp_block_id = 0 
GROUP BY p.id ORDER BY Credits desc;
