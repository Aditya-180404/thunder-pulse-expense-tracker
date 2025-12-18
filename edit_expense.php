<?php
require('db.php');
include("auth_session.php");

$user_id = $_SESSION['user_id'];
$id = $_GET['id'];
$msg = "";

/* FETCH EXPENSE */
$query = "SELECT * FROM expenses WHERE id='$id' AND user_id='$user_id'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);

/* UPDATE EXPENSE */
if(isset($_POST['submit'])) {
    $category_id = $_POST['category_id'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
    $description = $_POST['description'];

    $update_query = "
        UPDATE expenses 
        SET category_id='$category_id',
            amount='$amount',
            date='$date',
            description='$description'
        WHERE id='$id' AND user_id='$user_id'
    ";

    if(mysqli_query($con, $update_query)) {
        header("Location: manage_expenses.php");
        exit();
    } else {
        $msg = "Error updating record.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Expense - Expense Tracker</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

<style>
:root{
    --primary-color:#667eea;
    --secondary-color:#764ba2;
    --danger-color:#e74c3c;
    --card-bg:#ffffff;
    --text-color:#333;
    --shadow:0 12px 35px rgba(0,0,0,0.15);
}

/* BACKGROUND */
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

/* NAVBAR (SAME AS ADD EXPENSE) */
/* ===== NAVBAR ===== */
.navbar{
    background:linear-gradient(90deg,var(--primary-color),var(--secondary-color));
    padding:16px 5%;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:var(--shadow);
    border-radius:0 0 18px 18px;
    flex-wrap:wrap;
}

.navbar h2{
    color:#fff;
    font-weight:700;
    font-size:1.8rem;
    margin:0;
}

/* Nav links container */
.navbar div{
    display:flex;
    align-items:center;
    gap:12px;
}

/* Nav links */
.navbar a{
    color:#eef0ff;
    text-decoration:none;
    font-weight:600;
    padding:8px 14px;
    border-radius:8px;
    transition:0.3s ease;
    font-size:0.95rem;
}

/* Hover */
.navbar a:hover{
    background:#ffffff;
    color:var(--primary-color);
}

/* Active page */
.navbar a.active{
    background:#ffffff;
    color:var(--primary-color);
    box-shadow:0 4px 10px rgba(0,0,0,0.15);
}

/* Logout button */
.navbar .btn-danger{
    background:var(--danger-color);
    color:#fff !important;
    padding:8px 16px;
    border-radius:10px;
    font-weight:600;
}

.navbar .btn-danger:hover{
    background:#c0392b;
    transform:translateY(-2px);
}

/* ===== MOBILE NAVBAR ===== */
@media(max-width:700px){
    .navbar{
        flex-direction:column;
        align-items:flex-start;
        gap:12px;
    }

    .navbar div{
        flex-wrap:wrap;
        gap:8px;
    }
}

/* CONTAINER */
.container{
    max-width:600px;
    margin:60px auto;
    padding:0 15px;
    display:flex;
    justify-content:center;
}

/* CARD */
.card{
    width:100%;
    background:var(--card-bg);
    border-radius:18px;
    padding:35px;
    box-shadow:var(--shadow);
    transition:0.35s ease;
}
.card:hover{
    transform:translateY(-6px);
    box-shadow:0 20px 50px rgba(0,0,0,0.25);
}

/* TITLE */
h2{
    text-align:center;
    color:var(--primary-color);
    margin-bottom:25px;
}

/* FORM */
.card form{
    display:flex;
    flex-direction:column;
    align-items:center;
}
.form-group{
    width:100%;
    margin-bottom:20px;
}
label{
    font-weight:600;
    display:block;
    margin-bottom:8px;
    color:var(--text-color);
}

/* INPUTS */
input[type="text"],
input[type="number"],
input[type="date"],
select{
    width:100%;
    padding:12px 15px;
    border-radius:10px;
    border:1px solid #ccc;
    font-size:1rem;
    transition:0.3s ease;
}
input:focus,
select:focus{
    outline:none;
    border-color:var(--primary-color);
    box-shadow:0 0 8px rgba(102,126,234,0.35);
}

/* BUTTONS */
.btn{
    padding:12px 22px;
    border:none;
    border-radius:10px;
    font-size:1rem;
    cursor:pointer;
    transition:0.3s ease;
    text-decoration:none;
}
.btn-primary{
    background:var(--primary-color);
    color:#fff;
    display:block;
    margin:15px auto 0 auto;
}
.btn-primary:hover{
    background:#5a67d8;
    transform:translateY(-2px);
}
.btn-secondary{
    background:#6c757d;
    color:#fff;
    display:block;
    margin:10px auto 0 auto;
    text-align:center;
}
.btn-secondary:hover{
    background:#5a6268;
}
.btn-danger{
    background:#e74c3c;
    color:#fff;
}
.btn-danger:hover{
    background:#c0392b;
    transform:translateY(-2px);
}
/* ===== FOOTER ===== */
.footer{
    margin-top:50px;
    background:linear-gradient(90deg,var(--primary-color),var(--secondary-color));
    color:#fff;
    border-radius:18px 18px 0 0;
    box-shadow:0 -10px 30px rgba(0,0,0,0.2);
}

/* Footer content grid */
.footer-content{
    max-width:1100px;
    margin:auto;
    padding:30px 20px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:25px;
}

/* Sections */
.footer-section h4{
    margin-bottom:10px;
    font-weight:700;
    letter-spacing:0.5px;
}
.footer-section p{
    font-size:0.95rem;
    line-height:1.6;
    opacity:0.95;
}

/* Links */
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
    font-size:0.95rem;
    transition:0.3s;
}
.footer-section ul li a:hover{
    text-decoration:underline;
    opacity:0.85;
}

/* Bottom bar */
.footer-bottom{
    text-align:center;
    padding:12px 10px;
    background:rgba(0,0,0,0.15);
    font-size:0.9rem;
    letter-spacing:0.5px;
}


/* ALERT */
.alert{
    background:#ffe6e6;
    border-left:5px solid var(--danger-color);
    padding:12px 15px;
    border-radius:8px;
    margin-bottom:20px;
    color:var(--danger-color);
}

/* RESPONSIVE */
@media(max-width:600px){
    .navbar{
        flex-direction:column;
        gap:10px;
    }
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
    <h2>Fin Track</h2>
    <div class="nav-links">
        <a href="home.php">Home</a>
        <a href="index.php">Dashboard</a>
        <a href="manage_expenses.php">Manage Expenses</a>
        <a href="settings.php">Settings</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="card">
        <h2>Edit Expense</h2>

        <?php if($msg) echo "<div class='alert'>$msg</div>"; ?>

        <form method="post">

            <div class="form-group">
                <label>Amount (₹)</label>
                <input type="number" step="0.01" name="amount"
                       value="<?php echo $row['amount']; ?>" required>
            </div>

            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date"
                       value="<?php echo $row['date']; ?>" required>
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category_id" required>
                    <?php
                    $cats = mysqli_query($con,
                        "SELECT * FROM categories 
                         WHERE user_id IS NULL OR user_id='$user_id'
                         ORDER BY name");
                    while($c = mysqli_fetch_assoc($cats)){
                        $selected = ($c['id'] == $row['category_id']) ? "selected" : "";
                        echo "<option value='{$c['id']}' $selected>{$c['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description"
                       value="<?php echo $row['description']; ?>">
            </div>

            <button type="submit" name="submit" class="btn btn-primary">
                Update Expense
            </button>

            <a href="manage_expenses.php" class="btn btn-secondary">
                Cancel
            </a>

        </form>
    </div>
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
