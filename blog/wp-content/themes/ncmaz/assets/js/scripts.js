jQuery(document).ready(function ($) {
  "use strict";

  _toogleMobileSidebarMenu();
  _toggleMobileSubmenuItem();
  _toggleNcmazModal();
  //   ==========================================
  function _toggleMobileSubmenuItem() {
    $("#mobile-menu-list .menu-item-has-children .icon-after-menu").click(
      function (e) {
        e.preventDefault();
        $(this).parent().siblings().toggle();
      }
    );
  }

  //   ==========================================
  function _toogleMobileSidebarMenu() {
    $(".btn-toogle-mobile-sidebar-menu").click(function () {
      $("#site-navigation-mobile").fadeToggle(200);
    });
    $("#ncmaz-headlessui-dialog-overlay-84").click(function () {
      $("#site-navigation-mobile").fadeToggle(200);
    });
    $("#btn-close-modal-mobile-sidebar-menu").click(function () {
      $("#site-navigation-mobile").fadeToggle(200);
    });
  }
  //   ==========================================

  function _toggleNcmazModal() {
    // GET ALL BUTTON OPEN MODAL
    let ncmazBtnOpens = document.querySelectorAll("[data-ncmaz-open-modal]");
    // GET ALL BUTTON CLOSE MODAL
    let ncmazBtnClose = document.querySelectorAll("[data-ncmaz-close-modal]");
    if (!ncmazBtnOpens.length) {
      return;
    }

    [...Array.from(ncmazBtnOpens), ...Array.from(ncmazBtnClose)].map((item) => {
      if (
        !item.getAttribute("data-ncmaz-open-modal") &&
        !item.getAttribute("data-ncmaz-close-modal")
      ) {
        return;
      }

      item.addEventListener("click", (e) => {
        e.preventDefault();
        // 0pen modal
        if (item.hasAttribute("data-ncmaz-open-modal")) {
          $(
            `[data-ncmaz-modal-name='${item.getAttribute(
              "data-ncmaz-open-modal"
            )}']`
          ).fadeIn(200);
        }

        // Close modal
        if (item.hasAttribute("data-ncmaz-close-modal")) {
          $(
            `[data-ncmaz-modal-name='${item.getAttribute(
              "data-ncmaz-close-modal"
            )}']`
          ).fadeOut(200);
        }
      });
    });
  }
});
