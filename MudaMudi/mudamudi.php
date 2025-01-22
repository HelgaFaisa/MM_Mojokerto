<?php 
require 'koneksi.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit'])) {
        $nama = trim($_POST['nama']);
        $id_desa = trim($_POST['id_desa']);
        $id_kelompok = trim($_POST['id_kelompok']);
        $tanggal_lahir = trim($_POST['tanggal_lahir']);
        $kelas = trim($_POST['kelas']);

        if (empty($nama) || empty($id_desa) || empty($id_kelompok) || empty($tanggal_lahir) || empty($kelas)) {
            $_SESSION['error_message'] = "Semua field harus diisi!";
        } else {
            // Get last code from database
            $query = "SELECT id_muda_mudi FROM muda_mudi ORDER BY id_muda_mudi DESC LIMIT 1";
            $result = $config->query($query);
            $row = $result->fetch_assoc();

            if ($row) {
                $last_kode = intval(substr($row['id_muda_mudi'], 1));
                $new_kode = 'M' . str_pad($last_kode + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $new_kode = 'M001';
            }

            // Generate barcode (using timestamp and random number)
            $barcode = time() . rand(1000, 9999);

            $stmt = $config->prepare("INSERT INTO muda_mudi (id_muda_mudi, nama, id_desa, id_kelompok, tanggal_lahir, kelas, barcode, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssssss", $new_kode, $nama, $id_desa, $id_kelompok, $tanggal_lahir, $kelas, $barcode);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Data Muda-Mudi berhasil ditambahkan!";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan Data: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    elseif (isset($_POST['update'])) {
        $id_muda_mudi = $_POST['id_muda_mudi'];
        $nama = trim($_POST['nama']);
        $id_desa = trim($_POST['id_desa']);
        $id_kelompok = trim($_POST['id_kelompok']);
        $tanggal_lahir = trim($_POST['tanggal_lahir']);
        $kelas = trim($_POST['kelas']);

        if (empty($nama) || empty($id_desa) || empty($id_kelompok) || empty($tanggal_lahir) || empty($kelas)) {
            $_SESSION['error_message'] = "Semua field harus diisi!";
        } else {
            $stmt = $config->prepare("UPDATE muda_mudi SET nama = ?, id_desa = ?, id_kelompok = ?, tanggal_lahir = ?, kelas = ?, updated_at = NOW() WHERE id_muda_mudi = ?");
            $stmt->bind_param("ssssss", $nama, $id_desa, $id_kelompok, $tanggal_lahir, $kelas, $id_muda_mudi);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Data berhasil diupdate!";
            } else {
                $_SESSION['error_message'] = "Gagal mengupdate data: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    elseif (isset($_POST['delete'])) {
        $id_muda_mudi = $_POST['id_muda_mudi'];

        $stmt = $config->prepare("DELETE FROM muda_mudi WHERE id_muda_mudi = ?");
        $stmt->bind_param("s", $id_muda_mudi);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Data berhasil dihapus!";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus data: " . $stmt->error;
        }
        $stmt->close();
    }

    elseif (isset($_POST['promote_class'])) {
        $current_class = $_POST['current_class'];
        $new_class = $current_class + 1;

        $stmt = $config->prepare("UPDATE muda_mudi SET kelas = ?, updated_at = NOW() WHERE kelas = ?");
        $stmt->bind_param("ii", $new_class, $current_class);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Semua siswa di kelas " . $current_class . " berhasil dinaikkan ke kelas " . $new_class;
        } else {
            $_SESSION['error_message'] = "Gagal menaikkan kelas: " . $stmt->error;
        }
        $stmt->close();
    }
}

$search_term = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $_GET['search'];
    $query = "SELECT mm.*, d.nama_desa, k.nama_kelompok 
              FROM muda_mudi mm 
              JOIN desa d ON mm.id_desa = d.id_desa 
              JOIN kelompok k ON mm.id_kelompok = k.id_kelompok 
              WHERE mm.nama LIKE ? OR d.nama_desa LIKE ? OR k.nama_kelompok LIKE ?
              ORDER BY mm.id_muda_mudi DESC";
    $stmt = $config->prepare($query);
    $like_term = "%" . $search_term . "%";
    $stmt->bind_param("sss", $like_term, $like_term, $like_term);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $query = "SELECT mm.*, d.nama_desa, k.nama_kelompok 
              FROM muda_mudi mm 
              JOIN desa d ON mm.id_desa = d.id_desa 
              JOIN kelompok k ON mm.id_kelompok = k.id_kelompok 
              ORDER BY mm.id_muda_mudi DESC";
    $result = $config->query($query);
}

// Get desa data for dropdown
$desa_query = "SELECT * FROM desa ORDER BY nama_desa";
$desa_result = $config->query($desa_query);

// Get kelompok data for dropdown
$kelompok_query = "SELECT * FROM kelompok ORDER BY nama_kelompok";
$kelompok_result = $config->query($kelompok_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Muda-Mudi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Base styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            margin-left: 250px;
            transition: margin-left 0.3s;
        }

        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 10px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
            position: relative;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #0A3981;
        }

        /* Form styles */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
        }

        /* Table styles */
        .table-responsive {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            white-space: nowrap;
            border-radius: 12px;
            overflow: hidden;
        }

        th, td {
            padding: 12px 15px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #0A3981;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        /* Button styles */
        .button {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 2px;
            transition: background-color 0.3s;
        }

        .btn-add, .btn-update, .btn-promote {
            background-color: #0A3981;
            color: white;
        }

        .btn-add:hover, .btn-update:hover, .btn-promote:hover {
            background-color: #072a61;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        /* Search form and action buttons */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            flex-wrap: wrap;
            gap: 10px;
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-form input[type="text"] {
            width: 250px;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .button-group {
            display: flex;
            gap: 10px;
        }

        /* Barcode display */
        .barcode {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            letter-spacing: 2px;
        }

        /* Alert styles */
        .alert {
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid transparent;
            border-radius: 4px;
            opacity: 1;
            transition: opacity 0.5s ease-out;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .modal-content {
                width: 95%;
                margin: 10% auto;
            }

            .action-bar {
                flex-direction: column;
            }

            .search-form {
                width: 100%;
            }

            .search-form input[type="text"] {
                flex-grow: 1;
            }

            .button-group {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include('sidebar.php'); ?>

        <div class="main-content">
            <div class="container">
                <h1>Data Muda-Mudi</h1>

                <!-- Action buttons and search form -->
                <div class="action-bar">
                    <div class="button-group">
                        <button onclick="openModal()" class="button btn-add">
                            <i class="fas fa-plus"></i> Tambah Data
                        </button>
                        <button onclick="openPromoteModal()" class="button btn-promote">
                            <i class="fas fa-graduation-cap"></i> Naikkan Kelas
                        </button>
                    </div>
                    <div class="search-form">
                        <input type="text" id="searchInput" placeholder="Search..." 
                               value="<?= htmlspecialchars($search_term) ?>">
                        <button onclick="searchData()" class="button btn-add">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

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

                <!-- Table -->
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th>No</th>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Desa</th>
                            <th>Kelompok</th>
                            <th>Tanggal Lahir</th>
                            <th>Kelas</th>
                            <th>Barcode</th>
                            <th>Aksi</th>
                        </tr>
                        <?php
                        $no = 1;
                        while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= $row['id_muda_mudi']; ?></td>
                            <td><?= $row['nama']; ?></td>
                            <td><?= $row['nama_desa']; ?></td>
                            <td><?= $row['nama_kelompok']; ?></td>
                            <td><?= date('d-m-Y', strtotime($row['tanggal_lahir'])); ?></td>
                            <td><?= $row['kelas']; ?></td>
                            <td class="barcode"><?= $row['barcode']; ?></td>
                            <td>
                                <button class="button btn-update" 
                                    onclick="editData('<?= $row['id_muda_mudi']; ?>', 
                                                    '<?= htmlspecialchars($row['nama']); ?>', 
                                                    '<?= $row['id_desa']; ?>', 
                                                    '<?= $row['id_kelompok']; ?>',
                                                    '<?= $row['tanggal_lahir']; ?>',
                                                    '<?= $row['kelas']; ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id_muda_mudi" value="<?= $row['id_muda_mudi']; ?>">
                                    <button type="submit" name="delete" class="button btn-delete"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal Form -->
    <div id="dataModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Tambah Data Muda-Mudi</h2>
            <form id="dataForm" method="POST">
                <input type="hidden" id="id_muda_mudi" name="id_muda_mudi">
                
                <div class="form-group">
                    <label for="nama">Nama:</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                
                <div class="form-group">
                    <label for="id_desa">Desa:</label>
                    <select id="id_desa" name="id_desa" required>
                        <option value="">Pilih Desa</option>
                        <?php 
                        mysqli_data_seek($desa_result, 0);
                        while ($desa = $desa_result->fetch_assoc()) {
                            echo '<option value="' . $desa['id_desa'] . '">' . 
                                 htmlspecialchars($desa['nama_desa']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="id_kelompok">Kelompok:</label>
                    <select id="id_kelompok" name="id_kelompok" required>
                        <option value="">Pilih Kelompok</option>
                        <?php 
                        mysqli_data_seek($kelompok_result, 0);
                        while ($kelompok = $kelompok_result->fetch_assoc()) {
                            echo '<option value="' . $kelompok['id_kelompok'] . '">' . 
                                 htmlspecialchars($kelompok['nama_kelompok']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="tanggal_lahir">Tanggal Lahir:</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" required>
                </div>
                
                <div class="form-group">
                    <label for="kelas">Kelas:</label>
                    <input type="number" id="kelas" name="kelas" min="1" max="12" required>
                </div>
                
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" class="button" onclick="closeModal()" 
                            style="background-color: #6c757d; color: white;">
                        Batal
                    </button>
                    <button type="submit" id="submitBtn" name="submit" class="button btn-add">
                        Simpan
                    </button>
                    <button type="submit" id="updateBtn" name="update" class="button btn-update" 
                            style="display:none;">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Promote Class Modal -->
    <div id="promoteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePromoteModal()">&times;</span>
            <h2>Naikkan Kelas</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="current_class">Pilih Kelas yang Akan Dinaikkan:</label>
                    <select id="current_class" name="current_class" required>
                        <?php for($i = 1; $i <= 11; $i++): ?>
                            <option value="<?= $i ?>">Kelas <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" class="button" onclick="closePromoteModal()" 
                            style="background-color: #6c757d; color: white;">
                        Batal
                    </button>
                    <button type="submit" name="promote_class" class="button btn-promote">
                        Naikkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openModal() {
            document.getElementById('dataModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Tambah Data Muda-Mudi';
            document.getElementById('dataForm').reset();
            document.getElementById('submitBtn').style.display = 'inline';
            document.getElementById('updateBtn').style.display = 'none';
        }

        function closeModal() {
            document.getElementById('dataModal').style.display = 'none';
            document.getElementById('dataForm').reset();
        }

        function openPromoteModal() {
            document.getElementById('promoteModal').style.display = 'block';
        }

        function closePromoteModal() {
            document.getElementById('promoteModal').style.display = 'none';
        }

        // Edit function
        function editData(id, nama, idDesa, idKelompok, tanggalLahir, kelas) {
            document.getElementById('modalTitle').textContent = 'Edit Data Muda-Mudi';
            document.getElementById('id_muda_mudi').value = id;
            document.getElementById('nama').value = nama;
            document.getElementById('id_desa').value = idDesa;
            document.getElementById('id_kelompok').value = idKelompok;
            document.getElementById('tanggal_lahir').value = tanggalLahir;
            document.getElementById('kelas').value = kelas;
            
            document.getElementById('submitBtn').style.display = 'none';
            document.getElementById('updateBtn').style.display = 'inline';
            document.getElementById('dataModal').style.display = 'block';
        }

        // Search function
        function searchData() {
            const searchTerm = document.getElementById('searchInput').value;
            window.location.href = `muda_mudi.php?search=${encodeURIComponent(searchTerm)}`;
        }

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

        // Close modals when clicking outside
        window.onclick = function(event) {
            const dataModal = document.getElementById('dataModal');
            const promoteModal = document.getElementById('promoteModal');
            if (event.target == dataModal) {
                closeModal();
            }
            if (event.target == promoteModal) {
                closePromoteModal();
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            dismissAlerts();
        });

        // Enter key search handler
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchData();
            }
        });
    </script>
</body>
</html>