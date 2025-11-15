<?php
require_once '../config/config.php';
requireUserType(['admin']);

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total companies
$stats['total_companies'] = $conn->query("SELECT COUNT(*) as total FROM companies")->fetch_assoc()['total'];

// Total students
$stats['total_students'] = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];

// Total internships
$stats['total_internships'] = $conn->query("SELECT COUNT(*) as total FROM internships")->fetch_assoc()['total'];

// Active internships
$stats['active_internships'] = $conn->query("SELECT COUNT(*) as total FROM internships WHERE status = 'active'")->fetch_assoc()['total'];

// Total applications
$stats['total_applications'] = $conn->query("SELECT COUNT(*) as total FROM applications")->fetch_assoc()['total'];

// Pending applications
$stats['pending_applications'] = $conn->query("SELECT COUNT(*) as total FROM applications WHERE status = 'pending'")->fetch_assoc()['total'];

// User type distribution for chart
$user_types = $conn->query("SELECT user_type, COUNT(*) as count FROM users WHERE user_type != 'admin' GROUP BY user_type")->fetch_all(MYSQLI_ASSOC);
$user_type_data = ['Student' => 0, 'Company' => 0];
foreach ($user_types as $ut) {
    if ($ut['user_type'] == 'student') {
        $user_type_data['Student'] = $ut['count'];
    } elseif ($ut['user_type'] == 'company') {
        $user_type_data['Company'] = $ut['count'];
    }
}

// Recent activities
$recent_internships = $conn->query("SELECT i.*, c.company_name FROM internships i 
                                    JOIN companies c ON i.company_id = c.id 
                                    ORDER BY i.posted_date DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

$pageTitle = 'Admin Dashboard';
include '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
    </div>
</div>

<div class="container my-5">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="100">
            <div class="stats-card">
                <h3><?php echo $stats['total_companies']; ?></h3>
                <p><i class="fas fa-building"></i> Total Companies</p>
            </div>
        </div>
        <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="200">
            <div class="stats-card" style="border-left-color: var(--primary-color);">
                <h3 style="color: var(--primary-color);"><?php echo $stats['total_students']; ?></h3>
                <p><i class="fas fa-user-graduate"></i> Total Students</p>
            </div>
        </div>
        <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="300">
            <div class="stats-card" style="border-left-color: var(--success-color);">
                <h3 style="color: var(--success-color);"><?php echo $stats['total_internships']; ?></h3>
                <p><i class="fas fa-briefcase"></i> Total Internships</p>
            </div>
        </div>
        <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="400">
            <div class="stats-card" style="border-left-color: var(--warning-color);">
                <h3 style="color: var(--warning-color);"><?php echo $stats['total_applications']; ?></h3>
                <p><i class="fas fa-file-alt"></i> Total Applications</p>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="500">
            <div class="stats-card" style="border-left-color: var(--success-color);">
                <h3 style="color: var(--success-color);"><?php echo $stats['active_internships']; ?></h3>
                <p><i class="fas fa-check-circle"></i> Active Internships</p>
            </div>
        </div>
        <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="600">
            <div class="stats-card" style="border-left-color: var(--warning-color);">
                <h3 style="color: var(--warning-color);"><?php echo $stats['pending_applications']; ?></h3>
                <p><i class="fas fa-clock"></i> Pending Applications</p>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- User Type Chart -->
        <div class="col-md-6 mb-4" data-aos="fade-up">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie"></i> User Type Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="userTypeChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Internships -->
        <div class="col-md-6 mb-4" data-aos="fade-up">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-clock"></i> Recent Internships</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_internships)): ?>
                        <p class="text-muted">No internships posted yet.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($recent_internships as $internship): ?>
                                <div class="list-group-item">
                                    <h6><?php echo htmlspecialchars($internship['title']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($internship['company_name']); ?></small>
                                    <span class="badge bg-<?php 
                                        echo $internship['status'] == 'active' ? 'success' : 
                                            ($internship['status'] == 'closed' ? 'danger' : 'secondary'); 
                                    ?> float-end">
                                        <?php echo ucfirst($internship['status']); ?>
                                    </span>
                                    <br><small class="text-muted">Posted: <?php echo formatDateTime($internship['posted_date']); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// User Type Chart
const ctx = document.getElementById('userTypeChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Students', 'Companies'],
        datasets: [{
            data: [
                <?php echo $user_type_data['Student']; ?>,
                <?php echo $user_type_data['Company']; ?>
            ],
            backgroundColor: [
                '#6366f1',
                '#8b5cf6'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>

