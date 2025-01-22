<?php 
require 'koneksi.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit'])) {
        $nama_kelompok = trim($_POST['nama_kelompok']);
        $id_desa = trim($_POST['id_desa']);
        $jumlah_mudamudi = trim($_POST['jumlah_mudamudi']);

        if (empty($nama_kelompok) || empty($id_desa) || empty($jumlah_mudamudi)) {
            $_SESSION['error_message'] = "Semua field harus diisi!";
        } else {
            // Get last code from database
            $query = "SELECT id_kelompok FROM kelompok ORDER BY id_kelompok DESC LIMIT 1";
            $result = $config->query($query);
            $row = $result->fetch_assoc();

            if ($row) {
                $last_kode = intval(substr($row['id_kelompok'], 1));
                $new_kode = 'K' . str_pad($last_kode + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $new_kode = 'K001';
            }

            $stmt = $config->prepare("INSERT INTO kelompok (id_kelompok, nama_kelompok, id_desa, jumlah_mudamudi) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $new_kode, $nama_kelompok, $id_desa, $jumlah_mudamudi);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Kelompok berhasil ditambahkan!";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan Kelompok: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    elseif (isset($_POST['update'])) {
        $id_kelompok = $_POST['id_kelompok'];
        $nama_kelompok = trim($_POST['nama_kelompok']);
        $id_desa = trim($_POST['id_desa']);
        $jumlah_mudamudi = trim($_POST['jumlah_mudamudi']);

        if (empty($nama_kelompok) || empty($id_desa) || empty($jumlah_mudamudi)) {
            $_SESSION['error_message'] = "Semua field harus diisi!";
        } else {
            $stmt = $config->prepare("UPDATE kelompok SET nama_kelompok = ?, id_desa = ?, jumlah_mudamudi = ? WHERE id_kelompok = ?");
            $stmt->bind_param("ssis", $nama_kelompok, $id_desa, $jumlah_mudamudi, $id_kelompok);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Kelompok berhasil diupdate!";
            } else {
                $_SESSION['error_message'] = "Gagal mengupdate kelompok: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    elseif (isset($_POST['delete'])) {
        $id_kelompok = $_POST['id_kelompok'];

        $stmt = $config->prepare("DELETE FROM kelompok WHERE id_kelompok = ?");
        $stmt->bind_param("s", $id_kelompok);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Kelompok berhasil dihapus!";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus Kelompok: " . $stmt->error;
        }
        $stmt->close();
    }
}

$search_term = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $_GET['search'];
    $kelompok_query = "SELECT k.*, d.nama_desa FROM kelompok k 
                      JOIN desa d ON k.id_desa = d.id_desa 
                      WHERE k.nama_kelompok LIKE ? OR d.nama_desa LIKE ?
                      ORDER BY k.id_kelompok DESC";
    $stmt = $config->prepare($kelompok_query);
    $like_term = "%" . $search_term . "%";
    $stmt->bind_param("ss", $like_term, $like_term);
    $stmt->execute();
    $kelompok_result = $stmt->get_result();
    $stmt->close();
} else {
    $kelompok_query = "SELECT k.*, d.nama_desa FROM kelompok k 
                      JOIN desa d ON k.id_desa = d.id_desa 
                      ORDER BY k.id_kelompok DESC";
    $kelompok_result = $config->query($kelompok_query);
}

// Get desa data for dropdown
$desa_query = "SELECT * FROM desa ORDER BY nama_desa";
$desa_result = $config->query($desa_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelompok</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Base styles - same as desa page */
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

        /* Table styles - same as desa page */
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

        .btn-add, .btn-update {
            background-color: #0A3981;
            color: white;
        }

        .btn-add:hover, .btn-update:hover {
            background-color: #072a61;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        /* Search form */
        .search-form {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            gap: 10px;
        }

        .search-form input[type="text"] {
            width: 250px;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
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

            .search-form {
                flex-direction: column;
                align-items: stretch;
            }

            .search-form input[type="text"] {
                width: 100%;
            }
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
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include('sidebar.php'); ?>

        <div class="main-content">
            <div class="container">
                <h1>Data Kelompok</h1>

                <!-- Add button and search form -->
                <div class="search-form">
                    <button onclick="openModal()" class="button btn-add">
                        <i class="fas fa-plus"></i> Tambah Kelompok
                    </button>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="searchInput" placeholder="Search..." 
                               onkeyup="if(event.key === 'Enter') document.getElementById('searchButton').click()">
                        <button id="searchButton" onclick="searchKelompok()" class="button btn-add">
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
                            <th>Kode Kelompok</th>
                            <th>Nama Kelompok</th>
                            <th>Desa</th>
                            <th>Jumlah Muda-Mudi</th>
                            <th>Aksi</th>
                        </tr>
                        <?php
                        $no = 1;
                        while ($kelompok = $kelompok_result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= $kelompok['id_kelompok']; ?></td>
                            <td><?= $kelompok['nama_kelompok']; ?></td>
                            <td><?= $kelompok['nama_desa']; ?></td>
                            <td><?= $kelompok['jumlah_mudamudi']; ?></td>
                            <td>
                                <button class="button btn-update" 
                                    onclick="editKelompok('<?= $kelompok['id_kelompok']; ?>', 
                                                        '<?= htmlspecialchars($kelompok['nama_kelompok']); ?>', 
                                                        '<?= $kelompok['id_desa']; ?>', 
                                                        <?= $kelompok['jumlah_mudamudi']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id_kelompok" value="<?= $kelompok['id_kelompok']; ?>">
                                    <button type="submit" name="delete" class="button btn-delete"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus kelompok ini?')">
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

    <!-- Modal Form -->
    <div id="kelompokModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Tambah Kelompok</h2>
            <form id="kelompokForm" method="POST" action="kelompok.php">
                <input type="hidden" id="id_kelompok" name="id_kelompok">
                
                <div class="form-group">
                    <label for="nama_kelompok">Nama Kelompok:</label>
                    <input type="text" id="nama_kelompok" name="nama_kelompok" required>
                    </div>
                
                <div class="form-group">
                    <label for="id_desa">Desa:</label>
                    <select id="id_desa" name="id_desa" required>
                        <option value="">Pilih Desa</option>
                        <?php 
                        while ($desa = $desa_result->fetch_assoc()) {
                            echo '<option value="' . $desa['id_desa'] . '">' . 
                                 htmlspecialchars($desa['nama_desa']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="jumlah_mudamudi">Jumlah Muda-Mudi:</label>
                    <input type="number" id="jumlah_mudamudi" name="jumlah_mudamudi" min="0" required>
                </div>
                
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" class="button" onclick="closeModal()" style="background-color: #6c757d; color: white;">
                        Batal
                    </button>
                    <button type="submit" id="submitBtn" name="submit" class="button btn-add">
                        Simpan
                    </button>
                    <button type="submit" id="updateBtn" name="update" class="button btn-update" style="display:none;">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openModal() {
            document.getElementById('kelompokModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Tambah Kelompok';
            document.getElementById('kelompokForm').reset();
            document.getElementById('submitBtn').style.display = 'inline';
            document.getElementById('updateBtn').style.display = 'none';
        }

        function closeModal() {
            document.getElementById('kelompokModal').style.display = 'none';
            document.getElementById('kelompokForm').reset();
        }

        // Edit function
        function editKelompok(id, nama, idDesa, jumlah) {
            document.getElementById('modalTitle').textContent = 'Edit Kelompok';
            document.getElementById('id_kelompok').value = id;
            document.getElementById('nama_kelompok').value = nama;
            document.getElementById('id_desa').value = idDesa;
            document.getElementById('jumlah_mudamudi').value = jumlah;
            
            document.getElementById('submitBtn').style.display = 'none';
            document.getElementById('updateBtn').style.display = 'inline';
            document.getElementById('kelompokModal').style.display = 'block';
        }

        // Search function
        function searchKelompok() {
            const searchTerm = document.getElementById('searchInput').value;
            window.location.href = `kelompok.php?search=${encodeURIComponent(searchTerm)}`;
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

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('kelompokModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            dismissAlerts();
        });

        // Enter key search handler
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchKelompok();
            }
        });
    </script>
</body>
</html>