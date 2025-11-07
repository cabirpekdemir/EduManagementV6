$(document).ready(function () {
    $(".nav-item.has-treeview > a").on("click", function (e) {
        e.preventDefault();
        let parent = $(this).parent();

        if (parent.hasClass("menu-open")) {
            parent.removeClass("menu-open");
            parent.find(".nav-treeview").slideUp();
        } else {
            $(".nav-item.has-treeview").removeClass("menu-open");
            $(".nav-treeview").slideUp();

            parent.addClass("menu-open");
            parent.find(".nav-treeview").slideDown();
        }
    });

    // Sayfa yenilendiğinde açık olan menüyü göster
    $(".nav-item.has-treeview.menu-open .nav-treeview").show();
});
