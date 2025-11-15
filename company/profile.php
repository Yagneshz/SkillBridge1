<?php
require_once '../config/config.php';
requireUserType(['company']);

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get company profile
$stmt = $conn->prepare("SELECT c.*, u.email FROM companies c JOIN users u ON c.user_id = u.id WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();

if (!$company) {
    // Create profile if doesn't exist
    $stmt = $conn->prepare("INSERT INTO companies (user_id, company_name) VALUES (?, ?)");
    $company_name = $_SESSION['username'];
    $stmt->bind_param("is", $user_id, $company_name);
    $stmt->execute();
    
    $stmt = $conn->prepare("SELECT c.*, u.email FROM companies c JOIN users u ON c.user_id = u.id WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $company = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_name = sanitizeInput($_POST['company_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $website = sanitizeInput($_POST['website'] ?? '');
    $industry = sanitizeInput($_POST['industry'] ?? '');
    
    // Validation
    if (empty($company_name)) {
        $error = 'Company name is required!';
    } elseif (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
        $error = 'Invalid website URL!';
    } else {
        // Handle logo upload
        $logo_path = $company['logo_path'];
        
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $filename = 'logo_' . $user_id . '_' . time() . '.' . $ext;
                $target = LOGO_DIR . $filename;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $target)) {
                    if ($logo_path && file_exists(LOGO_DIR . $logo_path)) {
                        unlink(LOGO_DIR . $logo_path);
                    }
                    $logo_path = $filename;
                }
            }
        }
        
        // Update profile
        $stmt = $conn->prepare("UPDATE companies SET company_name = ?, phone = ?, address = ?, description = ?, website = ?, industry = ?, logo_path = ? WHERE user_id = ?");
        $stmt->bind_param("sssssssi", $company_name, $phone, $address, $description, $website, $industry, $logo_path, $user_id);
        
        if ($stmt->execute()) {
            $success = 'Profile updated successfully!';
            // Refresh data
            $stmt = $conn->prepare("SELECT c.*, u.email FROM companies c JOIN users u ON c.user_id = u.id WHERE c.user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $company = $stmt->get_result()->fetch_assoc();
        } else {
            $error = 'Failed to update profile!';
        }
    }
}

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
    <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card" data-aos="fade-up">
                <div class="card-header">
                    <h5><i class="fas fa-edit"></i> Edit Profile</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" id="profileForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company Name *</label>
                                <input type="text" class="form-control" name="company_name" 
                                       value="<?php echo htmlspecialchars($company['company_name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($company['email']); ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone" 
                                       value="<?php echo htmlspecialchars($company['phone'] ?? ''); ?>"
                                       pattern="[0-9]{10,15}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Industry</label>
                                <input type="text" class="form-control" name="industry" 
                                       value="<?php echo htmlspecialchars($company['industry'] ?? ''); ?>"
                                       placeholder="e.g., Technology, Finance, Healthcare">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Website</label>
                            <input type="url" class="form-control" name="website" 
                                   value="<?php echo htmlspecialchars($company['website'] ?? ''); ?>"
                                   placeholder="https://example.com">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($company['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="5" placeholder="Tell us about your company..."><?php echo htmlspecialchars($company['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Company Logo</label>
                            <input type="file" class="form-control" name="logo" accept="image/*">
                            <?php if ($company['logo_path']): ?>
                                <small class="text-muted">Current: <?php echo htmlspecialchars($company['logo_path']); ?></small>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card" data-aos="fade-up" data-aos-delay="200">
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
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

