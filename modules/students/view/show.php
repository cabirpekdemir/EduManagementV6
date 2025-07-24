<section class="content">
    <div class="container-fluid">
        <div class="card card-info mt-4">
            <div class="card-header">
                <h3 class="card-title">Öğrenci Tanıtım Kartı</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Ad Soyad</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($student['name']) ?></dd>

                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($student['email']) ?></dd>

                    <dt class="col-sm-3">Sınıf</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($student['sinif']) ?></dd>

                    <dt class="col-sm-3">Okul</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($student['okul']) ?></dd>

                    <dt class="col-sm-3">Telefon</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($student['phone']) ?></dd>

                    <dt class="col-sm-3">Adres</dt>
                    <dd class="col-sm-9"><?= nl2br(htmlspecialchars($student['address'])) ?></dd>

                    <dt class="col-sm-3">Cinsiyet</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($student['gender']) ?></dd>

                    <dt class="col-sm-3">Doğum Yeri</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($student['birth_place']) ?></dd>

                    <dt class="col-sm-3">Doğum Tarihi</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($student['birth_date']) ?></dd>

                    <dt class="col-sm-3">Anne Adı</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($student['mother_name']) ?></dd>

                    <dt class="col-sm-3">Baba Adı</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($student['father_name']) ?></dd>

                    <dt class="col-sm-3">Veli Adı</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($student['guardian_name']) ?></dd>

                    <dt class="col-sm-3">Öğrenci Notu</dt>
                    <dd class="col-sm-9"><?= nl2br(htmlspecialchars($student['student_note'])) ?></dd>
                </dl>
            </div>
            <div class="card-footer">
                <a href="index.php?module=students&action=list" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri Dön
                </a>
            </div>
        </div>
    </div>
</section>
