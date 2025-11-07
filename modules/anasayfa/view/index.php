<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Ana Sayfa</h1>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    
    <!-- ADMIN DASHBOARD -->
    <?php if ($role === 'admin'): ?>
        
        <!-- İstatistik Kartları -->
        <div class="row">
            <!-- Toplam Öğrenci -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $stats['total_students'] ?></h3>
                        <p>Toplam Öğrenci</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <a href="index.php?module=students&action=list" class="small-box-footer">
                        Detaylar <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <!-- Toplam Öğretmen -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $stats['total_teachers'] ?></h3>
                        <p>Toplam Öğretmen</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <a href="index.php?module=teachers&action=list" class="small-box-footer">
                        Detaylar <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <!-- Toplam Ders -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $stats['total_courses'] ?></h3>
                        <p>Toplam Ders</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <a href="index.php?module=courses&action=list" class="small-box-footer">
                        Detaylar <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <!-- Toplam Veli -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= $stats['total_parents'] ?></h3>
                        <p>Toplam Veli</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Detaylar <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Hızlı Aksiyonlar -->
        <div class="row">
            <div class="col-md-3">
                <div class="card card-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-user-plus fa-3x mb-3"></i>
                        <h5>Öğrenci Ekle</h5>
                        <a href="index.php?module=students&action=create" class="btn btn-primary btn-block mt-3">
                            <i class="fas fa-plus"></i> Yeni Öğrenci
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card card-success">
                    <div class="card-body text-center">
                        <i class="fas fa-chalkboard-teacher fa-3x mb-3"></i>
                        <h5>Öğretmen Ekle</h5>
                        <a href="index.php?module=teachers&action=create" class="btn btn-success btn-block mt-3">
                            <i class="fas fa-plus"></i> Yeni Öğretmen
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card card-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-book-open fa-3x mb-3"></i>
                        <h5>Ders Ekle</h5>
                        <a href="index.php?module=courses&action=create" class="btn btn-warning btn-block mt-3">
                            <i class="fas fa-plus"></i> Yeni Ders
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card card-info">
                    <div class="card-body text-center">
                        <i class="fas fa-bullhorn fa-3x mb-3"></i>
                        <h5>Duyuru Yayınla</h5>
                        <a href="index.php?module=announcements&action=create" class="btn btn-info btn-block mt-3">
                            <i class="fas fa-plus"></i> Yeni Duyuru
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
    <?php endif; ?>
    
    
    <!-- ÖĞRETMEN DASHBOARD -->
    <?php if ($role === 'teacher'): ?>
        
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $stats['my_courses'] ?></h3>
                        <p>Derslerim</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <a href="index.php?module=courses&action=mycourses" class="small-box-footer">
                        Detaylar <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-4 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $stats['total_students'] ?></h3>
                        <p>Toplam Öğrenci</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <a href="index.php?module=students&action=list" class="small-box-footer">
                        Detaylar <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-clipboard-check fa-3x mb-3"></i>
                        <h5>Yoklama Al</h5>
                        <a href="index.php?module=attendance&action=take" class="btn btn-primary btn-block mt-3">
                            Yoklama Sayfası
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card card-success">
                    <div class="card-body text-center">
                        <i class="fas fa-edit fa-3x mb-3"></i>
                        <h5>Not Gir</h5>
                        <a href="index.php?module=grades&action=enter" class="btn btn-success btn-block mt-3">
                            Not Girişi
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
    <?php endif; ?>
    
    
    <!-- ÖĞRENCİ DASHBOARD -->
    <?php if ($role === 'student'): ?>
        
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $stats['my_courses'] ?></h3>
                        <p>Kayıtlı Derslerim</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <a href="index.php?module=courses&action=mycourses" class="small-box-footer">
                        Detaylar <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Hoş Geldiniz!</h3>
            </div>
            <div class="card-body">
                <p>Buradan derslerinizi, notlarınızı ve devamsızlık durumunuzu takip edebilirsiniz.</p>
            </div>
        </div>
        
    <?php endif; ?>
    
    
    <!-- VELİ DASHBOARD -->
    <?php if ($role === 'parent'): ?>
        
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $stats['my_children'] ?></h3>
                        <p>Çocuklarım</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Detaylar <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Hoş Geldiniz!</h3>
            </div>
            <div class="card-body">
                <p>Buradan çocuğunuzun/çocuklarınızın ders programı, notları ve devamsızlık durumunu takip edebilirsiniz.</p>
            </div>
        </div>
        
    <?php endif; ?>
    
  </div>
</section>