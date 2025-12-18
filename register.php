<?php
require('db.php');
session_start();

$msg = "";
$success = false;

if (isset($_POST['username'])) {

    $username = mysqli_real_escape_string($con, $_POST['username']);
    $email    = mysqli_real_escape_string($con, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($con, "SELECT id FROM users WHERE username='$username' OR email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $msg = "Username or email already exists.";
    } else {
        $query = "INSERT INTO users (username, email, password)
                  VALUES ('$username','$email','$password')";
        if (mysqli_query($con, $query)) {
            $success = true;
        } else {
            $msg = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css?family=Montserrat:400,800" rel="stylesheet">

<style>
@import url('https://fonts.googleapis.com/css?family=Montserrat:400,800');
*{box-sizing:border-box}

/* ---------- BACKGROUND ---------- */
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

    background-size:400% 400%, cover;
    background-position:center;
    background-repeat:no-repeat;
    background-attachment:fixed;
    animation:gradientBG 15s ease infinite;
}

@keyframes gradientBG {
    0%   { background-position:0% 50%, center; }
    50%  { background-position:100% 50%, center; }
    100% { background-position:0% 50%, center; }
}

/* ---------- UI ---------- */
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

.sign-up-container{
	left:0;
	width:50%;
	transform:translateX(100%);
	z-index:5;
}

.overlay-container{
	position:absolute;
	top:0;
	left:50%;
	width:50%;
	height:100%;
	overflow:hidden;
	z-index:100;
	transform:translateX(-100%);
}

.overlay{
	background:linear-gradient(to right,#0A1A2F,#102A43);
	color:#fff;
	position:relative;
	left:-100%;
	height:100%;
	width:200%;
	transform:translateX(50%);
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

.overlay-left{
	transform:translateX(0);
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

/* ---------- SUCCESS MESSAGE ---------- */
.success-message{
    position:fixed;
    inset:0;
    background:rgba(255,255,255,0.85);
    backdrop-filter:blur(10px);
    display:flex;
    align-items:center;
    justify-content:center;
    flex-direction:column;
    z-index:9999;
    color:#0A1A2F;
    font-size:18px;
}
</style>
</head>

<body>

<?php if ($success): ?>
    <div class="success-message">
        <strong>Registration successful.</strong>
        <p>Redirecting to login page...</p>
    </div>
    <script>
        setTimeout(() => {
            window.location.href = "login.php";
        }, 2000);
    </script>
</body>
</html>
<?php exit(); endif; ?>

<div class="container right-panel-active">

	<div class="form-container sign-up-container">
		<form method="post">
			<h1>Create Account</h1>

			<?php if($msg): ?>
				<div class="error"><?php echo $msg; ?></div>
			<?php endif; ?>

			<div class="social-container">
				<a href="#"><i class="fab fa-facebook-f"></i></a>
				<a href="#"><i class="fab fa-google-plus-g"></i></a>
				<a href="#"><i class="fab fa-linkedin-in"></i></a>
			</div>

			<span>or use your email for registration</span>
			<input type="text" name="username" placeholder="Username" required>
			<input type="email" name="email" placeholder="Email" required>
			<input type="password" name="password" placeholder="Password" required>
			<button type="submit">Sign Up</button>
		</form>
	</div>

	<div class="overlay-container">
		<div class="overlay">
			<div class="overlay-panel overlay-left">
				<h1>Welcome Back!</h1>
				<p>To keep connected with us please login</p>
				<a href="login.php">
					<button class="ghost">Sign In</button>
				</a>
			</div>
		</div>
	</div>

</div>

</body>
</html>