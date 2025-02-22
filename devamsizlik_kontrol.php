<?php
include 'db_connection.php';

$sql = "SELECT ogrenci_id, 
               CASE 
                   WHEN devamsizlik_sayisi > 5 THEN 'Devamsızlıktan kaldınız'
                   ELSE 'Devamsızlık durumu uygun'
               END AS devamsizlik_durumu
        FROM devamsizliklar";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Öğrenci ID: " . $row["ogrenci_id"] . " - " . $row["devamsizlik_durumu"] . "<br>";
    }
} else {
    echo "Sonuç bulunamadı.";
}

$conn->close();
?>
