CREATE DATABASE IF NOT EXISTS arzo_course_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE arzo_course_manager;

CREATE TABLE IF NOT EXISTS settings (
 id INT PRIMARY KEY, center_name VARCHAR(255) NOT NULL, short_name VARCHAR(100) DEFAULT 'آرزو', phone VARCHAR(60), address VARCHAR(255), admin_phone VARCHAR(60), manager_name VARCHAR(150), manager_photo VARCHAR(255), logo VARCHAR(255), about_text TEXT, goals_text TEXT, plan_text TEXT, org_text TEXT, db_access_code_hash VARCHAR(255), time_adjust_minutes INT NOT NULL DEFAULT 0, date_adjust_days INT NOT NULL DEFAULT 0
);
INSERT INTO settings(id,center_name,short_name,phone,about_text,goals_text,plan_text,org_text) VALUES
(1,'کورس آموزشی و صحی غیر انتفاعی وسیمه فیضی','وسیمه فیضی','',
'کورس آموزشی و صحی غیر انتفاعی - آرزو یک مرکز رایگان برای آموزش، توانمندسازی و ارائه خدمات صحی حمایتی به دانش‌آموزان است.',
'فراهم‌کردن آموزش رایگان، رشد مهارت‌های آموزشی و حرفه‌ای، حمایت صحی و ایجاد محیط سالم و منظم برای دانش‌آموزان.',
'ثبت و مدیریت منظم دانش‌آموزان، آموزش صنفی و حرفه خیاطی، فعالیت‌های عملی، خدمات صحی، مدیریت اداری و گزارش‌دهی ماهوار.',
'مدیریت کورس، بخش کارمندان و معلمان، بخش دانش‌آموزان، آموزش حرفه‌ای خیاطی، آموزش و خدمات صحی و اسناد اداری.')
ON DUPLICATE KEY UPDATE id=id;

CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(80) UNIQUE NOT NULL, password_hash VARCHAR(255) NOT NULL, full_name VARCHAR(150) NOT NULL, active TINYINT(1) DEFAULT 1, role VARCHAR(30) NOT NULL DEFAULT 'admin', teacher_id INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS students (
 id INT AUTO_INCREMENT PRIMARY KEY, student_code VARCHAR(50) UNIQUE NOT NULL, first_name VARCHAR(100) NOT NULL, father_name VARCHAR(100), guardian_phone VARCHAR(60), course_type ENUM('صنف درسی','حرفه خیاطی') NOT NULL DEFAULT 'صنف درسی', class_level VARCHAR(100), study_time ENUM('تایم اول','تایم دوم') NOT NULL DEFAULT 'تایم اول', course_number VARCHAR(100), photo VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS teachers (
 id INT AUTO_INCREMENT PRIMARY KEY, staff_code VARCHAR(20) UNIQUE, sort_order INT NULL, name VARCHAR(150) NOT NULL, last_name VARCHAR(150), father_name VARCHAR(150), phone VARCHAR(60), duty VARCHAR(150), education_level VARCHAR(150), department VARCHAR(120), salary DECIMAL(12,2) DEFAULT 0, hire_date DATE NULL, photo VARCHAR(255), activities_done TEXT, suggestions TEXT, report_goal TEXT, report_plan TEXT, report_month VARCHAR(50), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS employee_documents (
 id INT AUTO_INCREMENT PRIMARY KEY, teacher_id INT NOT NULL, document_type VARCHAR(100) NOT NULL, title VARCHAR(200), file_path VARCHAR(255), uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS expenses (id INT AUTO_INCREMENT PRIMARY KEY, expense_date DATE NOT NULL, category VARCHAR(100) NOT NULL, amount DECIMAL(12,2) NOT NULL, description VARCHAR(255), paid_to VARCHAR(150));
CREATE TABLE IF NOT EXISTS payroll (id INT AUTO_INCREMENT PRIMARY KEY, teacher_id INT NOT NULL, pay_month VARCHAR(30) NOT NULL, amount DECIMAL(12,2) NOT NULL, paid_date DATE NULL, note VARCHAR(255), FOREIGN KEY(teacher_id) REFERENCES teachers(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS tailoring_training (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, training_date DATE, topic VARCHAR(200), practical_work TEXT, result VARCHAR(200), teacher VARCHAR(150), FOREIGN KEY(student_id) REFERENCES students(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS tailoring_products (id INT AUTO_INCREMENT PRIMARY KEY, product_date DATE, title VARCHAR(200), description TEXT, student_id INT NULL, photo VARCHAR(255), FOREIGN KEY(student_id) REFERENCES students(id) ON DELETE SET NULL);
CREATE TABLE IF NOT EXISTS health_education (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, service_date DATE, topic VARCHAR(200), content TEXT, provider VARCHAR(150), FOREIGN KEY(student_id) REFERENCES students(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS health_exams (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, exam_date DATE, result TEXT, provider VARCHAR(150), student_class VARCHAR(100), study_time VARCHAR(100), disease_type VARCHAR(200), FOREIGN KEY(student_id) REFERENCES students(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS prescriptions (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, prescription_date DATE, medicine_name VARCHAR(200), dosage VARCHAR(150), instructions TEXT, prescriber VARCHAR(150), FOREIGN KEY(student_id) REFERENCES students(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS health_reports (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, report_date DATE, file_path VARCHAR(255), description TEXT, uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS legal_docs (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, doc_date DATE, reference_no VARCHAR(100), description TEXT, file_path VARCHAR(255));
CREATE TABLE IF NOT EXISTS monthly_reports (id INT AUTO_INCREMENT PRIMARY KEY, report_month VARCHAR(30) NOT NULL, department VARCHAR(150), title VARCHAR(200), report_text TEXT, file_path VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);


CREATE TABLE IF NOT EXISTS teacher_reports (id INT AUTO_INCREMENT PRIMARY KEY, teacher_id INT NOT NULL, report_date DATE NULL, file_path VARCHAR(255) NULL, activities_done TEXT NULL, suggestions TEXT NULL, report_month VARCHAR(100) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(teacher_id) REFERENCES teachers(id) ON DELETE CASCADE);
CREATE TABLE IF NOT EXISTS salary_records (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, last_name VARCHAR(150), father_name VARCHAR(150), duty VARCHAR(150), education_level VARCHAR(150), deduction DECIMAL(12,2) DEFAULT 0, payable DECIMAL(12,2) DEFAULT 0, note VARCHAR(255), pay_month VARCHAR(50), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS expense_items (id INT AUTO_INCREMENT PRIMARY KEY, expense_date DATE NOT NULL, expense_type VARCHAR(150) NOT NULL, quantity DECIMAL(12,2) DEFAULT 0, price DECIMAL(12,2) DEFAULT 0, note VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);

CREATE TABLE IF NOT EXISTS admin_documents (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
