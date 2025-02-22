<?php
session_start();
include 'db_connect.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mudur') {
    header("Location: login.php");
    exit;
}


if (isset($_POST['ogrenci_ekle'])) {
    $ogrenci_adi = $_POST['ogrenci_adi'];
    $ogrenci_soyadi = $_POST['ogrenci_soyadi'];
    $sinif = $_POST['sinif']; 
    $tc_kimlik_no = $_POST['tc_kimlik_no']; 

    
    $random_password = bin2hex(random_bytes(4)); 
    
    $query_kullanici_ekle = $conn->prepare("INSERT INTO kullanicilar (tc_kimlik_no, sifre, rol) VALUES (?, ?, 'ogrenci')");
    $query_kullanici_ekle->bind_param("ss", $tc_kimlik_no, $random_password);

    if ($query_kullanici_ekle->execute()) {
        
        $kullanici_id = $conn->insert_id;  
        
        $query_ogrenci_ekle = $conn->prepare("INSERT INTO ogrenciler (ogrenci_id, ogrenci_adi, ogrenci_soyadi, sinif) VALUES (?, ?, ?, ?)");
        $query_ogrenci_ekle->bind_param("isss", $kullanici_id, $ogrenci_adi, $ogrenci_soyadi, $sinif);

        if ($query_ogrenci_ekle->execute()) {
            echo "Öğrenci başarıyla eklendi!";
        } else {
            echo "Öğrenci eklenirken bir hata oluştu.";
        }
    } else {
        echo "Kullanıcı eklenirken bir hata oluştu.";
    }
}


if (isset($_POST['ogrenci_sil'])) {
    $ogrenci_id = $_POST['ogrenci_id'];

    
    $query_ogrenci_sil = $conn->prepare("DELETE FROM ogrenciler WHERE ogrenci_id = ?");
    $query_ogrenci_sil->bind_param("i", $ogrenci_id);

    if ($query_ogrenci_sil->execute()) {
        
        $query_kullanici_sil = $conn->prepare("DELETE FROM kullanicilar WHERE kullanici_id = ?");
        $query_kullanici_sil->bind_param("i", $ogrenci_id);  

        if ($query_kullanici_sil->execute()) {
            echo "Öğrenci ve kullanıcı başarıyla silindi!";
        } else {
            echo "Kullanıcı silinirken bir hata oluştu.";
        }
    } else {
        echo "Öğrenci silinirken bir hata oluştu.";
    }
}


if (isset($_POST['ders_ekle'])) {
    $ders_adi = $_POST['ders_adi'];
    $ogretmen_id = $_POST['ogretmen_sec']; 

    $query_ders_ekle = $conn->prepare("INSERT INTO dersler (ders_adi, ogretmen_id) VALUES (?, ?)");
    $query_ders_ekle->bind_param("si", $ders_adi, $ogretmen_id);

    if ($query_ders_ekle->execute()) {
        echo "Ders başarıyla eklendi!";
    } else {
        echo "Ders eklenirken bir hata oluştu.";
    }
}

if (isset($_POST['ders_sil'])) {
    $ders_id = $_POST['ders_id']; 

    
    $query_ders_sil = $conn->prepare("DELETE FROM dersler WHERE ders_id = ?");
    $query_ders_sil->bind_param("i", $ders_id);

    if ($query_ders_sil->execute()) {
        echo "Ders başarıyla silindi!";
    } else {
        echo "Ders silinirken bir hata oluştu.";
    }
}


if (isset($_POST['ogretmen_ekle'])) {
    $ogretmen_adi = $_POST['ogretmen_adi'];
    $ogretmen_soyadi = $_POST['ogretmen_soyadi'];
    $tc_kimlik_no = $_POST['tc_kimlik_no']; 

    
    $random_password = bin2hex(random_bytes(4)); 

    
    $query_kullanici_ekle = $conn->prepare("INSERT INTO kullanicilar (tc_kimlik_no, sifre, rol) VALUES (?, ?, 'ogretmen')");
    $query_kullanici_ekle->bind_param("ss", $tc_kimlik_no, $random_password);

    if ($query_kullanici_ekle->execute()) {
        
        $kullanici_id = $conn->insert_id; 
        
        $query_ogretmen_ekle = $conn->prepare("INSERT INTO ogretmenler (ogretmen_id, ogretmen_adi, ogretmen_soyadi) VALUES (?, ?, ?)");
        $query_ogretmen_ekle->bind_param("iss", $kullanici_id, $ogretmen_adi, $ogretmen_soyadi);

        if ($query_ogretmen_ekle->execute()) {
            echo "Öğretmen başarıyla eklendi!";
        } else {
            echo "Öğretmen eklenirken bir hata oluştu.";
        }
    } else {
        echo "Kullanıcı eklenirken bir hata oluştu. TC Kimlik Numarası zaten kayıtlı olabilir.";
    }
}




if (isset($_POST['ogretmen_sil'])) {
    $ogretmen_id = $_POST['ogretmen_id'];

    
    $query_ogretmen_sil = $conn->prepare("DELETE FROM ogretmenler WHERE ogretmen_id = ?");
    $query_ogretmen_sil->bind_param("i", $ogretmen_id);

    if ($query_ogretmen_sil->execute()) {
        
        $query_kullanici_sil = $conn->prepare("DELETE FROM kullanicilar WHERE kullanici_id = ?");
        $query_kullanici_sil->bind_param("i", $ogretmen_id);

        if ($query_kullanici_sil->execute()) {
            echo "Öğretmen başarıyla silindi!";
        } else {
            echo "Kullanıcı kaydı silinirken bir hata oluştu.";
        }
    } else {
        echo "Öğretmen silinirken bir hata oluştu.";
    }
}

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoşgeldiniz, Müdür</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #81807d; /* Hafif gri arka plan */
    color: #333;
}
header {
    background-color: #2c3e50; /* Lacivert arka plan */
    color: white;
    padding: 1rem;
    text-align: center;
    font-size: 1.5rem;
}
.container {
    max-width: 800px;
    margin: 2rem auto;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    padding: 2rem;
}
h1, h3, h4 {
    color:#a7a7a7; /* Lacivert yazı rengi */
}
form {
    margin-bottom: 2rem;
}
label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: bold;
    color: #34495e; /* Koyu gri yazı */
}
input, select, button {
    width: 100%;
    padding: 0.8rem;
    margin-bottom: 1rem;
    border: 1px solid #bdc3c7; /* Açık gri çerçeve */
    border-radius: 4px;
}
button {
    background-color: #34495e; /* Koyu gri buton */
    color: white;
    border: none;
    cursor: pointer;
    font-weight: bold;
}
button:hover {
    background-color: #2c3e50; /* Lacivert hover rengi */
}
.form-group {
    margin-bottom: 1.5rem;
}

    </style>
</head>
<body>
    <header>
        <h1>Hoşgeldiniz, Müdür</h1>
    </header>

    <div class="container">
        <h3>Ders Ekle</h3>
        <form method="POST" action="mudur.php">
            <div class="form-group">
                <label for="ders_adi">Ders Adı:</label>
                <input type="text" name="ders_adi" id="ders_adi" required>
            </div>

            <div class="form-group">
                <label for="ogretmen_sec">Öğretmen Seçin:</label>
                <select name="ogretmen_sec" id="ogretmen_sec" required>
                    <?php
                    $query_ogretmenler = $conn->prepare("SELECT kullanici_id, tc_kimlik_no FROM kullanicilar WHERE rol = 'ogretmen'");
                    $query_ogretmenler->execute();
                    $ogretmenler = $query_ogretmenler->get_result();
                    while ($ogretmen = $ogretmenler->fetch_assoc()) {
                        echo "<option value='{$ogretmen['kullanici_id']}'>TC: {$ogretmen['tc_kimlik_no']}</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" name="ders_ekle">Ders Ekle</button>
        </form>

        <h3>Öğrenci Ekle</h3>
        <form method="POST" action="mudur.php">
            <div class="form-group">
                <label for="ogrenci_adi">Öğrenci Adı:</label>
                <input type="text" name="ogrenci_adi" id="ogrenci_adi" required>
            </div>

            <div class="form-group">
                <label for="ogrenci_soyadi">Öğrenci Soyadı:</label>
                <input type="text" name="ogrenci_soyadi" id="ogrenci_soyadi" required>
            </div>

            <div class="form-group">
                <label for="tc_kimlik_no">TC Kimlik Numarası:</label>
                <input type="text" name="tc_kimlik_no" id="tc_kimlik_no" required>
            </div>

            <div class="form-group">
                <label for="sinif">Sınıf:</label>
                <select name="sinif" id="sinif" required>
                    <option value="10A">10 A</option>
                    <option value="10B">10 B</option>
                    <option value="10C">10 C</option>
                    <option value="10D">10 D</option>
                    <option value="11A">11 A</option>
                    <option value="11B">11 B</option>
                    <option value="11C">11 C</option>
                    <option value="11D">11 D</option>
                    <option value="12A">12 A</option>
                    <option value="12B">12 B</option>
                    <option value="12C">12 C</option>
                    <option value="12D">12 D</option>
                </select>
            </div>

            <button type="submit" name="ogrenci_ekle">Öğrenci Ekle</button>
        </form>

        <h3>Öğretmen Ekle</h3>
        <form method="POST" action="mudur.php">
            <div class="form-group">
                <label for="ogretmen_adi">Öğretmen Adı:</label>
                <input type="text" name="ogretmen_adi" id="ogretmen_adi" required>
            </div>

            <div class="form-group">
                <label for="ogretmen_soyadi">Öğretmen Soyadı:</label>
                <input type="text" name="ogretmen_soyadi" id="ogretmen_soyadi" required>
            </div>

            <div class="form-group">
                <label for="tc_kimlik_no">TC Kimlik Numarası:</label>
                <input type="text" name="tc_kimlik_no" id="tc_kimlik_no" required>
            </div>

            <button type="submit" name="ogretmen_ekle">Öğretmen Ekle</button>
        </form>

        <h3>Silme İşlemleri</h3>

        <h4>Öğrenci Sil</h4>
        <form method="POST" action="mudur.php">
            <div class="form-group">
                <label for="ogrenci_id">Öğrenci Seçin:</label>
                <select name="ogrenci_id" id="ogrenci_id">
                    <?php
                    $query_ogrenciler = $conn->prepare("SELECT ogrenci_id, ogrenci_adi, ogrenci_soyadi FROM ogrenciler");
                    $query_ogrenciler->execute();
                    $ogrenciler = $query_ogrenciler->get_result();
                    while ($ogrenci = $ogrenciler->fetch_assoc()) {
                        echo "<option value='{$ogrenci['ogrenci_id']}'>Öğrenci: {$ogrenci['ogrenci_adi']} {$ogrenci['ogrenci_soyadi']}</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" name="ogrenci_sil">Öğrenci Sil</button>
        </form>

        <h4>Öğretmen Sil</h4>
        <form method="POST" action="mudur.php">
            <div class="form-group">
                <label for="ogretmen_id">Öğretmen Seçin:</label>
                <select name="ogretmen_id" id="ogretmen_id">
                    <?php
                    $query_ogretmenler = $conn->prepare("SELECT kullanici_id, tc_kimlik_no FROM kullanicilar WHERE rol = 'ogretmen'");
                    $query_ogretmenler->execute();
                    $ogretmenler = $query_ogretmenler->get_result();
                    while ($ogretmen = $ogretmenler->fetch_assoc()) {
                        echo "<option value='{$ogretmen['kullanici_id']}'>Öğretmen: {$ogretmen['tc_kimlik_no']}</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" name="ogretmen_sil">Öğretmen Sil</button>
        </form>

        <h4>Ders Sil</h4>
        <form method="POST" action="mudur.php">
            <div class="form-group">
                <label for="ders_id">Ders Seçin:</label>
                <select name="ders_id" id="ders_id">
                    <?php
                    $query_dersler = $conn->prepare("SELECT ders_id, ders_adi FROM dersler");
                    $query_dersler->execute();
                    $dersler = $query_dersler->get_result();
                    while ($ders = $dersler->fetch_assoc()) {
                        echo "<option value='{$ders['ders_id']}'>Ders: {$ders['ders_adi']}</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" name="ders_sil">Ders Sil</button>
        </form>
    </div>
</body>
</html>