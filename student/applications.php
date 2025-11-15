<?php
require_once '../config/config.php';
requireUserType(['student']);

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get student ID
$stmt = $conn->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$student_id = $student['id'] ?? 0;

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Filter
$filter = isset($_GET['filter']) ? sanitizeInput($_GET['filter']) : 'all';

// Get total count
$count_query = "SELECT COUNT(*) as total FROM applications a 
                JOIN internships i ON a.internship_id = i.id 
                WHERE a.student_id = $student_id";
if ($filter != 'all') {
    $count_query .= " AND a.status = '$filter'";
}
$total_result = $conn->query($count_query);
$total = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Get applications
$query = "SELECT a.*, i.title, i.description, i.location, i.stipend, i.duration, 
          c.company_name, c.logo_path
          FROM applications a 
          JOIN internships i ON a.internship_id = i.id 
          JOIN companies c ON i.company_id = c.id 
          WHERE a.student_id = $student_id";
if ($filter != 'all') {
    $query .= " AND a.status = '$filter'";
}
$query .= " ORDER BY a.applied_at DESC LIMIT $per_page OFFSET $offset";

$applications = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

$pageTitle = 'My Applications';
include '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-file-alt"></i> My Applications</h1>
    </div>
</div>

<div class="container my-5">
    <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
    
    <!-- Filter -->
    <div class="card mb-4" data-aos="fade-up">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <select class="form-select" name="filter" onchange="this.form.submit()">
                        <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>All Applications</option>
                        <option value="pending" <?php echo $filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="accepted" <?php echo $filter == 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                        <option value="rejected" <?php echo $filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Applications List -->
    <?php if (empty($applications)): ?>
        <div class="card" data-aos="fade-up">
            <div class="card-body text-center">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h4>No applications found</h4>
                <p class="text-muted">You haven't applied for any internships yet.</p>
                <a href="internships.php" class="btn btn-primary">Browse Internships</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($applications as $app): ?>
            <div class="card mb-3" data-aos="fade-up">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-10">
                            <h5><?php echo htmlspecialchars($app['title']); ?></h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-building"></i> <?php echo htmlspecialchars($app['company_name']); ?>
                                <?php if ($app['location']): ?>
                                    | <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($app['location']); ?>
                                <?php endif; ?>
                            </p>
                            <p class="mb-2"><?php echo substr(htmlspecialchars($app['description']), 0, 150); ?>...</p>
                            <?php if ($app['cover_letter']): ?>
                                <div class="mb-2">
                                    <strong>Cover Letter:</strong>
                                    <p class="text-muted small"><?php echo nl2br(htmlspecialchars($app['cover_letter'])); ?></p>
                                </div>
                            <?php endif; ?>
                            <small class="text-muted">
                                Applied: <?php echo formatDateTime($app['applied_at']); ?>
                            </small>
                        </div>
                        <div class="col-md-2 text-end">
                            <span class="badge bg-<?php 
                                echo $app['status'] == 'accepted' ? 'success' : 
                                    ($app['status'] == 'rejected' ? 'danger' : 'warning'); 
                            ?> mb-2 d-block" style="font-size: 1rem;">
                                <?php echo ucfirst($app['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

