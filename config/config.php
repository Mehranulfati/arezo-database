<?php
session_start();
$host='127.0.0.1'; $db='arzo_course_manager'; $user='root'; $pass='';
try{
 $pdo=new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4",$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
}catch(Exception $e){die('اتصال به MySQL برقرار نشد. ابتدا MySQL را در XAMPP روشن و دیتابیس arzo_course_manager را ایجاد کنید.');}
function h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function setting($key){global $pdo;static $s;if($s===null)$s=$pdo->query('SELECT * FROM settings WHERE id=1')->fetch()?:[];return $s[$key]??'';}
function is_logged_in(){return !empty($_SESSION['user_id']);}
function current_user(){return $_SESSION['user_role']??'';}
function is_admin(){return is_logged_in() && current_user()==='admin';}
function is_teacher(){return is_logged_in() && current_user()==='teacher';}
function require_login(){if(!is_logged_in()){header('Location: '.base_url().'login.php');exit;}}
function require_admin(){if(!is_admin()){if(is_teacher()){header('Location: '.base_url().'teacher_portal.php');}else{header('Location: '.base_url().'login.php');}exit;}}
function require_teacher(){if(!is_teacher()){if(is_admin()){header('Location: '.base_url().'index.php');}else{header('Location: '.base_url().'login.php');}exit;}}
function base_url(){return '/Arezo%20Databass/';}
function office_phone(){ return trim((string)setting('phone')); }
function course_address(){ return trim((string)setting('address')); }
function money($n){return number_format((float)$n,0).' افغانی';}
function money_usd($n){return number_format((float)$n,0).' افغانی';}
function logo_url(){ $l=setting('logo'); return $l ? base_url().'uploads/'.rawurlencode($l) : base_url().'assets/logo.png'; }
function manager_photo_url(){ $l=setting('manager_photo'); return $l ? base_url().'uploads/'.rawurlencode($l) : logo_url(); }
ensure_column('settings','manager_name','VARCHAR(150) NULL');
ensure_column('settings','time_adjust_minutes','INT NOT NULL DEFAULT 0');
ensure_column('settings','date_adjust_days','INT NOT NULL DEFAULT 0');
ensure_column('settings','manager_photo','VARCHAR(255) NULL');
function column_exists($table,$column){global $pdo;$st=$pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?");$st->execute([$table,$column]);return (bool)$st->fetchColumn();}
function ensure_column($table,$column,$definition){global $pdo;if(!column_exists($table,$column)){try{$pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");}catch(Exception $e){}}}
function ensure_table($sql){global $pdo;try{$pdo->exec($sql);}catch(Exception $e){}}

// Create the small tables used by the current interface when an older database is installed.
ensure_column('users','role',"VARCHAR(30) NOT NULL DEFAULT 'admin'");
ensure_column('users','teacher_id','INT NULL');
try{$pdo->exec("UPDATE users SET role='admin' WHERE role IS NULL OR role=''");}catch(Exception $e){}
ensure_table("CREATE TABLE IF NOT EXISTS teacher_reports (id INT AUTO_INCREMENT PRIMARY KEY, teacher_id INT NOT NULL, report_date DATE NULL, file_path VARCHAR(255) NULL, activities_done TEXT NULL, suggestions TEXT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
ensure_column('teacher_reports','activities_done','TEXT NULL');
ensure_column('teacher_reports','suggestions','TEXT NULL');
ensure_column('teacher_reports','report_month','VARCHAR(100) NULL');
try{$pdo->exec("ALTER TABLE teacher_reports MODIFY file_path VARCHAR(255) NULL");}catch(Exception $e){}
ensure_table("CREATE TABLE IF NOT EXISTS salary_records (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, father_name VARCHAR(150), duty VARCHAR(150), education_level VARCHAR(150), deduction DECIMAL(12,2) DEFAULT 0, payable DECIMAL(12,2) DEFAULT 0, note VARCHAR(255), pay_month VARCHAR(50), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
ensure_column('salary_records','signature_path','VARCHAR(255) NULL');
ensure_table("CREATE TABLE IF NOT EXISTS expense_items (id INT AUTO_INCREMENT PRIMARY KEY, expense_date DATE NOT NULL, expense_type VARCHAR(150) NOT NULL, quantity DECIMAL(12,2) DEFAULT 0, price DECIMAL(12,2) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
ensure_column('expense_items','note','VARCHAR(255) NULL');
ensure_column('expense_items','expense_item_name','VARCHAR(255) NULL');
ensure_table("CREATE TABLE IF NOT EXISTS money_receipts (id INT AUTO_INCREMENT PRIMARY KEY, received_date DATE NOT NULL, amount DECIMAL(12,2) DEFAULT 0, subject VARCHAR(255) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
ensure_table("CREATE TABLE IF NOT EXISTS receipt_files (id INT AUTO_INCREMENT PRIMARY KEY, receipt_id INT NOT NULL, file_path VARCHAR(255) NOT NULL, original_name VARCHAR(255) NULL, uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
ensure_table("CREATE TABLE IF NOT EXISTS admin_documents (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

// Compatibility with databases created by previous versions.
ensure_column('settings','manager_name','VARCHAR(150) NULL');
ensure_column('settings','manager_photo','VARCHAR(255) NULL');
ensure_column('health_exams','student_class','VARCHAR(100) NULL');
ensure_column('health_exams','study_time','VARCHAR(100) NULL');
ensure_column('health_exams','disease_type','VARCHAR(200) NULL');
ensure_column('health_exams','report_month','VARCHAR(100) NULL');
ensure_column('salary_records','last_name','VARCHAR(150) NULL AFTER name');
ensure_column('teachers','staff_code','VARCHAR(20) NULL AFTER id');
ensure_column('teachers','sort_order','INT NULL');
ensure_column('teachers','last_name','VARCHAR(150) NULL AFTER name');
ensure_column('teachers','duty','VARCHAR(150) NULL AFTER phone');
ensure_column('teachers','education_level','VARCHAR(150) NULL AFTER duty');
ensure_column('teachers','activities_done','TEXT NULL');
ensure_column('teachers','suggestions','TEXT NULL');
ensure_column('teachers','report_goal','TEXT NULL');
ensure_column('teachers','report_plan','TEXT NULL');
ensure_column('teachers','report_month','VARCHAR(50) NULL');
try{$rowsForCodes=$pdo->query("SELECT id FROM teachers WHERE staff_code IS NULL OR staff_code='' ORDER BY id ASC")->fetchAll();$maxCode=(int)$pdo->query("SELECT COALESCE(MAX(CAST(staff_code AS UNSIGNED)),0) FROM teachers WHERE staff_code REGEXP '^[0-9]+$'")->fetchColumn();foreach($rowsForCodes as $rc){$maxCode++;$pdo->prepare('UPDATE teachers SET staff_code=? WHERE id=?')->execute([str_pad((string)$maxCode,2,'0',STR_PAD_LEFT),(int)$rc['id']]);}}catch(Exception $e){}
try{$rowsForOrder=$pdo->query("SELECT id FROM teachers WHERE sort_order IS NULL ORDER BY id ASC")->fetchAll();$maxOrder=(int)$pdo->query('SELECT COALESCE(MAX(sort_order),0) FROM teachers')->fetchColumn();foreach($rowsForOrder as $ro){$maxOrder++;$pdo->prepare('UPDATE teachers SET sort_order=? WHERE id=?')->execute([$maxOrder,(int)$ro['id']]);}}catch(Exception $e){}
ensure_table("CREATE TABLE IF NOT EXISTS employee_documents (id INT AUTO_INCREMENT PRIMARY KEY, teacher_id INT NOT NULL, document_type VARCHAR(100) NOT NULL, title VARCHAR(200), file_path VARCHAR(255), uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
try{$pdo->exec("ALTER TABLE employee_documents MODIFY document_type VARCHAR(100) NOT NULL");}catch(Exception $e){}
ensure_column('monthly_reports','title','VARCHAR(200) NULL');
ensure_column('monthly_reports','report_text','TEXT NULL');
ensure_table("CREATE TABLE IF NOT EXISTS monthly_reports (id INT AUTO_INCREMENT PRIMARY KEY, report_month VARCHAR(30) NOT NULL, department VARCHAR(150) NULL, title VARCHAR(200) NULL, report_text TEXT NULL, file_path VARCHAR(255) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
ensure_column('monthly_reports','report_month','VARCHAR(30) NULL');
ensure_column('monthly_reports','department','VARCHAR(150) NULL');
ensure_column('monthly_reports','title','VARCHAR(200) NULL');
ensure_column('monthly_reports','report_text','TEXT NULL');
ensure_column('monthly_reports','file_path','VARCHAR(255) NULL');


// V6 defaults and terminology migrations.
try{
 $old=$pdo->query("SELECT center_name FROM settings WHERE id=1")->fetchColumn();
 if(!$old || in_array($old,['کورس آموزشی آرزو','کورس آموزشی و صحی غیر انتفاعی - آرزو','کورس آموزشی و صحی غیر انتفاعی وسیمه فیضی','کورس آموزشی و صحی وسیمه فیضی'],true)){
  $pdo->prepare('UPDATE settings SET center_name=?,short_name=? WHERE id=1')->execute(['کورس آموزشی خصوصی غیر انتفاعی وسیمه فیضی','کورس آموزشی وسیمه فیضی']);
 }
}catch(Exception $e){}
try{
 if(column_exists('students','course_type')){
  $pdo->exec("UPDATE students SET course_type='صنف درسی' WHERE course_type='صنف آموزشی'");
  $pdo->exec("ALTER TABLE students MODIFY course_type ENUM('صنف درسی','حرفه خیاطی') NOT NULL DEFAULT 'صنف درسی'");
 }
}catch(Exception $e){}
try{$n=(int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();if($n===0){$st=$pdo->prepare('INSERT INTO users(username,password_hash,full_name,active) VALUES(?,?,?,1)');$st->execute(['admin',password_hash('Admin@12345',PASSWORD_DEFAULT),'مدیر کورس']);}}catch(Exception $e){}
try{$has=$pdo->query("SELECT db_access_code_hash FROM settings WHERE id=1")->fetchColumn();if(!$has)$pdo->prepare('UPDATE settings SET db_access_code_hash=? WHERE id=1')->execute([password_hash('ARZO-DB-2026',PASSWORD_DEFAULT)]);}catch(Exception $e){}
?>
