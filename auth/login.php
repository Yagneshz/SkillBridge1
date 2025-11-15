<?php
require_once '../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required!';
    } else {
        $conn = getDBConnection();
        
        // Check username (case-sensitive) and password
        $stmt = $conn->prepare("SELECT id, username, email, password, user_type, status FROM users WHERE BINARY username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            $error = 'Invalid username or password!';
        } else {
            $user = $result->fetch_assoc();
            
            if ($user['status'] == 'inactive') {
                $error = 'Your account is inactive. Please contact admin.';
            } elseif ($password === $user['password']) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Redirect based on user type
                if ($user['user_type'] == 'admin') {
                    redirect('admin/dashboard.php');
                } elseif ($user['user_type'] == 'company') {
                    redirect('company/dashboard.php');
                } elseif ($user['user_type'] == 'student') {
                    redirect('student/dashboard.php');
                }
            } else {
                $error = 'Invalid username or password!';
            }
        }
        
        closeDBConnection($conn);
    }
}

$pageTitle = 'Login';
include '../includes/header.php';
?>

<div class="container my-5">
    <a href="<?php echo BASE_URL; ?>index.php" class="back-btn mb-3"><i class="fas fa-home"></i> Home</a>
    
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card" data-aos="fade-up">
                <div class="card-header text-center">
                    <h3><i class="fas fa-sign-in-alt"></i> Login to SkillBridge</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" id="loginForm">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required autofocus>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Don't have an account? <a href="register.php">Register here</a></p>
                    </div>
                    
                    <!-- <div class="text-center mt-2">
                        <small class="text-muted">
                            <strong>Demo Credentials:</strong><br>
                            Admin: admin / admin123<br>
                        </small>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

