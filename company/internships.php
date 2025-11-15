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

// Handle delete
if (isset($_GET['delete'])) {
    $internship_id = (int)$_GET['delete'];
    
    // Verify ownership
    $stmt = $conn->prepare("SELECT id FROM internships WHERE id = ? AND company_id = ?");
    $stmt->bind_param("ii", $internship_id, $company_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM internships WHERE id = ?");
        $stmt->bind_param("i", $internship_id);
        if ($stmt->execute()) {
            $success = 'Internship deleted successfully!';
        } else {
            $error = 'Failed to delete internship!';
        }
    } else {
        $error = 'Invalid internship!';
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Get total count
$count_query = "SELECT COUNT(*) as total FROM internships WHERE company_id = $company_id";
if ($search) {
    $count_query .= " AND (title LIKE '%$search%' OR description LIKE '%$search%')";
}
$total_result = $conn->query($count_query);
$total = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Get internships
$query = "SELECT i.*, 
          (SELECT COUNT(*) FROM applications a WHERE a.internship_id = i.id) as application_count
          FROM internships i 
          WHERE i.company_id = $company_id";
if ($search) {
    $query .= " AND (i.title LIKE '%$search%' OR i.description LIKE '%$search%')";
}
$query .= " ORDER BY i.posted_date DESC LIMIT $per_page OFFSET $offset";

$internships = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

$pageTitle = 'Manage Internships';
include '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-briefcase"></i> Manage Internships</h1>
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
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>My Internships</h3>
        <a href="internship_add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Post New Internship
        </a>
    </div>
    
    <!-- Search Box -->
    <div class="card mb-4" data-aos="fade-up">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-10">
                    <div class="search-box">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search internships..." 
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
                <p class="text-muted">Start by posting your first internship.</p>
                <a href="internship_add.php" class="btn btn-primary">Post Internship</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover" id="internshipsTable">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Applications</th>
                        <th>Posted Date</th>
                        <th>Deadline</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($internships as $internship): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($internship['title']); ?></strong>
                                <?php if ($internship['location']): ?>
                                    <br><small class="text-muted"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($internship['location']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $internship['status'] == 'active' ? 'success' : 
                                        ($internship['status'] == 'closed' ? 'danger' : 'secondary'); 
                                ?>">
                                    <?php echo ucfirst($internship['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?php echo $internship['application_count']; ?></span>
                            </td>
                            <td><?php echo formatDate($internship['posted_date']); ?></td>
                            <td><?php echo $internship['deadline'] ? formatDate($internship['deadline']) : 'N/A'; ?></td>
                            <td>
                                <a href="internship_edit.php?id=<?php echo $internship['id']; ?>" 
                                   class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $internship['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirmDelete('Are you sure you want to delete this internship?')"
                                   data-bs-toggle="tooltip" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
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

<script>
function confirmDelete(message) {
    return confirm(message);
}
</script>

<?php include '../includes/footer.php'; ?>

