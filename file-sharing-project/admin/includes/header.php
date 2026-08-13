<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_admin();
$adminName=$_SESSION['admin_username']??'Admin';
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($page_title??'Admin')?> — ShareVault</title><link rel="stylesheet" href="../assets/css/admin.css"></head><body><div class="admin-shell"><aside class="sidebar"><a class="admin-brand" href="dashboard.php">ShareVault<span>ADMIN</span></a><nav><a href="dashboard.php">Dashboard</a><a href="files.php">Files</a><a href="settings.php">Settings</a><form method="post" action="logout.php"><?=csrf_field()?><button>Logout</button></form></nav></aside><main class="admin-main"><header class="admin-top"><div><strong><?=e($page_title??'Dashboard')?></strong></div><span><?=e($adminName)?></span></header>