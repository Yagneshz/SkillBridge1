<?php
require_once '../config/config.php';
requireUserType(['student']);

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get student profile
$stmt = $conn->prepare("SELECT s.*, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    // Create profile if doesn't exist
    $stmt = $conn->prepare("INSERT INTO students (user_id, full_name) VALUES (?, ?)");
    $full_name = $_SESSION['username'];
    $stmt->bind_param("is", $user_id, $full_name);
    $stmt->execute();
    
    $stmt = $conn->prepare("SELECT s.*, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $bio = sanitizeInput($_POST['bio'] ?? '');
    $skills = sanitizeInput($_POST['skills'] ?? '');
    $education = sanitizeInput($_POST['education'] ?? '');
    
    // Validation
    if (empty($full_name)) {
        $error = 'Full name is required!';
    } else {
        // Handle file uploads
        $profile_image = $student['profile_image'];
        $resume_path = $student['resume_path'];
        
        // Profile image upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
                $target = PROFILE_DIR . $filename;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target)) {
                    if ($profile_image && file_exists(PROFILE_DIR . $profile_image)) {
                        unlink(PROFILE_DIR . $profile_image);
                    }
                    $profile_image = $filename;
                }
            }
        }
        
        // Resume upload
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
            $allowed = ['pdf', 'doc', 'docx'];
            $ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $filename = 'resume_' . $user_id . '_' . time() . '.' . $ext;
                $target = RESUME_DIR . $filename;
                
                if (move_uploaded_file($_FILES['resume']['tmp_name'], $target)) {
                    if ($resume_path && file_exists(RESUME_DIR . $resume_path)) {
                        unlink(RESUME_DIR . $resume_path);
                    }
                    $resume_path = $filename;
                }
            }
        }
        
        // Update profile
        $stmt = $conn->prepare("UPDATE students SET full_name = ?, phone = ?, address = ?, bio = ?, skills = ?, education = ?, profile_image = ?, resume_path = ? WHERE user_id = ?");
        $stmt->bind_param("ssssssssi", $full_name, $phone, $address, $bio, $skills, $education, $profile_image, $resume_path, $user_id);
        
        if ($stmt->execute()) {
            $success = 'Profile updated successfully!';
            // Refresh data
            $stmt = $conn->prepare("SELECT s.*, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $student = $stmt->get_result()->fetch_assoc();
        } else {
            $error = 'Failed to update profile!';
        }
    }
}

closeDBConnection($conn);

$pageTitle = 'My Profile';
include '../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-user"></i> My Profile</h1>
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
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="full_name" 
                                       value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone" 
                                       value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>"
                                       pattern="[0-9]{10,15}">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Profile Image</label>
                                <input type="file" class="form-control" name="profile_image" accept="image/*">
                                <?php if ($student['profile_image']): ?>
                                    <small class="text-muted">Current: <?php echo htmlspecialchars($student['profile_image']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Bio</label>
                            <textarea class="form-control" name="bio" rows="3" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($student['bio'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Skills</label>
                            <textarea class="form-control" name="skills" rows="3" placeholder="e.g., PHP, JavaScript, MySQL, etc."><?php echo htmlspecialchars($student['skills'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Education</label>
                            <textarea class="form-control" name="education" rows="3" placeholder="Your educational background..."><?php echo htmlspecialchars($student['education'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Resume (PDF, DOC, DOCX)</label>
                            <input type="file" class="form-control" name="resume" accept=".pdf,.doc,.docx">
                            <?php if ($student['resume_path']): ?>
                                <small class="text-muted">Current: <a href="<?php echo BASE_URL . 'uploads/resumes/' . $student['resume_path']; ?>" target="_blank"><?php echo htmlspecialchars($student['resume_path']); ?></a></small>
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
                    <?php if ($student['profile_image']): ?>
                        <img src="<?php echo BASE_URL . 'uploads/profiles/' . $student['profile_image']; ?>" 
                             class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-user-circle fa-5x text-muted mb-3"></i>
                    <?php endif; ?>
                    <h4><?php echo htmlspecialchars($student['full_name']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($student['email']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

