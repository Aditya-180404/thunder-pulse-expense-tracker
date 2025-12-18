<?php
require('db.php');
session_start();

$user_logged_in = false;
$username = "Guest";
$allowance = 0;
$total_spent = 0;
$remaining_balance = 0;

if (isset($_SESSION['user_id'])) {
    $user_logged_in = true;
    $user_id = $_SESSION['user_id'];

    // Fetch user data
    $user_query = "SELECT * FROM users WHERE id='$user_id'";
    $user_result = mysqli_query($con, $user_query);
    $user_data = mysqli_fetch_assoc($user_result);

    $username = $user_data['username'] ?? 'User';
    $allowance = $user_data['monthly_allowance'] ?? 0;

    // Monthly expenses
    $month = date('m');
    $year = date('Y');

    $expense_query = "SELECT SUM(amount) AS total FROM expenses 
                      WHERE user_id='$user_id' AND MONTH(date)='$month' AND YEAR(date)='$year'";
    $expense_result = mysqli_query($con, $expense_query);
    $expense_data = mysqli_fetch_assoc($expense_result);

    $total_spent = $expense_data['total'] ?? 0;
    $remaining_balance = $allowance - $total_spent;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100..900&display=swap" rel="stylesheet" />

<title>Expense Tracker | Track Smarter</title>

<!-- KEEP YOUR EXISTING CSS EXACTLY SAME -->
<style>
/* ⚠️ SAME CSS AS YOU PROVIDED — NOT REPEATING TO SAVE SPACE */
  :root {
        --primary-color: #8263a3;
        --text-dark: #232637;
        --white: #ffffff;
        --max-width: 1200px;
      }

      * {
        padding: 0;
        margin: 0;
        box-sizing: border-box;
      }

      body {
        font-family: "Noto Sans JP", sans-serif;
        background-color: #dbdce0;
      }

      body::after {
        position: fixed;
        content: "";
        height: 100%;
        width: 0;
        top: 0;
        right: 0;
        background-color: var(--text-dark);
        z-index: -1;
        animation: body-bg 1s ease-in-out forwards;
      }

      @keyframes body-bg {
        0% {
          width: 0vw;
        }
        100% {
          width: 50vw;
        }
      }

      body::before {
        position: fixed;
        content: "0";
        top: 0;
        left: 0;
        transform: translate(-50%, -50%);
        font-size: 70rem;
        font-weight: 200;
        color: var(--white);
        z-index: -1;
        opacity: 0.5;
      }

      a {
        text-decoration: none;
        transition: 0.3s;
      }

      .btn {
        position: absolute;
        padding: 1rem 2rem;
        border: none;
        outline: none;
        font-weight: 600;
        cursor: pointer;
      }

      .container {
        position: relative;
        isolation: isolate;
        min-height: 100vh;
        max-width: var(--max-width);
        margin-inline: auto;
        overflow: hidden;
      }

      nav {
        padding-block: 2rem 0;
        padding-inline: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .nav__links {
        list-style: none;
        display: flex;
        gap: 2rem;
      }

      .nav__links a {
        font-weight: 500;
      }

      .nav__left a {
        color: var(--text-dark);
      }

      .nav__right a {
        color: var(--white);
      }

      .nav__links .logo {
        font-size: 1.2rem;
        font-weight: 800;
      }

      .nav__links a:hover {
        color: var(--primary-color);
      }

      .letter-s {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 40rem;
        font-weight: 900;
        color: var(--primary-color);
      }

      h4 {
        position: absolute;
        top: 50%;
        left: 50%;
        font-size: 5rem;
        letter-spacing: 25px;
        color: var(--white);
      }

      .text__left {
        transform: translate(calc(-50% - 250px), -50%);
      }

      .text__right {
        transform: translate(calc(-50% + 250px), -50%);
      }

      .explore {
        top: 45%;
        left: 50%;
        transform: translate(-50%, calc(-50% + 225px));
        background: var(--white);
        color: var(--text-dark);
        box-shadow: 0 0 50px rgba(0, 0, 0, 0.4);
      }

      .print {
        top: 50%;
        right: 0;
        transform: translateY(-50%) rotate(90deg);
        background: transparent;
        color: var(--white);
        border: 1px solid var(--white);
      }

      .catalog {
        top: 25%;
        left: 0;
        transform: translateY(-50%) rotate(-90deg);
        background: transparent;
        color: var(--text-dark);
        border: 1px solid var(--text-dark);
      }

      h5 {
        position: absolute;
        top: 50%;
        left: 50%;
        font-size: 1.2rem;
      }

      h5::after {
        content: "";
        position: absolute;
        width: 100px;
        height: 1px;
        top: 50%;
      }

      .feature-1 {
        transform: translate(calc(-50% - 300px), calc(-50% - 200px));
        color: var(--text-dark);
      }

      .feature-2 {
        transform: translate(calc(-50% + 300px), calc(-50% - 200px));
        color: var(--white);
      }

      .feature-3 {
        transform: translate(calc(-50% - 300px), calc(-50% + 200px));
        color: var(--text-dark);
      }

      .feature-4 {
        transform: translate(calc(-50% + 300px), calc(-50% + 200px));
        color: var(--white);
      }

      .feature-1::after,
      .feature-3::after {
        right: 0;
        transform: translate(calc(100% + 40px), -50%);
        background: var(--text-dark);
      }

      .feature-2::after,
      .feature-4::after {
        left: 0;
        transform: translate(calc(-100% - 40px), -50%);
        background: var(--white);
      }
      /* ===== 3D FOOTER STATS ===== */
.footer-3d {
  position: absolute;
  bottom: 25px;
  left: 50%;
  transform: translateX(-50%) perspective(1000px);
}

.footer-card {
  display: flex;
  gap: 2rem;
  padding: 1rem 2.5rem;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(12px);
  border-radius: 20px;
  box-shadow:
    0 15px 30px rgba(0, 0, 0, 0.4),
    inset 0 1px 0 rgba(255, 255, 255, 0.3);
  transform: rotateX(10deg);
  transition: transform 0.4s ease, box-shadow 0.4s ease;
}

.footer-card:hover {
  transform: rotateX(0deg) translateY(-5px);
  box-shadow:
    0 25px 50px rgba(0, 0, 0, 0.6);
}

.footer-item {
  text-align: center;
  color: black;
}

.footer-item span {
  display: block;
  font-size: 1.2remrem;
  opacity: 0.8;
  color:black;
}

.footer-item strong {
  font-size: 1.2rem;
  font-weight: 700;
}



.footer-balance {
  color: #4caf50;
}

.footer-balance.negative {
  color: #ff5252;
}

</style>
</head>

<body>
<div class="container">

<!-- NAVBAR -->
<nav>
    <ul class="nav__links nav__left">
        <li><a class="logo" href="home.php">Fin Track</a></li>
        <?php if($user_logged_in): ?>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="manage_expenses.php">Expenses</a></li>
            <li><a href="reports.php">Reports</a></li>
        <?php endif; ?>
    </ul>

    <ul class="nav__links nav__right">
        <?php if($user_logged_in): ?>
            <li><a href="add_expense.php">Add Expense</a></li>
            <li><a href="change_details.php"><?php echo htmlspecialchars($username); ?></a></li>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>

<!-- BACKGROUND SYMBOL -->
<span class="letter-s">₹</span>

<!-- TITLES -->
<h4 class="text__left">FIN</h4>
<h4 class="text__right">TRACK</h4>

<!-- USER INFO (LOGGED IN ONLY) -->
<?php if($user_logged_in): ?>
<?php if($user_logged_in): ?>
<div class="footer-3d">
  <div class="footer-card">

    <div class="footer-item">
      <span>Allowance</span>
      <strong>₹<?php echo number_format($allowance, 2); ?></strong>
    </div>

    <div class="footer-item">
      <span>Spent</span>
      <strong>₹<?php echo number_format($total_spent, 2); ?></strong>
    </div>

    <div class="footer-item">
      <span>Balance</span>
      <strong class="footer-balance <?php echo ($remaining_balance < 0) ? 'negative' : ''; ?>">
        ₹<?php echo number_format($remaining_balance, 2); ?>
      </strong>
    </div>

  </div>
</div>
<?php endif; ?>

    
</p>
<?php endif; ?>

<!-- BUTTONS -->
<a href="<?php echo $user_logged_in ? 'index.php' : 'login.php'; ?>">
    <button class="btn explore">TRACK NOW</button>
</a>

<?php if($user_logged_in): ?>
<a href="index.php"><button class="btn print">FIN</button></a>
<a href="manage_expenses.php"><button class="btn catalog">TRACK</button></a>
<?php endif; ?>

<!-- FEATURES -->
<h5 class="feature-1">Smart Tracking</h5>
<h5 class="feature-2">Budget Control</h5>
<h5 class="feature-3">Real-Time Stats</h5>
<h5 class="feature-4">Secure Data</h5>

</div>

<script src="https://unpkg.com/scrollreveal"></script>
<script>
const scrollRevealOption = {
  distance: "50px",
  origin: "bottom",
  duration: 1000,
};

ScrollReveal().reveal(".letter-s", { delay: 1000 });
ScrollReveal().reveal(".text__left", { ...scrollRevealOption, origin: "right", delay: 2000 });
ScrollReveal().reveal(".text__right", { ...scrollRevealOption, origin: "left", delay: 2000 });
ScrollReveal().reveal(".explore", { delay: 2500 });
ScrollReveal().reveal("h5", { interval: 500, delay: 3000 });
ScrollReveal().reveal(".catalog", { delay: 5000 });
ScrollReveal().reveal(".print", { delay: 5500 });
</script>

</body>
</html>