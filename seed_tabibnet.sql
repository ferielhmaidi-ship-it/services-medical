USE tabibnet;
SET @pw := (SELECT password FROM admins ORDER BY id LIMIT 1);
SET @pw := IFNULL(@pw, '$2y$13$n5/57ICeJfRfRYwfVX8sGe1UjgyrOpQHBuiHe635ruvjUshDn7m36');

INSERT IGNORE INTO admins (email,roles,password,first_name,last_name,age,gender,is_active,name) VALUES
('admin2@tabibnet.tn','["ROLE_ADMIN"]',@pw,'Nour','Admin',34,'F',1,'Admin Two'),
('admin3@tabibnet.tn','["ROLE_ADMIN"]',@pw,'Karim','Admin',38,'M',1,'Admin Three'),
('admin4@tabibnet.tn','["ROLE_ADMIN"]',@pw,'Sami','Admin',41,'M',1,'Admin Four'),
('admin5@tabibnet.tn','["ROLE_ADMIN"]',@pw,'Lina','Admin',29,'F',1,'Admin Five');

INSERT IGNORE INTO patients (email,roles,password,first_name,last_name,age,gender,is_active,phone_number,address,date_of_birth,has_insurance,insurance_number) VALUES
('patient1@tabibnet.tn','["ROLE_PATIENT"]',@pw,'Amel','Ben Salah',27,'F',1,'20100001','Tunis','1998-03-12',1,'INS-1001'),
('patient2@tabibnet.tn','["ROLE_PATIENT"]',@pw,'Youssef','Trabelsi',35,'M',1,'20100002','Sfax','1990-08-20',0,NULL),
('patient3@tabibnet.tn','["ROLE_PATIENT"]',@pw,'Meriem','Gharbi',31,'F',1,'20100003','Sousse','1994-11-05',1,'INS-1003'),
('patient4@tabibnet.tn','["ROLE_PATIENT"]',@pw,'Hatem','Mansouri',45,'M',1,'20100004','Nabeul','1980-01-17',1,'INS-1004'),
('patient5@tabibnet.tn','["ROLE_PATIENT"]',@pw,'Rania','Ayadi',23,'F',1,'20100005','Bizerte','2002-07-30',0,NULL);

INSERT IGNORE INTO medecins (email,roles,password,first_name,last_name,age,gender,is_active,specialty,cin,address,education,experience,governorate,is_verified,phone_number,ai_average_score,ai_score_updated_at) VALUES
('medecin1@tabibnet.tn','["ROLE_MEDECIN"]',@pw,'Nader','Khadhraoui',42,'M',1,'Cardiologie','90000001','Tunis','Fac Med Tunis','12 ans','Tunis',1,'51100001',4.5,NOW()),
('medecin2@tabibnet.tn','["ROLE_MEDECIN"]',@pw,'Salma','Karray',39,'F',1,'Pediatrie','90000002','Sousse','Fac Med Sousse','10 ans','Sousse',1,'51100002',4.2,NOW()),
('medecin3@tabibnet.tn','["ROLE_MEDECIN"]',@pw,'Walid','Jaziri',47,'M',1,'Dermatologie','90000003','Sfax','Fac Med Sfax','18 ans','Sfax',0,'51100003',3.9,NOW()),
('medecin4@tabibnet.tn','["ROLE_MEDECIN"]',@pw,'Ines','Haddad',33,'F',1,'Neurologie','90000004','Monastir','Fac Med Monastir','7 ans','Monastir',1,'51100004',4.7,NOW()),
('medecin5@tabibnet.tn','["ROLE_MEDECIN"]',@pw,'Fares','Brahmi',51,'M',1,'Orthopedie','90000005','Nabeul','Fac Med Tunis','22 ans','Nabeul',1,'51100005',4.1,NOW());

INSERT IGNORE INTO patient (first_name,last_name,email,phone) VALUES
('Ali','Zidi','p1@legacy.tn','22100001'),
('Maya','Boussetta','p2@legacy.tn','22100002'),
('Skander','Tlili','p3@legacy.tn','22100003'),
('Oumaima','Dridi','p4@legacy.tn','22100004'),
('Nabil','Chaari','p5@legacy.tn','22100005');

INSERT INTO rendez_vous (appointment_date,message,statut,created_at,doctor_id,patient_id)
SELECT NOW() + INTERVAL 1 DAY,'Controle periodique','CONFIRMED',NOW(),1,1 WHERE (SELECT COUNT(*) FROM rendez_vous) < 1;
INSERT INTO rendez_vous (appointment_date,message,statut,created_at,doctor_id,patient_id)
SELECT NOW() + INTERVAL 2 DAY,'Douleur thoracique','PENDING',NOW(),2,2 WHERE (SELECT COUNT(*) FROM rendez_vous) < 2;
INSERT INTO rendez_vous (appointment_date,message,statut,created_at,doctor_id,patient_id)
SELECT NOW() + INTERVAL 3 DAY,'Suivi traitement','CONFIRMED',NOW(),3,3 WHERE (SELECT COUNT(*) FROM rendez_vous) < 3;
INSERT INTO rendez_vous (appointment_date,message,statut,created_at,doctor_id,patient_id)
SELECT NOW() + INTERVAL 4 DAY,'Controle annuel','PENDING',NOW(),4,4 WHERE (SELECT COUNT(*) FROM rendez_vous) < 4;
INSERT INTO rendez_vous (appointment_date,message,statut,created_at,doctor_id,patient_id)
SELECT NOW() + INTERVAL 5 DAY,'Consultation generale','CONFIRMED',NOW(),5,5 WHERE (SELECT COUNT(*) FROM rendez_vous) < 5;

INSERT INTO feedback (rating,comment,created_at,patient_id,medecin_id,rendez_vous_id,sentiment_score)
SELECT 5,'Excellent service',NOW(),1,1,1,0.95 WHERE (SELECT COUNT(*) FROM feedback) < 1;
INSERT INTO feedback (rating,comment,created_at,patient_id,medecin_id,rendez_vous_id,sentiment_score)
SELECT 4,'Tres bonne prise en charge',NOW(),2,2,2,0.82 WHERE (SELECT COUNT(*) FROM feedback) < 2;
INSERT INTO feedback (rating,comment,created_at,patient_id,medecin_id,rendez_vous_id,sentiment_score)
SELECT 3,'Correct mais attente longue',NOW(),3,3,3,0.22 WHERE (SELECT COUNT(*) FROM feedback) < 3;
INSERT INTO feedback (rating,comment,created_at,patient_id,medecin_id,rendez_vous_id,sentiment_score)
SELECT 5,'Medecin a l ecoute',NOW(),4,4,4,0.90 WHERE (SELECT COUNT(*) FROM feedback) < 4;
INSERT INTO feedback (rating,comment,created_at,patient_id,medecin_id,rendez_vous_id,sentiment_score)
SELECT 4,'Satisfait globalement',NOW(),5,5,5,0.70 WHERE (SELECT COUNT(*) FROM feedback) < 5;

INSERT INTO appointment (patient_id,date,start_time,duration,status,doctor_id)
SELECT 1,CURDATE(),'09:00:00',30,'confirmed',1 WHERE (SELECT COUNT(*) FROM appointment) < 1;
INSERT INTO appointment (patient_id,date,start_time,duration,status,doctor_id)
SELECT 2,CURDATE() + INTERVAL 1 DAY,'10:00:00',45,'pending',2 WHERE (SELECT COUNT(*) FROM appointment) < 2;
INSERT INTO appointment (patient_id,date,start_time,duration,status,doctor_id)
SELECT 3,CURDATE() + INTERVAL 2 DAY,'11:00:00',30,'confirmed',3 WHERE (SELECT COUNT(*) FROM appointment) < 3;
INSERT INTO appointment (patient_id,date,start_time,duration,status,doctor_id)
SELECT 4,CURDATE() + INTERVAL 3 DAY,'14:00:00',60,'cancelled',4 WHERE (SELECT COUNT(*) FROM appointment) < 4;
INSERT INTO appointment (patient_id,date,start_time,duration,status,doctor_id)
SELECT 5,CURDATE() + INTERVAL 4 DAY,'15:30:00',30,'pending',5 WHERE (SELECT COUNT(*) FROM appointment) < 5;

INSERT INTO calendar_setting (slot_duration,pause_start,pause_end,doctor_id)
SELECT 30,'12:00:00','13:00:00',1 WHERE (SELECT COUNT(*) FROM calendar_setting) < 1;
INSERT INTO calendar_setting (slot_duration,pause_start,pause_end,doctor_id)
SELECT 20,'12:30:00','13:00:00',2 WHERE (SELECT COUNT(*) FROM calendar_setting) < 2;
INSERT INTO calendar_setting (slot_duration,pause_start,pause_end,doctor_id)
SELECT 30,'13:00:00','14:00:00',3 WHERE (SELECT COUNT(*) FROM calendar_setting) < 3;
INSERT INTO calendar_setting (slot_duration,pause_start,pause_end,doctor_id)
SELECT 15,'12:00:00','12:30:00',4 WHERE (SELECT COUNT(*) FROM calendar_setting) < 4;
INSERT INTO calendar_setting (slot_duration,pause_start,pause_end,doctor_id)
SELECT 25,'13:00:00','13:30:00',5 WHERE (SELECT COUNT(*) FROM calendar_setting) < 5;

INSERT INTO indisponibilite (date,doctor_id,is_emergency)
SELECT CURDATE() + INTERVAL 7 DAY,1,0 WHERE (SELECT COUNT(*) FROM indisponibilite) < 1;
INSERT INTO indisponibilite (date,doctor_id,is_emergency)
SELECT CURDATE() + INTERVAL 8 DAY,2,1 WHERE (SELECT COUNT(*) FROM indisponibilite) < 2;
INSERT INTO indisponibilite (date,doctor_id,is_emergency)
SELECT CURDATE() + INTERVAL 9 DAY,3,0 WHERE (SELECT COUNT(*) FROM indisponibilite) < 3;
INSERT INTO indisponibilite (date,doctor_id,is_emergency)
SELECT CURDATE() + INTERVAL 10 DAY,4,0 WHERE (SELECT COUNT(*) FROM indisponibilite) < 4;
INSERT INTO indisponibilite (date,doctor_id,is_emergency)
SELECT CURDATE() + INTERVAL 11 DAY,5,1 WHERE (SELECT COUNT(*) FROM indisponibilite) < 5;

INSERT INTO temps_travail (day_of_week,start_time,end_time,doctor_id,specific_date)
SELECT 'monday','08:00:00','16:00:00',1,NULL WHERE (SELECT COUNT(*) FROM temps_travail) < 1;
INSERT INTO temps_travail (day_of_week,start_time,end_time,doctor_id,specific_date)
SELECT 'tuesday','09:00:00','17:00:00',2,NULL WHERE (SELECT COUNT(*) FROM temps_travail) < 2;
INSERT INTO temps_travail (day_of_week,start_time,end_time,doctor_id,specific_date)
SELECT 'wednesday','08:30:00','15:30:00',3,NULL WHERE (SELECT COUNT(*) FROM temps_travail) < 3;
INSERT INTO temps_travail (day_of_week,start_time,end_time,doctor_id,specific_date)
SELECT 'thursday','10:00:00','18:00:00',4,NULL WHERE (SELECT COUNT(*) FROM temps_travail) < 4;
INSERT INTO temps_travail (day_of_week,start_time,end_time,doctor_id,specific_date)
SELECT 'friday','08:00:00','14:00:00',5,NULL WHERE (SELECT COUNT(*) FROM temps_travail) < 5;
