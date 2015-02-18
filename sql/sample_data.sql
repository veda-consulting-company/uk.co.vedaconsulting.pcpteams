
-- insert event
-- ids, all id columns finan type id, 

SELECT @event_title := 'bikeathon mexico 2015';
SELECT @event_name  := 'bikeathon_mexico_2015';

-- price set
INSERT INTO `civicrm_price_set` (`domain_id`, `name`, `title`, `is_active`, `help_pre`, `help_post`, `javascript`, `extends`, `financial_type_id`, `is_quick_config`, `is_reserved`) VALUES
(NULL, @event_name, @event_title, 1, NULL, NULL, NULL, '1', NULL, 1, 0);
SELECT @price_set_id := LAST_INSERT_ID();

-- price field 
INSERT INTO `civicrm_price_field` (`price_set_id`, `name`, `label`, `html_type`, `is_enter_qty`, `help_pre`, `help_post`, `weight`, `is_display_amounts`, `options_per_line`, `is_active`, `is_required`, `active_on`, `expire_on`, `javascript`, `visibility_id`) VALUES
(@price_set_id, 'tournament_fees', 'Tournament Fees', 'Radio', 0, NULL, NULL, 1, 1, 1, 1, 1, NULL, NULL, NULL, 1);
SELECT @price_field_id := LAST_INSERT_ID();

INSERT INTO `civicrm_price_field_value` (`price_field_id`, `name`, `label`, `description`, `amount`, `count`, `max_value`, `weight`, `membership_type_id`, `membership_num_terms`, `is_default`, `is_active`, `financial_type_id`, `deductible_amount`) VALUES
(@price_field_id, 'tiny_tots__ages_5_8_', 'Tiny-tots (ages 5-8)', NULL, '800', NULL, NULL, 1, NULL, NULL, 1, 1, 4, 0.00),
(@price_field_id, 'junior_Stars__ages_9_12_', 'Junior Stars (ages 9-12)', NULL, '1000', NULL, NULL, 2, NULL, NULL, 0, 1, 4, 0.00),
(@price_field_id, 'super_Stars__ages_13_18_', 'Super Stars (ages 13-18)', NULL, '1500', NULL, NULL, 3, NULL, NULL, 0, 1, 4, 0.00);

-- loc
SELECT @loc_block_id := max(loc_block_id) from civicrm_event;

-- event : payment processor, camp-id = 1, 
INSERT INTO `civicrm_event` (`title`, `summary`, `description`, `event_type_id`, `participant_listing_id`, `is_public`, `start_date`, `end_date`, `is_online_registration`, `registration_link_text`, `registration_start_date`, `registration_end_date`, `max_participants`, `event_full_text`, `is_monetary`, `financial_type_id`, `payment_processor`, `is_map`, `is_active`, `fee_label`, `is_show_location`, `loc_block_id`, `default_role_id`, `intro_text`, `footer_text`, `confirm_title`, `confirm_text`, `confirm_footer_text`, `is_email_confirm`, `confirm_email_text`, `confirm_from_name`, `confirm_from_email`, `cc_confirm`, `bcc_confirm`, `default_fee_id`, `default_discount_fee_id`, `thankyou_title`, `thankyou_text`, `thankyou_footer_text`, `is_pay_later`, `pay_later_text`, `pay_later_receipt`, `is_partial_payment`, `initial_amount_label`, `initial_amount_help_text`, `min_initial_amount`, `is_multiple_registrations`, `allow_same_participant_emails`, `has_waitlist`, `requires_approval`, `expiration_time`, `waitlist_text`, `approval_req_text`, `is_template`, `template_title`, `created_id`, `created_date`, `currency`, `campaign_id`, `is_share`, `is_confirm_enabled`, `parent_event_id`, `slot_label_id`, `dedupe_rule_group_id`) VALUES
(@event_title, 'Sign up your team to participate in this fun tournament which benefits several Rain-forest protection groups in maxico.', '<p>This is a FYSA Sanctioned Tournament, which is open to all USSF/FIFA affiliated organizations for boys and girls in age groups: U9-U10 (6v6), U11-U12 (8v8), and U13-U17 (Full Sided).</p>', 3, 1, 1, '2015-10-05 07:00:00', '2015-10-07 17:00:00', 1, 'Register Now', NULL, NULL, 500, 'Sorry! All available team slots for this tournament have been filled. Contact Jill Futbol for information about the waiting list and next years event.', 1, 4, '1', 0, 1, 'Tournament Fees', 1, 3, 1, 'Complete the form below to register your team for this year''s tournament.', '<em>A Soccer Youth Event</em>', 'Review and Confirm Your Registration Information', '', '<em>A Soccer Youth Event</em>', 1, 'Contact our Tournament Director for eligibility details.', 'Tournament Director', 'tournament@example.org', '', NULL, NULL, NULL, 'Thanks for Your Support!', '<p>Thank you for your support. Your participation will help save thousands of acres of rainforest.</p>', '<p><a href=http://civicrm.org>Back to CiviCRM Home Page</a></p>', 0, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 'USD', NULL, 1, 1, NULL, NULL, NULL);

SELECT @event_id := LAST_INSERT_ID();

INSERT INTO `civicrm_price_set_entity` (`entity_table`, `entity_id`, `price_set_id`) VALUES
('civicrm_event', @event_id, @price_set_id);

INSERT INTO `civicrm_pcp_block` (`entity_table`, `entity_id`, `target_entity_type`, `target_entity_id`, `supporter_profile_id`, `is_approval_needed`, `is_tellfriend_enabled`, `tellfriend_limit`, `link_text`, `is_active`, `notify_email`) VALUES
('civicrm_event', @event_id, 'event', @event_id, 2, 1, 1, 5, 'Promote this event with a personal campaign page', 1, 'deepak@vedaconsulting.co.uk');



-
