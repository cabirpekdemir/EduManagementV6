<?php
// Buraya yeni ve unutmayacağınız bir şifre girin
$yeniSifre = 'Lepistes1';

// Şifreyi PHP'nin en güvenli yöntemiyle hash'liyoruz
$hashlenmisSifre = password_hash($yeniSifre, PASSWORD_DEFAULT);

echo "<h3>Yeni Şifre Hash'iniz Hazır!</h3>";
echo "<p><b>Seçtiğiniz Şifre:</b> " . htmlspecialchars($yeniSifre) . "</p>";
echo "<p><b>Veritabanına Kaydedilecek Hash Değeri (aşağıdakini kopyalayın):</b></p>";
echo "<textarea rows='3' cols='70' readonly>" . htmlspecialchars($hashlenmisSifre) . "</textarea>";
?>