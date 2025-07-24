<?php if (!isset($studentCount)) $studentCount = 0; ?>
<?php if (!isset($teacherCount)) $teacherCount = 0; ?>
<?php if (!isset($assignmentCount)) $assignmentCount = 0; ?>
<?php if (!isset($pendingAssignmentCount)) $pendingAssignmentCount = 0; ?>

<h1>Admin Paneline Hoşgeldiniz!</h1>

<div class="card bg-light p-3 m-2">
  <h4>🎓 Öğrenci Sayısı:</h4>
  <p><?= $studentCount ?></p>
</div>

<div class="card bg-light p-3 m-2">
  <h4>👨‍🏫 Öğretmen Sayısı:</h4>
  <p><?= $teacherCount ?></p>
</div>

<div class="card bg-light p-3 m-2">
  <h4>📂 Toplam Ödev Sayısı:</h4>
  <p><?= $assignmentCount ?></p>
</div>

<div class="card bg-light p-3 m-2">
  <h4>⏳ Bekleyen Ödev Sayısı:</h4>
  <p><?= $pendingAssignmentCount ?></p>
</div>
