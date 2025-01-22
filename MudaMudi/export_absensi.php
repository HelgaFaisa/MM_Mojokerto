<?php
require 'koneksi.php';
require 'vendor/autoload.php'; // You need to install PhpSpreadsheet via composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
$id_kelompok = $_GET['id_kelompok'];
$id_desa = $_GET['id_desa'];

// Use the same query from rekap-absensi.php
$query = "SELECT 
            m.nama,
            k.nama_kelompok,
            d.nama_desa,
            COUNT(CASE WHEN a.status = 'H' THEN 1 END) as hadir,
            COUNT(CASE WHEN a.status = 'I' THEN 1 END) as izin,
            COUNT(CASE WHEN a.status = 'A' THEN 1 END) as alfa
          FROM muda_mudi m
          LEFT JOIN kelompok k ON m.id_kelompok = k.id_kelompok
          LEFT JOIN desa d ON k.id_desa = d.id_desa
          LEFT JOIN absensi a ON m.id_muda_mudi = a.id_muda_mudi 
            AND a.tanggal BETWEEN ? AND ?
          WHERE 1=1";

$params = array($start_date, $end_date);
$types = "ss";

if ($id_kelompok) {
    $query .= " AND m.id_kelompok = ?";
    $params[] = $id_kelompok;
    $types .= "s";
}

if ($id_desa) {
    $query .= " AND k.id_desa = ?";
    $params[] = $id_desa;
    $types .= "s";
}

$query .= " GROUP BY m.id_muda_mudi, m.nama, k.nama_kelompok, d.nama_desa
            ORDER BY d.nama_desa, k.nama_kelompok, m.nama";

$stmt = $config->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Create new spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers
$sheet->setCellValue('A1', 'No');
$sheet->setCellValue('B1', 'Nama');
$sheet->setCellValue('C1', 'Kelompok');
$sheet->setCellValue('D1', 'Desa');
$sheet->setCellValue('E1', 'Hadir');
$sheet->setCellValue('F1', 'Izin');
$sheet->setCellValue('G1', 'Alfa');
$sheet->setCellValue('H1', 'Persentase Kehadiran');

// Fill data
$row = 2;
$no = 1;
while ($data = $result->fetch_assoc()) {
    $total_absensi = $data['hadir'] + $data['izin'] + $data['alfa'];
    $persentase = $total_absensi > 0 ? 
        round(($data['hadir'] / $total_absensi) * 100, 1) : 0;
    
    $sheet->setCellValue('A' . $row, $no++);
    $sheet->setCellValue('B' . $row, $data['nama']);
    $sheet->setCellValue('C' . $row, $data['nama_kelompok']);
    $sheet->setCellValue('D' . $row, $data['nama_desa']);
    $sheet->setCellValue('E' . $row, $data['hadir']);
    $sheet->setC