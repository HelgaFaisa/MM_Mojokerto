<?php 
require 'koneksi.php';

session_start(); 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit'])) {
        $nama_desa = trim($_POST['nama_desa']);

        if (empty($nama_desa)) {
            $_SESSION['error_message'] = "Nama desa tidak boleh kosong!";
        } else {
            $tgl_input = date('Y-m-d');

            $query = "SELECT id_desa FROM desa ORDER BY id_desa DESC LIMIT 1";
            $result = $config->query($query);
            $row = $result->fetch_assoc();

            if ($row) {
                $last_kode = intval(substr($row['id_desa'], 2));
                $new_kode = 'D' . str_pad($last_kode + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $new_kode = 'D001';
            }

            $stmt = $config->prepare("INSERT INTO desa (id_desa, nama_desa) VALUES (?, ?)");
            $stmt->bind_param("ss", $new_kode, $nama_desa);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Desa berhasil ditambahkan!";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan Desa: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    elseif (isset($_POST['update'])) {
        $id_desa = $_POST['id_desa'];
        $nama_desa = trim($_POST['nama_desa']);

        if (empty($nama_desa)) {
            $_SESSION['error_message'] = "Nama Desa tidak boleh kosong!";
        } else {
            $stmt = $config->prepare("UPDATE desa SET nama_desa = ? WHERE id_desa = ?");
            $stmt->bind_param("ss", $nama_desa, $id_desa);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Desa berhasil diupdate!";
            } else {
                $_SESSION['error_message'] = "Gagal mengupdate nama desa: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    elseif (isset($_POST['delete'])) {
        $id_desa = $_POST['id_desa'];

        $stmt = $config->prepare("DELETE FROM desa WHERE id_desa = ?");
        $stmt->bind_param("s", $id_desa);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Desa berhasil dihapus!";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus Desa: " . $stmt->error;
        }
        $stmt->close();
    }
}

$search_term = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $_GET['search'];
    $kategori_query = "SELECT * FROM desa WHERE nama_desa LIKE ? ORDER BY id_desa DESC";
    $stmt = $config->prepare($kategori_query);
    $like_term = "%" . $search_term . "%";
    $stmt->bind_param("s", $like_term);
    $stmt->execute();
    $desa_result = $stmt->get_result();
    $stmt->close();
} else {
    $desa_query = "SELECT * FROM desa ORDER BY id_desa DESC";
    $desa_result = $config->query($desa_query);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
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
            margin-left: 250px; /* Match sidebar width */
            transition: margin-left 0.3s;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }

        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 10px; /* Space for fixed navbar */
        }

        .message-container {
            width: 100%;
            margin-bottom: 20px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid transparent;
            border-radius: 4px;
            opacity: 1;
            transition: opacity 0.5s ease-out, transform 0.5s ease-out;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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

        .alert.fade-out {
            opacity: 0;
            transform: translateY(-100%);
        }

        input[type="text"] {
            width: 60%;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            border: 1px solid #ced4da;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
        }

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
            background-color: #0A3981; /* Changed to navy blue */
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

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

        .search-form {
            display: flex;
            justify-content: flex-end;
            margin: 20px 0;
            gap: 10px;
        }

        .search-form input[type="text"] {
            width: 250px;
            padding: 8px;
        }

        .search-form button {
            background-color: #0A3981; /* Changed to navy blue */
            color: white;
        }

        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
                align-items: stretch;
            }

            .search-form input[type="text"] {
                width: 100%;
            }

            input[type="text"] {
                width: 100%;
            }

            .table-responsive {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include('sidebar.php'); ?>

        <div class="main-content">
            <div class="container">
                <h1>Data Desa</h1>

                <form method="POST" action="desa.php" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <input type="hidden" id="id_desa" name="id_desa">
                    <input type="text" id="nama_desa" name="nama_desa" placeholder="Masukan Nama Desa" required>
                    <button type="submit" id="submitBtn" name="submit" class="button btn-add">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button type="submit" id="updateBtn" name="update" class="button btn-update" style="display:none;">
                        <i class="fas fa-pen"></i>
                    </button>
                </form>

                <form method="GET" action="desa.php" class="search-form">
                    <input type="text" name="search" placeholder="Search" value="<?= htmlspecialchars($search_term); ?>">
                    <button type="submit" class="button">
                        <i class="fas fa-search"></i>
                    </button>
                </form>

                <div class="message-container">
                    <?php
                    if (isset($_SESSION['success_message'])) {
                        echo '<div class="alert alert-success">' . 
                             htmlspecialchars($_SESSION['success_message']) . 
                             '</div>';
                        unset($_SESSION['success_message']);
                    }
                    ?>
                </div>

                <div class="table-responsive">
                    <table>
                        <tr>
                            <th>No</th>
                            <th>Kode Desa</th>
                            <th>Nama Desa</th>
                            <th>Aksi</th>
                        </tr>
                        <?php
                        $no = 1;
                        while ($desa = $desa_result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= $desa['id_desa']; ?></td>
                            <td><?= $desa['nama_desa']; ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id_desa" value="<?= $desa['id_desa']; ?>">
                                    <button type="submit" name="delete" 
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus desa ini?');"
                                        class="button btn-delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                                <button class="button btn-update" 
                                    onclick="editDesa('<?= $desa['id_desa']; ?>', '<?= htmlspecialchars($desa['nama_desa']); ?>')">
                                    <i class="fa fa-pencil-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editDesa(id, nama) {
            document.getElementById('id_desa').value = id;
            document.getElementById('nama_desa').value = nama;
            document.getElementById('submitBtn').style.display = 'none';
            document.getElementById('updateBtn').style.display = 'inline';
        }

        function dismissAlerts() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.add('fade-out');
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }, 5000);
            });
        }

        document.addEventListener('DOMContentLoaded', dismissAlerts);
    </script>
</body>
</html>