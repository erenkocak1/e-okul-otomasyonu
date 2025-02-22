<?php 
session_start();
include 'db_connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(0);
ini_set('display_errors', 0);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $tc_kimlik_no = $_POST['username']; 
    $sifre = $_POST['password'];       

   
    $query = $conn->prepare("SELECT * FROM kullanicilar WHERE tc_kimlik_no = ? AND sifre = ?");
    $query->bind_param("ss", $tc_kimlik_no, $sifre);
    $query->execute();
    $result = $query->get_result();
    
    if ($result->num_rows > 0) {
        
        $user = $result->fetch_assoc();
        
        $_SESSION['user_id'] = $user['kullanici_id'];
        $_SESSION['role'] = $user['rol']; 

        
        if ($user['rol'] == 'mudur') {
            header("Location: mudur.php");
            exit;
        } elseif ($user['rol'] == 'ogretmen') {
            header("Location: ogretmen.php");
            exit;
        } elseif ($user['rol'] == 'ogrenci') {
            header("Location: ogrenci.php");
            exit;
        } else {
            echo "Bilinmeyen bir rol tespit edildi!";
        }
    } else {
        
        $error = "TC Kimlik Numarası veya Şifre hatalı!";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Okul Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color:rgba(19, 63, 51, 0.84);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: rgb(255, 255, 255);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            border-color: #007bff;
            outline: none;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>E-Okul Giriş</h2>
        <form action="" method="POST">
            <div class="form-group">
                <label for="tc_kimlik_no">TC Kimlik Numarası:</label>
                <input type="text" id="tc_kimlik_no" name="tc_kimlik_no" placeholder="TC Kimlik Numaranızı girin" required>
            </div>
            <div class="form-group">
                <label for="sifre">Şifre:</label>
                <input type="password" id="sifre" name="sifre" placeholder="Şifrenizi girin" required>
            </div>
            <button type="submit" class="btn">Giriş Yap</button>
            <?php
            session_start();

            if (!isset($conn)) {
                include 'db_connect.php';
            }

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $tc_kimlik_no = trim($_POST['tc_kimlik_no']);
                $sifre = trim($_POST['sifre']);

                
                if ($stmt = $conn->prepare("SELECT * FROM kullanicilar WHERE tc_kimlik_no = ? AND sifre = ?")) {
                    $stmt->bind_param("ss", $tc_kimlik_no, $sifre);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        
                        $user = $result->fetch_assoc();

                        $_SESSION['user_id'] = $user['kullanici_id'];
                        $_SESSION['role'] = $user['rol']; 
                        
                        switch ($user['rol']) {
                            case 'mudur':
                                header("Location: mudur.php");
                                exit;
                            case 'ogretmen':
                                header("Location: ogretmen.php");
                                exit;
                            case 'ogrenci':
                                header("Location: ogrenci.php");
                                exit;
                            default:
                                echo "<p class='error'>Bilinmeyen bir rol tespit edildi!</p>";
                        }
                    } else {
                        
                        echo "<p class='error'>TC Kimlik Numarası veya Şifre hatalı!</p>";
                    }
                    $stmt->close();
                } else {
                    echo "<p class='error'>Veritabanı sorgusu başarısız oldu.</p>";
                }
            }
            ?>
        </form>
    </div>
</body>
</html>
