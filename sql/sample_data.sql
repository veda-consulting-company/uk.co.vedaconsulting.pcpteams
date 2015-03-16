-- set vars
SELECT @event_title := 'bikeathon 2015';
SELECT @event_name  := 'bikeathon_2015';
SELECT @contrib_page_title := 'Help Support PCP Project!';
SELECT @contrib_page_name  := 'help_support_pcp_project!';
SELECT @contact_type_team  := "Team";
SELECT @contact_type_branch:= "Branch";
SELECT @contact_type_in_mem:= "In_Memory";
SELECT @contact_type_partner:= "Corporate_Partner";
SELECT @contact_type_in_celb:= "In_Celebration";

-- price set for event
INSERT INTO `civicrm_price_set` (`domain_id`, `name`, `title`, `is_active`, `help_pre`, `help_post`, `javascript`, `extends`, `financial_type_id`, `is_quick_config`, `is_reserved`) VALUES
(NULL, @event_name, @event_title, 1, NULL, NULL, NULL, '1', NULL, 1, 0);
SELECT @price_set_id := LAST_INSERT_ID();

-- price field 
INSERT INTO `civicrm_price_field` (`price_set_id`, `name`, `label`, `html_type`, `is_enter_qty`, `help_pre`, `help_post`, `weight`, `is_display_amounts`, `options_per_line`, `is_active`, `is_required`, `active_on`, `expire_on`, `javascript`, `visibility_id`) VALUES
(@price_set_id, 'athon_fees', 'athon Fees', 'Radio', 0, NULL, NULL, 1, 1, 1, 1, 1, NULL, NULL, NULL, 1);
SELECT @price_field_id := LAST_INSERT_ID();

INSERT INTO `civicrm_price_field_value` (`price_field_id`, `name`, `label`, `description`, `amount`, `count`, `max_value`, `weight`, `membership_type_id`, `membership_num_terms`, `is_default`, `is_active`, `financial_type_id`, `deductible_amount`) VALUES
(@price_field_id, 'stud_14_20', 'Students (ages 14-20)', NULL, '800', NULL, NULL, 1, NULL, NULL, 1, 1, 4, 0.00),
(@price_field_id, 'young_21_30', 'Young Stars (ages 21-30)', NULL, '1000', NULL, NULL, 2, NULL, NULL, 0, 1, 4, 0.00),
(@price_field_id, 'super_30_45', 'Super Stars (ages 30-45)', NULL, '900',  NULL, NULL, 3, NULL, NULL, 0, 1, 4, 0.00);

-- price set for contribution page
INSERT INTO `civicrm_price_set` (`domain_id`, `name`, `title`, `is_active`, `help_pre`, `help_post`, `javascript`, `extends`, `financial_type_id`, `is_quick_config`, `is_reserved`) VALUES
(NULL, @contrib_page_name, @contrib_page_title, 1, NULL, NULL, NULL, '2', 1, 1, 0);
SELECT @price_set_id_contribution := LAST_INSERT_ID();

-- price field 
INSERT INTO `civicrm_price_field` (`price_set_id`, `name`, `label`, `html_type`, `is_enter_qty`, `help_pre`, `help_post`, `weight`, `is_display_amounts`, `options_per_line`, `is_active`, `is_required`, `active_on`, `expire_on`, `javascript`, `visibility_id`) VALUES
(@price_set_id_contribution, 'contribution_amount', 'Contribution Amount', 'Radio', 0, NULL, NULL, 2, 1, 1, 1, 0, NULL, NULL, NULL, 1);
SELECT @price_field_id_contribution := LAST_INSERT_ID();

INSERT INTO `civicrm_price_field_value` (`price_field_id`, `name`, `label`, `description`, `amount`, `count`, `max_value`, `weight`, `membership_type_id`, `membership_num_terms`, `is_default`, `is_active`, `financial_type_id`, `deductible_amount`) VALUES
(@price_field_id_contribution, 'small', 'Small', NULL, '100', NULL, NULL, 1, NULL, NULL, 1, 1, 1, '0.00'),
(@price_field_id_contribution, 'medium', 'Medium', NULL, '500', NULL, NULL, 1, NULL, NULL, 1, 1, 1, '0.00'),
(@price_field_id_contribution, 'Large', 'Large', NULL, '1000', NULL, NULL, 2, NULL, NULL, 0, 1, 1, '0.00');

-- Choose a loc
SELECT @loc_block_id := max(loc_block_id) from civicrm_event;

-- Payment Processor
SELECT @payment_processor_type_id := id FROM civicrm_payment_processor_type WHERE name = 'Dummy';
INSERT INTO `civicrm_payment_processor` (`domain_id`, `name`, `description`, `payment_processor_type_id`, `is_active`, `is_default`, `is_test`, `user_name`, `password`, `signature`, `url_site`, `url_api`, `url_recur`, `url_button`, `subject`, `class_name`, `billing_mode`, `is_recur`, `payment_type`) VALUES
(1, 'Test Processor', '', @payment_processor_type_id, 1, 0, 0, 'dummy', NULL, NULL, 'http://dummy.com', NULL, 'http://dummyrecur.com', NULL, NULL, 'Payment_Dummy', 1, 1, 1) ON DUPLICATE KEY UPDATE `name` = VALUES (`name`)  ;
SELECT @payment_processor := id FROM civicrm_payment_processor WHERE name = 'Test Processor' AND is_test = 0;

-- Event type
-- SELECT @event_type_name := 'Fundraiser';
-- SELECT @event_type_op_name	:= 'event_type';
SELECT @event_type_id := value FROM civicrm_option_value WHERE name = 'Fundraiser' AND option_group_id = ( SELECT id FROM civicrm_option_group WHERE name = 'event_type');

-- financial type
select @financial_type_id := id from civicrm_financial_type where name = 'Event Fee';

-- ############################## Event ########################################################
-- fixme: camp-id
-- create event
INSERT INTO `civicrm_event` (`title`, `summary`, `description`, `event_type_id`, `participant_listing_id`, `is_public`, `start_date`, `end_date`, `is_online_registration`, `registration_link_text`, `registration_start_date`, `registration_end_date`, `max_participants`, `event_full_text`, `is_monetary`, `financial_type_id`, `payment_processor`, `is_map`, `is_active`, `fee_label`, `is_show_location`, `loc_block_id`, `default_role_id`, `intro_text`, `footer_text`, `confirm_title`, `confirm_text`, `confirm_footer_text`, `is_email_confirm`, `confirm_email_text`, `confirm_from_name`, `confirm_from_email`, `cc_confirm`, `bcc_confirm`, `default_fee_id`, `default_discount_fee_id`, `thankyou_title`, `thankyou_text`, `thankyou_footer_text`, `is_pay_later`, `pay_later_text`, `pay_later_receipt`, `is_partial_payment`, `initial_amount_label`, `initial_amount_help_text`, `min_initial_amount`, `is_multiple_registrations`, `allow_same_participant_emails`, `has_waitlist`, `requires_approval`, `expiration_time`, `waitlist_text`, `approval_req_text`, `is_template`, `template_title`, `created_id`, `created_date`, `currency`, `campaign_id`, `is_share`, `is_confirm_enabled`, `parent_event_id`, `slot_label_id`, `dedupe_rule_group_id`) VALUES
(@event_title, 'Sign up your team to participate in this fun athon which benefits several Rain-forest protection groups in maxico.', '<p>This is a FYSA Sanctioned athon, which is open to all USSF/FIFA affiliated organizations for boys and girls in age groups: U9-U10 (6v6), U11-U12 (8v8), and U13-U17 (Full Sided).</p>', @event_type_id, 1, 1, '2015-10-05 07:00:00', '2015-10-07 17:00:00', 1, 'Register Now', NULL, NULL, 500, 'Sorry! All available team slots for this athon have been filled. Contact Jill Futbol for information about the waiting list and next years event.', 1, @financial_type_id, @payment_processor, 0, 1, 'athon Fees', 1, @loc_block_id, 1, 'Complete the form below to register your team for this year''s athon.', '<em>A Soccer Youth Event</em>', 'Review and Confirm Your Registration Information', '', '<em>A Soccer Youth Event</em>', 1, 'Contact our athon Director for eligibility details.', 'athon Director', 'athon@example.org', '', NULL, NULL, NULL, 'Thanks for Your Support!', '<p>Thank you for your support. Your participation will help save thousands of acres of rainforest.</p>', '<p><a href=http://civicrm.org>Back to CiviCRM Home Page</a></p>', 0, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 'USD', NULL, 1, 1, NULL, NULL, NULL);
SELECT @event_id := LAST_INSERT_ID();

-- link profile with event
select @uf_group_id := id from civicrm_uf_group where name = 'event_registration';
INSERT INTO `civicrm_uf_join` (`is_active`, `module`, `entity_table`, `entity_id`, `weight`, `uf_group_id`, `module_data`) VALUES
(1, 'CiviEvent', 'civicrm_event', @event_id, 1, @uf_group_id, NULL);

-- link price set with event
INSERT INTO `civicrm_price_set_entity` (`entity_table`, `entity_id`, `price_set_id`) VALUES
('civicrm_event', @event_id, @price_set_id);

-- ############################## Contribution and PCP Pages #####################################
-- create contribution page
SELECT @financial_type_id := id from civicrm_financial_type where name = 'Donation';

INSERT INTO `civicrm_contribution_page` (`title`, `intro_text`, `financial_type_id`, `payment_processor`, `is_credit_card_only`, `is_monetary`, `is_recur`, `is_confirm_enabled`, `recur_frequency_unit`, `is_recur_interval`, `is_recur_installments`, `is_pay_later`, `pay_later_text`, `pay_later_receipt`, `is_partial_payment`, `initial_amount_label`, `initial_amount_help_text`, `min_initial_amount`, `is_allow_other_amount`, `default_amount_id`, `min_amount`, `max_amount`, `goal_amount`, `thankyou_title`, `thankyou_text`, `thankyou_footer`, `is_for_organization`, `for_organization`, `is_email_receipt`, `receipt_from_name`, `receipt_from_email`, `cc_receipt`, `bcc_receipt`, `receipt_text`, `is_active`, `footer_text`, `amount_block_is_active`, `start_date`, `end_date`, `created_id`, `created_date`, `currency`, `campaign_id`, `is_share`) VALUES
(@contrib_page_title, 'Do you love PCP? Do you use PCP? Then please support PCP and Contribute NOW by trying out our new online contribution features!', @financial_type_id, @payment_processor, 0, 1, 0, 1, NULL, 0, 0, 0, NULL, NULL, 0, NULL, NULL, NULL, 1, 137, 10.00, 10000.00, 100000.00, 'Thanks for Your Support!', '<p>Thank you for your support. Your contribution will help us build even better tools.</p><p>Please tell your friends and colleagues about PCP!</p>', '<p><a href=http://civicrm.org>Back to PCP Home Page</a></p>', 0, NULL, 1, 'PCP Fundraising Dept.', 'donationFake@civicrm.org', 'receipt@example.com', 'bcc@example.com', 'Your donation is tax deductible under IRS 501(c)(3) regulation. Our tax identification number is: 93-123-4567', 1, NULL, 1, NULL, NULL, NULL, NULL, 'USD', NULL, 1);
SELECT @contrib_page_id := LAST_INSERT_ID();

-- link with price set entity
INSERT INTO `civicrm_price_set_entity` (`entity_table`, `entity_id`, `price_set_id`) VALUES
('civicrm_contribution_page', @contrib_page_id, @price_set_id_contribution);

-- create pcp block for contribution page
select @supporter_profile_id := id from civicrm_uf_group where name = 'Pcp_Supporter_Profile';
INSERT INTO `civicrm_pcp_block` (`entity_table`, `entity_id`, `target_entity_type`, `target_entity_id`, `supporter_profile_id`, `is_approval_needed`, `is_tellfriend_enabled`, `tellfriend_limit`, `link_text`, `is_active`, `notify_email`) VALUES
('civicrm_event', @event_id, 'contribute', @contrib_page_id, @supporter_profile_id, 1, 1, 5, 'Promote this donation with a personal campaign page', 1, 'deepak@vedaconsulting.co.uk');
SELECT @pcp_block_id := LAST_INSERT_ID();

-- create contact1
SELECT @org_name := CONCAT('LLR Team', " ", CEIL(RAND()*100));

INSERT INTO `civicrm_contact` (`contact_type`, `contact_sub_type`, `do_not_email`, `do_not_phone`, `do_not_mail`, `do_not_sms`, `do_not_trade`, `is_opt_out`, `legal_identifier`, `external_identifier`, `sort_name`, `display_name`, `nick_name`, `legal_name`, `image_URL`, `preferred_communication_method`, `preferred_language`, `preferred_mail_format`, `hash`, `api_key`, `source`, `first_name`, `middle_name`, `last_name`, `prefix_id`, `suffix_id`, `formal_title`, `communication_style_id`, `email_greeting_id`, `email_greeting_custom`, `email_greeting_display`, `postal_greeting_id`, `postal_greeting_custom`, `postal_greeting_display`, `addressee_id`, `addressee_custom`, `addressee_display`, `job_title`, `gender_id`, `birth_date`, `is_deceased`, `deceased_date`, `household_name`, `primary_contact_id`, `organization_name`, `sic_code`, `user_unique_id`, `employer_id`, `is_deleted`) VALUES
('Organization', @contact_type_team, 0, 0, 0, 0, 0, 0, NULL, NULL, @org_name, @org_name, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @org_name, NULL, NULL, NULL, 0, NULL, NULL, NULL, @org_name, NULL, NULL, NULL, 0);
SELECT @contact_id_lteam := LAST_INSERT_ID();

-- pcp page for contact1
INSERT INTO `civicrm_pcp` (`contact_id`, `status_id`, `title`, `intro_text`, `page_text`, `donate_link_text`, `page_id`, `page_type`, `pcp_block_id`, `is_thermometer`, `is_honor_roll`, `goal_amount`, `currency`, `is_active`) VALUES
(@contact_id_lteam, 2, 'LLR Team PCP', 'LLR PCP Welcome message', 'This campaign is really important for PCP project to be successful. ', 'Donate Plz', @event_id, 'event', @pcp_block_id, 1, 1, 50000.00, 'USD', 1);
SELECT @pcp_id_llr := LAST_INSERT_ID();

-- create contact2
-- @fn = chris  @ln = morley CEIL(RAND()*100)
SELECT @fn := 'Chris';
SELECT @ln := CONCAT('Morley', " ", CEIL(RAND()*100));

INSERT INTO `civicrm_contact` (`contact_type`, `contact_sub_type`, `do_not_email`, `do_not_phone`, `do_not_mail`, `do_not_sms`, `do_not_trade`, `is_opt_out`, `legal_identifier`, `external_identifier`, `sort_name`, `display_name`, `nick_name`, `legal_name`, `image_URL`, `preferred_communication_method`, `preferred_language`, `preferred_mail_format`, `api_key`, `source`, `first_name`, `middle_name`, `last_name`, `prefix_id`, `suffix_id`, `formal_title`, `communication_style_id`, `email_greeting_id`, `email_greeting_custom`, `email_greeting_display`, `postal_greeting_id`, `postal_greeting_custom`, `postal_greeting_display`, `addressee_id`, `addressee_custom`, `addressee_display`, `job_title`, `gender_id`, `birth_date`, `is_deceased`, `deceased_date`, `household_name`, `primary_contact_id`, `organization_name`, `sic_code`, `user_unique_id`, `employer_id`, `is_deleted`) VALUES
('Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, CONCAT(@ln, ", ", @fn), CONCAT(@fn, " ", @ln), NULL, NULL, NULL, NULL, 'en_US', 'Both', NULL, NULL, @fn, NULL, @ln, NULL, NULL, NULL, NULL, 1, NULL, 'Dear chris', 1, NULL, 'Dear chris', 1, NULL, CONCAT(@fn, " ", @ln), NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);
SELECT @contact_id_chris := LAST_INSERT_ID();

-- pcp page for contact2
INSERT INTO `civicrm_pcp` (`contact_id`, `status_id`, `title`, `intro_text`, `page_text`, `donate_link_text`, `page_id`, `page_type`, `pcp_block_id`, `is_thermometer`, `is_honor_roll`, `goal_amount`, `currency`, `is_active`) VALUES
(@contact_id_chris, 2, 'Chris PCP', 'Chris PCP Welcome message', 'This campaign is really important for PCP project to be successful. ', 'Join Us', @event_id, 'event', @pcp_block_id, 1, 1, 25000.00, 'USD', 1);
SELECT @pcp_id_chris := LAST_INSERT_ID();

-- relationship type
SELECT @rel_type_a_b := 'Team Member of';
SELECT @rel_type_b_a := 'Team Member is';
INSERT INTO `civicrm_relationship_type` (`name_a_b`, `label_a_b`, `name_b_a`, `label_b_a`, `description`, `contact_type_a`, `contact_type_b`, `contact_sub_type_a`, `contact_sub_type_b`, `is_reserved`, `is_active`) VALUES
( @rel_type_a_b, @rel_type_a_b, @rel_type_b_a, @rel_type_b_a, 'Team Member relationship.', 'Individual', 'Organization', NULL, 'Team', 0, 1) ON DUPLICATE KEY UPDATE `name_a_b` = VALUES ( `name_a_b` ), `name_b_a` = VALUES ( `name_b_a` ), `label_a_b` = VALUES ( `label_a_b` ), label_b_a = VALUES ( `label_b_a` );
SELECT @relationship_type_id := id FROM civicrm_relationship_type WHERE name_a_b = @rel_type_a_b ;

-- relationship
INSERT INTO `civicrm_relationship` (`contact_id_a`, `contact_id_b`, `relationship_type_id`, `start_date`, `end_date`, `is_active`, `description`, `is_permission_a_b`, `is_permission_b_a`, `case_id`) VALUES
(@contact_id_chris, @contact_id_lteam, @relationship_type_id, NULL, NULL, 1, NULL, 1, 0, NULL) ON DUPLICATE KEY UPDATE `contact_id_a` = VALUES (`contact_id_a`), `contact_id_b` = VALUES (`contact_id_b`), `relationship_type_id` = VALUES (`relationship_type_id`), `is_active` = VALUES (`is_active`), `is_permission_a_b` = VALUES (`is_permission_a_b`);

-- ############################## Donations and Soft Credits for PCP Pages #####################################
-- create donor contact1
SELECT @fn := 'Che';
SELECT @ln := CONCAT('Molava', " ", CEIL(RAND()*100));

INSERT INTO `civicrm_contact` (`contact_type`, `contact_sub_type`, `do_not_email`, `do_not_phone`, `do_not_mail`, `do_not_sms`, `do_not_trade`, `is_opt_out`, `legal_identifier`, `external_identifier`, `sort_name`, `display_name`, `nick_name`, `legal_name`, `image_URL`, `preferred_communication_method`, `preferred_language`, `preferred_mail_format`, `api_key`, `source`, `first_name`, `middle_name`, `last_name`, `prefix_id`, `suffix_id`, `formal_title`, `communication_style_id`, `email_greeting_id`, `email_greeting_custom`, `email_greeting_display`, `postal_greeting_id`, `postal_greeting_custom`, `postal_greeting_display`, `addressee_id`, `addressee_custom`, `addressee_display`, `job_title`, `gender_id`, `birth_date`, `is_deceased`, `deceased_date`, `household_name`, `primary_contact_id`, `organization_name`, `sic_code`, `user_unique_id`, `employer_id`, `is_deleted`) VALUES
('Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, CONCAT(@ln, ", ", @fn), CONCAT(@fn, " ", @ln), NULL, NULL, NULL, NULL, 'en_US', 'Both', NULL, NULL, @fn, NULL, @ln, NULL, NULL, NULL, NULL, 1, NULL, 'Dear chris', 1, NULL, 'Dear chris', 1, NULL, CONCAT(@fn, " ", @ln), NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);
SELECT @donor_contact_id := LAST_INSERT_ID();

-- donation record
INSERT INTO `civicrm_contribution` (`contact_id`, `financial_type_id`, `contribution_page_id`, `payment_instrument_id`, `receive_date`, `non_deductible_amount`, `total_amount`, `fee_amount`, `net_amount`, `trxn_id`, `invoice_id`, `currency`, `cancel_date`, `cancel_reason`, `receipt_date`, `thankyou_date`, `source`, `amount_level`, `contribution_recur_id`, `is_test`, `is_pay_later`, `contribution_status_id`, `address_id`, `check_number`, `campaign_id`) VALUES
(@donor_contact_id, 1, @contrib_page_id, 1, '2015-02-19 13:21:07', NULL, 500.00, NULL, 500.00, 'live_00000001', '1771914bfb7d365b23dc9623f64b8545', 'USD', NULL, '0', '2015-02-19 13:21:07', NULL, 'Online Contribution: Chris PCP', 'Medium', NULL, 0, 0, 1, NULL, NULL, NULL);
SELECT @contrib_id := LAST_INSERT_ID();

-- soft credit to chris
select @soft_credit_type_id := value from civicrm_option_value where name = 'pcp' AND option_group_id = (select id from civicrm_option_group where name = 'soft_credit_type');
INSERT INTO `civicrm_contribution_soft` (`contribution_id`, `contact_id`, `amount`, `currency`, `pcp_id`, `pcp_display_in_roll`, `pcp_roll_nickname`, `pcp_personal_note`, `soft_credit_type_id`) VALUES
(@contrib_id, @contact_id_chris, 500.00, 'USD', @pcp_id_chris, 1, 'Che', 'Ches personal note. Don''t read it please.', @soft_credit_type_id);

-- soft credit to team
INSERT INTO `civicrm_contribution_soft` (`contribution_id`, `contact_id`, `amount`, `currency`, `pcp_id`, `pcp_display_in_roll`, `pcp_roll_nickname`, `pcp_personal_note`, `soft_credit_type_id`) VALUES
(@contrib_id, @contact_id_lteam, 500.00, 'USD', @pcp_id_chris, 1, 'LLR Team', 'LLR Team personal note. Don''t read it please.', @soft_credit_type_id);

-- create donor contact2
SELECT @fn := 'James';
SELECT @ln := CONCAT('South', " ", CEIL(RAND()*100));

INSERT INTO `civicrm_contact` (`contact_type`, `contact_sub_type`, `do_not_email`, `do_not_phone`, `do_not_mail`, `do_not_sms`, `do_not_trade`, `is_opt_out`, `legal_identifier`, `external_identifier`, `sort_name`, `display_name`, `nick_name`, `legal_name`, `image_URL`, `preferred_communication_method`, `preferred_language`, `preferred_mail_format`, `api_key`, `source`, `first_name`, `middle_name`, `last_name`, `prefix_id`, `suffix_id`, `formal_title`, `communication_style_id`, `email_greeting_id`, `email_greeting_custom`, `email_greeting_display`, `postal_greeting_id`, `postal_greeting_custom`, `postal_greeting_display`, `addressee_id`, `addressee_custom`, `addressee_display`, `job_title`, `gender_id`, `birth_date`, `is_deceased`, `deceased_date`, `household_name`, `primary_contact_id`, `organization_name`, `sic_code`, `user_unique_id`, `employer_id`, `is_deleted`) VALUES
('Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, CONCAT(@ln, ", ", @fn), CONCAT(@fn, " ", @ln), NULL, NULL, NULL, NULL, 'en_US', 'Both', NULL, NULL, @fn, NULL, @ln, NULL, NULL, NULL, NULL, 1, NULL, 'Dear chris', 1, NULL, 'Dear chris', 1, NULL, CONCAT(@fn, " ", @ln), NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);
SELECT @donor_contact_id := LAST_INSERT_ID();

-- donation record
INSERT INTO `civicrm_contribution` (`contact_id`, `financial_type_id`, `contribution_page_id`, `payment_instrument_id`, `receive_date`, `non_deductible_amount`, `total_amount`, `fee_amount`, `net_amount`, `trxn_id`, `invoice_id`, `currency`, `cancel_date`, `cancel_reason`, `receipt_date`, `thankyou_date`, `source`, `amount_level`, `contribution_recur_id`, `is_test`, `is_pay_later`, `contribution_status_id`, `address_id`, `check_number`, `campaign_id`) VALUES
(@donor_contact_id, 1, @contrib_page_id, 1, '2015-02-19 13:21:07', NULL, 1000.00, NULL, 1000.00, 'live_00000002', '1771914bfb7d365b23dc9623f64b8546', 'USD', NULL, '0', '2015-02-19 13:21:07', NULL, 'Online Contribution: Chris PCP', 'Medium', NULL, 0, 0, 1, NULL, NULL, NULL);
SELECT @contrib_id := LAST_INSERT_ID();

-- soft credit to chris
INSERT INTO `civicrm_contribution_soft` (`contribution_id`, `contact_id`, `amount`, `currency`, `pcp_id`, `pcp_display_in_roll`, `pcp_roll_nickname`, `pcp_personal_note`, `soft_credit_type_id`) VALUES
(@contrib_id, @contact_id_chris, 1000.00, 'USD', @pcp_id_chris, 1, 'Che', 'Ches personal note. Don''t read it please.', @soft_credit_type_id);

-- soft credit to team
INSERT INTO `civicrm_contribution_soft` (`contribution_id`, `contact_id`, `amount`, `currency`, `pcp_id`, `pcp_display_in_roll`, `pcp_roll_nickname`, `pcp_personal_note`, `soft_credit_type_id`) VALUES
(@contrib_id, @contact_id_lteam, 1000.00, 'USD', @pcp_id_chris, 1, 'LLR Team', 'LLR Team personal note. Don''t read it please.', @soft_credit_type_id);

-- set custom value 
-- pcp type contact
SELECT @fn := 'zombie';
SELECT @ln := CONCAT('gone', " ", CEIL(RAND()*100));

INSERT INTO `civicrm_contact` (`contact_type`, `contact_sub_type`, `do_not_email`, `do_not_phone`, `do_not_mail`, `do_not_sms`, `do_not_trade`, `is_opt_out`, `legal_identifier`, `external_identifier`, `sort_name`, `display_name`, `nick_name`, `legal_name`, `image_URL`, `preferred_communication_method`, `preferred_language`, `preferred_mail_format`, `api_key`, `source`, `first_name`, `middle_name`, `last_name`, `prefix_id`, `suffix_id`, `formal_title`, `communication_style_id`, `email_greeting_id`, `email_greeting_custom`, `email_greeting_display`, `postal_greeting_id`, `postal_greeting_custom`, `postal_greeting_display`, `addressee_id`, `addressee_custom`, `addressee_display`, `job_title`, `gender_id`, `birth_date`, `is_deceased`, `deceased_date`, `household_name`, `primary_contact_id`, `organization_name`, `sic_code`, `user_unique_id`, `employer_id`, `is_deleted`) VALUES
('Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, CONCAT(@ln, ", ", @fn), CONCAT(@fn, " ", @ln), NULL, NULL, NULL, NULL, 'en_US', 'Both', NULL, NULL, @fn, NULL, @ln, NULL, NULL, NULL, NULL, 1, NULL, 'Dear Zombie', 1, NULL, 'Dear Zombie', 1, NULL, CONCAT(@fn, " ", @ln), NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);
SELECT @pcp_type_contact_id := LAST_INSERT_ID();

-- custom set 
INSERT INTO `civicrm_value_pcp_custom_set`(`entity_id`, `team_pcp_id`, `tribute`, `tribute_contact_id`) VALUES (@pcp_id_chris, @pcp_id_llr, 'in_memory', @pcp_type_contact_id);


-- set custom value for llr pcp 
SELECT @fn := 'Santa';
SELECT @ln := CONCAT('Claus', " ", CEIL(RAND()*100));

INSERT INTO `civicrm_contact` (`contact_type`, `contact_sub_type`, `do_not_email`, `do_not_phone`, `do_not_mail`, `do_not_sms`, `do_not_trade`, `is_opt_out`, `legal_identifier`, `external_identifier`, `sort_name`, `display_name`, `nick_name`, `legal_name`, `image_URL`, `preferred_communication_method`, `preferred_language`, `preferred_mail_format`, `api_key`, `source`, `first_name`, `middle_name`, `last_name`, `prefix_id`, `suffix_id`, `formal_title`, `communication_style_id`, `email_greeting_id`, `email_greeting_custom`, `email_greeting_display`, `postal_greeting_id`, `postal_greeting_custom`, `postal_greeting_display`, `addressee_id`, `addressee_custom`, `addressee_display`, `job_title`, `gender_id`, `birth_date`, `is_deceased`, `deceased_date`, `household_name`, `primary_contact_id`, `organization_name`, `sic_code`, `user_unique_id`, `employer_id`, `is_deleted`) VALUES
('Individual', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, CONCAT(@ln, ", ", @fn), CONCAT(@fn, " ", @ln), NULL, NULL, NULL, NULL, 'en_US', 'Both', NULL, NULL, @fn, NULL, @ln, NULL, NULL, NULL, NULL, 1, NULL, 'Dear Sant', 1, NULL, 'Dear Santa', 1, NULL, CONCAT(@fn, " ", @ln), NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);
SELECT @pcp_type_contact_id_2 := LAST_INSERT_ID();

-- custom set 
INSERT INTO `civicrm_value_pcp_custom_set`(`entity_id`, `team_pcp_id`, `tribute`, `tribute_contact_id`) VALUES (@pcp_id_llr, NULL, 'in_celebration', @pcp_type_contact_id_2);

-- ############################## Sample data changes Mv:12/03/2015 #####################################
-- set Foreign key for team pcp id 
ALTER TABLE `civicrm_value_pcp_custom_set`
  MODIFY `team_pcp_id` int(10) unsigned DEFAULT NULL,
  ADD CONSTRAINT `FK_civicrm_value_pcp_custom_set_team_pcp_id` FOREIGN KEY (`team_pcp_id`) REFERENCES `civicrm_pcp` (`id`) ON DELETE SET NULL;

-- create sample teams
SELECT @veda_name := CONCAT('Veda Team', " ", CEIL(RAND()*100));
SELECT @test_name := CONCAT('Test Team', " ", CEIL(RAND()*100));
SELECT @pcp_name  := CONCAT('Pcp Team', " ", CEIL(RAND()*100));
SELECT @sample_name  := CONCAT('Sample Team', " ", CEIL(RAND()*100));

-- create sample contact1
INSERT INTO `civicrm_contact` (`contact_type`, `contact_sub_type`, `do_not_email`, `do_not_phone`, `do_not_mail`, `do_not_sms`, `do_not_trade`, `is_opt_out`, `legal_identifier`, `external_identifier`, `sort_name`, `display_name`, `nick_name`, `legal_name`, `image_URL`, `preferred_communication_method`, `preferred_language`, `preferred_mail_format`, `hash`, `api_key`, `source`, `first_name`, `middle_name`, `last_name`, `prefix_id`, `suffix_id`, `formal_title`, `communication_style_id`, `email_greeting_id`, `email_greeting_custom`, `email_greeting_display`, `postal_greeting_id`, `postal_greeting_custom`, `postal_greeting_display`, `addressee_id`, `addressee_custom`, `addressee_display`, `job_title`, `gender_id`, `birth_date`, `is_deceased`, `deceased_date`, `household_name`, `primary_contact_id`, `organization_name`, `sic_code`, `user_unique_id`, `employer_id`, `is_deleted`) VALUES
('Organization', @contact_type_team, 0, 0, 0, 0, 0, 0, NULL, NULL, @veda_name, @veda_name, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @veda_name, NULL, NULL, NULL, 0, NULL, NULL, NULL, @veda_name, NULL, NULL, NULL, 0);
SELECT @contact_id_team_1 := LAST_INSERT_ID();

-- pcp page for contact1
INSERT INTO `civicrm_pcp` (`contact_id`, `status_id`, `title`, `intro_text`, `page_text`, `donate_link_text`, `page_id`, `page_type`, `pcp_block_id`, `is_thermometer`, `is_honor_roll`, `goal_amount`, `currency`, `is_active`) VALUES
(@contact_id_team_1, 2, 'Veda Team PCP', 'Veda PCP Welcome message', 'This campaign is really important for PCP project to be successful. ', 'Donate Plz', @event_id, 'event', @pcp_block_id, 1, 1, 50000.00, 'USD', 1);

-- create sample contact2
INSERT INTO `civicrm_contact` (`contact_type`, `contact_sub_type`, `do_not_email`, `do_not_phone`, `do_not_mail`, `do_not_sms`, `do_not_trade`, `is_opt_out`, `legal_identifier`, `external_identifier`, `sort_name`, `display_name`, `nick_name`, `legal_name`, `image_URL`, `preferred_communication_method`, `preferred_language`, `preferred_mail_format`, `hash`, `api_key`, `source`, `first_name`, `middle_name`, `last_name`, `prefix_id`, `suffix_id`, `formal_title`, `communication_style_id`, `email_greeting_id`, `email_greeting_custom`, `email_greeting_display`, `postal_greeting_id`, `postal_greeting_custom`, `postal_greeting_display`, `addressee_id`, `addressee_custom`, `addressee_display`, `job_title`, `gender_id`, `birth_date`, `is_deceased`, `deceased_date`, `household_name`, `primary_contact_id`, `organization_name`, `sic_code`, `user_unique_id`, `employer_id`, `is_deleted`) VALUES
('Organization', @contact_type_team, 0, 0, 0, 0, 0, 0, NULL, NULL, @test_name, @test_name, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @test_name, NULL, NULL, NULL, 0, NULL, NULL, NULL, @test_name, NULL, NULL, NULL, 0);
SELECT @contact_id_team_2 := LAST_INSERT_ID();

-- pcp page for contact2
INSERT INTO `civicrm_pcp` (`contact_id`, `status_id`, `title`, `intro_text`, `page_text`, `donate_link_text`, `page_id`, `page_type`, `pcp_block_id`, `is_thermometer`, `is_honor_roll`, `goal_amount`, `currency`, `is_active`) VALUES
(@contact_id_team_2, 2, 'Test Team PCP', 'Test PCP Welcome message', 'This campaign is really important for PCP project to be successful. ', 'Donate Plz', @event_id, 'event', @pcp_block_id, 1, 1, 50000.00, 'USD', 1);

-- create sample contact3
INSERT INTO `civicrm_contact` (`contact_type`, `contact_sub_type`, `do_not_email`, `do_not_phone`, `do_not_mail`, `do_not_sms`, `do_not_trade`, `is_opt_out`, `legal_identifier`, `external_identifier`, `sort_name`, `display_name`, `nick_name`, `legal_name`, `image_URL`, `preferred_communication_method`, `preferred_language`, `preferred_mail_format`, `hash`, `api_key`, `source`, `first_name`, `middle_name`, `last_name`, `prefix_id`, `suffix_id`, `formal_title`, `communication_style_id`, `email_greeting_id`, `email_greeting_custom`, `email_greeting_display`, `postal_greeting_id`, `postal_greeting_custom`, `postal_greeting_display`, `addressee_id`, `addressee_custom`, `addressee_display`, `job_title`, `gender_id`, `birth_date`, `is_deceased`, `deceased_date`, `household_name`, `primary_contact_id`, `organization_name`, `sic_code`, `user_unique_id`, `employer_id`, `is_deleted`) VALUES
('Organization', @contact_type_team, 0, 0, 0, 0, 0, 0, NULL, NULL, @pcp_name, @pcp_name, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @pcp_name, NULL, NULL, NULL, 0, NULL, NULL, NULL, @pcp_name, NULL, NULL, NULL, 0);
SELECT @contact_id_team_3 := LAST_INSERT_ID();

-- pcp page for contact3
INSERT INTO `civicrm_pcp` (`contact_id`, `status_id`, `title`, `intro_text`, `page_text`, `donate_link_text`, `page_id`, `page_type`, `pcp_block_id`, `is_thermometer`, `is_honor_roll`, `goal_amount`, `currency`, `is_active`) VALUES
(@contact_id_team_3, 2, 'Pcp Team PCP', 'PCP Welcome message', 'This campaign is really important for PCP project to be successful. ', 'Donate Plz', @event_id, 'event', @pcp_block_id, 1, 1, 50000.00, 'USD', 1);

-- create sample contact4
INSERT INTO `civicrm_contact` (`contact_type`, `contact_sub_type`, `do_not_email`, `do_not_phone`, `do_not_mail`, `do_not_sms`, `do_not_trade`, `is_opt_out`, `legal_identifier`, `external_identifier`, `sort_name`, `display_name`, `nick_name`, `legal_name`, `image_URL`, `preferred_communication_method`, `preferred_language`, `preferred_mail_format`, `hash`, `api_key`, `source`, `first_name`, `middle_name`, `last_name`, `prefix_id`, `suffix_id`, `formal_title`, `communication_style_id`, `email_greeting_id`, `email_greeting_custom`, `email_greeting_display`, `postal_greeting_id`, `postal_greeting_custom`, `postal_greeting_display`, `addressee_id`, `addressee_custom`, `addressee_display`, `job_title`, `gender_id`, `birth_date`, `is_deceased`, `deceased_date`, `household_name`, `primary_contact_id`, `organization_name`, `sic_code`, `user_unique_id`, `employer_id`, `is_deleted`) VALUES
('Organization', @contact_type_team, 0, 0, 0, 0, 0, 0, NULL, NULL, @sample_name, @sample_name, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @sample_name, NULL, NULL, NULL, 0, NULL, NULL, NULL, @sample_name, NULL, NULL, NULL, 0);
SELECT @contact_id_team_4 := LAST_INSERT_ID();

-- pcp page for contact4
INSERT INTO `civicrm_pcp` (`contact_id`, `status_id`, `title`, `intro_text`, `page_text`, `donate_link_text`, `page_id`, `page_type`, `pcp_block_id`, `is_thermometer`, `is_honor_roll`, `goal_amount`, `currency`, `is_active`) VALUES
(@contact_id_team_4, 2, 'Sample Team PCP', 'Sample PCP Welcome message', 'This campaign is really important for PCP project to be successful. ', 'Donate Plz', @event_id, 'event', @pcp_block_id, 1, 1, 50000.00, 'USD', 1);

SELECT @branch_1 := CONCAT('Test Branch', " ", CEIL(RAND()*100));
SELECT @branch_2 := CONCAT('Test Branch', " ", CEIL(RAND()*100));
SELECT @branch_3 := CONCAT('Test Branch', " ", CEIL(RAND()*100));
SELECT @branch_4 := CONCAT('Test Branch', " ", CEIL(RAND()*100));

SELECT @partner_1 := CONCAT('Test Partner', " ", CEIL(RAND()*100));
SELECT @partner_2 := CONCAT('Test Partner', " ", CEIL(RAND()*100));
SELECT @partner_3 := CONCAT('Test Partner', " ", CEIL(RAND()*100));
SELECT @partner_4 := CONCAT('Test Partner', " ", CEIL(RAND()*100));

SELECT @inmem_1 := CONCAT('Sample In Mem', " ", CEIL(RAND()*100));
SELECT @inmem_2 := CONCAT('Sample In Mem', " ", CEIL(RAND()*100));
SELECT @inmem_3 := CONCAT('Sample In Mem', " ", CEIL(RAND()*100));
SELECT @inmem_4 := CONCAT('Sample In Mem', " ", CEIL(RAND()*100));

SELECT @inceleb_1 := CONCAT('Sample In celeb', " ", CEIL(RAND()*100));
SELECT @inceleb_2 := CONCAT('Sample In celeb', " ", CEIL(RAND()*100));
SELECT @inceleb_3 := CONCAT('Sample In celeb', " ", CEIL(RAND()*100));
SELECT @inceleb_4 := CONCAT('Sample In celeb', " ", CEIL(RAND()*100));

-- sample contact for branch, corporate patner, in memory and in celebration
INSERT INTO `civicrm_contact` (`contact_type`, `contact_sub_type`, `do_not_email`, `do_not_phone`, `do_not_mail`, `do_not_sms`, `do_not_trade`, `is_opt_out`, `legal_identifier`, `external_identifier`, `sort_name`, `display_name`, `nick_name`, `legal_name`, `image_URL`, `preferred_communication_method`, `preferred_language`, `preferred_mail_format`, `hash`, `api_key`, `source`, `first_name`, `middle_name`, `last_name`, `prefix_id`, `suffix_id`, `formal_title`, `communication_style_id`, `email_greeting_id`, `email_greeting_custom`, `email_greeting_display`, `postal_greeting_id`, `postal_greeting_custom`, `postal_greeting_display`, `addressee_id`, `addressee_custom`, `addressee_display`, `job_title`, `gender_id`, `birth_date`, `is_deceased`, `deceased_date`, `household_name`, `primary_contact_id`, `organization_name`, `sic_code`, `user_unique_id`, `employer_id`, `is_deleted`) VALUES
('Organization', @contact_type_branch, 0, 0, 0, 0, 0, 0, NULL, NULL, @branch_1, @branch_1, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @branch_1, NULL, NULL, NULL, 0, NULL, NULL, NULL, @branch_1, NULL, NULL, NULL, 0),
('Organization', @contact_type_branch, 0, 0, 0, 0, 0, 0, NULL, NULL, @branch_2, @branch_2, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @branch_2, NULL, NULL, NULL, 0, NULL, NULL, NULL, @branch_2, NULL, NULL, NULL, 0),
('Organization', @contact_type_branch, 0, 0, 0, 0, 0, 0, NULL, NULL, @branch_3, @branch_3, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @branch_3, NULL, NULL, NULL, 0, NULL, NULL, NULL, @branch_3, NULL, NULL, NULL, 0),
('Organization', @contact_type_branch, 0, 0, 0, 0, 0, 0, NULL, NULL, @branch_4, @branch_4, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @branch_4, NULL, NULL, NULL, 0, NULL, NULL, NULL, @branch_4, NULL, NULL, NULL, 0),
('Organization', @contact_type_partner, 0, 0, 0, 0, 0, 0, NULL, NULL, @partner_1, @partner_1, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @partner_1, NULL, NULL, NULL, 0, NULL, NULL, NULL, @partner_1, NULL, NULL, NULL, 0),
('Organization', @contact_type_partner, 0, 0, 0, 0, 0, 0, NULL, NULL, @partner_2, @partner_2, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @partner_2, NULL, NULL, NULL, 0, NULL, NULL, NULL, @partner_2, NULL, NULL, NULL, 0),
('Organization', @contact_type_partner, 0, 0, 0, 0, 0, 0, NULL, NULL, @partner_3, @partner_3, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @partner_3, NULL, NULL, NULL, 0, NULL, NULL, NULL, @partner_3, NULL, NULL, NULL, 0),
('Organization', @contact_type_partner, 0, 0, 0, 0, 0, 0, NULL, NULL, @partner_4, @partner_4, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @partner_4, NULL, NULL, NULL, 0, NULL, NULL, NULL, @partner_4, NULL, NULL, NULL, 0),
('Organization', @contact_type_in_mem, 0, 0, 0, 0, 0, 0, NULL, NULL, @inmem_1, @inmem_1, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @inmem_1, NULL, NULL, NULL, 0, NULL, NULL, NULL, @inmem_1, NULL, NULL, NULL, 0),
('Organization', @contact_type_in_mem, 0, 0, 0, 0, 0, 0, NULL, NULL, @inmem_2, @inmem_2, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @inmem_2, NULL, NULL, NULL, 0, NULL, NULL, NULL, @inmem_2, NULL, NULL, NULL, 0),
('Organization', @contact_type_in_mem, 0, 0, 0, 0, 0, 0, NULL, NULL, @inmem_3, @inmem_3, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @inmem_3, NULL, NULL, NULL, 0, NULL, NULL, NULL, @inmem_3, NULL, NULL, NULL, 0),
('Organization', @contact_type_in_mem, 0, 0, 0, 0, 0, 0, NULL, NULL, @inmem_4, @inmem_4, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @inmem_4, NULL, NULL, NULL, 0, NULL, NULL, NULL, @inmem_4, NULL, NULL, NULL, 0),
('Organization', @contact_type_in_celb, 0, 0, 0, 0, 0, 0, NULL, NULL, @inceleb_1, @inceleb_1, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @inceleb_1, NULL, NULL, NULL, 0, NULL, NULL, NULL, @inceleb_1, NULL, NULL, NULL, 0),
('Organization', @contact_type_in_celb, 0, 0, 0, 0, 0, 0, NULL, NULL, @inceleb_2, @inceleb_2, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @inceleb_2, NULL, NULL, NULL, 0, NULL, NULL, NULL, @inceleb_2, NULL, NULL, NULL, 0),
('Organization', @contact_type_in_celb, 0, 0, 0, 0, 0, 0, NULL, NULL, @inceleb_3, @inceleb_3, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @inceleb_3, NULL, NULL, NULL, 0, NULL, NULL, NULL, @inceleb_3, NULL, NULL, NULL, 0),
('Organization', @contact_type_in_celb, 0, 0, 0, 0, 0, 0, NULL, NULL, @inceleb_4, @inceleb_4, NULL, NULL, NULL, NULL, NULL, 'Both', '3102204687', NULL, 'Sample Data', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, NULL, @inceleb_4, NULL, NULL, NULL, 0, NULL, NULL, NULL, @inceleb_4, NULL, NULL, NULL, 0);
