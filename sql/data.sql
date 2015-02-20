-- enable custom sets for PCPs
SELECT @option_group_id := max(id) from civicrm_option_group where name = 'cg_extend_objects';

INSERT INTO `civicrm_option_value` (`option_group_id`, `label`, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `domain_id`, `visibility_id`) VALUES
(@option_group_id, 'Personal Campaign Page', 'PCP', 'civicrm_pcp', NULL, 0, NULL, 1, NULL, 0, 0, 1, NULL, NULL, NULL);

-- Sample custom set
INSERT INTO `civicrm_custom_group` (`name`, `title`, `extends`, `extends_entity_column_id`, `extends_entity_column_value`, `style`, `collapse_display`, `help_pre`, `help_post`, `weight`, `is_active`, `table_name`, `is_multiple`, `min_multiple`, `max_multiple`, `collapse_adv_display`, `created_id`, `created_date`, `is_reserved`) VALUES
('PCP_Custom_Set', 'PCP Custom Set', 'PCP', NULL, NULL, 'Inline', 1, '', '', 2, 1, 'civicrm_value_pcp_custom_set', 0, NULL, NULL, 0, 202, '2015-02-18 17:08:38', 0);
SELECT @cg_id := LAST_INSERT_ID();

-- custom field option group and option value
INSERT INTO `civicrm_option_group` (`name`, `title`, `description`, `is_reserved`, `is_active`, `is_locked`) VALUES
('pcp_type_20150219182347', 'Pcp Type', NULL, 1, 1, 0);
SELECT @cf_option_group_id := LAST_INSERT_ID();

INSERT INTO `civicrm_option_value` (`option_group_id`, `label`, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `domain_id`, `visibility_id`) VALUES
(@cf_option_group_id, 'In Memory', 'in_memory', 'In Memory', NULL, NULL, 0, 1, NULL, 0, 0, 1, NULL, NULL, NULL),
(@cf_option_group_id, 'In Celebration', 'in_celebration', 'In Celebration', NULL, NULL, 0, 2, NULL, 0, 0, 1, NULL, NULL, NULL);

INSERT INTO `civicrm_custom_field` (`custom_group_id`, `name`, `label`, `data_type`, `html_type`, `default_value`, `is_required`, `is_searchable`, `is_search_range`, `weight`, `help_pre`, `help_post`, `mask`, `attributes`, `javascript`, `is_active`, `is_view`, `options_per_line`, `text_length`, `start_date_years`, `end_date_years`, `date_format`, `time_format`, `note_columns`, `note_rows`, `column_name`, `option_group_id`, `filter`, `in_selector`) VALUES
(@cg_id, 'Team_PCP_ID', 'Team PCP ID', 'Int', 'Text', NULL, 0, 0, 0, 5, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'team_pcp_id', NULL, NULL, 0),
(@cg_id, 'PCP_Type', 'PCP Type', 'String', 'Select', NULL, 0, 0, 0, 7, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'pcp_type', @cf_option_group_id, NULL, 0),
(@cg_id, 'PCP_Type_Contact', 'PCP Type Contact', 'ContactReference', 'Autocomplete-Select', NULL, 0, 0, 0, 9, NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 255, NULL, NULL, NULL, NULL, 60, 4, 'pcp_type_contact', NULL, NULL, 0);

CREATE TABLE IF NOT EXISTS `civicrm_value_pcp_custom_set` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Table that this extends',
  `team_pcp_id` int(11) DEFAULT NULL,
  `pcp_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pcp_type_contact` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_entity_id` (`entity_id`),
  KEY `FK_civicrm_value_pcp_custom_set_pcp_type_c` (`pcp_type_contact`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

ALTER TABLE `civicrm_value_pcp_custom_set`
  ADD CONSTRAINT `FK_civicrm_value_pcp_custom_set_entity_id` FOREIGN KEY (`entity_id`) REFERENCES `civicrm_pcp` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_civicrm_value_pcp_custom_set_p_0968c5aeb4b6cba5` FOREIGN KEY (`pcp_type_contact`) REFERENCES `civicrm_contact` (`id`) ON DELETE SET NULL;


--set custom value 
--pcp type contact
SELECT @fn := 'veda';
SELECT @ln := CONCAT('veda', " ", CEIL(RAND()*100));

INSERT INTO `civicrm_contact` (`contact_type`, `contact_sub_type`, `do_not_email`, `do_not_phone`, `do_not_mail`, `do_not_sms`, `do_not_trade`, `is_opt_out`, `legal_identifier`, `external_identifier`, `sort_name`, `display_name`, `nick_name`, `legal_name`, `image_URL`, `preferred_communication_method`, `preferred_language`, `preferred_mail_format`, `api_key`, `source`, `first_name`, `middle_name`, `last_name`, `prefix_id`, `suffix_id`, `formal_title`, `communication_style_id`, `email_greeting_id`, `email_greeting_custom`, `email_greeting_display`, `postal_greeting_id`, `postal_greeting_custom`, `postal_greeting_display`, `addressee_id`, `addressee_custom`, `addressee_display`, `job_title`, `gender_id`, `birth_date`, `is_deceased`, `deceased_date`, `household_name`, `primary_contact_id`, `organization_name`, `sic_code`, `user_unique_id`, `employer_id`, `is_deleted`) VALUES
('Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, CONCAT(@ln, ", ", @fn), CONCAT(@fn, " ", @ln), NULL, NULL, NULL, NULL, 'en_US', 'Both', NULL, NULL, @fn, NULL, @ln, NULL, NULL, NULL, NULL, 1, NULL, 'Dear veda', 1, NULL, 'Dear veda', 1, NULL, CONCAT(@fn, " ", @ln), NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);
SELECT @pcp_type_contact_id := LAST_INSERT_ID();


