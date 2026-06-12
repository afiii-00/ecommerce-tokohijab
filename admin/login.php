<?php
session_start();
include '../koneksi.php';

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = MD5($_POST['password']);

    $query = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username' AND password='$password'");

    if(mysqli_num_rows($query) > 0){
        $_SESSION['admin'] = $username;
        header('Location: dashboard.php');
    } else {
        echo "Login gagal";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login Admin</title>
<style>

    body{
font-family:Arial;
background:#fff5f7;
display:flex;
justify-content:center;
align-items:center;
height:100vh;
}

.box{
background:white;
padding:30px;
border-radius:15px;
width:300px;
box-shadow:0 0 10px rgba(0,0,0,0.1);
}

input{
width:100%;
padding:10px;
margin:10px 0;
}

button{
width:100%;
padding:10px;
background:#b04d6d;
color:white;
border:none;
border-radius:10px;
}
</style>
</head>
<body>
<div class="box">
<h2>Login Admin</h2>
<form method="POST">
<input type="text" name="username" placeholder="Username">
<input type="password" name="password" placeholder="Password">
<button name="login">Login</button>
</form>
</div>
</body>
</html>