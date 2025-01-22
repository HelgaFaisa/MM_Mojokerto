<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard dengan Sidebar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Amoresa&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            overflow-x: hidden;
        }

        /* Navbar Styling */
        .navbar {
            background-color: #0A3981;
            color: #ffffff;
            padding: 1rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .toggle-sidebar {
            color: #ffffff;
            font-size: 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .navbar-right {
            margin-left: auto;
        }

        .navbar a {
            color: #ffffff;
            text-decoration: none;
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
        }

        .navbar a:hover {
            color: #f0f0f0;
        }

        /* Sidebar Styling */
        .sidebar {
            background-color: #0A3981;
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 60px;
            left: 0;
            padding-top: 20px;
            z-index: 99;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .sidebar-header {
            text-align: center;
            margin-bottom: 20px;
            padding: 0 10px;
        }

        .sidebar-header img {
            width: 80px;
            height: auto;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed .sidebar-header img {
            width: 40px;
        }

        .sidebar-header h3 {
            color: #ffffff;
            font-size: 1.2rem;
            margin-top: 10px;
            transition: all 0.3s ease;
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar.collapsed .sidebar-header h3 {
            display: none;
        }

        .sidebar-menu {
            list-style: none;
            padding-left: 0;
        }

        .sidebar-menu li {
            position: relative;
        }

        .sidebar-menu li a {
            color: #ffffff;
            text-decoration: none;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-bottom: 1px solid #0062cc;
            font-size: 1rem;
            white-space: nowrap;
        }

        .sidebar-menu li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .sidebar.collapsed .sidebar-menu li a span {
            display: none;
        }

        .sidebar-menu li a:hover {
            background-color: #0062cc;
            padding-left: 25px;
        }

        /* Submenu Styling */
        .submenu {
            display: none;
            list-style: none;
            background-color: #0A3981;
        }

        .submenu li a {
            padding: 12px 20px 12px 50px;
            font-size: 0.9rem;
        }

        .sidebar-menu li.active > .submenu {
            display: block;
        }

        .sidebar.collapsed .submenu {
            position: absolute;
            left: 70px;
            top: 0;
            width: 200px;
            display: none;
        }

        .sidebar.collapsed .menu-toggle:hover > .submenu {
            display: block;
        }

        /* Main Content Area */
        .main-content {
            margin-left: 250px;
            margin-top: 60px;
            padding: 30px;
            min-height: calc(100vh - 60px);
            background: white;
            transition: all 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 70px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }

            .sidebar.collapsed {
                left: 0;
                width: 250px;
            }

            .sidebar.collapsed .sidebar-header h3,
            .sidebar.collapsed .sidebar-menu li a span {
                display: block;
            }

            .sidebar.collapsed .sidebar-header img {
                width: 80px;
            }

            .main-content {
                margin-left: 0;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .toggle-sidebar {
                display: block;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="toggle-sidebar">
        <i class="fas fa-bars"></i>
    </div>
    <div class="navbar-right">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="img/logo_baru.png" alt="Logo">
        <h3>MM Mojokerto Barat</h3>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="index.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="menu-toggle">
            <a href="javascript:void(0)">
                <i class="fas fa-cogs"></i>
                <span>Data Master</span>
            </a>
            <ul class="submenu">
                <li><a href="mudamudi.php"><i class="fas fa-tshirt"></i> <span>Muda Mudi</span></a></li>
                <li><a href="kelompok.php"><i class="fas fa-th"></i> <span>Kelompok</span></a></li>
                <li><a href="desa.php"><i class="fas fa-truck"></i> <span>Desa</span></a></li>
            </ul>
        </li>

        <li class="menu-toggle">
            <a href="javascript:void(0)">
                <i class="fas fa-sync-alt"></i>
                <span>Absensi</span>
            </a>
            <ul class="submenu">
                <li><a href="scanAbsen.php"><i class="fas fa-file-alt"></i> <span>Absen</span></a></li>
                <li><a href="rekapAbsen.php"><i class="fas fa-file-alt"></i> <span>Rekap Absensi</span></a></li>
            </ul>
        </li>

        <li>
            <a href="laporan.php">
                <i class="fas fa-undo"></i>
                <span>Laporan</span>
            </a>
        </li>
    </ul>
</div>

<script>
    // Toggle sidebar
    document.querySelector('.toggle-sidebar').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');
        
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    });

    // Toggle submenu
    document.querySelectorAll('.menu-toggle').forEach(function(menu) {
        menu.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                this.classList.toggle('active');
            }
        });

        // Handle hover for desktop
        menu.addEventListener('mouseenter', function() {
            if (window.innerWidth > 768) {
                this.classList.add('active');
            }
        });

        menu.addEventListener('mouseleave', function() {
            if (window.innerWidth > 768) {
                this.classList.remove('active');
            }
        });
    });
</script>

</body>
</html>