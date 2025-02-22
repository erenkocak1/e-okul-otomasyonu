<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ogretmen') {
    header("Location: login.php");
    exit;
}

$ogretmen_id = $_SESSION['user_id'];

$query_dersler = $conn->prepare("SELECT ders_id, ders_adi FROM dersler WHERE ogretmen_id = ?");
$query_dersler->bind_param("i", $ogretmen_id);
$query_dersler->execute();
$dersler = $query_dersler->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notlar'])) {
    $ogrenci_id = $_POST['ogrenci_id'];
    $ders_id = $_POST['ders_id'];
    $sinav1 = $_POST['sinav1'];
    $sinav2 = $_POST['sinav2'];
    $devamsizlik = $_POST['devamsizlik_saat'];

    $ortalama = ($sinav1 + $sinav2) / 2;

    $stmt_notlar = $conn->prepare("
        INSERT INTO notlar (ogrenci_id, ders_id, sinav1, sinav2, not_degeri)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE sinav1 = VALUES(sinav1), sinav2 = VALUES(sinav2), not_degeri = VALUES(not_degeri)
    ");
    $stmt_notlar->bind_param("iiiii", $ogrenci_id, $ders_id, $sinav1, $sinav2, $ortalama);
    $stmt_notlar->execute();

    $stmt_devamsizlik = $conn->prepare("
        INSERT INTO devamsizlik (ogrenci_id, ders_id, devamsizlik_saat)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE devamsizlik_saat = VALUES(devamsizlik_saat)
    ");
    $stmt_devamsizlik->bind_param("iii", $ogrenci_id, $ders_id, $devamsizlik);
    $stmt_devamsizlik->execute();
}

$selected_ders_id = null;
$ogrenciler = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ders_id'])) {
    $selected_ders_id = $_POST['ders_id'];

    $query_ogrenciler = $conn->prepare("
        SELECT 
            o.ogrenci_id, 
            o.ogrenci_adi, 
            o.ogrenci_soyadi,
            n.sinav1, 
            n.sinav2, 
            n.not_degeri, 
            d.devamsizlik_saat
        FROM ogrenciler o
        LEFT JOIN notlar n ON o.ogrenci_id = n.ogrenci_id AND n.ders_id = ?
        LEFT JOIN devamsizlik d ON o.ogrenci_id = d.ogrenci_id AND d.ders_id = ?
    ");
    $query_ogrenciler->bind_param("ii", $selected_ders_id, $selected_ders_id);
    $query_ogrenciler->execute();
    $ogrenciler = $query_ogrenciler->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Öğretmen Sayfası</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
        }

        h1, h2 {
            text-align: center;
            color: #2c3e50;
        }

        form {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        select, button, input {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        button {
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
        }

        input[type="number"] {
            width: 60px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hoşgeldiniz, Öğretmen</h1>

        <form method="POST" action="">
            <label for="ders_id">Ders Seçin:</label>
            <select name="ders_id" id="ders_id" required>
                <option value="" disabled selected>Ders Seçiniz</option>
                <?php while ($ders = $dersler->fetch_assoc()): ?>
                    <option value="<?= $ders['ders_id'] ?>" <?= $selected_ders_id == $ders['ders_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ders['ders_adi']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Seç</button>
        </form>

        <?php if ($selected_ders_id): ?>
            <h2>Öğrenci Listesi</h2>
            <table>
                <tr>
                    <th>Adı Soyadı</th>
                    <th>Sınav 1</th>
                    <th>Sınav 2</th>
                    <th>Ortalama</th>
                    <th>Devamsızlık (Saat)</th>
                    <th>Kaydet</th>
                </tr>
                <?php while ($ogrenci = $ogrenciler->fetch_assoc()): ?>
                    <tr>
                        <form method="POST" action="">
                            <td><?= htmlspecialchars($ogrenci['ogrenci_adi'] . ' ' . $ogrenci['ogrenci_soyadi']) ?></td>
                            <td><input type="number" name="sinav1" value="<?= $ogrenci['sinav1'] ?? '' ?>" required></td>
                            <td><input type="number" name="sinav2" value="<?= $ogrenci['sinav2'] ?? '' ?>" required></td>
                            <td><?= $ogrenci['not_degeri'] ?? '-' ?></td>
                            <td><input type="number" name="devamsizlik_saat" value="<?= $ogrenci['devamsizlik_saat'] ?? '' ?>" required></td>
                            <td>
                                <input type="hidden" name="ogrenci_id" value="<?= $ogrenci['ogrenci_id'] ?>">
                                <input type="hidden" name="ders_id" value="<?= $selected_ders_id ?>">
                                <button type="submit" name="save_notlar">Kaydet</button>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
