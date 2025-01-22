<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="style/dashboard.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f3f4f6;
    min-height: 100vh;
}

/* Sidebar Styling */
.sidebar {
    background-color: #343a40;
    width: 250px;
    height: 100vh;
    position: fixed;
    top: 60px;
    left: 0;
    padding-top: 20px;
    z-index: 99;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
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
    display: block;
    transition: all 0.3s ease-in-out;
    border-bottom: 1px solid #0062cc;
    font-size: 1rem;
}

.sidebar-menu li a:hover {
    background-color: #0062cc;
    padding-left: 25px;
}

.sidebar-menu li a i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

/* Submenu Styling */
.submenu {
    display: none;
    list-style: none;
    background-color: #0062cc;
}

.submenu li a {
    padding: 12px 20px 12px 50px;
    font-size: 0.9rem;
    border-bottom: 1px solid #0062cc;
}

.sidebar-menu li.active > .submenu {
    display: block;
}

/* Main Content Area */
.main-content {
    margin-left: 250px;
    margin-top: 60px;
    padding: 30px;
    min-height: calc(100vh - 60px);
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 1;
}

.main-content h1 {
    margin-top: 0;
    padding-top: 20px;
    margin-bottom: 30px;
    color: #333;
    font-size: 2rem;
    font-weight: 600;
}

/* Dashboard Cards */
.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(4, 1fr); 
    gap: 15px; 
    margin-bottom: 40px;
}

.card {
    background-color: #ffffff;
    padding: 20px; 
    border-radius: 10px; 
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease-in-out;
    cursor: pointer;
    border: 1px solid #eaeaea;
    position: relative;
    overflow: hidden;
}

.card:hover {
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    transform: translateY(-5px);
    background-color: #f8f9fa;
}

.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background-color: #007bff;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.card:hover::before {
    opacity: 1;
}

.card-title {
    font-size: 1.2rem;
    color: #6c757d;
    margin-bottom: 15px;
    font-weight: 500;
}

.card-value {
    font-size: 2.5rem;
    font-weight: bold;
    color: #007bff;
    line-height: 1.2;
}

/* Chart Container */
.chart-container {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-top: 30px;
    height: 400px;
}

.row {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

.col-md-12, .col-md-6 {
    flex: 1;
}

.chart-container canvas {
    width: 100% !important;
    height: 100% !important;
}

.filter-container {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    margin-bottom: 20px;
    gap: 10px;
}

.filter-container label {
    font-weight: bold;
    margin-right: 10px;
}

#timeFilter {
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ddd;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .dashboard-cards {
        grid-template-columns: repeat(2, 1fr);
    }
}


.dashboard-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .dashboard-cards .card {
            flex: 1;
            margin: 0 10px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .chart-container {
            height: 350px;
        }
        .charts-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .charts-row > div {
            flex: 1;
        }


    </style>
</head>
<body>
    <?php include('sidebar.php'); ?>

    <div class="main-content">
        <h1>Dashboard</h1>

        <div class="dashboard-cards">
            <div class="card" id="totalBarangCard">
                <div class="card-title">Jumlah Muda Mudi</div>
                <div class="card-value" id="totalBarangValue">0</div>
            </div>
            <div class="card" id="totalSupplierCard">
                <div class="card-title">SMP</div>
                <div class="card-value" id="totalSupplierValue">0</div>
            </div>
            <div class="card" id="penjualanCard">
                <div class="card-title">SMA</div>
                <div class="card-value" id="penjualanValue">0</div>
            </div>
            <div class="card" id="barangTerjualCard">
                <div class="card-title">Gema</div>
                <div class="card-value" id="barangTerjualValue">0</div>
            </div>
        </div>
    </div>
</body>
</html>
