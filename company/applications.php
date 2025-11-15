<?php
require_once '../config/config.php';
requireUserType(['company']);

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get company ID
$stmt = $conn->prepare("SELECT id FROM companies WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();
$company_id = $company['id'] ?? 0;

$error = '';
$success = '';

// Handle accept/reject
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $application_id = (int)$_POST['application_id'];
    $action = $_POST['action'];
    
    // Verify ownership
    $stmt = $conn->prepare("SELECT a.id FROM applications a 
                            JOIN internships i ON a.internship_id = i.id 
                            WHERE a.id = ? AND i.company_id = ?");
    $stmt->bind_param("ii", $application_id, $company_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $status = $action == 'accept' ? 'accepted' : 'rejected';
        $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $application_id);
        
        if ($stmt->execute()) {
            $success = 'Application ' . $status . ' successfully!';
        } else {
            $error = 'Failed to update application!';
        }
    } else {
        $error = 'Invalid application!';
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Filter
$filter = isset($_GET['filter']) ? sanitizeInput($_GET['filter']) : 'all';
$internship_filter = isset($_GET['internship']) ? (int)$_GET['internship'] : 0;

// Get total count
$count_query = "SELECT COUNT(*) as total FROM applications a 
                JOIN internships i ON a.internship_id = i.id 
                WHERE i.company_id = $company_id";
if ($filter != 'all') {
    $count_query .= " AND a.status = '$filter'";
}
if ($internship_filter > 0) {
    $count_query .= " AND a.internship_id = $internship_filter";
}
$total_result = $conn->query($count_query);
$total = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Get applications
$query = "SELECT a.*, i.title as internship_title, s.full_name, u.email, s.phone, s.bio, s.skills, s.resume_path
          FROM applications a 
          JOIN internships i ON a.internship_id = i.id 
          JOIN students s ON a.student_id = s.id 
          JOIN users u ON s.user_id = u.id 
          WHERE i.company_id = $company_id";
if ($filter != 'all') {
    $query .= " AND a.status = '$filter'";
}
if ($internship_filter > 0) {
    $query .= " AND a.internship_id = $internship_filter";
}
$query .= " ORDER BY a.applied_at DESC LIMIT $per_page OFFSET $offset";

$applications = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

// Get internships for filter
$stmt = $conn->prepare("SELECT id, title FROM internships WHERE company_id = ? ORDER BY title");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$internships = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

$pageTitle = 'Applications';
include '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-file-alt"></i> Applications</h1>
    </div>
</div>

<div class="container my-5">
    <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <!-- Filters -->
    <div class="card mb-4" data-aos="fade-up">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <select class="form-select" name="filter" onchange="this.form.submit()">
                        <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>All Applications</option>
                        <option value="pending" <?php echo $filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="accepted" <?php echo $filter == 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                        <option value="rejected" <?php echo $filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <select class="form-select" name="internship" onchange="this.form.submit()">
                        <option value="0">All Internships</option>
                        <?php foreach ($internships as $internship): ?>
                            <option value="<?php echo $internship['id']; ?>" 
                                    <?php echo $internship_filter == $internship['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($internship['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="applications.php" class="btn btn-secondary w-100">Reset</a>
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
                <p class="text-muted">No applications match your filters.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($applications as $app): ?>
            <div class="card mb-3" data-aos="fade-up">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5><?php echo htmlspecialchars($app['internship_title']); ?></h5>
                            <p class="mb-2">
                                <strong>Student:</strong> <?php echo htmlspecialchars($app['full_name']); ?><br>
                                <strong>Email:</strong> <?php echo htmlspecialchars($app['email']); ?><br>
                                <?php if ($app['phone']): ?>
                                    <strong>Phone:</strong> <?php echo htmlspecialchars($app['phone']); ?><br>
                                <?php endif; ?>
                            </p>
                            <?php if ($app['cover_letter']): ?>
                                <div class="mb-2">
                                    <strong>Cover Letter:</strong>
                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($app['cover_letter'])); ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if ($app['skills']): ?>
                                <div class="mb-2">
                                    <strong>Skills:</strong>
                                    <p class="text-muted"><?php echo htmlspecialchars($app['skills']); ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if ($app['resume_path']): ?>
                                <a href="<?php echo BASE_URL . 'uploads/resumes/' . $app['resume_path']; ?>" 
                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-file-pdf"></i> View Resume
                                </a>
                            <?php endif; ?>
                            <small class="text-muted d-block mt-2">
                                Applied: <?php echo formatDateTime($app['applied_at']); ?>
                            </small>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-<?php 
                                echo $app['status'] == 'accepted' ? 'success' : 
                                    ($app['status'] == 'rejected' ? 'danger' : 'warning'); 
                            ?> mb-3 d-block" style="font-size: 1rem;">
                                <?php echo ucfirst($app['status']); ?>
                            </span>
                            
                            <?php if ($app['status'] == 'pending'): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="btn btn-success btn-sm mb-2 w-100">
                                        <i class="fas fa-check"></i> Accept
                                    </button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-danger btn-sm w-100" 
                                            onclick="return confirm('Are you sure you want to reject this application?')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            <?php endif; ?>
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
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>&internship=<?php echo $internship_filter; ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>&internship=<?php echo $internship_filter; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>&internship=<?php echo $internship_filter; ?>">
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

