<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ogrenci') {
    header("Location: login.php");
    exit;
}

$ogrenci_id = $_SESSION['user_id'];

$query = $conn->prepare("
    SELECT 
        dersler.ders_adi, 
        COALESCE(notlar.sinav1, '0') AS sinav1, 
        COALESCE(notlar.sinav2, '0') AS sinav2, 
        COALESCE(notlar.not_degeri, '0') AS not_degeri, 
        COALESCE(devamsizlik.devamsizlik_saat, '0') AS devamsizlik_saat
    FROM notlar
    JOIN dersler ON notlar.ders_id = dersler.ders_id
    LEFT JOIN devamsizlik ON notlar.ogrenci_id = devamsizlik.ogrenci_id 
                          AND notlar.ders_id = devamsizlik.ders_id
    WHERE notlar.ogrenci_id = ?
");
$query->bind_param("i", $ogrenci_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğrenci Sayfası</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f9;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        .failed {
            color: red;
            font-weight: bold;
        }
        .passed {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Hoşgeldiniz, Öğrenci</h1>

    <table>
        <thead>
            <tr>
                <th>Ders</th>
                <th>1. Sınav</th>
                <th>2. Sınav</th>
                <th>Ortalama</th>
                <th>Durum</th>
                <th>Devamsızlık (Saat)</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['ders_adi']) ?></td>
                    <td><?= $row['sinav1'] ?></td>
                    <td><?= $row['sinav2'] ?></td>
                    <td><?= $row['not_degeri'] ?></td>
                    <td>
                        <?php
                        $not_degeri = (float) $row['not_degeri'];
                        $devamsizlik = (int) $row['devamsizlik_saat'];

                        if ($not_degeri < 50 || $devamsizlik > 5) {
                            echo "<span class='failed'>Kaldı</span>";
                        } else {
                            echo "<span class='passed'>Geçti</span>";
                        }
                        ?>
                    </td>
                    <td><?= $devamsizlik > 0 ? $devamsizlik : 'Bilgi Yok' ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
