<?php
require_once __DIR__.'/../config/config.php'; require_admin();
$msg=''; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  $action=$_POST['action']??'manager';
  if($action==='manager'){
   $username=trim($_POST['username']??''); $full=trim($_POST['full_name']??''); $pass=$_POST['password']??'';
   if(!$username||!$full||strlen($pass)<8) throw new Exception('نام، نام کاربری و رمز حداقل ۸ حرف الزامی است.');
   $pdo->prepare('INSERT INTO users(username,password_hash,full_name,active,role,teacher_id) VALUES(?,?,?,?,?,NULL)')->execute([$username,password_hash($pass,PASSWORD_DEFAULT),$full,1,'admin']);
   $msg='حساب مدیر با موفقیت ساخته شد.';
  } elseif($action==='teacher_account'){
   $tid=(int)($_POST['teacher_id']??0); $username=trim($_POST['username']??''); $pass=$_POST['password']??'';
   $st=$pdo->prepare('SELECT * FROM teachers WHERE id=?'); $st->execute([$tid]); $teacher=$st->fetch();
   if(!$teacher) throw new Exception('استاد انتخاب‌شده پیدا نشد.');
   if(!$username||strlen($pass)<8) throw new Exception('نام کاربری و رمز حداقل ۸ حرف الزامی است.');
   $exists=$pdo->prepare('SELECT COUNT(*) FROM users WHERE username=?');$exists->execute([$username]);if($exists->fetchColumn())throw new Exception('این نام کاربری قبلاً استفاده شده است.');
   $full=trim(($teacher['name']??'').' '.($teacher['last_name']??''));
   $pdo->prepare('INSERT INTO users(username,password_hash,full_name,active,role,teacher_id) VALUES(?,?,?,?,?,?)')->execute([$username,password_hash($pass,PASSWORD_DEFAULT),$full,1,'teacher',$tid]);
   $msg='حساب کاربری استاد با موفقیت ساخته شد.';
  } elseif($action==='toggle'){ $uid=(int)$_POST['user_id']; $pdo->prepare('UPDATE users SET active=IF(active=1,0,1) WHERE id=?')->execute([$uid]); $msg='وضعیت حساب تغییر کرد.'; }
 }catch(Exception $e){$err=$e->getMessage();}
}
$teachers=$pdo->query("SELECT t.*,u.id AS account_id,u.username AS account_username,u.active AS account_active FROM teachers t LEFT JOIN users u ON u.teacher_id=t.id AND u.role='teacher' ORDER BY t.id DESC")->fetchAll();
$admins=$pdo->query("SELECT id,username,full_name,active,created_at FROM users WHERE role='admin' ORDER BY id DESC")->fetchAll();
require '../partials/header.php';?>
<div class="panel form-panel"><div class="section-title"><div><h2>مدیریت حساب‌ها</h2><span class="muted">معاون کورس می‌تواند برای هر استاد نام کاربری و رمز اختصاصی بسازد.</span></div></div>
<?php if($msg):?><div class="badge">✓ <?=h($msg)?></div><?php endif;?><?php if($err):?><div class="alert">⚠ <?=h($err)?></div><?php endif;?>
<h3>ایجاد حساب استاد</h3><form method="post"><input type="hidden" name="action" value="teacher_account"><div class="row"><label>استاد *<select name="teacher_id" required><option value="">انتخاب استاد</option><?php foreach($teachers as $t): if(!$t['account_id']):?><option value="<?=$t['id']?>"><?=h(trim($t['name'].' '.($t['last_name']??'')))?></option><?php endif;endforeach;?></select></label><label>نام کاربری *<input name="username" required></label><label>رمز عبور *<input type="password" name="password" minlength="8" required></label></div><button>➕ ساخت حساب استاد</button></form>
</div>
<div class="panel table-panel"><div class="section-title"><h3>حساب‌های استادان</h3></div><div class="table-scroll"><table><tr><th>استاد</th><th>نام کاربری</th><th>وضعیت</th><th>عملیات</th></tr><?php foreach($teachers as $t): if($t['account_id']):?><tr><td><?=h(trim($t['name'].' '.($t['last_name']??'')))?></td><td><?=h($t['account_username'])?></td><td><?=((int)$t['account_active'])?'فعال':'غیرفعال'?></td><td><form method="post" style="display:inline"><input type="hidden" name="action" value="toggle"><input type="hidden" name="user_id" value="<?=$t['account_id']?>"><button class="btn secondary"><?=((int)$t['account_active'])?'غیرفعال کردن':'فعال کردن'?></button></form></td></tr><?php endif;endforeach;?></table></div></div>
<div class="panel form-panel"><div class="section-title"><h3>حساب‌های معاون / مدیر</h3></div><form method="post"><input type="hidden" name="action" value="manager"><div class="row"><label>نام مدیر *<input required name="full_name"></label><label>نام کاربری *<input required name="username"></label><label>رمز عبور *<input required type="password" name="password" minlength="8"></label></div><button>➕ ثبت مدیر</button></form><div class="table-scroll"><table><tr><th>نام</th><th>نام کاربری</th><th>وضعیت</th><th>تاریخ</th></tr><?php foreach($admins as $a):?><tr><td><?=h($a['full_name'])?></td><td><?=h($a['username'])?></td><td><?=((int)$a['active'])?'فعال':'غیرفعال'?></td><td><?=h($a['created_at'])?></td></tr><?php endforeach;?></table></div></div><?php require '../partials/footer.php';?>