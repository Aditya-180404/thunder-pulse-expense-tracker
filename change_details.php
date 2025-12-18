<?php
session_start();
require('db.php'); // Your database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch current user details
$user_query = "SELECT username, email, password FROM users WHERE id = ?";
$stmt = $con->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Basic validation
    if (empty($username) || empty($email)) {
        $message = "Username and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif (!empty($new_password)) {
        // If changing password, verify current password
        if (empty($current_password)) {
            $message = "Enter your current password to change password.";
        } elseif (!password_verify($current_password, $user_data['password'])) {
            $message = "Current password is incorrect.";
        } elseif ($new_password !== $confirm_password) {
            $message = "New password and confirm password do not match.";
        }
    }

    if (empty($message)) {
        // Update username and email
        $update_query = "UPDATE users SET username=?, email=? WHERE id=?";
        $stmt = $con->prepare($update_query);
        $stmt->bind_param("ssi", $username, $email, $user_id);
        $stmt->execute();
        $stmt->close();

        // Update password if provided
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $pass_query = "UPDATE users SET password=? WHERE id=?";
            $stmt = $con->prepare($pass_query);
            $stmt->bind_param("si", $hashed_password, $user_id);
            $stmt->execute();
            $stmt->close();
        }

        $message = "Profile updated successfully!";
        $user_data['username'] = $username;
        $user_data['email'] = $email;
        $_SESSION['username'] = $username;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Profile Details</title>
    <style>
        :root{
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        body{
    margin:0;
    font-family:'Inter',sans-serif;
    color:var(--text-color);

    background:
        linear-gradient(
            -45deg,
            rgba(102,126,234,0.85),
            rgba(118,75,162,0.85),
            rgba(195,207,226,0.85),
            rgba(245,247,250,0.85)
        ),
        url('images.jpg');

    background-size: 400% 400%, cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;

    animation: gradientBG 15s ease infinite;
}


        .navbar {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            color: #fff;
        }

        .navbar h2 { margin: 0; font-size: 1.8rem; }
        .nav-right { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
        .nav-right span { font-weight: 600; white-space: nowrap; }
        .nav-right a {
            color: #fff;
            text-decoration: none;
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: 600;
            transition: 0.3s;
        }
        .nav-right a:hover { background: #fff; color: var(--primary-color); }

        .container {
    max-width: 500px;
    margin: 50px auto;
    padding: 30px;
    border-radius: 15px;

    /* Darker glass effect */
    background: rgba(255, 255, 255, 0.25);

    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);

    box-shadow:
        0 8px 32px rgba(0, 0, 0, 0.35),
        inset 0 0 30px rgba(0, 0, 0, 0.08);

    border: 1px solid rgba(255, 255, 255, 0.25);

    color: #000;
}

@keyframes gradientBG {
    0%   { background-position: 0% 50%, center; }
    50%  { background-position: 100% 50%, center; }
    100% { background-position: 0% 50%, center; }
}



    input[type=text],
input[type=email],
input[type=password] {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 8px;
    border: 1px solid rgba(0, 0, 0, 0.3);
    background: rgba(255, 255, 255, 0.7); /* more opaque for readability */
    color: #000; /* black text */
}

input::placeholder {
    color: rgba(0, 0, 0, 0.6); /* placeholder in darker gray */
}

.logout-btn {
    background: #e53935; /* red */
    color: #fff !important;
    padding: 6px 14px;
    border-radius: 8px;
    font-weight: 700;
    transition: all 0.3s ease;
}

.logout-btn:hover {
    background: #ffffff; /* white */
    color: #e53935 !important;
}

input[type=submit] {
    padding: 10px 20px;
    background: var(--primary-color);
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

input[type=submit]:hover {
    background: var(--secondary-color);
}
/* ================= FOOTER ================= */
.footer{
    margin-top:50px;
    background:linear-gradient(90deg,var(--primary-color),var(--secondary-color));
    color:#fff;
    border-radius:18px 18px 0 0;
    box-shadow:0 -10px 30px rgba(0,0,0,0.2);
}

.footer-content{
    max-width:1100px;
    margin:auto;
    padding:30px 20px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:25px;
}

.footer-section h4{
    margin-bottom:10px;
    font-weight:700;
}

.footer-section p{
    font-size:0.95rem;
    line-height:1.6;
    opacity:0.95;
}

.footer-section ul{
    list-style:none;
    padding:0;
    margin:0;
}

.footer-section ul li{
    margin-bottom:8px;
}

.footer-section ul li a{
    color:#fff;
    text-decoration:none;
    transition:0.3s;
}

.footer-section ul li a:hover{
    text-decoration:underline;
    opacity:0.85;
}

.footer-bottom{
    text-align:center;
    padding:12px;
    background:rgba(0,0,0,0.15);
    font-size:0.9rem;
}

    </style>
</head>
<body>
    <!-- Sticky Navbar -->
    <nav class="navbar">
        <h2>Fin Track</h2>
        <div class="nav-right">
            <span>Hello, <?= htmlspecialchars($_SESSION['username'] ?? $user_data['username']) ?></span>
            <a href="home.php">Home</a>
            <a href="index.php">Dashboard</a>
            <a href="manage_expenses.php">Manage Expenses</a>
            <a href="change_details.php">Profile</a>
            <a href="settings.php">Settings</a>
            <a href="logout.php" class="logout-btn">Logout</a>

        </div>
    </nav>

    <div class="container">
        <h2>Update Profile</h2>
        <?php if($message): ?>
            <div class="<?= strpos($message,'successfully') !== false ? 'message' : 'error' ?>"><?= $message ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user_data['username']) ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required>

            <hr>
            <h3>Change Password</h3>
            <label>Current Password</label>
            <input type="password" name="current_password">

            <label>New Password</label>
            <input type="password" name="new_password">

            <label>Confirm New Password</label>
            <input type="password" name="confirm_password">

            <input type="submit" value="Update Profile">
        </form>
    </div>
    <!-- FOOTER -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h4>Expense Tracker</h4>
            <p>Track your daily expenses, manage budgets, and stay financially organized.</p>
        </div>

        <div class="footer-section">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="manage_expenses.php">Manage Expenses</a></li>
                <li><a href="settings.php">Settings</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h4>Contact</h4>
            <p>Email: support@thunderpulse.dev</p>
            <p>Version: 1.0.0</p>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© <?php echo date("Y"); ?> Expense Tracker | Developed by <strong>Thunder Pulse ⚡</strong></p>
    </div>
</footer>
</body>
</html>