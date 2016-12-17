CREATE TABLE hha_patients
( id integer NOT NULL AUTO_INCREMENT,
  created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated TIMESTAMP,
  lastname varchar(220) NOT NULL,
  firstname varchar(220) NOT NULL,
  date_of_birth date,
  email varchar(220),
  created_by integer not null,
  updated_by integer,
  primary key (id),
  foreign key (created_by) references mc_users(id),
  foreign key (updated_by) references mc_users(id)
) DEFAULT CHARSET=utf8;

create table hha_care_plan
(id integer not null auto_increment,
 created timestamp not null default current_timestamp,
 user_id integer not null,

visit_date DATE,
dnr BOOLEAN,
episode_period DATE,
hha_frequency VARCHAR(220),
primary_diagnosis VARCHAR(220),
secondary_diagnosis VARCHAR(220),
diet VARCHAR(220),
allergies VARCHAR(220),
vital_sign_na BOOLEAN,
spb_g SMALLINT,
spb_l SMALLINT,
dpb_g SMALLINT,
dpb_l SMALLINT,
hr_g SMALLINT,
hr_l SMALLINT,
resp_g SMALLINT,
resp_l SMALLINT,
temp_g SMALLINT,
temp_l SMALLINT,
weight_g SMALLINT,
weight_l SMALLINT,
anticoagulant_precautions BOOLEAN,
emergency_plan_developed BOOLEAN,
fall_precautions BOOLEAN,
keep_pathway_clear BOOLEAN,
keep_side_rails_up BOOLEAN,
neutropenic_precautions BOOLEAN,
o2_precautions BOOLEAN,
proper_position_during_meals BOOLEAN,
safety_in_adls BOOLEAN,
seizure_precautions BOOLEAN,
sharps_safety BOOLEAN,
slow_position_change BOOLEAN,
standard_infect_control BOOLEAN,
support_during_transfer BOOLEAN,
use_assistive_devices BOOLEAN,
sp_other TEXT,
ptemperature SMALLINT,
pblood_pressure SMALLINT,
pheart_rate SMALLINT,
prespirations SMALLINT,
pweight SMALLINT,
assist_w_bad SMALLINT,
assist_w_bsc SMALLINT,
incontinence_care SMALLINT,
empty_drainage_bag SMALLINT,
record_bowel_movement SMALLINT,
catheter_care SMALLINT,
bed_bath SMALLINT,
assist_w_chair_bath SMALLINT,
tub_bath SMALLINT,
shower SMALLINT,
shower_w_chair SMALLINT,
shampoo_hair SMALLINT,
hair_care SMALLINT,
oral_care SMALLINT,
skin_care SMALLINT,
pericare SMALLINT,
nail_care SMALLINT,
shave SMALLINT,
assist_w_dressing SMALLINT,
medication_remmder SMALLINT,
dangle_on_side_bed SMALLINT,
turn_and_position SMALLINT,
assist_w_transfer SMALLINT,
range_of_motion SMALLINT,
assist_w_ambulation SMALLINT,
equipment_care SMALLINT,
make_bed SMALLINT,
change_linen SMALLINT,
light_housekeeping SMALLINT,
meal_setup SMALLINT,
assist_w_feeding SMALLINT,
amputation BOOLEAN,
bower_bladder BOOLEAN,
contracture BOOLEAN,
hearing BOOLEAN,
paralisys BOOLEAN,
endurance BOOLEAN,
ambulation BOOLEAN,
speech BOOLEAN,
legally_blind BOOLEAN,
dyspnea_w_min_exertion BOOLEAN,
fl_other BOOLEAN,
complete_bed_rest BOOLEAN,
bed_rest_w_brp BOOLEAN,
up_as_tolerated BOOLEAN,
transfer_bed_chair BOOLEAN,
exercise_prescribed BOOLEAN,
partial_weight_bearing BOOLEAN,
independent_at_home BOOLEAN,
crutches BOOLEAN,
cane BOOLEAN,
wheelchair BOOLEAN,
walker BOOLEAN,
ap_other BOOLEAN,

primary key (id),
foreign key (user_id) references mc_users(id)
) DEFAULT CHARSET=utf8;

