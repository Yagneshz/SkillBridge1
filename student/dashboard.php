<?php
require_once '../config/config.php';
requireUserType(['student']);

$conn = getDBConnection();
$student_id = $_SESSION['user_id'];

// Get student profile
$stmt = $conn->prepare("SELECT s.* FROM students s WHERE s.user_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    redirect('profile.php');
}

$student_id_db = $student['id'];

// Get statistics
$stats = [];

// Total applications
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM applications WHERE student_id = ?");
$stmt->bind_param("i", $student_id_db);
$stmt->execute();
$stats['total_applications'] = $stmt->get_result()->fetch_assoc()['total'];

// Pending applications
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM applications WHERE student_id = ? AND status = 'pending'");
$stmt->bind_param("i", $student_id_db);
$stmt->execute();
$stats['pending'] = $stmt->get_result()->fetch_assoc()['total'];

// Accepted applications
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM applications WHERE student_id = ? AND status = 'accepted'");
$stmt->bind_param("i", $student_id_db);
$stmt->execute();
$stats['accepted'] = $stmt->get_result()->fetch_assoc()['total'];

// Rejected applications
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM applications WHERE student_id = ? AND status = 'rejected'");
$stmt->bind_param("i", $student_id_db);
$stmt->execute();
$stats['rejected'] = $stmt->get_result()->fetch_assoc()['total'];

// Recent applications
$stmt = $conn->prepare("SELECT a.*, i.title, i.company_id, c.company_name 
                        FROM applications a 
                        JOIN internships i ON a.internship_id = i.id 
                        JOIN companies c ON i.company_id = c.id 
                        WHERE a.student_id = ? 
                        ORDER BY a.applied_at DESC 
                        LIMIT 5");
$stmt->bind_param("i", $student_id_db);
$stmt->execute();
$recent_applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Application status distribution for chart
$status_data = [
    'Pending' => $stats['pending'],
    'Accepted' => $stats['accepted'],
    'Rejected' => $stats['rejected']
];

closeDBConnection($conn);

$pageTitle = 'Student Dashboard';
include '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-tachometer-alt"></i> Student Dashboard</h1>
        <p>Welcome back, <?php echo htmlspecialchars($student['full_name']); ?>!</p>
    </div>
</div>

<div class="container my-5">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="100">
            <div class="stats-card">
                <h3><?php echo $stats['total_applications']; ?></h3>
                <p><i class="fas fa-file-alt"></i> Total Applications</p>
            </div>
        </div>
        <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="200">
            <div class="stats-card" style="border-left-color: var(--warning-color);">
                <h3 style="color: var(--warning-color);"><?php echo $stats['pending']; ?></h3>
                <p><i class="fas fa-clock"></i> Pending</p>
            </div>
        </div>
        <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="300">
            <div class="stats-card" style="border-left-color: var(--success-color);">
                <h3 style="color: var(--success-color);"><?php echo $stats['accepted']; ?></h3>
                <p><i class="fas fa-check-circle"></i> Accepted</p>
            </div>
        </div>
        <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="400">
            <div class="stats-card" style="border-left-color: var(--danger-color);">
                <h3 style="color: var(--danger-color);"><?php echo $stats['rejected']; ?></h3>
                <p><i class="fas fa-times-circle"></i> Rejected</p>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Chart -->
        <div class="col-md-6 mb-4" data-aos="fade-up">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie"></i> Application Status Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Applications -->
        <div class="col-md-6 mb-4" data-aos="fade-up">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-clock"></i> Recent Applications</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_applications)): ?>
                        <p class="text-muted">No applications yet. <a href="internships.php">Browse internships</a></p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($recent_applications as $app): ?>
                                <div class="list-group-item">
                                    <h6><?php echo htmlspecialchars($app['title']); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($app['company_name']); ?></small>
                                    <span class="badge bg-<?php 
                                        echo $app['status'] == 'accepted' ? 'success' : 
                                            ($app['status'] == 'rejected' ? 'danger' : 'warning'); 
                                    ?> float-end">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                    <br><small class="text-muted">Applied: <?php echo formatDateTime($app['applied_at']); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="applications.php" class="btn btn-sm btn-primary mt-3">View All Applications</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Status Chart
const ctx = document.getElementById('statusChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Accepted', 'Rejected'],
        datasets: [{
            data: [
                <?php echo $status_data['Pending']; ?>,
                <?php echo $status_data['Accepted']; ?>,
                <?php echo $status_data['Rejected']; ?>
            ],
            backgroundColor: [
                '#f59e0b',
                '#10b981',
                '#ef4444'
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

