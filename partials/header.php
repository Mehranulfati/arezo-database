<?php require_once __DIR__.'/../config/config.php'; require_login(); ?>
<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h(setting('center_name')?:'کورس وسیمه فیضی')?></title><link rel="icon" type="image/png" href="<?=h(logo_url())?>"><link rel="shortcut icon" href="<?=h(logo_url())?>"><link rel="stylesheet" href="<?=base_url()?>assets/style.css?v=20261002v26"></head><body>
<aside class="sidebar"><div class="side-user"><img class="manager-avatar" src="<?=h(manager_photo_url())?>" alt="معاون کورس"><div><b><?=h(setting('manager_name') ?: ($_SESSION['admin_name']??'مدیر'))?></b><small>معاون کورس</small></div></div><?php $currentPath=$_SERVER['SCRIPT_NAME']??''; $currentSection=$_GET['section']??''; ?><nav class="side-nav">
<a class="nav-item <?=basename($currentPath)==='index.php' && $currentSection===''?'active':''?>" href="<?=base_url()?>">🏠 <span>صفحه اصلی</span></a>
<a class="nav-item <?=basename($currentPath)==="documents.php"?"active":""?>" href="<?=base_url()?>admin/documents.php">📁 <span>بخش اسناد اداری و اجرایوی</span></a>
<a class="nav-item <?=strpos($currentPath,"/teachers/")!==false?"active":""?>" href="<?=base_url()?>teachers/index.php">👨‍🏫 <span>بخش کارمندان / معلمان</span></a>
<a class="nav-item <?=$currentSection==="reports"?"active":""?>" href="<?=base_url()?>admin/records.php?section=reports">📝 <span>گزارشات کاری</span></a>
<a class="nav-item <?=strpos($currentPath,"/finance/")!==false?"active":""?>" href="<?=base_url()?>finance/index.php">💰 <span>بخش مالی</span></a>
<a class="nav-item <?=$currentSection==="health"?"active":""?>" href="<?=base_url()?>admin/records.php?section=health">🩺 <span>بخش صحی</span></a>
<a class="nav-item <?=strpos($currentPath,"/students/")!==false?"active":""?>" href="<?=base_url()?>students/index.php">👨‍🎓 <span>بخش دانش‌آموزان</span></a>
<a class="nav-item <?=strpos($currentPath,"/cards/")!==false?"active":""?>" href="<?=base_url()?>cards/list.php">🪪 <span>کارت‌های هویت</span></a>
<a class="nav-item <?=basename($currentPath)==="settings.php"?"active":""?>" href="<?=base_url()?>admin/settings.php">⚙️ <span>تنظیمات</span></a>
<a class="nav-item logout" href="<?=base_url()?>logout.php">↪ <span>خروج</span></a>
</nav></aside>
<div class="mobile-overlay" onclick="document.body.classList.remove('menu-open')"></div><main class="main-area"><header class="topbar"><button class="menu-btn" onclick="document.body.classList.toggle('menu-open')">☰</button><div class="top-title main-course-brand"><img src="<?=h(logo_url())?>" alt="لوگو"><div><b class="course-one-line">کورس آموزشی وسیمه فیضی</b></div></div><div class="top-datetime" aria-live="polite"><span id="liveTime">--:--:--</span><span id="liveGregorian">میلادی: --</span><span id="liveJalali">شمسی: --</span></div></header><div class="wrap">
