/*!
 * Sidebar Accordion – jQuery 3.x
 * Mevcut dinamik menüyü bozmadan <li> içinde <ul> olanları akordeona çevirir.
 * Font ikon bağımlılığı YOK; caret olarak Unicode karakterleri kullanır.
 */
(function ($) {
  $(function () {
    // Kök menüyü bul (soldaki sidebar -> nav -> ilk ul)
    var $root = $(".sidebar nav ul").first();
    if (!$root.length) return; // beklenen yapı yoksa sessizce çık

    // 1) UL içeren LI'leri işaretle, caret ekle, alt UL'leri kapat
    $root.find("li").each(function () {
      var $li = $(this);
      var $sub = $li.children("ul");
      if ($sub.length) {
        $li.addClass("has-children");

        var $a = $li.children("a").first();
        if ($a.length) {
          // Caret yoksa ekle (ok alanı)
          if (!$a.find(".caret").length) {
            $a.append('<span class="caret" aria-hidden="true"></span>');
          }
          // Erişilebilirlik
          if (!$a.attr("aria-expanded")) {
            $a.attr("aria-expanded", "false");
          }
        }

        // Başlangıçta kapalı
        $sub.hide();
      }
    });

    // 2) .active yolu açılsın (bazı temalarda aktif link class'ı veriliyor)
    $root.find("a.active").each(function () {
      $(this)
        .parents("li.has-children")
        .each(function () {
          var $pli = $(this);
          $pli.addClass("open");
          $pli.children("ul").show();
          $pli.children("a").attr("aria-expanded", "true");
        });
    });

    // 3) Tıklayınca aç/kapat – tüm satır tıklanabilir
    $root.on("click", "li.has-children > a", function (e) {
      // Ebeveyn entry'lerde gezinmeyi engelle (sadece toggle yapsın)
      // Eğer gerçekten gezinmek istersen, alt maddeleri kullan.
      e.preventDefault();

      var $a = $(this);
      var $li = $a.parent();
      var $sub = $li.children("ul");
      var wasOpen = $li.hasClass("open");

      // Aynı seviyedeki diğer açık menüleri kapat (akordeon hissi)
      $li
        .siblings(".has-children.open")
        .removeClass("open")
        .children("ul")
        .slideUp(150)
        .end()
        .children("a")
        .attr("aria-expanded", "false");

      // Hedefi toggle et
      if (wasOpen) {
        $li.removeClass("open");
        $sub.slideUp(150);
        $a.attr("aria-expanded", "false");
      } else {
        $li.addClass("open");
        $sub.slideDown(150);
        $a.attr("aria-expanded", "true");
      }
    });
  });
})(jQuery);
