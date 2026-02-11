CREATE TABLE question_likes_patients (question_id INT NOT NULL, patient_id INT NOT NULL, INDEX IDX_QP_Q (question_id), INDEX IDX_QP_P (patient_id), PRIMARY KEY(question_id, patient_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
ALTER TABLE question_likes_patients ADD CONSTRAINT FK_QP_Q FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE;
ALTER TABLE question_likes_patients ADD CONSTRAINT FK_QP_P FOREIGN KEY (patient_id) REFERENCES patients (id) ON DELETE CASCADE;

CREATE TABLE question_likes_medecins (question_id INT NOT NULL, medecin_id INT NOT NULL, INDEX IDX_QM_Q (question_id), INDEX IDX_QM_M (medecin_id), PRIMARY KEY(question_id, medecin_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
ALTER TABLE question_likes_medecins ADD CONSTRAINT FK_QM_Q FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE;
ALTER TABLE question_likes_medecins ADD CONSTRAINT FK_QM_M FOREIGN KEY (medecin_id) REFERENCES medecins (id) ON DELETE CASCADE;

CREATE TABLE reponse_likes_patients (reponse_id INT NOT NULL, patient_id INT NOT NULL, INDEX IDX_RP_R (reponse_id), INDEX IDX_RP_P (patient_id), PRIMARY KEY(reponse_id, patient_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
ALTER TABLE reponse_likes_patients ADD CONSTRAINT FK_RP_R FOREIGN KEY (reponse_id) REFERENCES reponse (id) ON DELETE CASCADE;
ALTER TABLE reponse_likes_patients ADD CONSTRAINT FK_RP_P FOREIGN KEY (patient_id) REFERENCES patients (id) ON DELETE CASCADE;

CREATE TABLE reponse_likes_medecins (reponse_id INT NOT NULL, medecin_id INT NOT NULL, INDEX IDX_RM_R (reponse_id), INDEX IDX_RM_M (medecin_id), PRIMARY KEY(reponse_id, medecin_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
ALTER TABLE reponse_likes_medecins ADD CONSTRAINT FK_RM_R FOREIGN KEY (reponse_id) REFERENCES reponse (id) ON DELETE CASCADE;
ALTER TABLE reponse_likes_medecins ADD CONSTRAINT FK_RM_M FOREIGN KEY (medecin_id) REFERENCES medecins (id) ON DELETE CASCADE;
