<?php
require 'koneksi.php';
session_start();

// Get filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$id_kelompok = isset($_GET['id_kelompok']) ? $_GET['id_kelompok'] : '';
$id_desa = isset($_GET['id_desa']) ? $_GET['id_desa'] : '';

// Base query
$query = "SELECT 
            m.id_muda_mudi,
            m.nama,
            k.nama_kelompok,
            d.nama_desa,
            COUNT(CASE WHEN a.status = 'H' THEN 1 END) as hadir,
            COUNT(CASE WHEN a.status = 'I' THEN 1 END) as izin,
            COUNT(CASE WHEN a.status = 'A' THEN 1 END) as alfa,
            COUNT(DISTINCT DATE(a.tanggal)) as total_hari
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

// Get desa and kelompok for filters
$desa_query = "SELECT * FROM desa ORDER BY nama_desa";
$desa_result = $config->query($desa_query);

$kelompok_query = "SELECT * FROM kelompok ORDER BY nama_kelompok";
$kelompok_result = $config->query($kelompok_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Previous head content remains the same -->
</head>
<body>
    <div class="wrapper">
        <?php include('sidebar.php'); ?>
        
        <div class="main-content">
            <div class="container">
                <h1>Rekap Absensi</h1>
                
                <!-- Filters -->
                <form class="filter-form">
                    <div class="form-group">
                        <label>Tanggal Mulai:</label>
                        <input type="date" name="start_date" value="<?= $start_date ?>" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Tanggal Selesai:</label>
                        <input type="date" name="end_date" value="<?= $end_date ?>" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Desa:</label>
                        <select name="id_desa" class="form-control">
                            <option value="">Semua Desa</option>
                            <?php while ($desa = $desa_result->fetch_assoc()): ?>
                            <option value="<?= $desa['id_desa'] ?>" 
                                <?= $id_desa == $desa['id_desa'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($desa['nama_desa']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Kelompok:</label>
                        <select name="id_kelompok" class="form-control">
                            <option value="">Semua Kelompok</option>
                            <?php 
                            mysqli_data_seek($kelompok_result, 0);
                            while ($kelompok = $kelompok_result->fetch_assoc()): 
                            ?>
                            <option value="<?= $kelompok['id_kelompok'] ?>"
                                <?= $id_kelompok == $kelompok['id_kelompok'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kelompok['nama_kelompok']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="align-self: end;">
                        <button type="submit" class="button btn-add">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="export_absensi.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&id_kelompok=<?= $id_kelompok ?>&id_desa=<?= $id_desa ?>" 
                           class="button btn-add" style="text-decoration: none;">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                    </div>
                </form>

                <!-- Summary Cards -->
                <?php
                $stmt = $config->prepare($query);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $total_muda_mudi = 0;
                $total_hadir = 0;
                $total_izin = 0;
                $total_alfa = 0;
                
                while ($row = $result->fetch_assoc()) {
                    $total_muda_mudi++;
                    $total_hadir += $row['hadir'];
                    $total_izin += $row['izin'];
                    $total_alfa += $row['alfa'];
                }
                mysqli_data_seek($result, 0);
                ?>
                
                <div class="summary-cards">
                    <div class="summary-card">
                        <h3>Total Muda-Mudi</h3>
                        <p><?= $total_muda_mudi ?></p>
                    </div>
                    <div class="summary-card">
                        <h3>Total Kehadiran</h3>
                        <p><?= $total_hadir ?></p>
                    </div>
                    <div class="summary-card">
                        <h3>Total Izin</h3>
                        <p><?= $total_izin ?></p>
                    </div>
                    <div class="summary-card">
                        <h3>Total Alfa</h3>
                        <p><?= $total_alfa ?></p>
                    </div>
                </div>

                <!-- Detailed Report Table -->
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Kelompok</th>
                            <th>Desa</th>
                            <th>Hadir</th>
                            <th>Izin</th>
                            <th>Alfa</th>
                            <th>Persentase</th>
                            <th>Aksi</th>
                        </tr>
                        <?php
                        $no = 1;
                        while ($row = $result->fetch_assoc()):
                            $total_absensi = $row['hadir'] + $row['izin'] + $row['alfa'];
                            $persentase = $total_absensi > 0 ? 
                                round(($row['hadir'] / $total_absensi) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['nama_kelompok']) ?></td>
                            <td><?= htmlspecialchars($row['nama_desa']) ?></td>
                            <td><?= $row['hadir'] ?></td>
                            <td><?= $row['izin'] ?></td>
                            <td><?= $row['alfa'] ?></td>
                            <td><?= $persentase ?>%</td>
                            <td>
                                <button onclick="showDetail('<?= $row['id_muda_mudi'] ?>')" 
                                        class="button btn-update">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeDetailModal()">&times;</span>
            <h2>Detail Absensi</h2>
            <div id="detailContent" class="table-responsive">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        function showDetail(id_muda_mudi) {
            const modal = document.getElementById('detailModal');
            const content = document.getElementById('detailContent');
            
            // Fetch detail data
            fetch(`get_detail_absensi.php?id_muda_mudi=${id_muda_mudi}&start_date=<?= $start_date ?>&end_date=<?= $end_date ?>`)
                .then(response => response.text())
                .then(data => {
                    content.innerHTML = data;
                    modal.style.display = 'block';
                });
        }

        function closeDetailModal() {
            document.getElementById('detailModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('detailModal');
            if (event.target == modal) {
                closeDetailModal();
            }
        }

        // Initialize date inputs
        document.addEventListener('DOMContentLoaded', function() {
            // Set max date to today
            const today = new Date().toISOString().split('T')[0];
            document.querySelector('input[name="start_date"]').max = today;
            document.querySelector('input[name="end_date"]').max = today;
        });
    </script>
</body>
</html>