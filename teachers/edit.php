<?php
require_once __DIR__.'/../config/config.php'; require_admin();
$id=(int)($_GET['id']??0); $st=$pdo->prepare('SELECT * FROM teachers WHERE id=?'); $st->execute([$id]); $teacher=$st->fetch();
if(!$teacher) die('کارمند / معلم پیدا نشد.');
$msg=isset($_GET['saved'])?'کارمند / معلم با موفقیت ثبت شد.':''; $err='';
function teacher_edit_upload_one($file){
 if(empty($file['name']) || ($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) return '';
 $f=$file; $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
 if(!in_array($ext,['pdf','doc','docx','jpg','jpeg','png','webp'],true)) throw new Exception('فرمت فایل مجاز نیست.');
 $dir=__DIR__.'/../uploads/'; if(!is_dir($dir)) @mkdir($dir,0775,true);
 $fn=uniqid('staff_',true).'.'.$ext; if(!move_uploaded_file($f['tmp_name'],$dir.$fn)) throw new Exception('آپلود فایل انجام نشد.'); return $fn;
}
function teacher_edit_upload_many($field){$out=[];if(empty($_FILES[$field]['name'])||!is_array($_FILES[$field]['name']))return $out;foreach($_FILES[$field]['name'] as $i=>$name){if($name==='')continue;$out[]=teacher_edit_upload_one(['name'=>$name,'type'=>$_FILES[$field]['type'][$i]??'','tmp_name'=>$_FILES[$field]['tmp_name'][$i]??'','error'=>$_FILES[$field]['error'][$i]??UPLOAD_ERR_NO_FILE,'size'=>$_FILES[$field]['size'][$i]??0]);}return $out;}
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  $action=$_POST['action']??'update';
  if($action==='update'){
   $photo=$teacher['photo']??'';
   $newPhoto=teacher_edit_upload_one($_FILES['photo']??[]); if($newPhoto) $photo=$newPhoto;
   $salary=$_POST['salary']!==''?$_POST['salary']:0;
   $pdo->prepare('UPDATE teachers SET staff_code=?,name=?,last_name=?,father_name=?,phone=?,duty=?,education_level=?,salary=?,hire_date=?,photo=? WHERE id=?')->execute([
    trim($_POST['staff_code']),trim($_POST['name']),trim($_POST['last_name']),trim($_POST['father_name']),trim($_POST['phone']),trim($_POST['duty']),trim($_POST['education_level']),$salary,$_POST['hire_date']?:null,$photo,$id
   ]);
   foreach(teacher_edit_upload_many('contract') as $fn)$pdo->prepare('INSERT INTO employee_documents(teacher_id,document_type,title,file_path) VALUES(?,?,?,?)')->execute([$id,'قرارداد خط','قرارداد خط',$fn]);
   foreach(teacher_edit_upload_many('education_documents') as $fn)$pdo->prepare('INSERT INTO employee_documents(teacher_id,document_type,title,file_path) VALUES(?,?,?,?)')->execute([$id,'سند تحصیلی','سند تحصیلی',$fn]);
   foreach(teacher_edit_upload_many('duties') as $fn)$pdo->prepare('INSERT INTO employee_documents(teacher_id,document_type,title,file_path) VALUES(?,?,?,?)')->execute([$id,'لایحه وظایف','لایحه وظایف',$fn]);
   $st=$pdo->prepare('SELECT * FROM teachers WHERE id=?');$st->execute([$id]);$teacher=$st->fetch(); $msg='اطلاعات استاد ویرایش شد.';
  }elseif($action==='report'){
   $activities=trim($_POST['activities_done']??''); $suggestions=trim($_POST['suggestions']??''); $report_month=trim($_POST['report_month']??''); $report_month=trim($_POST['report_month']??'');
   if($activities==='' && $suggestions==='') throw new Exception('گزارش خالی نمی‌تواند ثبت شود.');
   $pdo->prepare('INSERT INTO teacher_reports(teacher_id,activities_done,suggestions,report_month) VALUES(?,?,?,?)')->execute([$id,$activities,$suggestions,$report_month]);
   $msg='گزارش جدید ثبت شد.';
  }elseif($action==='delete_report'){
   $rid=(int)($_POST['report_id']??0); if($rid)$pdo->prepare('DELETE FROM teacher_reports WHERE id=? AND teacher_id=?')->execute([$rid,$id]);
   $msg='گزارش حذف شد.';
  }
 }catch(Exception $e){$err=$e->getMessage();}
}
$rr=$pdo->prepare('SELECT * FROM teacher_reports WHERE teacher_id=? ORDER BY id DESC');$rr->execute([$id]);$reports=$rr->fetchAll();
require '../partials/header.php';
$name=trim($teacher['name'].' '.($teacher['last_name']??''));
?>
<div class="panel form-panel">
 <div class="section-title"><div><h2>ویرایش کارمند / معلم</h2><span class="badge">شماره ID: <?=h($teacher['staff_code']??'')?></span><span class="muted"><?=h($name)?> — در همین صفحه می‌توانید گزارش‌های جدید استاد را ثبت کنید.</span></div><a class="btn secondary" href="index.php">بازگشت</a></div>
 <?php if($msg):?><div class="badge">✓ <?=h($msg)?></div><?php endif;?><?php if($err):?><div class="alert">⚠ <?=h($err)?></div><?php endif;?>
 <form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="update"><div class="row">
  <label>شماره ID *<input required name="staff_code" value="<?=h($teacher['staff_code']??'')?>"></label><label>نام *<input required name="name" value="<?=h($teacher['name'])?>"></label><label>تخلص<input name="last_name" value="<?=h($teacher['last_name']??'')?>"></label><label>نام پدر<input name="father_name" value="<?=h($teacher['father_name']??'')?>"></label><label>عنوان وظیفه<input name="duty" value="<?=h($teacher['duty']??'')?>"></label><label>درجه تحصیل<input name="education_level" value="<?=h($teacher['education_level']??'')?>"></label><label>معاش ماهوار (افغانی)<input type="number" step="0.01" min="0" name="salary" value="<?=h($teacher['salary']??'')?>"></label><label>تاریخ استخدام<input type="date" name="hire_date" value="<?=h($teacher['hire_date']??'')?>"></label><label>عکس جدید<input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
  <label>آپلود قرارداد خط (چند فایل، سال‌های مختلف)<input type="file" name="contract[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp"></label><label>آپلود لایحه وظایف<input type="file" name="duties[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp"></label><label>شماره تماس<input name="phone" value="<?=h($teacher['phone']??'')?>"></label>
  <label class="full-row">آپلود اسناد تحصیلی (حداکثر ۲ فایل)<input type="file" name="education_documents[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp"></label>
 </div><div class="actions"><button>ذخیره تغییرات</button><a class="btn secondary" href="../cards/teacher.php?id=<?=$id?>">کارت استاد</a></div></form>
</div>
<div class="panel form-panel report-card" id="report-form">
 <div class="section-title"><div><h2>گزارشات و پیشنهادات استاد</h2><span class="muted">برای هر بار گزارش جدید، فرم زیر را تکمیل کنید. گزارش‌های قبلی نیز در همین صفحه باقی می‌مانند.</span></div></div>
 <form method="post" class="report-form-grid"><input type="hidden" name="action" value="report">
  <label>از بابت ماه *<input required name="report_month" placeholder="مثلاً سنبله ۱۴۰۵"></label><label>فعالیت‌های انجام شده<textarea name="activities_done" rows="10" required placeholder="فعالیت‌های انجام شده را بنویسید..."></textarea></label>
  <label>پیشنهادات<textarea name="suggestions" rows="10" required placeholder="پیشنهادات خود را بنویسید..."></textarea></label>
  <div class="actions report-save"><button>➕ ثبت گزارش جدید</button></div>
 </form>
 <?php if($reports):?><div class="saved-reports"><h3>گزارش‌های ثبت‌شده</h3><?php foreach($reports as $rep):?><article class="saved-report"><div><b>از بابت ماه</b><p><?=h($rep['report_month']??'—')?></p><b>فعالیت‌های انجام شده</b><p><?=nl2br(h($rep['activities_done']??''))?></p><b>پیشنهادات</b><p><?=nl2br(h($rep['suggestions']??''))?></p></div><form method="post" onsubmit="return confirm('آیا از حذف این گزارش مطمئن هستید؟')"><input type="hidden" name="action" value="delete_report"><input type="hidden" name="report_id" value="<?=$rep['id']?>"><button class="btn danger" type="submit">حذف</button></form></article><?php endforeach;?></div><?php else:?><p class="muted">هنوز گزارشی برای این استاد ثبت نشده است.</p><?php endif;?>
</div>
<?php require '../partials/footer.php';?>
