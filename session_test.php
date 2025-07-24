<?php
// session_test.php
session_start();

echo "<h1>Oturum Test Sayfası</h1>";
echo "<p>Bu sayfadaki oturum (SESSION) değişkenlerinin içeriği:</p>";
echo "<hr>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>