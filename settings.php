<?php
require('db.php');
include("auth_session.php");

$user_id = $_SESSION['user_id'];
$msg = "";

/* ===== FETCH CURRENT ALLOWANCE ===== */
$res = mysqli_query($con,"SELECT monthly_allowance FROM users WHERE id='$user_id'");
$row = mysqli_fetch_assoc($res);
$current_allowance = $row['monthly_allowance'] ?? 0;

/* ===== UPDATE ALLOWANCE ===== */
if(isset($_POST['update_allowance'])){
    $allowance = floatval($_POST['monthly_allowance']);

    if($allowance < 0){
        $msg = "Allowance cannot be negative!";
    }else{
        $sum = mysqli_fetch_assoc(
            mysqli_query($con,"SELECT SUM(limit_amount) AS s FROM category_limits WHERE user_id='$user_id'")
        )['s'] ?? 0;

        if($sum > $allowance){
            $msg = "Category limits exceed allowance!";
        }else{
            mysqli_query($con,"UPDATE users SET monthly_allowance='$allowance' WHERE id='$user_id'");
            $current_allowance = $allowance;
            $msg = "Allowance updated successfully!";
        }
    }
}

/* ===== UPDATE CATEGORY LIMIT ===== */
if(isset($_POST['update_limit'])){
    $cat_id = $_POST['cat_id'];
    $limit  = floatval($_POST['limit_amount']);

    $other = mysqli_fetch_assoc(
        mysqli_query($con,"
            SELECT SUM(limit_amount) AS s 
            FROM category_limits 
            WHERE user_id='$user_id' AND category_id!='$cat_id'
        ")
    )['s'] ?? 0;

    if($other + $limit > $current_allowance){
        $msg = "Total category limits exceed allowance!";
    }else{
        $check = mysqli_query($con,"
            SELECT * FROM category_limits 
            WHERE user_id='$user_id' AND category_id='$cat_id'
        ");

        if(mysqli_num_rows($check)){
            mysqli_query($con,"
                UPDATE category_limits 
                SET limit_amount='$limit'
                WHERE user_id='$user_id' AND category_id='$cat_id'
            ");
        }else{
            mysqli_query($con,"
                INSERT INTO category_limits(user_id,category_id,limit_amount)
                VALUES('$user_id','$cat_id','$limit')
            ");
        }
        $msg = "Category limit updated!";
    }
}

/* ===== FETCH CATEGORIES ===== */
$limits = mysqli_query($con,"
    SELECT c.id,c.name,l.limit_amount 
    FROM categories c
    LEFT JOIN category_limits l 
    ON c.id=l.category_id AND l.user_id='$user_id'
    WHERE c.user_id IS NULL OR c.user_id='$user_id'
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Settings - Expense Tracker</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
/* ===== GLOBAL ===== */
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
.top-navbar{
    background:linear-gradient(90deg,#6a7be6,#7b4fa1);
    padding:18px 6%;
    display:flex;
    justify-content:space-between;
    align-items:center;
    color:#fff;
    border-radius:0 0 18px 18px;
    box-shadow:0 10px 25px rgba(0,0,0,.25);
}

.nav-left{font-size:26px;font-weight:700;}
.nav-right{display:flex;gap:22px;align-items:center;}

.nav-right a{
    color:#eaeaff;
    text-decoration:none;
    font-weight:600;
    padding:6px 14px;
    border-radius:8px;
    transition:.3s;
}
.nav-right a:hover{background:rgba(255,255,255,.18);}
.nav-right .active{background:#fff;color:#6a7be6;}

.logout-btn{
    background:#f15b42;
    color:#fff !important;
    padding:8px 16px;
    border-radius:8px;
}
.logout-btn:hover{background:#d94a33;}

/* ===== CONTAINER ===== */
.container{
    max-width:1000px;
    margin:40px auto;
    padding:0 20px;
}

/* ===== CARD ===== */
.card{
    background:#fff;
    border-radius:20px;
    padding:35px;
    box-shadow:0 15px 40px rgba(0,0,0,.12);
    transition:.3s;
}
.card:hover{
    transform:translateY(-3px);
    box-shadow:0 18px 45px rgba(0,0,0,.15);
}

/* ===== INPUT GROUP ===== */
.input-group{
    position:relative;
    width:320px;
    margin:20px auto;
}
.input-group.small{
    width:190px;
    margin:0;
}

.input-group input{
    width:100%;
    padding:16px 16px 16px 44px;
    font-size:16px;
    border-radius:14px;
    border:1.5px solid #d1d5db;
    background:#fff;
    transition:.25s;
}

.currency{
    position:absolute;
    left:14px;
    top:50%;
    transform:translateY(-50%);
    font-weight:600;
    color:#6b7280;
}

.input-group label{
    position:absolute;
    left:44px;
    top:50%;
    transform:translateY(-50%);
    color:#9ca3af;
    font-size:15px;
    background:#fff;
    padding:0 6px;
    transition:.25s;
    pointer-events:none;
}

.input-group input:focus + label,
.input-group input:not(:placeholder-shown) + label{
    top:-8px;
    font-size:13px;
    color:#6a7be6;
}

.input-group input:focus{
    outline:none;
    border-color:#6a7be6;
    box-shadow:0 0 0 4px rgba(106,123,230,.25);
}

/* ===== ERROR STATE ===== */
.input-group.error input{
    border-color:#ef4444;
    box-shadow:0 0 0 4px rgba(239,68,68,.25);
}
.input-group.error label{color:#ef4444;}

/* ===== CATEGORY GRID ===== */
.category-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:18px;
}
.cat-card{
    display:flex;
    align-items:center;
    gap:12px;
    padding:16px;
    background:#f9fafb;
    border:1px solid #e5e7eb;
    border-radius:14px;
    transition:.25s;
}
.cat-card:hover{
    transform:translateY(-4px);
    box-shadow:0 10px 25px rgba(0,0,0,.12);
}

/* ===== BUTTON ===== */
button{
    background:#6a7be6;
    border:none;
    color:#fff;
    padding:10px 18px;
    border-radius:10px;
    cursor:pointer;
    transition:.25s;
}
button:hover{
    background:#5b6dd6;
    transform:translateY(-2px);
}

/* ===== POPUP ===== */
.popup{
    position:fixed;
    top:20px;
    right:20px;
    background:#2563eb;
    color:#fff;
    padding:14px 22px;
    border-radius:12px;
    box-shadow:0 8px 25px rgba(0,0,0,.25);
}

/* ===== MOBILE ===== */
@media(max-width:768px){
    .category-grid{grid-template-columns:1fr;}
    .input-group,.input-group.small{width:100%;}
}
/* ===== SETTINGS TITLE ===== */
.settings-title{
    text-align:center;
    font-size:32px;
    font-weight:700;
    margin-bottom:35px;
    color:#1f2937;
    position:relative;
}

.settings-title::after{
    content:"";
    width:70px;
    height:4px;
    background:linear-gradient(90deg,#6a7be6,#7b4fa1);
    display:block;
    margin:12px auto 0;
    border-radius:10px;
}
/* ===== CENTER BUTTON ===== */
.center-btn{
    display:flex;
    justify-content:center;
    margin-top:20px;
}

/* ===== PRIMARY BUTTON (STYLISH) ===== */
.primary-btn{
    background:linear-gradient(90deg,#6a7be6,#7b4fa1);
    padding:12px 28px;
    font-size:16px;
    font-weight:600;
    border-radius:14px;
    box-shadow:0 8px 22px rgba(106,123,230,.35);
}

.primary-btn:hover{
    background:linear-gradient(90deg,#5b6dd6,#6a3f91);
    transform:translateY(-3px);
    box-shadow:0 12px 28px rgba(106,123,230,.45);
}
/* ===== FOOTER (FIXED) ===== */
.footer{
    margin-top:60px;
    background:linear-gradient(90deg,#6a7be6,#7b4fa1);
    color:#ffffff;
    border-radius:22px 22px 0 0;
    box-shadow:0 -12px 35px rgba(0,0,0,0.25);
}

/* Footer content grid */
.footer-content{
    max-width:1100px;
    margin:auto;
    padding:40px 25px 30px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:35px;
}

/* Sections */
.footer-section h4{
    margin-bottom:12px;
    font-size:18px;
    font-weight:700;
    letter-spacing:0.6px;
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
    margin-bottom:10px;
}

.footer-section ul li a{
    color:#f1f1ff;
    text-decoration:none;
    font-size:0.95rem;
    transition:0.25s ease;
}

.footer-section ul li a:hover{
    text-decoration:underline;
    opacity:0.9;
}

/* Bottom bar */
.footer-bottom{
    text-align:center;
    padding:14px 12px;
    background:rgba(0,0,0,0.25);
    font-size:0.9rem;
    letter-spacing:0.5px;
}

@keyframes gradientBG {
    0%   { background-position: 0% 50%, center; }
    50%  { background-position: 100% 50%, center; }
    100% { background-position: 0% 50%, center; }
}

</style>
</head>

<body>

<nav class="top-navbar">
    <div class="nav-left">Fin Track</div>
    <div class="nav-right">
        <a href="home.php">Home</a>
        <a href="index.php">Dashboard</a>
        <a href="manage_expenses.php">Manage Expenses</a>
        <a href="settings.php" class="active">Settings</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</nav>

<div class="container">
<div class="card">

<h2 class="settings-title">Settings</h2>

<form method="post">
    <div class="input-group">
        <span class="currency">₹</span>
        <input type="number" step="0.01" name="monthly_allowance"
               value="<?php echo $current_allowance; ?>" placeholder=" " required>
        <label>Monthly Allowance</label>
    </div>
    <div class="center-btn">
        <button name="update_allowance" class="primary-btn">
            Update Allowance
        </button>
    </div>

</form>

<h3 style="margin-top:40px;">Category Limits</h3>

<div class="category-grid">
<?php while($c=mysqli_fetch_assoc($limits)): ?>
<form method="post" class="cat-card">
    <strong style="flex:1;"><?php echo $c['name']; ?></strong>
    <input type="hidden" name="cat_id" value="<?php echo $c['id']; ?>">
    <div class="input-group small">
        <span class="currency">₹</span>
        <input type="number" step="0.01" name="limit_amount"
               value="<?php echo $c['limit_amount']; ?>" placeholder=" ">
        <label>Limit</label>
    </div>
    <button name="update_limit">Save</button>
</form>
<?php endwhile; ?>
</div>

</div>
</div>

<?php if($msg): ?>
<div class="popup"><?php echo $msg; ?></div>
<script>
setTimeout(()=>document.querySelector('.popup').remove(),3000);
</script>
<?php endif; ?>
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
