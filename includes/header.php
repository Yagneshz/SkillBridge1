<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - SkillBridge' : 'SkillBridge - Connect Students with Companies'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --dark-color: #1f2937;
            --light-color: #f9fafb;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 5px;
        }
        
        .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .back-btn {
            background: var(--dark-color);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: var(--primary-color);
            transform: translateX(-5px);
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">
                <i class="fas fa-bridge"></i> SkillBridge
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <?php if ($_SESSION['user_type'] == 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>index.php"><i class="fas fa-home"></i> Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/companies.php"><i class="fas fa-building"></i> Companies</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/students.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                        <?php elseif ($_SESSION['user_type'] == 'company'): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>index.php"><i class="fas fa-home"></i> Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>company/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>company/profile.php"><i class="fas fa-user"></i> Profile</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>company/internships.php"><i class="fas fa-briefcase"></i> Internships</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>company/applications.php"><i class="fas fa-file-alt"></i> Applications</a></li>
                        <?php elseif ($_SESSION['user_type'] == 'student'): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>index.php"><i class="fas fa-home"></i> Home</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>student/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>student/profile.php"><i class="fas fa-user"></i> Profile</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>student/internships.php"><i class="fas fa-briefcase"></i> Browse Internships</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>student/companies.php"><i class="fas fa-building"></i> Companies</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>student/applications.php"><i class="fas fa-file-alt"></i> My Applications</a></li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>auth/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>index.php"><i class="fas fa-home"></i> Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>auth/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>auth/register.php"><i class="fas fa-user-plus"></i> Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

