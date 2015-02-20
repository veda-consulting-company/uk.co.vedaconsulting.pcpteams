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

SELECT @title := '% PCP';
DELETE FROM civicrm_pcp where title like '% PCP';

DELETE FROM civicrm_contribution_page where title like 'Help Support PCP Project!';

SELECT @cg_pcp_id := id FROM civicrm_custom_group where name = 'PCP_Custom_Set';
DELETE FROM civicrm_custom_group where name like 'PCP_Custom_Set';
DELETE FROM civicrm_custom_Field where custom_group_id = @cg_pcp_id;

SELECT @cf_og := 'pcp_type_20150219182347';
DELETE FROM civicrm_option_group where name like @cf_og;

SELECT @price_set_name := 'help_support_pcp_project__';
DELETE pfv.* 
FROM civicrm_price_field_value pfv
INNER JOIN civicrm_price_field pf on pfv.price_field_id = pf.id
INNER JOIN civicrm_price_set ps ON  pf.price_set_id = ps.id
where ps.name like @price_set_name;

