-- Plus International School — starter data.
-- Every demo account below uses the password: Password123
-- Change these passwords before the platform goes live.

INSERT IGNORE INTO school_classes (id, name, section, level_order) VALUES
 (1,'Creche','nursery',1),(2,'Nursery 1','nursery',2),(3,'Nursery 2','nursery',3),
 (4,'Primary 1','primary',4),(5,'Primary 2','primary',5),(6,'Primary 3','primary',6),
 (7,'Primary 4','primary',7),(8,'Primary 5','primary',8),(9,'Primary 6','primary',9),
 (10,'JSS 1','secondary',10),(11,'JSS 2','secondary',11),(12,'JSS 3','secondary',12),
 (13,'SSS 1','secondary',13),(14,'SSS 2','secondary',14),(15,'SSS 3','secondary',15);

INSERT IGNORE INTO subjects (id, name, code, section) VALUES
 (1,'English Language','ENG','all'),(2,'Mathematics','MTH','all'),(3,'Basic Science','BSC','primary'),
 (4,'Civic Education','CIV','all'),(5,'Computer Studies','ICT','all'),(6,'Social Studies','SOS','primary'),
 (7,'Agricultural Science','AGR','secondary'),(8,'Biology','BIO','secondary'),(9,'Chemistry','CHM','secondary'),
 (10,'Physics','PHY','secondary'),(11,'Economics','ECO','secondary'),(12,'Literature in English','LIT','secondary'),
 (13,'Christian Religious Studies','CRS','all'),(14,'Islamic Religious Studies','IRS','all'),
 (15,'Physical & Health Education','PHE','all'),(16,'Creative Arts','CRA','primary'),(17,'French','FRE','all');

INSERT IGNORE INTO academic_sessions (id, name, term, is_current, starts_on, ends_on) VALUES
 (1,'2025/2026','First',1,'2025-09-15','2025-12-19');

-- Password for every demo account below: Password123
INSERT IGNORE INTO users (id, full_name, email, phone, password_hash, role, staff_no, admission_no, class_id, gender, student_status, status) VALUES
 (1,'System Administrator','admin@plusinternationalschool.ng','+2348000000001','$2y$10$NTTl6lK5UU7h0UUJUqhdVeczLie8UyYehPQ6qLku1kvNQ/wnSY/hG','superadmin','PIS/STF/001',NULL,NULL,'male',NULL,'active'),
 (2,'Aisha Bello','subadmin@plusinternationalschool.ng','+2348000000002','$2y$10$NTTl6lK5UU7h0UUJUqhdVeczLie8UyYehPQ6qLku1kvNQ/wnSY/hG','subadmin','PIS/STF/002',NULL,NULL,'female',NULL,'active'),
 (3,'Grace Ibrahim','cashier@plusinternationalschool.ng','+2348000000003','$2y$10$NTTl6lK5UU7h0UUJUqhdVeczLie8UyYehPQ6qLku1kvNQ/wnSY/hG','cashier','PIS/STF/003',NULL,NULL,'female',NULL,'active'),
 (4,'Musa Danjuma','teacher@plusinternationalschool.ng','+2348000000004','$2y$10$NTTl6lK5UU7h0UUJUqhdVeczLie8UyYehPQ6qLku1kvNQ/wnSY/hG','teacher','PIS/STF/004',NULL,NULL,'male',NULL,'active'),
 (5,'Fatima Yusuf','fatima.yusuf@plusinternationalschool.ng','+2348000000005','$2y$10$NTTl6lK5UU7h0UUJUqhdVeczLie8UyYehPQ6qLku1kvNQ/wnSY/hG','teacher','PIS/STF/005',NULL,NULL,'female',NULL,'active'),
 (6,'Zainab Aliyu','zainab.aliyu@student.plusinternationalschool.ng','+2348000000006','$2y$10$NTTl6lK5UU7h0UUJUqhdVeczLie8UyYehPQ6qLku1kvNQ/wnSY/hG','student',NULL,'PIS/2025/001',10,'female','returning','active'),
 (7,'Emeka Obi','emeka.obi@student.plusinternationalschool.ng','+2348000000007','$2y$10$NTTl6lK5UU7h0UUJUqhdVeczLie8UyYehPQ6qLku1kvNQ/wnSY/hG','student',NULL,'PIS/2025/002',10,'male','new','active'),
 (8,'Halima Sani','halima.sani@student.plusinternationalschool.ng','+2348000000008','$2y$10$NTTl6lK5UU7h0UUJUqhdVeczLie8UyYehPQ6qLku1kvNQ/wnSY/hG','student',NULL,'PIS/2025/003',10,'female','returning','active'),
 (9,'Mrs Ngozi Obi','parent@plusinternationalschool.ng','+2348000000009','$2y$10$NTTl6lK5UU7h0UUJUqhdVeczLie8UyYehPQ6qLku1kvNQ/wnSY/hG','parent',NULL,NULL,NULL,'female',NULL,'active');

INSERT IGNORE INTO parent_students (parent_id, student_id) VALUES (9,7);

-- The cashier and the subadmin get the permissions their roles need.
INSERT IGNORE INTO user_permissions (user_id, permission, granted_by) VALUES
 (2,'view_payments',1),(2,'manage_results',1),(2,'manage_records',1),(2,'manage_timetable',1),
 (2,'manage_announcements',1),(2,'view_audit_log',1),
 (3,'view_payments',1),(3,'approve_payments',1),
 (4,'manage_results',1),(5,'manage_results',1);

INSERT IGNORE INTO fee_structure (class_id, term, session_name, amount, description) VALUES
 (2,'First','2025/2026',85000.00,'Tuition, books and feeding'),
 (4,'First','2025/2026',110000.00,'Tuition, books and exam fee'),
 (10,'First','2025/2026',165000.00,'Tuition, books, laboratory and exam fee'),
 (13,'First','2025/2026',195000.00,'Tuition, books, laboratory and exam fee');

INSERT IGNORE INTO school_bank_accounts (id, bank_name, account_name, account_number) VALUES
 (1,'Zenith Bank','Plus International School','1010101010'),
 (2,'First Bank of Nigeria','Plus International School','3030303030');

INSERT IGNORE INTO timetable_slots (class_id, subject_id, teacher_id, day_of_week, period, starts_at, ends_at, room, session_name, term) VALUES
 (10,1,4,'Monday',1,'08:00','08:40','Block A / JSS 1','2025/2026','First'),
 (10,2,5,'Monday',2,'08:40','09:20','Block A / JSS 1','2025/2026','First'),
 (10,5,4,'Monday',3,'09:30','10:10','ICT Lab','2025/2026','First'),
 (10,2,5,'Tuesday',1,'08:00','08:40','Block A / JSS 1','2025/2026','First'),
 (10,8,4,'Tuesday',2,'08:40','09:20','Science Lab','2025/2026','First'),
 (10,1,4,'Wednesday',1,'08:00','08:40','Block A / JSS 1','2025/2026','First'),
 (10,4,5,'Thursday',1,'08:00','08:40','Block A / JSS 1','2025/2026','First'),
 (10,15,5,'Friday',1,'08:00','08:40','Sports field','2025/2026','First');

INSERT IGNORE INTO announcements (id, title, body, audience, created_by) VALUES
 (1,'Resumption for the first term','School resumes on Monday 15 September 2025. Students should report in full uniform with all their books.','public',1),
 (2,'Inter-house sports festival','Our annual inter-house sports festival holds on 28 November 2025 at the school sports complex. Parents are warmly invited.','public',1),
 (3,'Parent–teacher meeting','The termly parent–teacher meeting holds on Saturday 8 November 2025 at 10:00 a.m.','public',1);

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
 ('school_phone','+234 800 000 0000'),
 ('school_email','info@plusinternationalschool.ng'),
 ('school_address','Tunga, Minna, Niger State, Nigeria'),
 ('principal_name','Dr. Samuel Adeyemi'),
 ('results_locked','0'),
 ('payment_notice','Fees may be paid online with Paystack or Remita, or at the school bursary.');
