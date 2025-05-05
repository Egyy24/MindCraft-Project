<?php
if (isset($_GET['modal'])) {
    // Return only the form content for modal
    ?>
    <h2 style="margin-bottom: 20px;">Login to MindCraft</h2>
    <form id="loginForm" method="post" action="/MindCraft/controller/auth.php">
        <div class="form-group">
            <input type="text" name="username" placeholder="Username" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <button type="submit" name="login" class="register-btn">Login</button>
    </form>
    <?php
    exit();
}

// Regular page view
include('templates/header.php');
?>

<div class="login-container">
  <h2>Login ke MindCraft</h2>
  <form method="post" action="controller/auth.php">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit" name="login">Login</button>
  </form>
</div>

<?php include('templates/footer.php'); ?>
