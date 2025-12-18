<?php
require('db.php');
include("auth_session.php");

$user_id = $_SESSION['user_id'];
$msg = "";

/* =========================
   CONFIRM SPLIT HANDLER
========================= */
if (isset($_POST['confirm_split']) && isset($_SESSION['split_expense'])) {

    $data = $_SESSION['split_expense'];
    $split_amount = $_POST['split_amount'];
    $new_category = $_POST['new_category'];

    // Insert allowed amount into original category
    mysqli_query($con, "
        INSERT INTO expenses (user_id, category_id, amount, date, description)
        VALUES (
            '{$data['user_id']}',
            '{$data['category_id']}',
            '{$data['amount_main']}',
            '{$data['date']}',
            '{$data['description']}'
        )
    ");

    // Insert extra amount into new category
    mysqli_query($con, "
        INSERT INTO expenses (user_id, category_id, amount, date, description)
        VALUES (
            '{$data['user_id']}',
            '$new_category',
            '$split_amount',
            '{$data['date']}',
            '{$data['description']} (Split)'
        )
    ");

    unset($_SESSION['split_expense']);
    header("Location: index.php");
    exit();
}

/* =========================
   ADD EXPENSE HANDLER
========================= */
if (isset($_POST['submit'])) {

    $category_id = $_POST['category_id'];
    $amount      = $_POST['amount'];
    $date        = $_POST['date'];
    $description = $_POST['description'];

    if (empty($category_id) || empty($amount) || empty($date)) {
        $msg = "All fields are required.";
    } else {

        $month = date('m', strtotime($date));
        $year  = date('Y', strtotime($date));

        // Get category limit
        $limit_sql = "SELECT limit_amount 
                      FROM category_limits 
                      WHERE user_id='$user_id' 
                      AND category_id='$category_id'";
        $limit_result = mysqli_query($con, $limit_sql);

        if (mysqli_num_rows($limit_result) > 0) {

            $limit_row = mysqli_fetch_assoc($limit_result);
            $limit = $limit_row['limit_amount'];

            // Calculate current spending
            $spent_sql = "SELECT SUM(amount) AS total_spent
                          FROM expenses
                          WHERE user_id='$user_id'
                          AND category_id='$category_id'
                          AND MONTH(date)='$month'
                          AND YEAR(date)='$year'";
            $spent_result = mysqli_query($con, $spent_sql);
            $spent_row = mysqli_fetch_assoc($spent_result);

            $current_spent = $spent_row['total_spent'] ?? 0;

            // 🚫 LIMIT EXCEEDED
            if (($current_spent + $amount) > $limit) {

                $remaining = max(0, $limit - $current_spent);
                $extra = ($current_spent + $amount) - $limit;

                $_SESSION['split_expense'] = [
                    'user_id' => $user_id,
                    'category_id' => $category_id,
                    'amount_main' => $remaining,
                    'date' => $date,
                    'description' => $description
                ];

                echo "
                <script>
                    window.onload = function() {
                        openModal($extra, $limit, $current_spent);
                    };
                </script>";
            }
            // ✅ NORMAL INSERT
            else {
                mysqli_query($con, "
                    INSERT INTO expenses (user_id, category_id, amount, date, description)
                    VALUES ('$user_id','$category_id','$amount','$date','$description')
                ");
                header("Location: index.php");
                exit();
            }

        } else {
            // No limit set
            mysqli_query($con, "
                INSERT INTO expenses (user_id, category_id, amount, date, description)
                VALUES ('$user_id','$category_id','$amount','$date','$description')
            ");
            header("Location: index.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Expense - Expense Tracker</title>
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

/* ===== BODY & BACKGROUND ===== */
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


/* ===== NAVBAR ===== */
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
@keyframes gradientBG {
    0%   { background-position: 0% 50%, center; }
    50%  { background-position: 100% 50%, center; }
    100% { background-position: 0% 50%, center; }
}

/* ===== PAGE CENTERING ===== */
.container{
    max-width:600px;
    margin:60px auto;
    padding:0 15px;
    display:flex;
    justify-content:center;
}

/* ===== CARD ===== */
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

/* ===== TITLE ===== */
.card h2{
    text-align:center;
    color:var(--primary-color);
    margin-bottom:25px;
}

/* ===== FORM LAYOUT ===== */
.card form{
    display:flex;
    flex-direction:column;
    align-items:center;
}

/* ===== FORM GROUP ===== */
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

/* ===== INPUTS (SAME SIZE) ===== */
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

/* ===== INPUT FOCUS ===== */
input:focus,
select:focus{
    outline:none;
    border-color:var(--primary-color);
    box-shadow:0 0 8px rgba(102,126,234,0.35);
}

/* ===== READONLY DATE ===== */
input[readonly]{
    background:#f3f3f3;
    cursor:not-allowed;
}

/* ===== BUTTONS ===== */
.btn{
    padding:12px 22px;
    border:none;
    border-radius:10px;
    font-size:1rem;
    cursor:pointer;
    transition:0.3s ease;
}

/* SAVE BUTTON CENTERED */
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

/* LOGOUT BUTTON */
.btn-danger{
    background:var(--danger-color);
    color:#fff;
}
.btn-danger:hover{
    background:#c0392b;
}

/* ===== ALERT ===== */
.alert{
    background:#ffe6e6;
    border-left:5px solid var(--danger-color);
    padding:12px 15px;
    border-radius:8px;
    margin-bottom:20px;
    color:var(--danger-color);
}

/* ===== MODAL ===== */
.modal{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.55);
    z-index:999;
}
.modal-content{
    background:#fff;
    width:90%;
    max-width:450px;
    margin:10% auto;
    padding:28px;
    border-radius:16px;
    box-shadow:var(--shadow);
    animation:slideDown 0.35s ease;
}
.modal h3{
    text-align:center;
    color:var(--danger-color);
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

/* ===== ANIMATION ===== */
@keyframes slideDown{
    from{transform:translateY(-30px);opacity:0;}
    to{transform:translateY(0);opacity:1;}
}

/* ===== RESPONSIVE ===== */
@media(max-width:600px){
    .navbar{
        flex-direction:column;
        gap:10px;
    }
}

@keyframes gradientBG {
    0%   { background-position: 0% 50%, center; }
    50%  { background-position: 100% 50%, center; }
    100% { background-position: 0% 50%, center; }
}

</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <h2>Fin Tracker</h2>
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
        <h2>Add New Expense</h2>

        <?php if($msg) echo "<div class='alert'>$msg</div>"; ?>

        <form method="post">
            <div class="form-group">
                <label>Amount (₹)</label>
                <input type="number" step="0.01" name="amount" required>
            </div>

            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>

            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php
                    $cats = mysqli_query($con,
                        "SELECT * FROM categories 
                         WHERE user_id IS NULL OR user_id='$user_id'
                         ORDER BY name");
                    while($c = mysqli_fetch_assoc($cats)){
                        echo "<option value='{$c['id']}'>{$c['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" placeholder="e.g. Lunch, Travel">
            </div>

            <button type="submit" name="submit" class="btn btn-primary">Save Expense</button>
        </form>
    </div>
</div>

<!-- Modal -->
<div id="splitModal" class="modal">
    <div class="modal-content">
        <h3>⚠️ Category Limit Exceeded</h3>
        <p id="limitInfo"></p>

        <form method="post">
            <input type="hidden" name="confirm_split" value="1">

            <div class="form-group">
                <label>Extra Amount</label>
                <input type="number" step="0.01" name="split_amount" id="splitAmount" required>
            </div>

            <div class="form-group">
                <label>Add to Category</label>
                <select name="new_category" required>
                    <option value="">Select Category</option>
                    <?php
                    $cats2 = mysqli_query($con,
                        "SELECT * FROM categories 
                         WHERE user_id IS NULL OR user_id='$user_id'
                         ORDER BY name");
                    while($c = mysqli_fetch_assoc($cats2)){
                        echo "<option value='{$c['id']}'>{$c['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-primary">Confirm</button>
                <button type="button" class="btn btn-danger" onclick="closeModal()">Cancel</button>
            </div>
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

<script>
function openModal(extra, limit, spent){
    document.getElementById("splitModal").style.display = "block";
    document.getElementById("splitAmount").value = extra;
    document.getElementById("limitInfo").innerHTML =
        "<strong>Limit:</strong> ₹"+limit+"<br>"+
        "<strong>Spent:</strong> ₹"+spent+"<br>"+
        "<strong>Extra:</strong> ₹"+extra;
}
function closeModal(){
    document.getElementById("splitModal").style.display = "none";
}
window.onclick = function(event){
    const modal = document.getElementById("splitModal");
    if(event.target == modal){ modal.style.display = "none"; }
};
</script>

</body>
</html>