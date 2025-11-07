<?php
/**
 * widget_edit_form.php
 * Bu dosya artık renderView() tarafından çağrılıyor.
 * Değişkenler ($widget, $config_arr, $db, $pageTitle)
 * doğrudan DashboardController'dan geliyor.
 */

// Formun gönderileceği URL
$form_action_url = 'index.php?module=dashboard&action=updateWidget&id=' . $widget['id'];
?>