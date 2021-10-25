
-- SQL TO CREATE A REDCAP DEMO PROJECT --
set @project_title = 'Single Survey';
-- Obtain default values --
set @institution = (select value from redcap_config where field_name = 'institution' limit 1);
set @site_org_type = (select value from redcap_config where field_name = 'site_org_type' limit 1);
set @grant_cite = (select value from redcap_config where field_name = 'grant_cite' limit 1);
set @project_contact_name = (select value from redcap_config where field_name = 'project_contact_name' limit 1);
set @project_contact_email = (select value from redcap_config where field_name = 'project_contact_email' limit 1);
set @headerlogo = (select value from redcap_config where field_name = 'headerlogo' limit 1);
set @auth_meth = (select value from redcap_config where field_name = 'auth_meth_global' limit 1);
-- Create project --
INSERT INTO `redcap_projects`
(project_name, app_title, status, count_project, auth_meth, creation_time, production_time, institution, site_org_type, grant_cite, project_contact_name, project_contact_email, headerlogo, surveys_enabled, auto_inc_set, display_project_logo_institution) VALUES
(concat('redcap_demo_',LEFT(sha1(rand()),6)), @project_title, 1, 0, @auth_meth, now(), now(), @institution, @site_org_type, @grant_cite, @project_contact_name, @project_contact_email, @headerlogo, 1, 1, 0);
set @project_id = LAST_INSERT_ID();
-- Create single arm --
INSERT INTO redcap_events_arms (project_id, arm_num, arm_name) VALUES (@project_id, 1, 'Arm 1');
set @arm_id = LAST_INSERT_ID();
-- Create single event --
INSERT INTO redcap_events_metadata (arm_id, day_offset, offset_min, offset_max, descrip) VALUES (@arm_id, 0, 0, 0, 'Event 1');
set @event_id = LAST_INSERT_ID();
-- Insert into redcap_surveys
INSERT INTO redcap_surveys (project_id, font_family, form_name, title, instructions, acknowledgement, question_by_section, question_auto_numbering, survey_enabled, save_and_return, logo, hide_title) VALUES
(@project_id, '16', 'survey', 'Example Survey', '&lt;p style=&quot;margin-top: 10px; margin-right: 0px; margin-bottom: 10px; margin-left: 0px; font-family: Arial, Verdana, Helvetica, sans-serif; font-size: 12px; text-align: left; line-height: 1.5em; max-width: 700px; clear: both; padding: 0px;&quot;&gt;These are your survey instructions that you would enter for your survey participants. You may put whatever text you like here, which may include information about the purpose of the survey, who is taking the survey, or how to take the survey.&lt;/p&gt;<br>&lt;p style=&quot;margin-top: 10px; margin-right: 0px; margin-bottom: 10px; margin-left: 0px; font-family: Arial, Verdana, Helvetica, sans-serif; font-size: 12px; text-align: left; line-height: 1.5em; max-width: 700px; clear: both; padding: 0px;&quot;&gt;Surveys can use a single survey link for all respondents, which can be posted on a webpage or emailed out from your email application of choice. &lt;strong&gt;By default, all survey responses are collected anonymously&lt;/strong&gt; (that is, unless your survey asks for name, email, or other identifying information). If you wish to track individuals who have taken your survey, you may upload a list of email addresses into a Participant List within REDCap, in which you can have REDCap send them an email invitation, which will track if they have taken the survey and when it was taken. This method still collects responses anonymously, but if you wish to identify an individual respondent\'s answers, you may do so by also providing an Identifier in your Participant List. Of course, in that case you may want to inform your respondents in your survey\'s instructions that their responses are not being collected anonymously and can thus be traced back to them.&lt;/p&gt;', '&lt;p&gt;&lt;strong&gt;Thank you for taking the survey.&lt;/strong&gt;&lt;/p&gt;<br>&lt;p&gt;Have a nice day!&lt;/p&gt;', 0, 0, 1, 1, NULL, 0);
-- Insert into redcap_metadata --
INSERT INTO redcap_metadata (project_id, field_name, field_phi, form_name, form_menu_description, field_order, field_units, element_preceding_header, element_type, element_label, element_enum, element_note, element_validation_type, element_validation_min, element_validation_max, element_validation_checktype, branching_logic, field_req, edoc_id, edoc_display_img, custom_alignment, stop_actions, question_num) VALUES
(@project_id, 'participant_id', NULL, 'survey', 'Example Survey', 1, NULL, NULL, 'text', 'Participant ID', NULL, NULL, NULL, NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL),
(@project_id, 'radio', NULL, 'survey', NULL, 2, NULL, 'Section 1 (This is a section header with descriptive text. It only provides informational text and is used to divide the survey into sections for organization. If the survey is set to be displayed as "one section per page", then these section headers will begin each new page of the survey.)', 'radio', 'You may create MULTIPLE CHOICE questions and set the answer choices for them. You can have as many answer choices as you need. This multiple choice question is rendered as RADIO buttons.', '1, Choice One \\n 2, Choice Two \\n 3, Choice Three \\n 4, Etc.', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL),
(@project_id, 'dropdown', NULL, 'survey', NULL, 3, NULL, NULL, 'select', 'You may also set multiple choice questions as DROP-DOWN MENUs.', '1, Choice One \\n 2, Choice Two \\n 3, Choice Three \\n 4, Etc.', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL),
(@project_id, 'textbox', NULL, 'survey', NULL, 4, NULL, NULL, 'text', 'This is a TEXT BOX, which allows respondents to enter a small amount of text. A Text Box can be validated, if needed, as a number, integer, phone number, email, or zipcode. If validated as a number or integer, you may also set the minimum and/or maximum allowable values.\n\nThis question has "number" validation set with a minimum of 1 and a maximum of 10. ', NULL, NULL, 'float', '1', '10', 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL),
(@project_id, 'ma', NULL, 'survey', NULL, 5, NULL, NULL, 'checkbox', 'This type of multiple choice question, known as CHECKBOXES, allows for more than one answer choice to be selected, whereas radio buttons and drop-downs only allow for one choice.', '1, Choice One \\n 2, Choice Two \\n 3, Choice Three \\n 4, Select as many as you like', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL),
(@project_id, 'yn', NULL, 'survey', NULL, 6, NULL, NULL, 'yesno', 'You can create YES-NO questions.<br><br>This question has vertical alignment of choices on the right.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL),
(@project_id, 'tf', NULL, 'survey', NULL, 7, NULL, NULL, 'truefalse', 'And you can also create TRUE-FALSE questions.<br><br>This question has horizontal alignment.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 'RH', NULL, NULL),
(@project_id, 'date_ymd', NULL, 'survey', NULL, 8, NULL, NULL, 'text', 'DATE questions are also an option. If you click the calendar icon on the right, a pop-up calendar will appear, thus allowing the respondent to easily select a date. Or it can be simply typed in.', NULL, NULL, 'date_ymd', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL),
(@project_id, 'file', NULL, 'survey', NULL, 9, NULL, NULL, 'file', 'The FILE UPLOAD question type allows respondents to upload any type of document to the survey that you may afterward download and open when viewing your survey results.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL),
(@project_id, 'slider', NULL, 'survey', NULL, 10, NULL, NULL, 'slider', 'A SLIDER is a question type that allows the respondent to choose an answer along a continuum. The respondent''s answer is saved as an integer between 0 (far left) and 100 (far right) with a step of 1.', 'You can provide labels above the slider | Middle label | Right-hand label', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL),
(@project_id, 'descriptive', NULL, 'survey', NULL, 11, NULL, NULL, 'descriptive', 'You may also use DESCRIPTIVE TEXT to provide informational text within a survey section. ', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL);

INSERT INTO redcap_metadata (project_id, field_name, field_phi, form_name, form_menu_description, field_order, field_units, element_preceding_header, element_type, element_label, element_enum, element_note, element_validation_type, element_validation_min, element_validation_max, element_validation_checktype, branching_logic, field_req, edoc_id, edoc_display_img, custom_alignment, stop_actions, question_num, grid_name, misc) VALUES
(@project_id, 'gym', NULL, 'survey', NULL, 11.1, NULL, 'Below is a matrix of checkbox fields. A matrix can also be displayed as radio button fields.', 'checkbox', 'Gym (Weight Training)', '0, Monday \\n 1, Tuesday \\n 2, Wednesday \\n 3, Thursday \\n 4, Friday', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, 'weekly_schedule', NULL),
(@project_id, 'aerobics', NULL, 'survey', NULL, 11.2, NULL, NULL, 'checkbox', 'Aerobics', '0, Monday \\n 1, Tuesday \\n 2, Wednesday \\n 3, Thursday \\n 4, Friday', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, 'weekly_schedule', NULL),
(@project_id, 'eat', NULL, 'survey', NULL, 11.3, NULL, NULL, 'checkbox', 'Eat Out (Dinner/Lunch)', '0, Monday \\n 1, Tuesday \\n 2, Wednesday \\n 3, Thursday \\n 4, Friday', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, 'weekly_schedule', NULL),
(@project_id, 'drink', NULL, 'survey', NULL, 11.4, NULL, NULL, 'checkbox', 'Drink (Alcoholic Beverages)', '0, Monday \\n 1, Tuesday \\n 2, Wednesday \\n 3, Thursday \\n 4, Friday', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, 'weekly_schedule', NULL);

INSERT INTO redcap_metadata (project_id, field_name, field_phi, form_name, form_menu_description, field_order, field_units, element_preceding_header, element_type, element_label, element_enum, element_note, element_validation_type, element_validation_min, element_validation_max, element_validation_checktype, branching_logic, field_req, edoc_id, edoc_display_img, custom_alignment, stop_actions, question_num) VALUES
(@project_id, 'radio_branch', NULL, 'survey', NULL, 12, NULL, 'ADVANCED FEATURES: The questions below will illustrate how some advanced survey features are used.', 'radio', 'BRANCHING LOGIC: The question immediately following this one is using branching logic, which means that the question will stay hidden until defined criteria are specified.\n\nFor example, the following question has been set NOT to appear until the respondent selects the second option to the right.  ', '1, This option does nothing. \\n 2, Clicking this option will trigger the branching logic to reveal the next question. \\n 3, This option also does nothing.', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL),
(@project_id, 'hidden_branch', NULL, 'survey', NULL, 13, NULL, NULL, 'text', 'HIDDEN QUESTION: This question will only appear when you select the second option of the question immediately above.', NULL, NULL, NULL, 'undefined', 'undefined', 'soft_typed', '[radio_branch] = "2"', 0, NULL, 0, NULL, NULL, NULL),
(@project_id, 'stop_actions', NULL, 'survey', NULL, 14, NULL, NULL, 'checkbox', 'STOP ACTIONS may be used with any multiple choice question. Stop actions can be applied to any (or all) answer choices. When that answer choice is selected by a respondent, their survey responses are then saved, and the survey is immediately ended.\n\nThe third option to the right has a stop action.', '1, This option does nothing. \\n 2, This option also does nothing. \\n 3, Click here to trigger the stop action and end the survey.', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, '3', NULL),
(@project_id, 'comment_box', NULL, 'survey', NULL, 15, NULL, NULL, 'textarea', 'If you need the respondent to enter a large amount of text, you may use a NOTES BOX.<br><br>This question has also been set as a REQUIRED QUESTION, so the respondent cannot fully submit the survey until this question has been answered. ANY question type can be set to be required.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 'LH', NULL, NULL),
(@project_id, 'survey_complete', NULL, 'survey', NULL, 16, NULL, 'Form Status', 'select', 'Complete?', '0, Incomplete \\n 1, Unverified \\n 2, Complete', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL);

INSERT INTO `redcap_projects_templates` (`project_id`, `title`, `description`, `enabled`)
	VALUES (@project_id,  @project_title,  'Single data collection instrument enabled as a survey, which contains questions to demonstrate all the different field types.',  '1');