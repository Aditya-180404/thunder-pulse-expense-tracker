<?php
require('db.php');
include("auth_session.php");

$user_id = $_SESSION['user_id'];

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($con, "DELETE FROM expenses WHERE id='$id' AND user_id='$user_id'");
    header("Location: manage_expenses.php");
    exit();
}

// Selected Category Filter
$selected_category = $_GET['category'] ?? 'all';

// Build Query
$where = "e.user_id='$user_id'";
if ($selected_category != 'all') {
    $where .= " AND e.category_id='$selected_category'";
}

// Fetch Expenses
$query = "SELECT e.*, c.name AS category_name
          FROM expenses e
          JOIN categories c ON e.category_id = c.id
          WHERE $where
          ORDER BY e.date DESC";
$result = mysqli_query($con, $query);

// Fetch Categories
$cat_query = "SELECT * FROM categories 
              WHERE user_id IS NULL OR user_id='$user_id'
              ORDER BY name";
$cat_result = mysqli_query($con, $cat_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Expenses - Expense Tracker</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
:root{
    --primary-color:#667eea;
    --secondary-color:#764ba2;
    --danger-color:#e74c3c;
    --success-color:#28a745;
    --card-bg:#fff;
    --text-color:#333;
    --shadow:0 10px 30px rgba(0,0,0,0.1);
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

.navbar{ background:linear-gradient(90deg,var(--primary-color),var(--secondary-color)); padding:15px 5%; display:flex; justify-content:space-between; align-items:center; box-shadow: var(--shadow); border-radius:0 0 15px 15px; flex-wrap:wrap;}
.navbar h2{ color:#fff; font-weight:700; font-size:1.8rem; margin:0;}
.navbar a{ color:#fff; text-decoration:none; margin-left:15px; padding:5px 12px; border-radius:6px; font-weight:600; transition:0.3s;}
.navbar a:hover{ background:#fff; color:var(--primary-color);}
.container{ max-width:900px; margin:40px auto; padding:0 10px;}
.card{ background:var(--card-bg); border-radius:15px; padding:25px 30px; box-shadow:var(--shadow); margin-bottom:30px; transition:0.3s;}
.card:hover{ transform:translateY(-3px); box-shadow:0 15px 35px rgba(0,0,0,0.2);}
h2{ color:var(--primary-color); margin-bottom:20px;}
select{ padding:10px 12px; border-radius:10px; border:1px solid #ccc; font-size:1rem; transition:0.3s;}
select:focus{ outline:none; border-color:var(--primary-color); box-shadow:0 0 8px rgba(102,126,234,0.3);}
table{ width:100%; border-collapse:collapse; margin-top:15px; }
thead tr{ background:#f5f5f5; }
th, td{ padding:12px; text-align:left; font-size:0.95rem; }
tr:hover{ background:#f0f0f0; }
.btn{ padding:10px 18px; border:none; border-radius:10px; font-size:0.95rem; cursor:pointer; transition:0.3s; text-decoration:none; display:inline-block;}
.btn-primary{ background:var(--primary-color); color:#fff;}
.btn-primary:hover{ background:#5a67d8; transform:translateY(-2px);}
.btn-danger{ background:var(--danger-color); color:#fff;}
.btn-danger:hover{ background:#c0392b; transform:translateY(-2px);}
.action-links a{ margin-right:10px; transition:0.3s; }
.action-links a:hover{ text-decoration:underline; }
@media(max-width:700px){ .navbar{flex-direction:column; align-items:flex-start;} table, thead, tbody, th, td, tr{ display:block; } thead tr{ display:none; } td{ padding-left:50%; position:relative; margin-bottom:10px; } td:before{ content:attr(data-label); position:absolute; left:15px; font-weight:bold; } }
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
    <h2>Fin Track</h2>
    <div>
        <a href="home.php">Home</a>
        <a href="index.php">Dashboard</a>
        <a href="add_expense.php">Add Expense</a>
        <a href="manage_expenses.php" style="font-weight:bold;">Manage Expenses</a>
        <a href="settings.php">Settings</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</nav>

<div class="container">

    <!-- FILTER SECTION -->
    <div class="card">
        <form method="get" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
            <label style="font-weight:600;">Filter by Category:</label>
            <select name="category" onchange="this.form.submit()">
                <option value="all">All Categories</option>
                <?php while ($c = mysqli_fetch_assoc($cat_result)) { ?>
                    <option value="<?php echo $c['id']; ?>"
                        <?php if ($selected_category == $c['id']) echo 'selected'; ?> >
                        <?php echo $c['name']; ?>
                    </option>
                <?php } ?>
            </select>
        </form>
    </div>

    <!-- TRANSACTIONS TABLE -->
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h2><?php echo ($selected_category == 'all') ? "All Transactions" : "Category Wise Transactions"; ?></h2>
            <a href="add_expense.php" class="btn btn-primary">+ Add Expense</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td data-label="Date"><?php echo $row['date']; ?></td>
                            <td data-label="Category"><?php echo $row['category_name']; ?></td>
                            <td data-label="Description"><?php echo $row['description']; ?></td>
                            <td data-label="Amount" style="font-weight:bold;">₹<?php echo number_format($row['amount'],2); ?></td>
                            <td data-label="Action" class="action-links">
                                <a href="edit_expense.php?id=<?php echo $row['id']; ?>">Edit</a>
                                <a href="manage_expenses.php?delete=<?php echo $row['id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this expense?');" 
                                   style="color:var(--danger-color);">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:15px;">No transactions found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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
