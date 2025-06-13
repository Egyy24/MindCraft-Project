<?php
if (isset($_GET['modal'])) {
    // Return only the form content for modal
    ?>
    <h2 style="margin-bottom: 20px;">Register for MindCraft</h2>
    <form id="registerForm" method="post" action="/MindCraft/register.php">
        <div class="form-group">
            <input type="text" name="username" placeholder="Username" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <div class="form-group">
            <select name="role" required>
                <option value="">-- Select Role --</option>
                <option value="mentee">Mentee</option>
                <option value="mentor">Mentor</option>
            </select>
        </div>
        <button type="submit" name="register" class="register-btn">Register</button>
    </form>
    <?php
    exit();
}

// Regular form processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Add your registration logic here
    // For example:
    // registerUser($username, $password, $role);
    
    // Return JSON response for AJAX requests
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'redirect' => '/MindCraft/landingpage/landingpage.php']);
        exit();
    }
    
    // Regular redirect for non-AJAX requests
    header("Location: /MindCraft/landingpage/landingpage.php");
    exit();
}

// Include header only for full page view
include('templates/header.php');
?>

<!-- Full page registration form -->
<div class="login-container">
    <h2>Daftar Akun Baru</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <select name="role" required>
            <option value="">-- Pilih Role --</option>
            <option value="mentee">Mentee</option>
            <option value="mentor">Mentor</option>
        </select><br>
        <button type="submit" name="register">Register</button>
    </form>
    <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
</div>

<?php include('templates/footer.php'); ?>
