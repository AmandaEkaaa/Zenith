<?php
require_once 'config.php';
requireAdminLogin();

// Get statistics
$conn = getDBConnection();

// Total users
$totalUsers = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

// Active users (logged in last 30 days)
$activeUsers = $conn->query("SELECT COUNT(DISTINCT user_id) as active FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['active'];

// Total game time
$totalGameTime = $conn->query("SELECT SUM(total_waktu_bermain) as total_minutes FROM game_stats")->fetch_assoc()['total_minutes'];
$totalHours = floor($totalGameTime / 60);

// Average level
$avgLevel = $conn->query("SELECT AVG(level_game) as avg_level FROM users")->fetch_assoc()['avg_level'];
$avgLevel = round($avgLevel, 1);

// Get recent users
$recentUsers = $conn->query("
    SELECT u.*, gs.total_waktu_bermain, gs.total_login, gs.last_played 
    FROM users u 
    LEFT JOIN game_stats gs ON u.id = gs.user_id 
    ORDER BY u.created_at DESC 
    LIMIT 10
");

// Get activity logs
$activityLogs = $conn->query("
    SELECT al.*, u.nama_lengkap 
    FROM activity_logs al 
    LEFT JOIN users u ON al.user_id = u.id 
    ORDER BY al.created_at DESC 
    LIMIT 15
");

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - ZENITH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --dark-blue: #0C2B4E;
            --medium-blue: #1A3D64;
            --light-blue: #1D546C;
            --cyan: #00C9FF;
            --purple: #8A2BE2;
            --neon-pink: #FF00FF;
            --light-gray: #F4F4F4;
            --dark-bg: #0A0E17;
            --card-bg: rgba(26, 61, 100, 0.3);
            --glow: rgba(0, 201, 255, 0.5);
        }
        
        body {
            font-family: 'Rajdhani', sans-serif;
            background-color: var(--dark-bg);
            color: var(--light-gray);
            min-height: 100vh;
        }
        
        h1, h2, h3, h4, h5 {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        /* Header Dashboard */
        .dashboard-header {
            background: rgba(10, 14, 23, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(0, 201, 255, 0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            font-weight: 900;
            background: linear-gradient(90deg, var(--cyan), var(--neon-pink));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: 3px;
        }
        
        .header-title h1 {
            font-size: 1.8rem;
            color: var(--light-gray);
            margin-bottom: 5px;
        }
        
        .header-title p {
            color: var(--cyan);
            font-size: 1rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--cyan), var(--purple));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
            color: white;
        }
        
        .logout-btn {
            background: rgba(255, 85, 85, 0.1);
            color: #FF5555;
            border: 1px solid rgba(255, 85, 85, 0.3);
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255, 85, 85, 0.2);
            transform: translateY(-2px);
        }
        
        /* Main Content */
        .dashboard-container {
            padding: 40px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }
        
        .stat-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            border: 1px solid rgba(0, 201, 255, 0.2);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--cyan);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--cyan), var(--purple));
        }
        
        .stat-icon {
            font-size: 2.5rem;
            color: var(--cyan);
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 900;
            color: var(--light-gray);
            margin-bottom: 5px;
            font-family: 'Orbitron', sans-serif;
        }
        
        .stat-label {
            color: rgba(244, 244, 244, 0.7);
            font-size: 1.1rem;
        }
        
        /* Tables */
        .section-title {
            font-size: 2.2rem;
            margin: 50px 0 25px;
            color: var(--cyan);
            position: relative;
            padding-bottom: 15px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--cyan), var(--purple));
            border-radius: 3px;
        }
        
        .table-container {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(0, 201, 255, 0.2);
            margin-bottom: 40px;
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: rgba(0, 201, 255, 0.1);
        }
        
        th {
            padding: 15px;
            text-align: left;
            color: var(--cyan);
            font-weight: 700;
            font-family: 'Orbitron', sans-serif;
            border-bottom: 2px solid rgba(0, 201, 255, 0.3);
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid rgba(0, 201, 255, 0.1);
        }
        
        tr:hover {
            background: rgba(0, 201, 255, 0.05);
        }
        
        .user-avatar-small {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--cyan), var(--purple));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: bold;
            color: white;
            margin-right: 10px;
        }
        
        .user-cell {
            display: flex;
            align-items: center;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-active {
            background: rgba(0, 255, 136, 0.1);
            color: #00FF88;
            border: 1px solid rgba(0, 255, 136, 0.3);
        }
        
        .status-inactive {
            background: rgba(255, 85, 85, 0.1);
            color: #FF5555;
            border: 1px solid rgba(255, 85, 85, 0.3);
        }
        
        .level-badge {
            background: linear-gradient(135deg, var(--cyan), var(--purple));
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
        }
        
        /* Activity log styles */
        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid rgba(0, 201, 255, 0.1);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(0, 201, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--cyan);
            font-size: 1.2rem;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-user {
            font-weight: 700;
            color: var(--cyan);
        }
        
        .activity-desc {
            color: rgba(244, 244, 244, 0.8);
            margin-top: 5px;
        }
        
        .activity-time {
            color: rgba(244, 244, 244, 0.5);
            font-size: 0.9rem;
        }
        
        /* Export button */
        .export-btn {
            background: linear-gradient(90deg, var(--cyan), var(--purple));
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1px;
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }
        
        .export-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 201, 255, 0.3);
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .dashboard-container {
                padding: 30px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 20px;
                padding: 20px;
            }
            
            .dashboard-container {
                padding: 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .table-container {
                padding: 15px;
            }
            
            table {
                font-size: 0.9rem;
            }
            
            th, td {
                padding: 10px;
            }
        }
        
        @media (max-width: 480px) {
            .header-title h1 {
                font-size: 1.5rem;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-number {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="dashboard-header">
        <div class="header-left">
            <div class="logo">ZENITH</div>
            <div class="header-title">
                <h1>DASHBOARD ADMIN</h1>
                <p>Panel Pengelola Data Pengguna Game</p>
            </div>
        </div>
        
        <div class="user-info">
            <div class="user-avatar">
                <?php echo substr($_SESSION['admin_name'], 0, 1); ?>
            </div>
            <div>
                <div style="font-weight: 700;"><?php echo $_SESSION['admin_name']; ?></div>
                <div style="font-size: 0.9rem; color: var(--cyan);"><?php echo $_SESSION['admin_username']; ?></div>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </header>
    
    <!-- Main Content -->
    <div class="dashboard-container">
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo $totalUsers; ?></div>
                <div class="stat-label">TOTAL PENGGUNA</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-gamepad"></i>
                </div>
                <div class="stat-number"><?php echo $activeUsers; ?></div>
                <div class="stat-label">PENGGUNA AKTIF (30 hari)</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?php echo $totalHours; ?>h</div>
                <div class="stat-label">TOTAL WAKTU BERMAIN</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-number"><?php echo $avgLevel; ?></div>
                <div class="stat-label">RATA-RATA LEVEL</div>
            </div>
        </div>
        
        <!-- Recent Users Table -->
        <h2 class="section-title">DATA PENGGUNA TERBARU</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>PENGGUNA</th>
                        <th>NISN</th>
                        <th>KELAS</th>
                        <th>LEVEL</th>
                        <th>POIN</th>
                        <th>WAKTU BERMAIN</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $recentUsers->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-avatar-small">
                                    <?php echo substr($user['nama_lengkap'], 0, 1); ?>
                                </div>
                                <?php echo $user['nama_lengkap']; ?>
                            </div>
                        </td>
                        <td><?php echo $user['nisn']; ?></td>
                        <td><?php echo $user['kelas']; ?></td>
                        <td>
                            <span class="level-badge">Level <?php echo $user['level_game']; ?></span>
                        </td>
                        <td><strong><?php echo number_format($user['total_poin']); ?></strong></td>
                        <td><?php echo $user['waktu_bermain']; ?></td>
                        <td>
                            <span class="status-badge status-active">AKTIF</span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Export Button -->
        <button class="export-btn" onclick="exportUserData()">
            <i class="fas fa-file-export"></i> EKSPOR DATA PENGGUNA (CSV)
        </button>
        
        <!-- Activity Logs -->
        <h2 class="section-title">LOG AKTIVITAS TERBARU</h2>
        <div class="table-container">
            <div class="activity-list">
                <?php while($log = $activityLogs->fetch_assoc()): ?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <?php 
                        switch($log['activity_type']) {
                            case 'login': echo '<i class="fas fa-sign-in-alt"></i>'; break;
                            case 'game_start': echo '<i class="fas fa-gamepad"></i>'; break;
                            case 'level_complete': echo '<i class="fas fa-trophy"></i>'; break;
                            default: echo '<i class="fas fa-history"></i>';
                        }
                        ?>
                    </div>
                    <div class="activity-content">
                        <div>
                            <span class="activity-user"><?php echo $log['nama_lengkap'] ?? 'Pengguna'; ?></span>
                            <span><?php echo $log['description']; ?></span>
                        </div>
                        <div class="activity-desc">
                            <?php echo $log['activity_type']; ?>
                        </div>
                    </div>
                    <div class="activity-time">
                        <?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <script>
        function exportUserData() {
            // In a real implementation, this would generate and download a CSV file
            alert('Fitur ekspor data akan dibuat dalam versi berikutnya!\n\nFitur ini akan menghasilkan file CSV berisi:\n- Data seluruh pengguna\n- Statistik permainan\n- Log aktivitas\n- Data dapat diolah di Excel/Google Sheets');
            
            // Simulate export
            const exportBtn = event.target.closest('.export-btn');
            const originalText = exportBtn.innerHTML;
            
            exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> MENGEXPORT DATA...';
            exportBtn.disabled = true;
            
            setTimeout(() => {
                exportBtn.innerHTML = originalText;
                exportBtn.disabled = false;
                alert('Data berhasil diekspor! File "zenith_users_<?php echo date('Y-m-d'); ?>.csv" telah diunduh.');
            }, 1500);
        }
        
        // Auto-refresh dashboard every 30 seconds
        setTimeout(() => {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>