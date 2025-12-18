<?php
require('db.php');
session_start();

$msg = "";

if (isset($_POST['username'])) {
    $username = mysqli_real_escape_string($con, stripslashes($_POST['username']));
    $password = mysqli_real_escape_string($con, stripslashes($_POST['password']));

    $query  = "SELECT * FROM users WHERE username='$username' LIMIT 1";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_id']  = $row['id'];

            header("Location: home.php");
            exit();
        } else {
            $msg = "Incorrect password.";
        }
    } else {
        $msg = "Username not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">

<style>
@import url('https://fonts.googleapis.com/css?family=Montserrat:400,800');
*{box-sizing:border-box}

/* ---------- BACKGROUND DESIGN ---------- */
body{
    margin:0;
    font-family:'Montserrat',sans-serif;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;

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

@keyframes gradientBG {
    0%   { background-position: 0% 50%, center; }
    50%  { background-position: 100% 50%, center; }
    100% { background-position: 0% 50%, center; }
}

/* ---------- UI STYLES ---------- */
h1{font-weight:bold;margin:0}
p{font-size:14px;line-height:20px;margin:20px 0 30px}
span{font-size:12px}

button{
	border-radius:20px;
	border:1px solid #0A1A2F;
	background:#0A1A2F;
	color:#fff;
	font-size:12px;
	font-weight:bold;
	padding:12px 45px;
	letter-spacing:1px;
	text-transform:uppercase;
	cursor:pointer;
}

button.ghost{
	background:transparent;
	border-color:#fff;
}

form{
	background:#fff;
	display:flex;
	align-items:center;
	justify-content:center;
	flex-direction:column;
	padding:0 50px;
	height:100%;
	text-align:center;
}

input{
	background:#eee;
	border:none;
	padding:12px 15px;
	margin:8px 0;
	width:100%;
}

.container{
	background:#fff;
	border-radius:10px;
	box-shadow:0 14px 28px rgba(0,0,0,0.25);
	position:relative;
	overflow:hidden;
	width:768px;
	max-width:100%;
	min-height:480px;
}

.form-container{
	position:absolute;
	top:0;
	height:100%;
	transition:all 0.6s ease-in-out;
}

.sign-in-container{
	left:0;
	width:50%;
	z-index:2;
}

.overlay-container{
	position:absolute;
	top:0;
	left:50%;
	width:50%;
	height:100%;
	overflow:hidden;
	z-index:100;
}

.overlay{
	background:linear-gradient(to right,#0A1A2F,#102A43);
	color:#fff;
	position:relative;
	left:-100%;
	height:100%;
	width:200%;
}

.overlay-panel{
	position:absolute;
	display:flex;
	align-items:center;
	justify-content:center;
	flex-direction:column;
	padding:0 40px;
	text-align:center;
	top:0;
	height:100%;
	width:50%;
}

.overlay-right{
	right:0;
}

.social-container a{
	border:1px solid #ddd;
	border-radius:50%;
	display:inline-flex;
	justify-content:center;
	align-items:center;
	margin:0 5px;
	height:40px;
	width:40px;
}

.error{
	color:red;
	font-size:13px;
	margin-bottom:10px;
}
</style>
</head>

<body>

<div class="container">

	<div class="form-container sign-in-container">
		<form method="post">
			<h1>Sign in</h1>

			<?php if($msg): ?>
				<div class="error"><?php echo $msg; ?></div>
			<?php endif; ?>

			<div class="social-container">
				<a href="#"><i class="fab fa-facebook-f"></i></a>
				<a href="#"><i class="fab fa-google-plus-g"></i></a>
				<a href="#"><i class="fab fa-linkedin-in"></i></a>
			</div>

			<span>or use your account</span>
			<input type="text" name="username" placeholder="Username" required>
			<input type="password" name="password" placeholder="Password" required>
			<button type="submit">Sign In</button>
		</form>
	</div>

	<div class="overlay-container">
		<div class="overlay">
			<div class="overlay-panel overlay-right">
				<h1>Hello, Friend!</h1>
				<p>Enter your personal details</p>
				<a href="register.php">
					<button class="ghost">Sign Up</button>
				</a>
			</div>
		</div>
	</div>

</div>

</body>
</html>