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

// Search
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Get total count
$count_query = "SELECT COUNT(*) as total FROM internships i 
                JOIN companies c ON i.company_id = c.id 
                WHERE i.status = 'active'";
if ($search) {
    $count_query .= " AND (i.title LIKE '%$search%' OR i.description LIKE '%$search%' OR c.company_name LIKE '%$search%')";
}
$total_result = $conn->query($count_query);
$total = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Get internships
$query = "SELECT i.*, c.company_name, c.logo_path, 
          (SELECT COUNT(*) FROM applications a WHERE a.internship_id = i.id AND a.student_id = ?) as applied
          FROM internships i 
          JOIN companies c ON i.company_id = c.id 
          WHERE i.status = 'active'";
if ($search) {
    $query .= " AND (i.title LIKE '%$search%' OR i.description LIKE '%$search%' OR c.company_name LIKE '%$search%')";
}
$query .= " ORDER BY i.posted_date DESC LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $student_id, $per_page, $offset);
$stmt->execute();
$internships = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

$pageTitle = 'Browse Internships';
include '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-briefcase"></i> Browse Internships</h1>
    </div>
</div>

<div class="container my-5">
    <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
    
    <!-- Search Box -->
    <div class="card mb-4" data-aos="fade-up">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-10">
                    <div class="search-box">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search internships by title, description, or company name..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Internships List -->
    <?php if (empty($internships)): ?>
        <div class="card" data-aos="fade-up">
            <div class="card-body text-center">
                <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                <h4>No internships found</h4>
                <p class="text-muted">Try adjusting your search criteria.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($internships as $internship): ?>
            <div class="card mb-3" data-aos="fade-up">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-10">
                            <h5 class="card-title">
                                <a href="internship_details.php?id=<?php echo $internship['id']; ?>" 
                                   class="text-decoration-none">
                                    <?php echo htmlspecialchars($internship['title']); ?>
                                </a>
                            </h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-building"></i> <?php echo htmlspecialchars($internship['company_name']); ?>
                                <?php if ($internship['location']): ?>
                                    | <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($internship['location']); ?>
                                <?php endif; ?>
                            </p>
                            <p class="card-text">
                                <?php echo substr(htmlspecialchars($internship['description']), 0, 200); ?>...
                            </p>
                            <div class="mb-2">
                                <?php if ($internship['stipend']): ?>
                                    <span class="badge bg-success me-2">
                                        <i class="fas fa-money-bill-wave"></i> <?php echo htmlspecialchars($internship['stipend']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($internship['duration']): ?>
                                    <span class="badge bg-info me-2">
                                        <i class="fas fa-clock"></i> <?php echo htmlspecialchars($internship['duration']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($internship['deadline']): ?>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-calendar"></i> Deadline: <?php echo formatDate($internship['deadline']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <?php if ($internship['applied'] > 0): ?>
                                <span class="badge bg-success mb-2 d-block">
                                    <i class="fas fa-check"></i> Applied
                                </span>
                            <?php endif; ?>
                            <a href="internship_details.php?id=<?php echo $internship['id']; ?>" 
                               class="btn btn-sm btn-primary">
                                View Details
                            </a>
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
                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
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

