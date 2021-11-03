
-- SQL TO CREATE A REDCAP DEMO PROJECT --
set @project_title = 'Field Embedding Example Project';


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
(project_name, app_title, status, count_project, auth_meth, creation_time, production_time, institution, site_org_type, grant_cite, project_contact_name, project_contact_email, headerlogo, display_project_logo_institution, auto_inc_set) VALUES
(concat('redcap_demo_',LEFT(sha1(rand()),6)), @project_title, 1, 0, @auth_meth, now(), now(), @institution, @site_org_type, @grant_cite, @project_contact_name, @project_contact_email, @headerlogo, 0, 1);
set @project_id = LAST_INSERT_ID();
-- Create single arm --
INSERT INTO redcap_events_arms (project_id, arm_num, arm_name) VALUES (@project_id, 1, 'Arm 1');
set @arm_id = LAST_INSERT_ID();
-- Create single event --
INSERT INTO redcap_events_metadata (arm_id, day_offset, offset_min, offset_max, descrip) VALUES (@arm_id, 0, 0, 0, 'Event 1');
set @event_id = LAST_INSERT_ID();
-- Insert into redcap_metadata --
INSERT INTO `redcap_metadata` (`project_id`, `field_name`, `field_phi`, `form_name`, `form_menu_description`, `field_order`, `field_units`, `element_preceding_header`, `element_type`, `element_label`, `element_enum`, `element_note`, `element_validation_type`, `element_validation_min`, `element_validation_max`, `element_validation_checktype`, `branching_logic`, `field_req`, `edoc_id`, `edoc_display_img`, `custom_alignment`, `stop_actions`, `question_num`, `grid_name`, `grid_rank`, `misc`, `video_url`, `video_display_inline`) VALUES
(@project_id, 'record_id', NULL, 'field_embedding_demo', 'Field Embedding Demo', 1, NULL, NULL, 'text', 'Record ID', NULL, NULL, NULL, NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'desc1', NULL, 'field_embedding_demo', NULL, 2, NULL, '<div class=\"rich-text-field-label\"><p>The fields below illustrate random examples of <span style=\"color: #e03e2d;\">Field Embedding</span> as a means of customizing your forms and surveys.</p></div>', 'descriptive', '<div class=\"rich-text-field-label\"><table style=\"border-collapse: collapse; width: 100%; height: 80px;\" border=\"0\"> <tbody> <tr style=\"height: 20px;\"> <td style=\"width: 32.02%; height: 20px; text-align: left;\"> </td> <td style=\"width: 80px; height: 20px; text-align: center;\">2012</td> <td style=\"width: 80px; height: 20px; text-align: center;\">2013</td> <td style=\"width: 80px; height: 20px; text-align: center;\">2014</td> <td style=\"width: 80px; height: 20px; text-align: center;\">2015</td> <td style=\"width: 80px; text-align: center;\">2016</td> </tr> <tr style=\"height: 20px;\"> <td style=\"width: 32.02%; height: 20px; text-align: left;\">Federal Grants</td> <td style=\"width: 80px; height: 20px; text-align: center;\">{grant2012}</td> <td style=\"width: 80px; height: 20px; text-align: center;\">{grant2013}</td> <td style=\"width: 80px; height: 20px; text-align: center;\">{grant2014}</td> <td style=\"width: 80px; height: 20px; text-align: center;\">{grant2015}</td> <td style=\"width: 80px; text-align: center;\">{grant2024}</td> </tr> <tr style=\"height: 20px;\"> <td style=\"width: 32.02%; height: 20px; text-align: left;\">Non-federal Grants</td> <td style=\"width: 80px; height: 20px; text-align: center;\">{grant2016}</td> <td style=\"width: 80px; height: 20px; text-align: center;\">{nfgrant2012}</td> <td style=\"width: 80px; height: 20px; text-align: center;\">{nfgrant2013}</td> <td style=\"width: 80px; height: 20px; text-align: center;\">{nfgrant2014}</td> <td style=\"width: 80px; text-align: center;\">{grant2025}</td> </tr> <tr style=\"height: 20px;\"> <td style=\"width: 32.02%; height: 20px; text-align: left;\">Research Agreements/Contracts</td> <td style=\"width: 80px; height: 20px; text-align: center;\">{nfgrant2015}</td> <td style=\"width: 80px; height: 20px; text-align: center;\">{nfgrant2016}</td> <td style=\"width: 80px; height: 20px; text-align: center;\">{grant2022}</td> <td style=\"width: 80px; height: 20px; text-align: center;\">{grant2023}</td> <td style=\"width: 80px; text-align: center;\">{grant2026}</td> </tr> </tbody> </table></div>', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'grant2012', NULL, 'field_embedding_demo', NULL, 3, NULL, NULL, 'text', 'Federal Grants 2012', NULL, NULL, 'int', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'grant2013', NULL, 'field_embedding_demo', NULL, 4, NULL, NULL, 'text', 'Federal Grants 2013', NULL, NULL, 'int', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'grant2014', NULL, 'field_embedding_demo', NULL, 5, NULL, NULL, 'text', 'Federal Grants 2014', NULL, NULL, 'int', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'grant2015', NULL, 'field_embedding_demo', NULL, 6, NULL, NULL, 'text', 'Federal Grants 2015', NULL, NULL, 'int', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'grant2016', NULL, 'field_embedding_demo', NULL, 7, NULL, NULL, 'text', 'Federal Grants 2016', NULL, NULL, 'int', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'nfgrant2012', NULL, 'field_embedding_demo', NULL, 8, NULL, NULL, 'text', 'Non-federal Grants 2012', NULL, NULL, 'int', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'nfgrant2013', NULL, 'field_embedding_demo', NULL, 9, NULL, NULL, 'text', 'Non-federal Grants 2013', NULL, NULL, 'int', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'nfgrant2014', NULL, 'field_embedding_demo', NULL, 10, NULL, NULL, 'text', 'Non-federal Grants 2014', NULL, NULL, 'int', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'nfgrant2015', NULL, 'field_embedding_demo', NULL, 11, NULL, NULL, 'text', 'Non-federal Grants 2015', NULL, NULL, 'int', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'nfgrant2016', NULL, 'field_embedding_demo', NULL, 12, NULL, NULL, 'text', 'Non-federal Grants 2016', NULL, NULL, 'int', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'grant2022', NULL, 'field_embedding_demo', NULL, 13, NULL, NULL, 'text', 'Research Agreements/Contracts 2012', NULL, NULL, 'int', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'grant2023', NULL, 'field_embedding_demo', NULL, 14, NULL, NULL, 'text', 'Research Agreements/Contracts 2013', NULL, NULL, 'int', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'grant2024', NULL, 'field_embedding_demo', NULL, 15, NULL, NULL, 'text', 'Research Agreements/Contracts 2014', NULL, NULL, 'int', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'grant2025', NULL, 'field_embedding_demo', NULL, 16, NULL, NULL, 'text', 'Research Agreements/Contracts 2015', NULL, NULL, 'int', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'grant2026', NULL, 'field_embedding_demo', NULL, 17, NULL, NULL, 'text', 'Research Agreements/Contracts 2016', NULL, NULL, 'int', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'desc2', NULL, 'field_embedding_demo', NULL, 18, NULL, 'Food question', 'descriptive', '<div class=\"rich-text-field-label\"><table style=\"border-collapse: collapse; width: 100%;\" border=\"0\"> <tbody> <tr> <td style=\"width: 45.8502%; vertical-align: top;\">How often did you eat spicy foods last year?</td> <td style=\"width: 34.6791%; vertical-align: top;\"> <p><span style=\"font-weight: normal;\">{num_servings} number of servings</span></p> <p><span style=\"font-weight: normal;\">{not_know}</span></p> </td> <td style=\"width: 19.4706%; vertical-align: top;\"><span style=\"font-weight: normal;\">{food_units:icons}</span></td> </tr> </tbody> </table></div>', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'num_servings', NULL, 'field_embedding_demo', NULL, 19, NULL, NULL, 'text', 'Number of servings', NULL, NULL, 'int', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'not_know', NULL, 'field_embedding_demo', NULL, 20, NULL, NULL, 'checkbox', 'Do not know / Prefer not to answer', '1, Do not know / Prefer not to answer', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'food_units', NULL, 'field_embedding_demo', NULL, 21, NULL, NULL, 'radio', 'Food units', '1, Per day\\n2, Per week\\n3, Per month', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'radio_choices', NULL, 'field_embedding_demo', NULL, 22, NULL, ' Combo radio buttons with text boxes', 'radio', 'Radio choices with custom \"other\" option', '1, My first choice\\n2, My second choice\\n3, Other     {other1}', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'other1', NULL, 'field_embedding_demo', NULL, 23, NULL, NULL, 'text', NULL, NULL, NULL, NULL, NULL, NULL, 'soft_typed', '[radio_choices] = "3"', 0, NULL, 0, NULL, NULL, NULL, NULL, 0, '@PLACEHOLDER=\"Enter custom text\"', NULL, 0),
(@project_id, 'asdf', NULL, 'field_embedding_demo', NULL, 24, NULL, 'Lots of comments', 'descriptive', '<div class=\"rich-text-field-label\"><table style=\"border-collapse: collapse; width: 100%;\" border=\"0\"> <tbody> <tr> <td style=\"width: 25%; text-align: center;\">Main feedback about the event</td> <td style=\"width: 25%; text-align: center;\">Feedback regarding the amenities</td> <td style=\"width: 25%; text-align: center;\">Feedback regarding the venue</td> <td style=\"width: 25%; text-align: center;\">Additional comments</td> </tr> <tr> <td style=\"width: 25%; text-align: center;\">{notes1}</td> <td style=\"width: 25%; text-align: center;\">{notes2}</td> <td style=\"width: 25%; text-align: center;\">{notes3}</td> <td style=\"width: 25%; text-align: center;\">{notes4}</td> </tr> </tbody> </table></div>', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'notes1', NULL, 'field_embedding_demo', NULL, 25, NULL, NULL, 'textarea', 'Main feedback about the event', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'notes2', NULL, 'field_embedding_demo', NULL, 26, NULL, NULL, 'textarea', 'Feedback regarding the amenities', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'notes3', NULL, 'field_embedding_demo', NULL, 27, NULL, NULL, 'textarea', 'Feedback regarding the venue', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'notes4', NULL, 'field_embedding_demo', NULL, 28, NULL, NULL, 'textarea', 'Additional comments', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'pname', NULL, 'field_embedding_demo', NULL, 29, NULL, ' Patient Information', 'descriptive', '<div class=\"rich-text-field-label\"><table style=\"border-collapse: collapse; width: 100%;\"> <tbody> <tr> <td style=\"width: 33.3333%;\">Patient name:</td> <td style=\"width: 33.3333%;\">{first_name}</td> <td style=\"width: 33.3333%;\">{last_name}</td> </tr> </tbody> </table></div>', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'first_name', '1', 'field_embedding_demo', NULL, 30, NULL, NULL, 'text', 'Patient first name', NULL, NULL, NULL, NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, '@PLACEHOLDER=\"First\"', NULL, 0),
(@project_id, 'last_name', '1', 'field_embedding_demo', NULL, 31, NULL, NULL, 'text', 'Patient last name', NULL, NULL, NULL, NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, '@PLACEHOLDER=\"Last\"', NULL, 0),
(@project_id, 'dob_descriptive', NULL, 'field_embedding_demo', NULL, 32, NULL, NULL, 'descriptive', '<div class=\"rich-text-field-label\"><table style=\"border-collapse: collapse; width: 100%;\" cellpadding=\"5\"> <tbody> <tr style=\"height: 42px;\"> <th style=\"width: 31.0544%; height: 42px;\">Date of birth:</th> <td style=\"width: 17.6733%; height: 42px;\"> <div>{dob}</div> </td> <td style=\"width: 14.0046%; height: 42px;\"> <div>Sex:</div> <div>{sex}</div> </td> <td style=\"width: 6.51389%; height: 42px;\"> <div> <div>Ethnicity:</div> <div>{ethnicity}</div> </div> </td> </tr> <tr style=\"height: 42px;\"> <th style=\"width: 31.0544%; height: 42px;\">Age (in either years/months/days):</th> <td style=\"height: 42px; width: 17.6733%;\"> <div>{age}</div> </td> <td style=\"width: 14.0046%;\"> <div>Age units: {ageunit}</div> </td> <th style=\"width: 6.51389%; height: 42px;\"> </th> </tr> </tbody> </table></div>', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'id_descriptive', NULL, 'field_embedding_demo', NULL, 33, NULL, NULL, 'descriptive', '<div class=\"rich-text-field-label\"><table style=\"border-collapse: collapse; width: 100%;\" cellpadding=\"2\"> <tbody> <tr> <td>Reporting jurisdiction:</td> <td> <div>{state}</div> </td> <td>Case State/local ID:</td> <td> <div>{local_id}</div> </td> </tr> <tr> <td>Reporting health department:</td> <td> <div>{healthdept}</div> </td> <td>CDC 2019-nCoV ID:</td> <td> <div>{cdc_ncov2019_id}</div> </td> </tr> <tr> <th>Contact ID <sup>a</sup>:</th> <td> <div>{contact_id}</div> </td> <th>NNDSS loc.rec.ID/Case ID <sup>b</sup>:</th> <td> <div>{nndss_id}</div> </td> </tr> </tbody> </table></div>', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, ' ', NULL, 0),
(@project_id, 'hosp_descriptive', NULL, 'field_embedding_demo', NULL, 34, NULL, NULL, 'descriptive', '<div class=\"rich-text-field-label\"><table> <tbody> <tr> <th>Was the patient hospitalized?</th> <td> <div>{hosp_yn}</div> </td> <th>Admission Date</th> <td> <div>{adm1_dt}</div> </td> <th>Discharge Date</th> <td> <div>{dis1_dt}</div> </td> </tr> </tbody> </table></div>', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, ' ', NULL, 0),
(@project_id, 'interviewer_descriptive', NULL, 'field_embedding_demo', NULL, 35, NULL, 'Interviewer information', 'descriptive', '<div class=\"rich-text-field-label\"><table style=\"border-collapse: collapse; width: 100%;\"> <tbody> <tr> <th> Last Name:</th> <td align=\"left\"> <div>{interviewer_ln}</div> </td> <th>First Name:</th> <td align=\"left\"> <div>{interviewer_fn}</div> </td> </tr> <tr> <th>Affiliation/Organization:</th> <td align=\"left\"> <div>{interviewer_org}</div> </td> <th>Telephone:</th> <td align=\"left\"> <div>{interviewer_tele}</div> </td> </tr> <tr> <th>Email:</th> <td align=\"left\"> <div>{interviewer_email}</div> </td> </tr> </tbody> </table></div>', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'symptom_descriptive', NULL, 'field_embedding_demo', NULL, 36, NULL, 'Symptoms, clinical course, past medical history and social history', 'descriptive', '<div class=\"rich-text-field-label\"><table> <tbody> <tr> <th style=\"width: 639.219px;\" colspan=\"2\"> <p><strong>During the illness, did the patient experience any of the following symptoms?</strong></p> </th> <th style=\"width: 126.219px;\"> <p><strong>Onset Date</strong></p> </th> </tr> <tr> <th style=\"width: 226.219px;\">Fever>100.4F(38C)</th> <td style=\"width: 399.219px;\"> <div>{fever_yn}</div> </td> <td style=\"width: 126.219px;\"> <div>{fever_onset}</div> </td> </tr> <tr> <th style=\"width: 226.219px;\">Subjective fever (felt feverish)</th> <td style=\"width: 399.219px;\"> <div>{sfever_yn}</div> </td> <td style=\"width: 126.219px;\"> <div>{sfever_onset}</div> </td> </tr> <tr> <th style=\"width: 226.219px;\">Chills</th> <td style=\"width: 399.219px;\"> <div>{chills_yn}</div> </td> <td style=\"width: 126.219px;\"> <div>{chills_onset}</div> </td> </tr> <tr> <th style=\"width: 226.219px;\">Muscle aches (myalgia)</th> <td style=\"width: 399.219px;\"> <div>{myalgia_yn}</div> </td> <td style=\"width: 126.219px;\"> <div>{myalgia_onset}</div> </td> </tr> <tr> <th style=\"width: 226.219px;\">Other symptom 1</th> <td style=\"width: 399.219px;\"> <div>{othsym1_spec}</div> </td> <td style=\"width: 126.219px;\"> <div>{othsym1_onset}</div> </td> </tr> <tr> <th style=\"width: 226.219px;\">Other symptom 2</th> <td style=\"width: 399.219px;\"> <div>{othsym2_spec}</div> </td> <td style=\"width: 126.219px;\"> <div>{othsym2_onset}</div> </td> </tr> </tbody> </table></div>', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'state', NULL, 'field_embedding_demo', NULL, 37, NULL, NULL, 'text', 'Reporting jurisdiction', NULL, NULL, NULL, NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'healthdept', NULL, 'field_embedding_demo', NULL, 38, NULL, NULL, 'text', 'Reporting health department', NULL, NULL, NULL, NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'contact_id', NULL, 'field_embedding_demo', NULL, 39, NULL, NULL, 'text', 'Contact ID\n\nOnly complete if case-patient is a known contact of prior source case-patient. Assign Contact ID using CDC 2019-nCoV ID and sequential contact ID, e.g., Confirmed case CA102034567 has contacts CA102034567-01 and CA102034567-02', NULL, NULL, NULL, NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'local_id', NULL, 'field_embedding_demo', NULL, 40, NULL, NULL, 'text', 'Case state/local ID', NULL, NULL, NULL, NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'cdc_ncov2019_id', NULL, 'field_embedding_demo', NULL, 41, NULL, NULL, 'text', 'CDC 2019-nCoV ID', NULL, NULL, NULL, NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'nndss_id', NULL, 'field_embedding_demo', NULL, 42, NULL, NULL, 'text', 'NNDSS loc. rec. ID/Case ID\n\nFor NNDSS reporters, use GenV2 or NETSS patient identifier', NULL, NULL, NULL, NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'interviewer_ln', NULL, 'field_embedding_demo', NULL, 43, NULL, NULL, 'text', 'Interviewer last name', NULL, NULL, NULL, NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, 'LV', NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'interviewer_fn', NULL, 'field_embedding_demo', NULL, 44, NULL, NULL, 'text', 'Interviewer first name', NULL, NULL, NULL, NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'interviewer_org', NULL, 'field_embedding_demo', NULL, 45, NULL, NULL, 'text', 'Affiliation/Organization', NULL, NULL, NULL, NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'interviewer_tele', NULL, 'field_embedding_demo', NULL, 46, NULL, NULL, 'text', 'Telephone', NULL, NULL, 'phone', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'interviewer_email', NULL, 'field_embedding_demo', NULL, 47, NULL, NULL, 'text', 'Email', NULL, NULL, 'email', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'sex', NULL, 'field_embedding_demo', NULL, 48, NULL, NULL, 'radio', 'Sex', '1, Male\\n2, Female\\n9, Unknown\\n3, Other', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 'LV', NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'ethnicity', NULL, 'field_embedding_demo', NULL, 49, NULL, NULL, 'radio', 'Ethnicity', '1, Hispanic/Latino\\n0, Non-Hispanic-Latino\\n9, Not specified', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 'LV', NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'dob', NULL, 'field_embedding_demo', NULL, 50, NULL, NULL, 'text', 'Date of birth', NULL, NULL, 'date_mdy', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, '@HIDEBUTTON', NULL, 0),
(@project_id, 'age', NULL, 'field_embedding_demo', NULL, 51, NULL, NULL, 'text', 'Age\n\nPlease give age in either:\n-years (most common)\n-months\n-days\n\nYou will pick the age unit in the next question.', NULL, NULL, 'float', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'ageunit', NULL, 'field_embedding_demo', NULL, 52, NULL, NULL, 'radio', 'Age units\n\nThe number you gave above was in what units?', '1, Years\\n2, Months\\n3, Days', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'hosp_yn', NULL, 'field_embedding_demo', NULL, 53, NULL, NULL, 'radio', 'Was the patient hospitalized?', '1, Yes\\n0, No\\n9, Unknown', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'adm1_dt', NULL, 'field_embedding_demo', NULL, 54, NULL, NULL, 'text', 'If yes, admission date 1', NULL, NULL, 'date_mdy', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, '@HIDEBUTTON', NULL, 0),
(@project_id, 'dis1_dt', NULL, 'field_embedding_demo', NULL, 55, NULL, NULL, 'text', 'If yes, discharge date 1', NULL, NULL, 'date_mdy', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, '@HIDEBUTTON', NULL, 0),
(@project_id, 'fever_yn', NULL, 'field_embedding_demo', NULL, 56, NULL, NULL, 'radio', 'Fever >100.4F (38C)', '1, Yes \\n 0, No \\n 9, Unknown', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, 'symptoms', 0, NULL, NULL, 0),
(@project_id, 'sfever_yn', NULL, 'field_embedding_demo', NULL, 57, NULL, NULL, 'radio', 'Subjective fever (felt feverish)', '1, Yes \\n 0, No \\n 9, Unknown', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, 'symptoms', 0, NULL, NULL, 0),
(@project_id, 'chills_yn', NULL, 'field_embedding_demo', NULL, 58, NULL, NULL, 'radio', 'Chills', '1, Yes \\n 0, No \\n 9, Unknown', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, 'symptoms', 0, NULL, NULL, 0),
(@project_id, 'myalgia_yn', NULL, 'field_embedding_demo', NULL, 59, NULL, NULL, 'radio', 'Muscle aches (myalgia)', '1, Yes \\n 0, No \\n 9, Unknown', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, 'symptoms', 0, NULL, NULL, 0),
(@project_id, 'fever_onset', NULL, 'field_embedding_demo', NULL, 60, NULL, NULL, 'text', 'Fever onset date', NULL, NULL, 'date_mdy', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, '@HIDEBUTTON', NULL, 0),
(@project_id, 'sfever_onset', NULL, 'field_embedding_demo', NULL, 61, NULL, NULL, 'text', 'Subjective fever (felt feverish) onset date', NULL, NULL, 'date_mdy', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, '@HIDEBUTTON', NULL, 0),
(@project_id, 'chills_onset', NULL, 'field_embedding_demo', NULL, 62, NULL, NULL, 'text', 'Chills onset date', NULL, NULL, 'date_mdy', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, '@HIDEBUTTON', NULL, 0),
(@project_id, 'myalgia_onset', NULL, 'field_embedding_demo', NULL, 63, NULL, NULL, 'text', 'Muscle aches (myalgia) onset date', NULL, NULL, 'date_mdy', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, '@HIDEBUTTON', NULL, 0),
(@project_id, 'othsym1_spec', NULL, 'field_embedding_demo', NULL, 64, NULL, NULL, 'text', 'Other symptoms - 1, specify:', NULL, NULL, NULL, NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'othsym2_spec', NULL, 'field_embedding_demo', NULL, 65, NULL, NULL, 'text', 'Other symptoms - 2, specify:', NULL, NULL, NULL, NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0),
(@project_id, 'othsym1_onset', NULL, 'field_embedding_demo', NULL, 66, NULL, NULL, 'text', 'Other symptoms 1 onset date', NULL, NULL, 'date_mdy', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, '@HIDEBUTTON', NULL, 0),
(@project_id, 'othsym2_onset', NULL, 'field_embedding_demo', NULL, 67, NULL, NULL, 'text', 'Other symptoms 2 onset date', NULL, NULL, 'date_mdy', NULL, NULL, 'soft_typed', NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, '@HIDEBUTTON', NULL, 0),
(@project_id, 'field_embedding_demo_complete', NULL, 'field_embedding_demo', NULL, 68, NULL, 'Form Status', 'select', 'Complete?', '0, Incomplete \\n 1, Unverified \\n 2, Complete', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 0);

INSERT INTO `redcap_projects_templates` (`project_id`, `title`, `description`, `enabled`)
	VALUES (@project_id,  @project_title,  'Example of the Field Embedding feature.',  '1');