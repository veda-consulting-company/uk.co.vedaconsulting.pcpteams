SELECT @event_title := '% 2015';
DELETE FROM civicrm_event where title like '% 2015';
DELETE FROM civicrm_contribution where trxn_id like 'live_%';

DELETE FROM civicrm_contact where organization_name like 'LLR Team %';
DELETE FROM civicrm_contact where last_name like 'Morley %';
DELETE FROM civicrm_contact where last_name like 'Molava %';

DELETE pfv.* 
FROM civicrm_price_field_value pfv
INNER JOIN civicrm_price_field pf on pfv.price_field_id = pf.id
INNER JOIN civicrm_price_set ps ON  pf.price_set_id = ps.id
where ps.title like '% 2015';

DELETE pf.*
FROM civicrm_price_field pf 
INNER JOIN civicrm_price_set ps ON  pf.price_set_id = ps.id
where ps.title like '% 2015';

DELETE pse.*
FROM civicrm_price_set_entity pse
INNER JOIN civicrm_price_set ps ON pse.price_set_id = ps.id AND (pse.entity_table = 'civicrm_event' || pse.entity_table = 'civicrm_contribution_page')
where ps.title like '% 2015';

DELETE ps.*
FROM civicrm_price_set ps
where ps.title like '% 2015';

DELETE pfv.* 
FROM civicrm_price_field_value pfv
INNER JOIN civicrm_price_field pf on pfv.price_field_id = pf.id
INNER JOIN civicrm_price_set ps ON  pf.price_set_id = ps.id
where ps.title like '% PCP Project!';

DELETE pf.*
FROM civicrm_price_field pf 
INNER JOIN civicrm_price_set ps ON  pf.price_set_id = ps.id
where ps.title like '% PCP Project!';

DELETE pse.*
FROM civicrm_price_set_entity pse
INNER JOIN civicrm_price_set ps ON pse.price_set_id = ps.id AND (pse.entity_table = 'civicrm_event' || pse.entity_table = 'civicrm_contribution_page')
where ps.title like '% PCP Project!';

DELETE ps.*
FROM civicrm_price_set ps
where ps.title like '% PCP Project!';

DELETE FROM civicrm_contribution_page where title like 'Help Support PCP Project!';

SELECT @cg_pcp_id := id FROM civicrm_custom_group where name = 'PCP_Custom_Set';
DELETE FROM civicrm_custom_group where name like 'PCP_Custom_Set';
DELETE FROM civicrm_custom_field where custom_group_id = @cg_pcp_id;

DELETE FROM civicrm_option_group where name like 'pcp_type_20150219182347';

DROP TABLE IF EXISTS civicrm_value_pcp_custom_set;
