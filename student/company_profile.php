<?php
require_once '../config/config.php';
requireUserType(['student']);

$conn = getDBConnection();
$company_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get company details
$stmt = $conn->prepare("SELECT c.*, u.email FROM companies c 
                        JOIN users u ON c.user_id = u.id 
                        WHERE c.id = ? AND u.status = 'active'");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();

if (!$company) {
    redirect('companies.php');
}

// Get company internships
$stmt = $conn->prepare("SELECT * FROM internships WHERE company_id = ? AND status = 'active' ORDER BY posted_date DESC");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$internships = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

$pageTitle = 'Company Profile';
include '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-building"></i> Company Profile</h1>
    </div>
</div>

<div class="container my-5">
    <a href="companies.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Companies</a>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card" data-aos="fade-up">
                <div class="card-body text-center">
                    <?php if ($company['logo_path']): ?>
                        <img src="<?php echo BASE_URL . 'uploads/logos/' . $company['logo_path']; ?>" 
                             class="img-fluid mb-3" style="max-height: 200px;">
                    <?php else: ?>
                        <i class="fas fa-building fa-5x text-muted mb-3"></i>
                    <?php endif; ?>
                    <h4><?php echo htmlspecialchars($company['company_name']); ?></h4>
                    <?php if ($company['industry']): ?>
                        <p class="text-muted"><i class="fas fa-industry"></i> <?php echo htmlspecialchars($company['industry']); ?></p>
                    <?php endif; ?>
                    <?php if ($company['website']): ?>
                        <a href="<?php echo htmlspecialchars($company['website']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-globe"></i> Visit Website
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header">
                    <h5>About Company</h5>
                </div>
                <div class="card-body">
                    <?php if ($company['description']): ?>
                        <p><?php echo nl2br(htmlspecialchars($company['description'])); ?></p>
                    <?php else: ?>
                        <p class="text-muted">No description available.</p>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="row">
                        <?php if ($company['phone']): ?>
                            <div class="col-md-6 mb-2">
                                <strong><i class="fas fa-phone"></i> Phone:</strong>
                                <?php echo htmlspecialchars($company['phone']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($company['email']): ?>
                            <div class="col-md-6 mb-2">
                                <strong><i class="fas fa-envelope"></i> Email:</strong>
                                <?php echo htmlspecialchars($company['email']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($company['address']): ?>
                            <div class="col-md-12 mb-2">
                                <strong><i class="fas fa-map-marker-alt"></i> Address:</strong>
                                <?php echo htmlspecialchars($company['address']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Internships -->
            <div class="card" data-aos="fade-up" data-aos-delay="200">
                <div class="card-header">
                    <h5>Active Internships (<?php echo count($internships); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($internships)): ?>
                        <p class="text-muted">No active internships at the moment.</p>
                    <?php else: ?>
                        <?php foreach ($internships as $internship): ?>
                            <div class="mb-3 pb-3 border-bottom">
                                <h6>
                                    <a href="internship_details.php?id=<?php echo $internship['id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($internship['title']); ?>
                                    </a>
                                </h6>
                                <p class="small text-muted mb-2">
                                    <?php echo substr(htmlspecialchars($internship['description']), 0, 150); ?>...
                                </p>
                                <div>
                                    <?php if ($internship['location']): ?>
                                        <span class="badge bg-secondary me-2">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($internship['location']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($internship['stipend']): ?>
                                        <span class="badge bg-success me-2">
                                            <i class="fas fa-money-bill-wave"></i> <?php echo htmlspecialchars($internship['stipend']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($internship['deadline']): ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-calendar"></i> Deadline: <?php echo formatDate($internship['deadline']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <a href="internship_details.php?id=<?php echo $internship['id']; ?>" class="btn btn-sm btn-primary mt-2">
                                    View Details
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

