<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
$pdo = new PDO('mysql:host=localhost;dbname=absensi_galon;charset=utf8mb4', 'root', '', [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function tgl($d){ return date('d-m-Y', strtotime($d)); }
function need($role){ if (($_SESSION['role'] ?? '') !== $role) { header('Location: index.php'); exit; } }
function stok($pdo){ return (int)$pdo->query("SELECT stok FROM pengaturan WHERE id=1")->fetchColumn(); }
function stokInfo($pdo){
  $s = stok($pdo);
  return $s > 0
    ? '<div class="alert alert-success no-print">✅ Stok galon <b>TERSEDIA</b>: '.$s.' galon</div>'
    : '<div class="alert alert-danger no-print">❌ Stok galon <b>HABIS</b></div>';
}
function head($t){
  echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.e($t).' - Absensi Galon</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><style>@media print{.no-print{display:none!important}}</style></head><body class="bg-light"><nav class="navbar navbar-dark bg-primary no-print mb-4"><div class="container"><span class="navbar-brand">💧 Absensi Galon SMKN 1 Probolinggo</span>'.(isset($_SESSION['role']) ? '<a class="btn btn-sm btn-light" href="logout.php">Keluar</a>' : '').'</div></nav><div class="container pb-5">';
}
function foot(){ echo '</div></body></html>'; }
