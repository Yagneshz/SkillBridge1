<?php
require_once '../config/config.php';
requireUserType(['admin']);

$conn = getDBConnection();

$error = '';
$success = '';

// Handle delete
if (isset($_GET['delete'])) {
    $company_id = (int)$_GET['delete'];
    
    $stmt = $conn->prepare("SELECT user_id FROM companies WHERE id = ?");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result) {
        $user_id = $result['user_id'];
        // Delete user (cascade will delete company and related data)
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $success = 'Company deleted successfully!';
        } else {
            $error = 'Failed to delete company!';
        }
    } else {
        $error = 'Company not found!';
    }
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $user_id = (int)$_POST['user_id'];
    $status = sanitizeInput($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $user_id);
    
    if ($stmt->execute()) {
        $success = 'Status updated successfully!';
    } else {
        $error = 'Failed to update status!';
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Get total count
$count_query = "SELECT COUNT(*) as total FROM companies c JOIN users u ON c.user_id = u.id";
if ($search) {
    $count_query .= " WHERE (c.company_name LIKE '%$search%' OR u.email LIKE '%$search%' OR c.industry LIKE '%$search%')";
}
$total_result = $conn->query($count_query);
$total = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Get companies
$query = "SELECT c.*, u.email, u.status as user_status, u.created_at as registered_at,
          (SELECT COUNT(*) FROM internships i WHERE i.company_id = c.id) as total_internships
          FROM companies c 
          JOIN users u ON c.user_id = u.id";
if ($search) {
    $query .= " WHERE (c.company_name LIKE '%$search%' OR u.email LIKE '%$search%' OR c.industry LIKE '%$search%')";
}
$query .= " ORDER BY c.company_name ASC LIMIT $per_page OFFSET $offset";

$companies = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

$pageTitle = 'Manage Companies';
include '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-building"></i> Manage Companies</h1>
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
    
    <!-- Search Box -->
    <div class="card mb-4" data-aos="fade-up">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-10">
                    <div class="search-box">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search companies by name, email, or industry..." 
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
    
    <!-- Companies Table -->
    <?php if (empty($companies)): ?>
        <div class="card" data-aos="fade-up">
            <div class="card-body text-center">
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <h4>No companies found</h4>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover" id="companiesTable">
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Email</th>
                        <th>Industry</th>
                        <th>Internships</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companies as $company): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($company['company_name']); ?></strong>
                                <?php if ($company['phone']): ?>
                                    <br><small class="text-muted"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($company['phone']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($company['email']); ?></td>
                            <td><?php echo htmlspecialchars($company['industry'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge bg-primary"><?php echo $company['total_internships']; ?></span>
                            </td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?php echo $company['user_id']; ?>">
                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="active" <?php echo $company['user_status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $company['user_status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td><?php echo formatDate($company['registered_at']); ?></td>
                            <td>
                                <a href="?delete=<?php echo $company['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirmDelete('Are you sure you want to delete this company? This will delete all related data.')"
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

