SELECT @event_title := '% 2015';
DELETE FROM civicrm_event where title like '% 2015';

DELETE FROM civicrm_contact where organization_name like 'LLR Team %';
DELETE FROM civicrm_contact where last_name like 'Morley %';

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
