<?php
require_once __DIR__.'/../config/config.php';require_admin();
$type=$_GET['type']??'students';
if($type==='teachers'){$rows=$pdo->query('SELECT * FROM teachers ORDER BY COALESCE(sort_order,999999), id ASC')->fetchAll();$title='کارت استادان';}
elseif($type==='teacher'){$id=(int)($_GET['id']??0);$st=$pdo->prepare('SELECT * FROM teachers WHERE id=?');$st->execute([$id]);$one=$st->fetch();$rows=$one?[$one]:[];$title='کارت استاد';}
elseif($type==='student'){$id=(int)($_GET['id']??0);$st=$pdo->prepare('SELECT * FROM students WHERE id=?');$st->execute([$id]);$one=$st->fetch();$rows=$one?[$one]:[];$title='کارت دانش‌آموز';}
else{$type='students';$rows=$pdo->query('SELECT * FROM students ORDER BY id ASC')->fetchAll();$title='کارت شاگردان';}
function card_photo($path){return $path && is_file(__DIR__.'/../uploads/'.$path) ? '../uploads/'.rawurlencode($path) : '';}
?><!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($title)?></title><link rel="icon" href="<?=h(logo_url())?>"><link rel="stylesheet" href="../assets/style.css?v=20261001v24"></head><body class="print-page <?= in_array($type,['teachers','teacher'],true)?'teacher-print-page':'student-print-page' ?>">
<div class="screen-actions no-print"><button onclick="window.print()">🖨 چاپ همه کارت‌ها</button><a class="btn secondary" href="list.php">بازگشت</a></div>
<main class="print-sheet">
<?php if(in_array($type,['teachers','teacher'],true)): ?>
<div class="teacher-print-grid"><?php foreach($rows as $r):$photo=card_photo($r['photo']??'');?><article class="id-card teacher-id-card">
<div class="teacher-card-top"><img class="teacher-card-logo" src="<?=h(logo_url())?>" alt="لوگوی کورس"><div class="teacher-card-title"><strong>کورس آموزشی وسیمه فیضی</strong><span>کارت رسمی کارمند / معلم</span></div></div>
<div class="teacher-photo-area"><?php if($photo):?><img src="<?=h($photo)?>" class="teacher-card-photo" alt="عکس"><?php else:?><div class="teacher-card-photo placeholder">عکس</div><?php endif;?></div>
<div class="teacher-card-info"><div><b>نام</b><span><?=h($r['name'])?></span></div><div><b>تخلص</b><span><?=h($r['last_name']??'')?></span></div><div><b>نام پدر</b><span><?=h($r['father_name']??'')?></span></div><div><b>عنوان وظیفه</b><span><?=h($r['duty']??'')?></span></div><div><b>آدرس کورس</b><span><?=h(course_address())?></span></div></div>
<div class="teacher-card-bottom"><span>اداره کورس</span><span class="teacher-logo-sign"><img src="<?=h(logo_url())?>" alt="لوگو"></span></div></article><?php endforeach;?></div>
<?php else: ?>
<div class="student-print-grid"><?php foreach($rows as $r):$photo=card_photo($r['photo']??'');?><article class="id-card student-id-card">
<div class="student-card-top"><img class="id-card-logo" src="<?=h(logo_url())?>" alt="لوگو"><div class="id-card-title"><strong>کورس آموزشی وسیمه فیضی</strong><span>کارت هویت دانش‌آموز</span></div><img class="id-card-qr" src="../assets/card-qrcode.png" alt="QR"></div>
<div class="student-card-body"><div class="student-info"><div><b>شماره دانشجویی</b><span><?=h($r['student_code']??'')?></span></div><div><b>اسم دانش‌آموز</b><span><?=h($r['first_name'])?></span></div><div><b>اسم پدر</b><span><?=h($r['father_name']??'')?></span></div><div><b>نوع صنف</b><span><?=h($r['course_type']??'')?></span></div><div><b>درجه صنف</b><span><?=h($r['class_level']??'')?></span></div><div><b>تایم درسی</b><span><?=h($r['study_time']??'')?></span></div><div><b>شماره ولی دانش‌آموز</b><span><?=h($r['guardian_phone']??'')?></span></div><div class="address-field"><b>آدرس کورس</b><span><?=h(course_address())?></span></div></div><div class="student-photo-column"><?php if($photo):?><img src="<?=h($photo)?>" class="student-card-photo" alt="عکس"><?php else:?><div class="student-card-photo placeholder">عکس</div><?php endif;?><div class="stamp">مهر کورس</div></div></div>
<div class="student-card-bottom"><span><?=h("از آموزش تا توانمندی از سلامتی تا شکوفایی")?></span></div></article><?php endforeach;?></div>
<?php endif; ?>
</main></body></html>
