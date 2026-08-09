<?php
// src/views/header.php
if (!isset($app)) {
    $configTmp = require __DIR__ . '/../../config/config.php';
    $app = $configTmp['app'] ?? ['name' => 'African Disputes Resolution'];
}
$base = rtrim($app['base_url'] ?? '', '/');
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($app['name'], ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="/public/css/styles.css">
  <script defer src="/public/js/app.js"></script>
</head>
<body>
  <header class="container header">
    <div class="header-row">
      <span class="logo-mark" aria-hidden="true"></span>
      <div>
        <div class="brand"><?= htmlspecialchars($app['name'], ENT_QUOTES, 'UTF-8') ?></div>
        <div class="lead">A curated platform for dispute resolution law and practice across Africa.</div>
      </div>
    </div>

    <nav class="nav">
      <a href="/">Home</a>
      <a href="/?p=countries">Countries</a>
      <a href="/?p=documents">Documents</a>
      <a href="/?p=cases">Cases</a>
      <a href="/?p=institutions">Institutions</a>
      <a href="/?p=research">Research</a>
    </nav>
  </header>
  <main class="container">
