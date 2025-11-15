<?php
require_once '../config/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type = sanitizeInput($_POST['user_type'] ?? '');
    
    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($user_type)) {
        $error = 'All fields are required!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format!';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long!';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match!';
    } elseif (!in_array($user_type, ['student', 'company'])) {
        $error = 'Invalid user type!';
    } else {
        $conn = getDBConnection();
        
        // Check if username already exists (case-sensitive)
        $stmt = $conn->prepare("SELECT id FROM users WHERE BINARY username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username already exists!';
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Email already registered!';
            } else {
                // Hash password
                // Insert user
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, user_type) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $email, $password, $user_type);
                
                if ($stmt->execute()) {
                    $user_id = $conn->insert_id;
                    
                    // Create profile based on user type
                    if ($user_type == 'student') {
                        $stmt = $conn->prepare("INSERT INTO students (user_id, full_name) VALUES (?, ?)");
                        $stmt->bind_param("is", $user_id, $username);
                        $stmt->execute();
                    } elseif ($user_type == 'company') {
                        $stmt = $conn->prepare("INSERT INTO companies (user_id, company_name) VALUES (?, ?)");
                        $stmt->bind_param("is", $user_id, $username);
                        $stmt->execute();
                    }
                    
                    $success = 'Registration successful! Please login.';
                    header("refresh:2;url=login.php");
                } else {
                    $error = 'Registration failed! Please try again.';
                }
            }
        }
        
        closeDBConnection($conn);
    }
}

$pageTitle = 'Register';
include '../includes/header.php';
?>

<div class="container my-5">
    <a href="<?php echo BASE_URL; ?>index.php" class="back-btn mb-3"><i class="fas fa-home"></i> Home</a>
    
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card" data-aos="fade-up">
                <div class="card-header text-center">
                    <h3><i class="fas fa-user-plus"></i> Register</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" id="registerForm" onsubmit="return validateRegisterForm()">
                        <div class="mb-3">
                            <label class="form-label">User Type</label>
                            <select class="form-select" name="user_type" required>
                                <option value="">Select User Type</option>
                                <option value="student">Student</option>
                                <option value="company">Company</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required 
                                   pattern="[A-Za-z0-9_]{3,20}" 
                                   title="Username must be 3-20 characters (letters, numbers, underscore)">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required 
                                   minlength="8"
                                   title="Password must be at least 8 characters">
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Register
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function validateRegisterForm() {
    const form = document.getElementById('registerForm');
    const password = form.password.value;
    const confirmPassword = form.confirm_password.value;
    
    if (password !== confirmPassword) {
        Swal.fire('Error', 'Passwords do not match!', 'error');
        return false;
    }
    
    if (password.length < 8) {
        Swal.fire('Error', 'Password must be at least 8 characters long!', 'error');
        return false;
    }
    
    return true;
}
</script>

<?php include '../includes/footer.php'; ?>

