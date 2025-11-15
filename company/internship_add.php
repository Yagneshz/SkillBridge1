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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $requirements = sanitizeInput($_POST['requirements'] ?? '');
    $location = sanitizeInput($_POST['location'] ?? '');
    $duration = sanitizeInput($_POST['duration'] ?? '');
    $stipend = sanitizeInput($_POST['stipend'] ?? '');
    $skills_required = sanitizeInput($_POST['skills_required'] ?? '');
    $deadline = $_POST['deadline'] ?? '';
    $status = sanitizeInput($_POST['status'] ?? 'active');
    
    // Validation
    if (empty($title)) {
        $error = 'Title is required!';
    } elseif (empty($description)) {
        $error = 'Description is required!';
    } elseif ($deadline && strtotime($deadline) < strtotime('today')) {
        $error = 'Deadline cannot be in the past!';
    } else {
        // Check if company already posted internship with same title (case-sensitive)
        $stmt = $conn->prepare("SELECT id FROM internships WHERE company_id = ? AND BINARY title = ?");
        $stmt->bind_param("is", $company_id, $title);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'You have already posted an internship with this title! Please use a different title.';
        } else {
            // Insert internship
            $stmt = $conn->prepare("INSERT INTO internships (company_id, title, description, requirements, location, duration, stipend, skills_required, deadline, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssssss", $company_id, $title, $description, $requirements, $location, $duration, $stipend, $skills_required, $deadline, $status);
            
            if ($stmt->execute()) {
                $success = 'Internship posted successfully!';
                header("refresh:2;url=internships.php");
            } else {
                $error = 'Failed to post internship!';
            }
        }
    }
}

closeDBConnection($conn);

$pageTitle = 'Post Internship';
include '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-plus"></i> Post New Internship</h1>
    </div>
</div>

<div class="container my-5">
    <a href="internships.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="card" data-aos="fade-up">
        <div class="card-header">
            <h5>Internship Details</h5>
        </div>
        <div class="card-body">
            <form method="POST" id="internshipForm">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" required 
                               placeholder="e.g., Web Development Intern">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description *</label>
                    <textarea class="form-control" name="description" rows="5" required 
                              placeholder="Describe the internship position..."></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Requirements</label>
                    <textarea class="form-control" name="requirements" rows="4" 
                              placeholder="List the requirements for this position..."></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" 
                               placeholder="e.g., Remote, New York, etc.">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Duration</label>
                        <input type="text" class="form-control" name="duration" 
                               placeholder="e.g., 3 months, 6 months">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stipend</label>
                        <input type="text" class="form-control" name="stipend" 
                               placeholder="e.g., $500/month, Unpaid">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Deadline</label>
                        <input type="date" class="form-control" name="deadline" 
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Skills Required</label>
                    <textarea class="form-control" name="skills_required" rows="3" 
                              placeholder="e.g., PHP, JavaScript, MySQL, etc."></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="active" selected>Active</option>
                        <option value="draft">Draft</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Post Internship
                </button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

