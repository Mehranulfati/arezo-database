<?php
require_once __DIR__.'/../config/config.php'; require_admin();
$msg=''; $err='';
function staff_upload_one($file){
 if(empty($file['name']) || ($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) return '';
 $ext=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
 if(!in_array($ext,['pdf','doc','docx','jpg','jpeg','png','webp'],true)) throw new Exception('فرمت فایل مجاز نیست.');
 $dir=__DIR__.'/../uploads/'; if(!is_dir($dir)) @mkdir($dir,0775,true);
 $fn=uniqid('staff_',true).'.'.$ext; if(!move_uploaded_file($file['tmp_name'],$dir.$fn)) throw new Exception('آپلود فایل انجام نشد.'); return $fn;
}
function staff_upload($field){ return staff_upload_one($_FILES[$field]??[]); }
function staff_upload_many($field){
 $out=[]; if(empty($_FILES[$field]['name']) || !is_array($_FILES[$field]['name'])) return $out;
 foreach($_FILES[$field]['name'] as $i=>$name){
  if($name==='') continue;
  $file=['name'=>$name,'type'=>$_FILES[$field]['type'][$i]??'','tmp_name'=>$_FILES[$field]['tmp_name'][$i]??'','error'=>$_FILES[$field]['error'][$i]??UPLOAD_ERR_NO_FILE,'size'=>$_FILES[$field]['size'][$i]??0];
  $out[]=staff_upload_one($file);
 } return $out;
}
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='reorder') {
 try { $ids=$_POST['teacher_ids']??[]; foreach(array_values($ids) as $pos=>$tid){$pdo->prepare('UPDATE teachers SET sort_order=? WHERE id=?')->execute([$pos+1,(int)$tid]);} header('Location:index.php?reordered=1'); exit; } catch(Exception $e){$err=$e->getMessage();}
}
if(isset($_GET['delete_teacher'])){ $did=(int)$_GET['delete_teacher']; $pdo->prepare('DELETE FROM teachers WHERE id=?')->execute([$did]); header('Location:index.php'); exit; }
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  $action=$_POST['action']??'staff';
  if($action==='staff'){
   $photo=staff_upload('photo');
   $_salary=$_POST['salary']!==''?$_POST['salary']:0;
   $max=(int)$pdo->query('SELECT COALESCE(MAX(CAST(staff_code AS UNSIGNED)),0) FROM teachers WHERE staff_code REGEXP "^[0-9]+$"')->fetchColumn();
   $staffCode=trim($_POST['staff_code']??''); if($staffCode==='')$staffCode=str_pad((string)($max+1),2,'0',STR_PAD_LEFT);
   $nextOrder=(int)$pdo->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM teachers')->fetchColumn();
   $pdo->prepare('INSERT INTO teachers(staff_code,sort_order,name,last_name,father_name,phone,duty,education_level,salary,hire_date,photo,activities_done,suggestions,report_goal,report_plan,report_month) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute([
    $staffCode,$nextOrder,trim($_POST['name']),trim($_POST['last_name']),trim($_POST['father_name']),trim($_POST['phone']),trim($_POST['duty']),trim($_POST['education_level']),$_salary,$_POST['hire_date']?:null,$photo,'','','','',''
   ]);
   $tid=(int)$pdo->lastInsertId();
   foreach(staff_upload_many('contract') as $fn)$pdo->prepare('INSERT INTO employee_documents(teacher_id,document_type,title,file_path) VALUES(?,?,?,?)')->execute([$tid,'قرارداد خط','قرارداد خط',$fn]);
   foreach(staff_upload_many('education_documents') as $fn)$pdo->prepare('INSERT INTO employee_documents(teacher_id,document_type,title,file_path) VALUES(?,?,?,?)')->execute([$tid,'سند تحصیلی','سند تحصیلی',$fn]);
   foreach(staff_upload_many('duties') as $fn)$pdo->prepare('INSERT INTO employee_documents(teacher_id,document_type,title,file_path) VALUES(?,?,?,?)')->execute([$tid,'لایحه وظایف','لایحه وظایف',$fn]);
   header('Location: edit.php?id='.$tid.'&saved=1#report-form'); exit;
  }elseif($action==='report'){
   $tid=(int)($_POST['teacher_id']??0);
   $activities=trim($_POST['activities_done']??'');
   $suggestions=trim($_POST['suggestions']??'');
   $report_month=trim($_POST['report_month']??'');
   if(!$tid || ($activities==='' && $suggestions==='')) throw new Exception('گزارش خالی نمی‌تواند ثبت شود.');
   $pdo->prepare('INSERT INTO teacher_reports(teacher_id,activities_done,suggestions,report_month) VALUES(?,?,?,?)')->execute([$tid,$activities,$suggestions,$report_month]);
   $msg='گزارش کارمند / معلم ثبت شد.';
  }elseif($action==='delete_report'){
   $rid=(int)($_POST['report_id']??0);
   if($rid) $pdo->prepare('DELETE FROM teacher_reports WHERE id=?')->execute([$rid]);
   $msg='گزارش حذف شد.';
  }
 }catch(Exception $e){$err=$e->getMessage();}
}
$q=trim($_GET['q']??''); $sql='SELECT * FROM teachers'; $args=[];
if($q){$sql.=' WHERE name LIKE ? OR last_name LIKE ? OR father_name LIKE ? OR phone LIKE ? OR duty LIKE ? OR education_level LIKE ?';$x="%$q%";$args=[$x,$x,$x,$x,$x,$x];}
$sql.=' ORDER BY COALESCE(sort_order,999999), id ASC';$st=$pdo->prepare($sql);$st->execute($args);$rows=$st->fetchAll();
$reportTeacher=null; $teacherReports=[];
if(isset($_GET['report'])){ $rid=(int)$_GET['report']; $rs=$pdo->prepare('SELECT * FROM teachers WHERE id=?');$rs->execute([$rid]);$reportTeacher=$rs->fetch(); if($reportTeacher){$rr=$pdo->prepare('SELECT * FROM teacher_reports WHERE teacher_id=? ORDER BY id DESC');$rr->execute([$rid]);$teacherReports=$rr->fetchAll();} }
require '../partials/header.php';
?>
<?php if($q===''):?><div class="panel form-panel"><div class="section-title"><div><h2>بخش کارمندان / معلمان</h2><span class="muted">ثبت اطلاعات، قرارداد خط، لایحه وظایف و ثبت گزارش هر کارمند / معلم.</span></div></div>
<?php if($msg):?><div class="badge">✓ <?=h($msg)?></div><?php endif;?><?php if($err):?><div class="alert">⚠ <?=h($err)?></div><?php endif;?>
<form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="staff"><div class="row">
<label>شماره ID<input value="<?=h($_POST['staff_code']??str_pad((string)(((int)$pdo->query('SELECT COALESCE(MAX(CAST(staff_code AS UNSIGNED)),0) FROM teachers WHERE staff_code REGEXP "^[0-9]+$"')->fetchColumn())+1),2,'0',STR_PAD_LEFT))?>" ></label><label>نام *<input required name="name"></label><label>تخلص<input name="last_name"></label><label>نام پدر<input name="father_name"></label><label>عنوان وظیفه<input name="duty"></label><label>درجه تحصیل<input name="education_level"></label><label>معاش ماهوار (افغانی)<input type="number" step="0.01" min="0" name="salary"></label><label>تاریخ استخدام<input type="date" name="hire_date"></label><label>عکس<input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
<label>آپلود قرارداد خط (چند فایل، سال‌های مختلف)<input type="file" name="contract[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp"></label><label>آپلود لایحه وظایف *<input type="file" name="duties[]" required multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp"></label><label>شماره تماس<input name="phone"></label><label class="full-row">آپلود اسناد تحصیلی (حداکثر ۲ فایل)<input type="file" name="education_documents[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp"></label>
</div><div class="actions"><button>ثبت کارمند / معلم</button></div></form></div>
<?php else: ?><div class="panel"><div class="section-title"><h2>کارمندان / معلمان ثبت شده</h2><span class="muted">نتیجه جستجو در فهرست زیر نمایش داده می‌شود.</span></div></div><?php endif; ?>
<?php if($reportTeacher): ?>
<div class="panel form-panel report-card" id="report-form">
<div class="section-title"><div><h2>گزارشات و پیشنهادات استاد</h2><span class="muted"><?=h(trim($reportTeacher['name'].' '.($reportTeacher['last_name']??'')))?></span></div><a class="btn secondary" href="index.php">بستن</a></div>
<form method="post" class="report-form-grid"><input type="hidden" name="action" value="report"><input type="hidden" name="teacher_id" value="<?=$reportTeacher['id']?>">
<label>از بابت ماه *<input required name="report_month" placeholder="مثلاً سنبله ۱۴۰۵"></label><label>فعالیت‌های انجام شده<textarea name="activities_done" rows="12" required placeholder="فعالیت‌های انجام شده را بنویسید..." ></textarea></label>
<label>پیشنهادات<textarea name="suggestions" rows="12" required placeholder="پیشنهادات خود را بنویسید..." ></textarea></label>
<div class="actions report-save"><button>ثبت گزارش</button></div></form>
<?php if($teacherReports): ?><div class="saved-reports"><h3>گزارش‌های ثبت‌شده این استاد</h3><?php foreach($teacherReports as $rep): ?><article class="saved-report"><div><b>از بابت ماه</b><p><?=h($rep['report_month']??'—')?></p><b>فعالیت‌های انجام شده</b><p><?=nl2br(h($rep['activities_done']??''))?></p><b>پیشنهادات</b><p><?=nl2br(h($rep['suggestions']??''))?></p></div><form method="post" onsubmit="return confirm('آیا از حذف این گزارش مطمئن هستید؟')"><input type="hidden" name="action" value="delete_report"><input type="hidden" name="report_id" value="<?=$rep['id']?>"><button class="btn danger" type="submit">حذف</button></form></article><?php endforeach; ?></div><?php endif; ?>
</div>
<?php endif; ?>
<div class="panel registered-staff-band"><a class="btn" href="#registered-staff">👨‍🏫 کارمندان و معلمان ثبت شده</a></div><div class="panel table-panel" id="registered-staff"><div class="section-title"><div><h3>کارمندان و معلمان ثبت شده</h3><span class="muted">برای هر استاد، روی «ثبت گزارش» بزنید تا فعالیت‌های انجام شده و پیشنهادات خود را بنویسد.</span></div></div><form class="searchbar"><input name="q" value="<?=h($q)?>" placeholder="جستجو نام، تخلص، عنوان وظیفه یا شماره تماس"><button>جستجو</button></form><form method="post" id="reorder-form"><input type="hidden" name="action" value="reorder"><div class="table-scroll"><table><tr><th>جابه‌جایی</th><th>شماره ID</th><th>عکس</th><th>نام</th><th>تخلص</th><th>نام پدر</th><th>عنوان وظیفه</th><th>درجه تحصیل</th><th>معاش</th><th>ویرایش / گزارش</th><th>کارت</th><th>حذف</th></tr><?php foreach($rows as $idx=>$r):?><tr draggable="true" data-id="<?=$r['id']?>"><td><button type="button" class="move-up" title="بالا">↑</button> <button type="button" class="move-down" title="پایین">↓</button></td><td><?=h($r['staff_code']??'')?></td><td><?php if(!empty($r['photo'])&&is_file('../uploads/'.$r['photo'])):?><img class="thumb" src="../uploads/<?=h($r['photo'])?>"><?php else:?><div class="thumb">—</div><?php endif;?></td><td><?=h($r['name'])?></td><td><?=h($r['last_name']??'')?></td><td><?=h($r['father_name'])?></td><td><?=h($r['duty']??'')?></td><td><?=h($r['education_level']??'')?></td><td><?=money($r['salary'])?></td><td><a class="btn secondary" href="edit.php?id=<?=$r['id']?>">✏️ ویرایش</a> <a class="btn report-btn" href="?report=<?=$r['id']?>#report-form">📝 گزارش</a></td><td><a class="btn secondary" href="../cards/teacher.php?id=<?=$r['id']?>">کارت</a></td><td><a class="btn danger" href="?delete_teacher=<?=$r['id']?>" onclick="return confirm('این کارمند / معلم حذف شود؟')">🗑 حذف</a></td></tr><?php endforeach;?></table></div><div class="actions"><button type="submit">💾 ذخیره ترتیب کارمندان</button></div></form></div>
<script>(function(){const form=document.getElementById("reorder-form"); if(!form)return; const table=form.querySelector("tbody")||form.querySelector("table"); function sync(){form.querySelectorAll("input[name='teacher_ids[]']").forEach(e=>e.remove());form.querySelectorAll("tr[data-id]").forEach(tr=>{const i=document.createElement("input");i.type="hidden";i.name="teacher_ids[]";i.value=tr.dataset.id;form.appendChild(i);});} function rows(){return [...form.querySelectorAll("tr[data-id]")];} form.addEventListener("click",e=>{const tr=e.target.closest("tr[data-id]");if(!tr)return; if(e.target.classList.contains("move-up")){const prev=tr.previousElementSibling;if(prev&&prev.dataset.id)prev.before(tr);} if(e.target.classList.contains("move-down")){const next=tr.nextElementSibling;if(next&&next.dataset.id)next.after(tr);} sync();}); let drag=null; form.addEventListener("dragstart",e=>{drag=e.target.closest("tr[data-id]");if(drag)drag.classList.add("dragging");});form.addEventListener("dragend",()=>{if(drag)drag.classList.remove("dragging");drag=null;sync();});form.addEventListener("dragover",e=>{e.preventDefault();const tr=e.target.closest("tr[data-id]");if(!drag||!tr||tr===drag)return;const rect=tr.getBoundingClientRect();if(e.clientY<rect.top+rect.height/2)tr.before(drag);else tr.after(drag);});form.addEventListener("submit",sync);sync();})();</script>
<?php require '../partials/footer.php';?>