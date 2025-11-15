<?php
require_once 'config/config.php';

$pageTitle = 'Home';
include 'includes/header.php';
?>

<div class="page-header">
    <div class="container text-center">
        <h1 data-aos="fade-down">Welcome to SkillBridge</h1>
        <p data-aos="fade-up" class="lead">Connecting Students with Companies for Internship Opportunities</p>
    </div>
</div>

<div class="container my-5">
    <?php if (!isLoggedIn()): ?>
        <div class="row text-center mb-5">
            <div class="col-md-12">
                <h2 data-aos="fade-up">Get Started</h2>
                <p class="lead" data-aos="fade-up">Choose your role to begin</p>
                <div class="mt-4">
                    <a href="auth/login.php" class="btn btn-primary btn-lg me-3" data-aos="zoom-in">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="auth/register.php" class="btn btn-outline-primary btn-lg" data-aos="zoom-in">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row text-center mb-5">
            <div class="col-md-12">
                <h2 data-aos="fade-up">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                <p class="lead" data-aos="fade-up">Navigate to your dashboard to get started</p>
                <div class="mt-4">
                    <?php if ($_SESSION['user_type'] == 'admin'): ?>
                        <a href="admin/dashboard.php" class="btn btn-primary btn-lg" data-aos="zoom-in">
                            <i class="fas fa-tachometer-alt"></i> Go to Admin Dashboard
                        </a>
                    <?php elseif ($_SESSION['user_type'] == 'company'): ?>
                        <a href="company/dashboard.php" class="btn btn-primary btn-lg" data-aos="zoom-in">
                            <i class="fas fa-tachometer-alt"></i> Go to Company Dashboard
                        </a>
                    <?php elseif ($_SESSION['user_type'] == 'student'): ?>
                        <a href="student/dashboard.php" class="btn btn-primary btn-lg" data-aos="zoom-in">
                            <i class="fas fa-tachometer-alt"></i> Go to Student Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="row mt-5">
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-user-graduate fa-3x text-primary mb-3"></i>
                    <h4>For Students</h4>
                    <p>Find internships, build your profile, and connect with top companies.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-building fa-3x text-primary mb-3"></i>
                    <h4>For Companies</h4>
                    <p>Post internships, find talented students, and grow your team.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-briefcase fa-3x text-primary mb-3"></i>
                    <h4>Internship Opportunities</h4>
                    <p>Browse through hundreds of internship opportunities across various fields.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

