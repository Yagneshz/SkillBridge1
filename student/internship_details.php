<?php
require_once '../config/config.php';
requireUserType(['student']);

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$internship_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get student ID
$stmt = $conn->prepare("SELECT id FROM students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$student_id = $student['id'] ?? 0;

// Get internship details
$stmt = $conn->prepare("SELECT i.*, c.company_name, c.description as company_description, c.website, c.logo_path, c.id as company_id
                        FROM internships i 
                        JOIN companies c ON i.company_id = c.id 
                        WHERE i.id = ? AND i.status = 'active'");
$stmt->bind_param("i", $internship_id);
$stmt->execute();
$internship = $stmt->get_result()->fetch_assoc();

if (!$internship) {
    redirect('internships.php');
}

// Check if already applied
$stmt = $conn->prepare("SELECT id, status FROM applications WHERE student_id = ? AND internship_id = ?");
$stmt->bind_param("ii", $student_id, $internship_id);
$stmt->execute();
$application = $stmt->get_result()->fetch_assoc();

// Handle application
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply'])) {
    if ($application) {
        $error = 'You have already applied for this internship!';
    } else {
        $cover_letter = sanitizeInput($_POST['cover_letter'] ?? '');
        
        $stmt = $conn->prepare("INSERT INTO applications (internship_id, student_id, cover_letter) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $internship_id, $student_id, $cover_letter);
        
        if ($stmt->execute()) {
            $success = 'Application submitted successfully!';
            $application = ['id' => $conn->insert_id, 'status' => 'pending'];
        } else {
            $error = 'Failed to submit application!';
        }
    }
}

closeDBConnection($conn);

$pageTitle = 'Internship Details';
include '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-briefcase"></i> Internship Details</h1>
    </div>
</div>

<div class="container my-5">
    <a href="internships.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Internships</a>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4" data-aos="fade-up">
                <div class="card-header">
                    <h4><?php echo htmlspecialchars($internship['title']); ?></h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        <i class="fas fa-building"></i> 
                        <a href="company_profile.php?id=<?php echo $internship['company_id']; ?>" class="text-decoration-none">
                            <?php echo htmlspecialchars($internship['company_name']); ?>
                        </a>
                    </p>
                    
                    <div class="mb-3">
                        <h6>Description</h6>
                        <p><?php echo nl2br(htmlspecialchars($internship['description'])); ?></p>
                    </div>
                    
                    <?php if ($internship['requirements']): ?>
                        <div class="mb-3">
                            <h6>Requirements</h6>
                            <p><?php echo nl2br(htmlspecialchars($internship['requirements'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($internship['skills_required']): ?>
                        <div class="mb-3">
                            <h6>Skills Required</h6>
                            <p><?php echo nl2br(htmlspecialchars($internship['skills_required'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="row mb-3">
                        <?php if ($internship['location']): ?>
                            <div class="col-md-6">
                                <strong><i class="fas fa-map-marker-alt"></i> Location:</strong>
                                <?php echo htmlspecialchars($internship['location']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($internship['duration']): ?>
                            <div class="col-md-6">
                                <strong><i class="fas fa-clock"></i> Duration:</strong>
                                <?php echo htmlspecialchars($internship['duration']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($internship['stipend']): ?>
                            <div class="col-md-6 mt-2">
                                <strong><i class="fas fa-money-bill-wave"></i> Stipend:</strong>
                                <?php echo htmlspecialchars($internship['stipend']); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($internship['deadline']): ?>
                            <div class="col-md-6 mt-2">
                                <strong><i class="fas fa-calendar"></i> Deadline:</strong>
                                <?php echo formatDate($internship['deadline']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <small class="text-muted">
                        Posted: <?php echo formatDateTime($internship['posted_date']); ?>
                    </small>
                </div>
            </div>
            
            <!-- Application Form -->
            <?php if (!$application): ?>
                <div class="card" data-aos="fade-up">
                    <div class="card-header">
                        <h5><i class="fas fa-paper-plane"></i> Apply for this Internship</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Cover Letter</label>
                                <textarea class="form-control" name="cover_letter" rows="5" 
                                          placeholder="Write a cover letter explaining why you're interested in this internship..."></textarea>
                            </div>
                            <button type="submit" name="apply" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Application
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="card" data-aos="fade-up">
                    <div class="card-body">
                        <div class="alert alert-<?php echo $application['status'] == 'accepted' ? 'success' : ($application['status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                            <h5>Application Status: <?php echo ucfirst($application['status']); ?></h5>
                            <p>You have already applied for this internship.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-4">
            <div class="card" data-aos="fade-up" data-aos-delay="200">
                <div class="card-body text-center">
                    <?php if ($internship['logo_path']): ?>
                        <img src="<?php echo BASE_URL . 'uploads/logos/' . $internship['logo_path']; ?>" 
                             class="img-fluid mb-3" style="max-height: 150px;">
                    <?php else: ?>
                        <i class="fas fa-building fa-4x text-muted mb-3"></i>
                    <?php endif; ?>
                    <h5><?php echo htmlspecialchars($internship['company_name']); ?></h5>
                    <?php if ($internship['website']): ?>
                        <a href="<?php echo htmlspecialchars($internship['website']); ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fas fa-globe"></i> Visit Website
                        </a>
                    <?php endif; ?>
                    <a href="company_profile.php?id=<?php echo $internship['company_id']; ?>" class="btn btn-sm btn-primary mt-2">
                        <i class="fas fa-eye"></i> View Company Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

