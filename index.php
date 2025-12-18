
<?php
require('db.php');
include("auth_session.php");

$user_id = $_SESSION['user_id'];

// Fetch User Details & Allowance
$user_query = "SELECT * FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($con, $user_query);
$user_data = mysqli_fetch_assoc($user_result);
$allowance = $user_data['monthly_allowance'];

// Calculate Total Spent (Current Month)
$current_month = date('m');
$current_year = date('Y');
$expense_query = "SELECT SUM(amount) as total_spent FROM expenses WHERE user_id='$user_id' AND MONTH(date)='$current_month' AND YEAR(date)='$current_year'";
$expense_result = mysqli_query($con, $expense_query);
$expense_data = mysqli_fetch_assoc($expense_result);
$total_spent = $expense_data['total_spent'] ?? 0;

$remaining_balance = $allowance - $total_spent;
$percentage_spent = ($allowance > 0) ? ($total_spent / $allowance) * 100 : 0;

// Fetch Category-wise Spending for Chart
$cat_query = "SELECT c.id, c.name, IFNULL(SUM(e.amount),0) as total_spent
              FROM categories c
              LEFT JOIN expenses e 
              ON c.id = e.category_id AND e.user_id='$user_id' AND MONTH(e.date)='$current_month' AND YEAR(e.date)='$current_year'
              GROUP BY c.id, c.name";
$cat_result = mysqli_query($con, $cat_query);

$categories = [];
$cat_totals = [];
$cat_limits = [];

while($row = mysqli_fetch_assoc($cat_result)) {
    $categories[] = $row['name'];
    $cat_totals[] = $row['total_spent'];
}

// Use equal split of total allowance as limit per category
$category_count = count($categories);
if($category_count > 0){
    foreach($categories as $cat){
        $cat_limits[] = $allowance / $category_count;
    }
}

// Fetch Recent Expenses
$recent_query = "SELECT e.*, c.name as category_name, c.color 
                 FROM expenses e 
                 JOIN categories c ON e.category_id = c.id 
                 WHERE e.user_id='$user_id' 
                 ORDER BY e.date DESC, e.id DESC LIMIT 5";
$recent_result = mysqli_query($con, $recent_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Expense Tracker</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root{
    --primary-color:#667eea;
    --secondary-color:#764ba2;
    --danger-color:#e74c3c;
    --success-color:#28a745;
    --warning-color:#ffc107;
    --card-bg:#ffffff;
    --text-color:#333;
    --text-muted:#777;
    --shadow:0 10px 30px rgba(0,0,0,0.12);
}

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


/* ================= NAVBAR ================= */
.navbar{
    background:linear-gradient(90deg,var(--primary-color),var(--secondary-color));
    padding:16px 5%;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:var(--shadow);
    border-radius:0 0 18px 18px;
    position:sticky;
    top:0;
    z-index:1000;
    flex-wrap:wrap;
}

.navbar h2{
    color:#fff;
    font-size:1.8rem;
    font-weight:700;
    margin:0;
}

.nav-links{
    display:flex;
    align-items:center;
    gap:12px;
}

.nav-links a{
    color:#eef0ff;
    text-decoration:none;
    font-weight:600;
    padding:8px 14px;
    border-radius:8px;
    transition:0.3s ease;
    font-size:0.95rem;
}

.nav-links a:hover{
    background:#ffffff;
    color:var(--primary-color);
}

.nav-links a.active{
    background:#ffffff;
    color:var(--primary-color);
    box-shadow:0 4px 10px rgba(0,0,0,0.15);
}

.nav-links .btn-danger{
    background:var(--danger-color);
    color:#fff !important;
    padding:8px 16px;
    border-radius:10px;
}

.nav-links .btn-danger:hover{
    background:#c0392b;
    transform:translateY(-2px);
}

/* ===== CONTAINER ===== */
.container{
    max-width:1200px;
    margin:auto;
    padding:30px 15px;
}

/* ================= CARDS ================= */
.card{
    background:var(--card-bg);
    border-radius:18px;
    padding:25px;
    box-shadow:var(--shadow);
    transition:0.3s;
}

.card:hover{
    transform:translateY(-4px);
    box-shadow:0 15px 35px rgba(0,0,0,0.2);
}

/* ===== SUMMARY GRID ===== */
.summary-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-bottom:30px;
    text-align:center;
}

.summary-card h3{
    margin-bottom:10px;
}

.summary-card p{
    font-size:1.8rem;
    font-weight:700;
}

/* ===== PROGRESS BAR ===== */
.progress-bar{
    height:10px;
    background:#eee;
    border-radius:6px;
    overflow:hidden;
    margin-top:10px;
}

.progress{
    height:100%;
    border-radius:6px;
    transition:width 0.5s;
}

/* ===== CONTENT GRID ===== */
.content-grid{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
}

/* ===== CHARTS ===== */
canvas{
    background:#f9fafb;
    border-radius:12px;
    padding:10px;
}

/* ===== RECENT TRANSACTIONS ===== */
.recent-list{
    list-style:none;
    margin:0;
    padding:0;
}

.recent-list li{
    border-bottom:1px solid #eee;
    padding:10px 0;
    display:flex;
    justify-content:space-between;
    align-items:center;
    transition:0.25s;
}

.recent-list li:hover{
    background:#f5f5f5;
    padding:10px 15px;
    border-radius:10px;
}

/* ===== AI CHAT ===== */
#chat-box{
    height:200px;
    overflow-y:auto;
    border:1px solid #ddd;
    border-radius:10px;
    padding:10px;
    background:#f9fafb;
    margin-bottom:10px;
}

#ai-input{
    width:100%;
    padding:10px;
    border-radius:8px;
    border:1px solid #ccc;
}

/* ===== BUTTONS ===== */
.btn{
    display:inline-block;
    padding:10px 20px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-weight:600;
    text-decoration:none;
    transition:0.3s;
}

.btn-primary{
    background:var(--primary-color);
    color:#fff;
}

.btn-primary:hover{
    background:#5a67d8;
    transform:translateY(-2px);
}

.btn-danger{
    background:var(--danger-color);
    color:#fff;
}

.btn-danger:hover{
    background:#c0392b;
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

/* ===== MOBILE ===== */
@media(max-width:900px){
    .content-grid{
        grid-template-columns:1fr;
    }

    .navbar{
        flex-direction:column;
        align-items:flex-start;
        gap:12px;
    }

    .nav-links{
        flex-wrap:wrap;
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
    <h2>Expense Tracker</h2>
    <div class="nav-links">
        <a href="home.php">Home</a>
        <a href="index.php">Dashboard</a>
        <a href="manage_expenses.php">Manage Expenses</a>
        <a href="settings.php">Settings</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</nav>

<div class="container">
    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="card summary-card">
            <h3>Monthly Allowance</h3>
            <p>₹<?php echo number_format($allowance,2); ?></p>
            <div class="progress-bar"><div class="progress" style="width:100%; background:var(--secondary-color);"></div></div>
        </div>
        <div class="card summary-card">
            <h3>Total Spent</h3>
            <p>₹<?php echo number_format($total_spent,2); ?></p>
            <div class="progress-bar"><div class="progress" style="width:<?php echo min(100,$percentage_spent); ?>%; background:var(--danger-color);"></div></div>
        </div>
        <div class="card summary-card">
            <h3>Remaining Balance</h3>
            <p style="color:<?php echo ($remaining_balance<0)?'red':'green'; ?>">₹<?php echo number_format($remaining_balance,2); ?></p>
            <div class="progress-bar"><div class="progress" style="width:<?php echo max(0,100-$percentage_spent); ?>%; background:<?php echo ($remaining_balance<0)?'red':'var(--success-color)'; ?>;"></div></div>
            <?php if($percentage_spent>=80): ?>
                <p style="color:red;font-weight:bold;margin-top:5px;">⚠️ Warning: >80% Spent!</p>
            <?php endif; ?>
        </div>
        <div class="card summary-card" style="display:flex; align-items:center; justify-content:center;">
            <a href="add_expense.php" class="btn btn-primary" style="font-size:1.2rem;">+ Add Expense</a>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">

        <!-- Left: Charts -->
        <div>
            <div class="card">
                <h3>Spending Overview (This Month)</h3>
                <canvas id="expenseChart" style="max-height:300px;"></canvas>
            </div>

            <div class="card" style="margin-top:20px;">
                <h3>Category-wise Allowance vs Spent</h3>
                <select id="allowance-type" style="padding:5px 10px; margin-bottom:10px; border-radius:6px; border:1px solid #ccc;">
                    <option value="monthly">Monthly</option>
                    <option value="weekly">Weekly</option>
                    <option value="daily">Daily</option>
                </select>
                <canvas id="allowanceChart" style="max-height:300px;"></canvas>
            </div>
        </div>

        <!-- Right Column -->
        <div>
            <!-- Recent Transactions -->
            <div class="card">
                <h3>Recent Transactions</h3>
                <ul class="recent-list">
                    <?php while($row = mysqli_fetch_assoc($recent_result)): ?>
                        <li>
                            <div>
                                <span style="display:block;font-weight:bold;"><?php echo htmlspecialchars($row['description']); ?></span>
                                <span style="font-size:0.85rem;color:var(--text-muted);">
                                    <?php echo $row['date']; ?> • <span style="color:<?php echo $row['color']; ?>"><?php echo $row['category_name']; ?></span>
                                </span>
                            </div>
                            <span style="font-weight:bold;color:var(--danger-color);">-₹<?php echo number_format($row['amount'],2); ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
                <div style="margin-top:10px;text-align:center;">
                    <a href="manage_expenses.php" style="color:var(--primary-color); font-weight:bold;">View All</a>
                </div>
            </div>

            <!-- AI Assistant -->
            <div class="card" style="margin-top:20px;">
                <h3>🤖 AI Assistant</h3>
                <div id="chat-box"><div style="color:#888;">Start chatting with your AI Assistant...</div></div>
                <input type="text" id="ai-input" placeholder="Type your message...">
                <button onclick="sendMessage()" class="btn btn-primary" style="width:100%; margin-top:10px;">Send</button>
            </div>
        </div>

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
const categories = <?php echo json_encode($categories); ?>;
const spentData = <?php echo json_encode($cat_totals); ?>;
const categoryLimits = <?php echo json_encode($cat_limits); ?>;

// Doughnut Chart
const ctx = document.getElementById('expenseChart').getContext('2d');
const expenseChart = new Chart(ctx,{
    type:'doughnut',
    data:{ labels: categories, datasets:[{ data: spentData, backgroundColor:['#FF5733','#33FF57','#3357FF','#F333FF','#33FFF5','#FF33A8','#808080'] }]},
    options:{ responsive:true, maintainAspectRatio:false }
});

// Bar Chart: Allowance vs Spent
const ctxAllowance = document.getElementById('allowanceChart').getContext('2d');
function getAdjustedAllowance(type){
    let factor=1;
    if(type==="weekly") factor=1/4;
    if(type==="daily") factor=1/30;
    return categoryLimits.map(val => val*factor);
}
let allowanceChart = new Chart(ctxAllowance,{
    type:'bar',
    data:{
        labels: categories,
        datasets:[
            {label:'Allowance', data:getAdjustedAllowance('monthly'), backgroundColor:'#33B5FF'},
            {label:'Spent', data:spentData, backgroundColor:'#FF3333'}
        ]
    },
    options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'top'}}, scales:{y:{beginAtZero:true}} }
});
document.getElementById('allowance-type').addEventListener('change', function(){
    const type=this.value;
    allowanceChart.data.datasets[0].data=getAdjustedAllowance(type);
    allowanceChart.update();
});

// AI Chat
function sendMessage(){
    const input=document.getElementById("ai-input");
    const chatBox=document.getElementById("chat-box");
    const message=input.value.trim();
    if(message==="") return;
    const userDiv=document.createElement("div"); userDiv.style.textAlign="right"; userDiv.style.margin="5px 0";
    userDiv.innerHTML=`<strong>You:</strong> ${message}`; chatBox.appendChild(userDiv); chatBox.scrollTop=chatBox.scrollHeight;
    input.value="";
}
</script>

</body>
</html>
