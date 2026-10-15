<?php
require_once __DIR__.'/../config/config.php';require_admin();
$rows=$pdo->query("SELECT * FROM students ORDER BY id DESC")->fetchAll();
$staff=$pdo->query('SELECT * FROM teachers ORDER BY id DESC')->fetchAll();
require '../partials/header.php';
?>
<div class="panel cards-hub no-print">
  <div class="section-title"><div><h2>کارت‌های هویت</h2><span class="muted">کارت دانش‌آموزان و کارت کارمندان / معلمان با طراحی جداگانه و آماده چاپ.</span></div></div>
  <div class="card-type-grid">
    <div class="card-type-box student-type"><div class="type-icon">🎓</div><div><h3>کارت شاگردان</h3><p>کارت افقی دانش‌آموزان، شامل صنف درسی و حرفه خیاطی.</p></div><a class="btn" href="print.php?type=students">🖨 چاپ همه کارت شاگردان</a></div>
    <div class="card-type-box teacher-type"><div class="type-icon">👨‍🏫</div><div><h3>کارت استادان</h3><p>کارت عمودی و رسمی مخصوص کارمندان / معلمان.</p></div><a class="btn" href="print.php?type=teachers">🖨 چاپ همه کارت استادان</a><a class="btn secondary" href="print.php?type=teacher">🖨 صفحه چاپ جداگانه استادان</a></div>
  </div>
</div>

<div class="panel no-print card-section student-card-section">
  <div class="section-title"><div><h3>کارت‌های هویت دانش‌آموزان</h3><span class="muted">دو کارت افقی در هر ردیف.</span></div></div>
  <div class="student-card-grid">
  <?php foreach($rows as $r): ?>
    <article class="id-card student-id-card">
      <div class="student-card-top"><img class="id-card-logo" src="<?=h(logo_url())?>" alt="لوگو"><div class="id-card-title"><strong>کورس آموزشی وسیمه فیضی</strong><span>کارت هویت دانش‌آموز</span></div><img class="id-card-qr" src="../assets/card-qrcode.png" alt="QR"></div>
      <div class="student-card-body"><div class="student-info">
        <div><b>شماره دانشجویی</b><span><?=h($r['student_code'])?></span></div><div><b>اسم دانش‌آموز</b><span><?=h($r['first_name'])?></span></div><div><b>اسم پدر</b><span><?=h($r['father_name'])?></span></div><div><b>نوع صنف</b><span><?=h($r['course_type'])?></span></div><div><b>درجه صنف</b><span><?=h($r['class_level'])?></span></div><div><b>تایم درسی</b><span><?=h($r['study_time'])?></span></div><div><b>شماره ولی دانش‌آموز</b><span><?=h($r['guardian_phone'])?></span></div><div class="address-field"><b>آدرس کورس</b><span><?=h(course_address())?></span></div>
      </div><div class="student-photo-column"><?php if(!empty($r['photo'])&&is_file('../uploads/'.$r['photo'])):?><img src="../uploads/<?=h($r['photo'])?>" class="student-card-photo" alt="عکس"><?php else:?><div class="student-card-photo placeholder">عکس</div><?php endif;?><div class="stamp">مهر کورس</div></div></div>
      <div class="student-card-bottom"><span><?=h("از آموزش تا توانمندی از سلامتی تا شکوفایی")?></span></div><a class="btn secondary no-print card-single-print" target="_blank" href="print.php?type=student&id=<?=$r['id']?>">🖨 چاپ این کارت</a>
    </article>
  <?php endforeach; ?>
  </div>
  <?php if(!$rows): ?><p class="muted">هنوز دانش‌آموزی ثبت نشده است.</p><?php endif; ?>
</div>

<div class="panel no-print card-section teacher-card-section">
  <div class="section-title"><div><h3>کارت‌های هویت استادان و کارمندان</h3><span class="muted">کارت عمودی و با طراحی متفاوت از کارت شاگردان.</span></div></div>
  <div class="teacher-card-grid">
  <?php foreach($staff as $r): ?>
    <article class="id-card teacher-id-card">
      <div class="teacher-card-top"><img class="teacher-card-logo" src="<?=h(logo_url())?>" alt="لوگوی کورس"><div class="teacher-card-title"><strong>کورس آموزشی وسیمه فیضی</strong><span>کارت رسمی کارمند / معلم</span></div></div>
      <div class="teacher-photo-area"><?php if(!empty($r['photo'])&&is_file('../uploads/'.$r['photo'])):?><img src="../uploads/<?=h($r['photo'])?>" class="teacher-card-photo" alt="عکس"><?php else:?><div class="teacher-card-photo placeholder">عکس</div><?php endif;?></div>
      <div class="teacher-card-info"><div><b>نام</b><span><?=h($r['name'])?></span></div><div><b>تخلص</b><span><?=h($r['last_name']??'')?></span></div><div><b>نام پدر</b><span><?=h($r['father_name']??'')?></span></div><div><b>عنوان وظیفه</b><span><?=h($r['duty']??'')?></span></div><div><b>آدرس کورس</b><span><?=h(course_address())?></span></div></div>
      <div class="teacher-card-bottom"><span>اداره کورس</span><span class="teacher-logo-sign"><img src="<?=h(logo_url())?>" alt="لوگو"></span></div><a class="btn secondary no-print card-single-print" target="_blank" href="print.php?type=teacher&id=<?=$r['id']?>">🖨 چاپ این کارت</a>
    </article>
  <?php endforeach; ?>
  </div>
  <?php if(!$staff): ?><p class="muted">هنوز کارمند یا معلمی ثبت نشده است.</p><?php endif; ?>
</div>
<?php require '../partials/footer.php'; ?>
