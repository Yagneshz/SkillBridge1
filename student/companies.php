<?php
require_once '../config/config.php';
requireUserType(['student']);

$conn = getDBConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Search
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Get total count
$count_query = "SELECT COUNT(*) as total FROM companies c 
                JOIN users u ON c.user_id = u.id 
                WHERE u.status = 'active'";
if ($search) {
    $count_query .= " AND (c.company_name LIKE '%$search%' OR c.description LIKE '%$search%' OR c.industry LIKE '%$search%')";
}
$total_result = $conn->query($count_query);
$total = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Get companies
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM internships i WHERE i.company_id = c.id AND i.status = 'active') as active_internships
          FROM companies c 
          JOIN users u ON c.user_id = u.id 
          WHERE u.status = 'active'";
if ($search) {
    $query .= " AND (c.company_name LIKE '%$search%' OR c.description LIKE '%$search%' OR c.industry LIKE '%$search%')";
}
$query .= " ORDER BY c.company_name ASC LIMIT $per_page OFFSET $offset";

$companies = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

$pageTitle = 'Companies';
include '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-building"></i> Companies</h1>
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
                               placeholder="Search companies by name, description, or industry..." 
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
    
    <!-- Companies Grid -->
    <?php if (empty($companies)): ?>
        <div class="card" data-aos="fade-up">
            <div class="card-body text-center">
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <h4>No companies found</h4>
                <p class="text-muted">Try adjusting your search criteria.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($companies as $company): ?>
                <div class="col-md-4 mb-4" data-aos="fade-up">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <?php if ($company['logo_path']): ?>
                                <img src="<?php echo BASE_URL . 'uploads/logos/' . $company['logo_path']; ?>" 
                                     class="img-fluid mb-3" style="max-height: 100px;">
                            <?php else: ?>
                                <i class="fas fa-building fa-4x text-muted mb-3"></i>
                            <?php endif; ?>
                            <h5><?php echo htmlspecialchars($company['company_name']); ?></h5>
                            <?php if ($company['industry']): ?>
                                <p class="text-muted"><i class="fas fa-industry"></i> <?php echo htmlspecialchars($company['industry']); ?></p>
                            <?php endif; ?>
                            <?php if ($company['description']): ?>
                                <p class="small"><?php echo substr(htmlspecialchars($company['description']), 0, 100); ?>...</p>
                            <?php endif; ?>
                            <p class="mb-3">
                                <span class="badge bg-primary">
                                    <?php echo $company['active_internships']; ?> Active Internships
                                </span>
                            </p>
                            <a href="company_profile.php?id=<?php echo $company['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> View Profile
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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

<?php include '../includes/footer.php'; ?>

