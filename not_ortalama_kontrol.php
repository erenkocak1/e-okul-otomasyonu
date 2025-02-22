<?php
include 'db_connection.php';

$sql = "SELECT ogrenci_id, 
               AVG(notu) AS not_ortalama,
               CASE 
                   WHEN AVG(notu) < 50 THEN 'Kaldınız'
                   ELSE 'Geçtiniz'
               END AS not_durumu
        FROM notlar
        GROUP BY ogrenci_id";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Öğrenci ID: " . $row["ogrenci_id"] . " - Not Ortalaması: " . $row["not_ortalama"] . " - " . $row["not_durumu"] . "<br>";
    }
} else {
    echo "Sonuç bulunamadı.";
}

$conn->close();
?>
