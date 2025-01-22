<?php
require 'koneksi.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['barcode'])) {
        $barcode = trim($_POST['barcode']);
        
        // Check if barcode exists
        $stmt = $config->prepare("SELECT id_muda_mudi, nama FROM muda_mudi WHERE barcode = ?");
        $stmt->bind_param("s", $barcode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $muda_mudi = $result->fetch_assoc();
            $id_muda_mudi = $muda_mudi['id_muda_mudi'];
            $nama = $muda_mudi['nama'];
            
            // Check if already attended today
            $today = date('Y-m-d');
            $check_stmt = $config->prepare("SELECT id_absensi FROM absensi WHERE id_muda_mudi = ? AND tanggal = ?");
            $check_stmt->bind_param("ss", $id_muda_mudi, $today);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows > 0) {
                $_SESSION['error_message'] = "Sudah melakukan absensi hari ini!";
            } else {
                // Record attendance
                $waktu = date('H:i:s');
                $status = 'H'; // Hadir
                
                $insert_stmt = $config->prepare("INSERT INTO absensi (id_muda_mudi, tanggal, waktu, status) VALUES (?, ?, ?, ?)");
                $insert_stmt->bind_param("ssss", $id_muda_mudi, $today, $waktu, $status);
                
                if ($insert_stmt->execute()) {
                    $_SESSION['success_message'] = "Absensi berhasil: " . $nama;
                } else {
                    $_SESSION['error_message'] = "Gagal melakukan absensi!";
                }
                $insert_stmt->close();
            }
            $check_stmt->close();
        } else {
            $_SESSION['error_message'] = "Barcode tidak ditemukan!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Absensi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        /* Copy the existing styles from the previous page */
        /* Add scanner specific styles */
        #reader {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            border: 2px solid #0A3981;
            border-radius: 8px;
        }
        
        .scanner-container {
            text-align: center;
            margin: 20px 0;
        }
        
        .manual-input {
            margin: 20px 0;
            text-align: center;
        }
        
        .manual-input input {
            padding: 10px;
            width: 200px;
            margin-right: 10px;
        }
        
        .recent-scans {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include('sidebar.php'); ?>
        
        <div class="main-content">
            <div class="container">
                <h1>Scan Absensi</h1>
                
                <!-- Message container -->
                <div id="messageContainer">
                    <?php
                    if (isset($_SESSION['success_message'])) {
                        echo '<div class="alert alert-success">' . 
                             htmlspecialchars($_SESSION['success_message']) . 
                             '</div>';
                        unset($_SESSION['success_message']);
                    }
                    if (isset($_SESSION['error_message'])) {
                        echo '<div class="alert alert-danger">' . 
                             htmlspecialchars($_SESSION['error_message']) . 
                             '</div>';
                        unset($_SESSION['error_message']);
                    }
                    ?>
                </div>
                
                <!-- Scanner -->
                <div class="scanner-container">
                    <div id="reader"></div>
                </div>
                
                <!-- Manual Input -->
                <div class="manual-input">
                    <form method="POST">
                        <input type="text" name="barcode" placeholder="Masukkan kode barcode..." autofocus>
                        <button type="submit" class="button btn-add">Submit</button>
                    </form>
                </div>
                
                <!-- Recent Scans -->
                <div class="recent-scans">
                    <h2>Absensi Hari Ini</h2>
                    <div class="table-responsive">
                        <table>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Kelompok</th>
                                <th>Desa</th>
                                <th>Waktu Absen</th>
                                <th>Status</th>
                            </tr>
                            <?php
                            $today = date('Y-m-d');
                            $query = "SELECT a.*, m.nama, k.nama_kelompok, d.nama_desa 
                                    FROM absensi a
                                    JOIN muda_mudi m ON a.id_muda_mudi = m.id_muda_mudi
                                    JOIN kelompok k ON m.id_kelompok = k.id_kelompok
                                    JOIN desa d ON k.id_desa = d.id_desa
                                    WHERE a.tanggal = ?
                                    ORDER BY a.waktu DESC";
                            $stmt = $config->prepare($query);
                            $stmt->bind_param("s", $today);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            $no = 1;
                            while ($row = $result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['nama']); ?></td>
                                <td><?= htmlspecialchars($row['nama_kelompok']); ?></td>
                                <td><?= htmlspecialchars($row['nama_desa']); ?></td>
                                <td><?= $row['waktu']; ?></td>
                                <td><?= $row['status']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize QR Scanner
        function onScanSuccess(decodedText, decodedResult) {
            // Submit the form with the scanned barcode
            const form = document.createElement('form');
            form.method = 'POST';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'barcode';
            input.value = decodedText;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }

        function onScanFailure(error) {
            // handle scan failure, usually better to ignore and keep scanning
            console.warn(`Code scan error = ${error}`);
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", { fps: 10, qrbox: {width: 250, height: 250} }
        );
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);

        // Alert auto-dismiss
        function dismissAlerts() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }, 5000);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            dismissAlerts();
        });
    </script>
</body>
</html>