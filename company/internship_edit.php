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

$internship_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get internship
$stmt = $conn->prepare("SELECT * FROM internships WHERE id = ? AND company_id = ?");
$stmt->bind_param("ii", $internship_id, $company_id);
$stmt->execute();
$internship = $stmt->get_result()->fetch_assoc();

if (!$internship) {
    redirect('internships.php');
}

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
        // Check if another internship with same title exists (excluding current)
        $stmt = $conn->prepare("SELECT id FROM internships WHERE company_id = ? AND BINARY title = ? AND id != ?");
        $stmt->bind_param("isi", $company_id, $title, $internship_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'You already have an internship with this title! Please use a different title.';
        } else {
            // Update internship
            $stmt = $conn->prepare("UPDATE internships SET title = ?, description = ?, requirements = ?, location = ?, duration = ?, stipend = ?, skills_required = ?, deadline = ?, status = ? WHERE id = ? AND company_id = ?");
            $stmt->bind_param("sssssssssii", $title, $description, $requirements, $location, $duration, $stipend, $skills_required, $deadline, $status, $internship_id, $company_id);
            
            if ($stmt->execute()) {
                $success = 'Internship updated successfully!';
                // Refresh data
                $stmt = $conn->prepare("SELECT * FROM internships WHERE id = ? AND company_id = ?");
                $stmt->bind_param("ii", $internship_id, $company_id);
                $stmt->execute();
                $internship = $stmt->get_result()->fetch_assoc();
            } else {
                $error = 'Failed to update internship!';
            }
        }
    }
}

closeDBConnection($conn);

$pageTitle = 'Edit Internship';
include '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-edit"></i> Edit Internship</h1>
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
            <h5>Edit Internship Details</h5>
        </div>
        <div class="card-body">
            <form method="POST" id="internshipForm">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" required 
                               value="<?php echo htmlspecialchars($internship['title']); ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description *</label>
                    <textarea class="form-control" name="description" rows="5" required><?php echo htmlspecialchars($internship['description']); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Requirements</label>
                    <textarea class="form-control" name="requirements" rows="4"><?php echo htmlspecialchars($internship['requirements'] ?? ''); ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" 
                               value="<?php echo htmlspecialchars($internship['location'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Duration</label>
                        <input type="text" class="form-control" name="duration" 
                               value="<?php echo htmlspecialchars($internship['duration'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stipend</label>
                        <input type="text" class="form-control" name="stipend" 
                               value="<?php echo htmlspecialchars($internship['stipend'] ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Deadline</label>
                        <input type="date" class="form-control" name="deadline" 
                               value="<?php echo $internship['deadline'] ? date('Y-m-d', strtotime($internship['deadline'])) : ''; ?>"
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Skills Required</label>
                    <textarea class="form-control" name="skills_required" rows="3"><?php echo htmlspecialchars($internship['skills_required'] ?? ''); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="active" <?php echo $internship['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="draft" <?php echo $internship['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="closed" <?php echo $internship['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Internship
                </button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

