const bannerSwiper = new Swiper(".bannerSwiper", {
    loop: true,
    speed: 1000,
    autoHeight: true,
    autoplay: {
        delay: 5000,
        disableOnInteraction: false,
    },
});
AOS.init();
const testimonialSlider = new Swiper(".testimonialSlider", {
    slidesPerView: 1,
    centerSlide: true,

    spaceBetween: 20,
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    autoplay: {
        delay: 5000,
        disableOnInteraction: false,
    },
    loop: true,
    speed: 800,
});
const ourClientSwiper = new Swiper(".our-client-swiper", {
    slidesPerView: 1,
    centerSlide: true,
    spaceBetween: 80,
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
     autoplay: {
         delay: 3000,
        disableOnInteraction: false,
     },
    loop: true,
    speed: 800,
});
const productSlider = new Swiper(".productSlider", {
    slidesPerView: 4,
    centerSlide: true,
    spaceBetween: 24,
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    autoplay: {
        delay: 2000,
        disableOnInteraction: false,
    },
    loop: true,
    speed: 800,
    breakpoints: {
        1024: {
            slidesPerView: 4,
        },
        768: {
            slidesPerView: 2,
        },
        480: {
            slidesPerView: 1,
        },
        0: {
            spaceBetween: 24,
            slidesPerView: 1,
        },
    }
});
// home new
const ourProductsSlider = new Swiper(".our-products-slider", {
    slidesPerView: 6,
    centerSlide: true,
    spaceBetween: 24,
    autoplay: {
        delay: 2000,
        disableOnInteraction: false,
    },
    loop: true,
    speed: 800,
    breakpoints: {
        1024: {
            slidesPerView: 6,
        },
        768: {
            slidesPerView: 5,
        },
        480: {
            slidesPerView: 3,
        },
        0: {
            spaceBetween: 14,
            slidesPerView: 1.5,
        },
    }
});
function initSwiper() {
    const isMobile = window.innerWidth <= 768; // You can adjust this value based on your needs

    const aviationBannerSwiper = new Swiper(".aviation-banner-swiper", {
        loop: true,
        slidesPerView: 1,
        centerSlide: true,
        spaceBetween: 24,
        speed: 1000,
        autoHeight: isMobile, // Set autoHeight to true if it's a mobile view
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });

    return aviationBannerSwiper;
}

let swiperInstance = initSwiper();

// Re-initialize Swiper when resizing the window
window.addEventListener('resize', function() {
    swiperInstance.destroy(); // Destroy the previous swiper instance
    swiperInstance = initSwiper(); // Re-initialize with the new window size
});

jQuery.noConflict();
jQuery(document).ready(function () {
    var sliderSettings = {
        nav: false,
        loop: true,
        items: 7,
        dots: false,
        margin: 20,
        autoplay: true,
        slideTransition: "linear",
        autoplaySpeed: 5000,
        center: true,
        lazyLoad: true,
        touchDrag: false,
        mouseDrag: false,
        autoplayHoverPause: false,
        responsive: {
          0: { items: 2 },
          600: { items: 3 },
          1000: { items: 7 },
        },
    };

    var trustedBySliderLeft = jQuery(".trusted-by-slider.slideLeft");
    var trustedBySliderRight = jQuery(".trusted-by-slider.slideRight");

    trustedBySliderLeft.owlCarousel(sliderSettings);
    trustedBySliderRight.owlCarousel({ ...sliderSettings, rtl: true });
    function handleAutoplay(event, slider) {
        slider.trigger("play.owl.autoplay");
    }
    handleAutoplay(event, trustedBySliderLeft);
    handleAutoplay(event, trustedBySliderRight);
});

jQuery.noConflict();
jQuery(document).ready(function ($) {
    var $homepageCtSlider = jQuery(".homepage-ct-slider.vid");
    $homepageCtSlider.owlCarousel({
        stagePadding: 60,
        items: 2,
        loop: true,
        dots: true,
        nav: true,
        margin: 40,
        autoplay: true,
        autoplaySpeed: 5000,
        slideSpeed: 1000,
        autoplayHoverPause: true,
        touchDrag: true,
        mouseDrag: true,
        center: true,
        responsive: {
            0: {
                items: 1,
                stagePadding: 0,
                center: true,
                loop: false,
            },
            992: {
                items: 1,
                stagePadding: 100,
            },
            1200: {
                items: 2,
                center: false,
            },
        },
    });
    var $maxair360Slider = jQuery(".maxair360-slider");
    $maxair360Slider.owlCarousel({
        items: 1,
        loop: true,
        dots: true,
        nav: true,
        autoHeight: true,
        margin: 40,
        autoplay: true,
        autoplaySpeed: 3000,
        slideSpeed: 1000,
        autoplayHoverPause: true,
        touchDrag: true,
        mouseDrag: true,
        center: true,
        onInitialized: syncTabsAndDots,
    });
    function syncTabsAndDots() {
        $(".maxair360-tabs .tab360").removeClass("active");
        $(".maxair360-tabs .tab360[data-index='0']").addClass("active");
    }

    // Handle tab clicks
    $(".maxair360-tabs .tab360").on("click", function () {
        const index = $(this).data("index");
        $(".maxair360-tabs .tab360").removeClass("active");
        $(this).addClass("active");
        $maxair360Slider.trigger("to.owl.carousel", [index, 300]);
    });
    $maxair360Slider.on("changed.owl.carousel", function (event) {
        const currentIndex =
            event.item.index - event.relatedTarget._clones.length / 2;
        const totalItems = event.item.count;
        const realIndex = (currentIndex + totalItems) % totalItems;

        $(".maxair360-tabs .tab360").removeClass("active");
        $(`.maxair360-tabs .tab360[data-index='${realIndex}']`).addClass(
            "active"
        );
        const $dots = $(".maxair360-slider .owl-dots .owl-dot");
        $dots.removeClass("active");
        $dots.eq(realIndex).addClass("active");
    });
    
      // Create a new variable to store the Calendly link
  var $calendlyLink = jQuery(".slide .text a:last-child");

  // Add functionality to pause the slide when the Calendly link is clicked
  $calendlyLink.on("click", function () {
    $maxair360Slider.trigger("stop.owl.autoplay");
  });

  jQuery(document).on(
    "click",
    ".calendly-overlay .calendly-popup-close",
    function () {
      $maxair360Slider.trigger("play.owl.autoplay");
      $maxair360Slider.trigger("refresh.owl.carousel");
    }
  );

    var $homepageCtSliderCs = jQuery(".homepage-ct-slider.cs");
    $homepageCtSliderCs.owlCarousel({
        stagePadding: 60,
        items: 1,
        loop: true,
        dots: true,
        nav: true,
        margin: 40,
        autoplay: true,
        autoplaySpeed: 5000,
        slideSpeed: 1000,
        autoplayHoverPause: false,
        touchDrag: true,
        mouseDrag: true,
        center: true,
    });

    const $videos = jQuery(".homepage-ct-slider.vid .video");
  const $playButtons = jQuery(".homepage-ct-slider.vid .btn-box");

  jQuery(".homepage-ct-slider.vid").on("", ".btn-box", function () {
    const index = $playButtons.index(this);
    togglePlay(index);
  });

  function togglePlay(index) {
    const $video = $videos.eq(index);

    $videos.each(function (i) {
      if (i !== index) {
        this.pause();
      }
    });

    if ($video.get(0).paused || $video.get(0).ended) {
      $video.get(0).play();
    } else {
      $video.get(0).pause();
    }
  }
  $homepageCtSlider.find(".owl-dots .owl-dot").attr("aria-label", "slide");
  $homepageCtSlider
    .find(".owl-nav button")
    .attr({ "aria-label": "navigation", role: "button" });
});

const $videos = jQuery(".homepage-ct-slider.vid .video");
const $playButtons = jQuery(".homepage-ct-slider.vid .btn-box");
const $videoModal = jQuery(".video-modal");
const $videoModalContent = jQuery(".video-modal .modal-content");
const $videoModalVideo = jQuery("#video-modal");
const $closeModalButton = jQuery(".video-modal .close-modal");

$playButtons.on("click", function () {
  const videoSrc = jQuery(this).data("video-src");
  const videoPoster = jQuery(this).data("video-poster");

  $videoModalVideo.attr("src", videoSrc);
  $videoModalVideo.attr("poster", videoPoster);
  $videoModal.show();
});

$closeModalButton.on("click", function () {
  $videoModal.hide();
  $videoModalVideo.attr("src", "");
  $videoModalVideo.attr("poster", "");
});

$videoModal.on("click", function (e) {
  if (e.target === $videoModal[0]) {
    $videoModal.hide();
    $videoModalVideo.attr("src", "");
    $videoModalVideo.attr("poster", "");
  }
});


// Pause background slides when btn-box is clicked
$playButtons.on("click", function() {
  jQuery(".homepage-ct-slider.vid").trigger("stop.owl.autoplay");
});

// Resume background slides when video modal is closed
$closeModalButton.on("click", function() {
  jQuery(".homepage-ct-slider.vid").trigger("play.owl.autoplay");
});

$videoModal.on("click", function(e) {
  if (e.target === $videoModal[0]) {
    jQuery(".homepage-ct-slider.vid").trigger("play.owl.autoplay");
  }
});

// jQuery.noConflict();
// jQuery(document).ready(function ($) {
//     const $contactFormFloat = $(".contact-form-float");
//     const $contactFormCloseCta = $(".contact-form-float .btn-close");
//     const $mobContactBtn = $(".mob-contact-btn");

//     $contactFormCloseCta.click(function () {
//         $contactFormFloat.fadeOut();
//     });
//     $mobContactBtn.click(function () {
//         $contactFormFloat.slideToggle();
//     })

//     const $logoInner = $(".brand-logo-inner");
//     $logoInner.append($logoInner.html());

//     const totalWidth = $logoInner.width();
//     let currentPosition = 0;

//     function animateLogos() {
//         function moveLogos() {
//             currentPosition -= 1;
//             if (currentPosition <= -totalWidth) {
//                 currentPosition = 0;
//             }

//             $logoInner.css('transform', `translateX(${currentPosition}px)`);

//             requestAnimationFrame(moveLogos);
//         }

//         moveLogos();
//     }

//     animateLogos();

//     if (window.location.hash) {
//         var target = $(window.location.hash);
//         if (target.length) {
//             $('html, body').animate({
//                 scrollTop: target.offset().top - 100
//             }, 800);
//         }
//     }
//     $('a[href^="#"]').on('click', function (e) {
//         e.preventDefault();
//         var target = $(this.hash);
//         if (target.length) {
//             $('html, body').animate({
//                 scrollTop: target.offset().top - 300
//             }, 800);
//         }
//     });

//     const $videoPlayBtn = $(".video-grid-container .video-box .playBtn");
//     const $currentVideoBx = $(".video-grid-container .video-box");
//     const $fullscreenBtn = $(".video-grid-container .video-box .fullscreenBtn");

//     $videoPlayBtn.click(function () {
//         var $currentVideo = $(this).closest('.video-box').find('video')[0];
//         const $videoPlayBtnIcon = $(this).closest('.video-box').find('.play')
//         const $videoPauseBtnIcon = $(this).closest('.video-box').find('.pause')
//         if ($currentVideo.paused) {
//             $currentVideo.play();
//             $videoPlayBtnIcon.hide();
//             $videoPauseBtnIcon.show();

//         } else {
//             $currentVideo.pause();
//             $videoPlayBtnIcon.show();
//             $videoPauseBtnIcon.hide();
//         }
//     })

//     $fullscreenBtn.click(function () {
//         var $currentVideoBox = $(this).closest('.video-box')[0];
//         if (!document.fullscreenElement) {
//             if ($currentVideoBox.requestFullscreen) {
//                 $currentVideoBox.requestFullscreen();
//             } else if ($currentVideoBox.mozRequestFullScreen) {
//                 $currentVideoBox.mozRequestFullScreen();
//             } else if ($currentVideoBox.webkitRequestFullscreen) {
//                 $currentVideoBox.webkitRequestFullscreen();
//             } else if ($currentVideoBox.msRequestFullscreen) {
//                 $currentVideoBox.msRequestFullscreen();
//             }
//         } else {
//             if (document.exitFullscreen) {
//                 document.exitFullscreen();
//             } else if (document.mozCancelFullScreen) {
//                 document.mozCancelFullScreen();
//             } else if (document.webkitExitFullscreen) {
//                 document.webkitExitFullscreen();
//             } else if (document.msExitFullscreen) {
//                 document.msExitFullscreen();
//             }
//         }
//     })
//     $(window).scroll(function () {
//         if ($(this).scrollTop() > 100) {
//             $('#scrollToTopBtn').fadeIn();
//         } else {
//             $('#scrollToTopBtn').fadeOut();
//         }
//     });

//     $('#scrollToTopBtn').click(function () {
//         $('html, body').animate({ scrollTop: 0 }, 500);
//         return false;
//     });
//     $(".tab-container").each(function () {
//         var $container = $(this);
//         $container.find(".tab-buttons .btn").click(function () {
//             var targetTab = $(this).data("tab");
//             $container.find(".tab-buttons .btn").removeClass("active");
//             $container.find(".tab-content").removeClass("active");
//             $(this).addClass("active");
//             $("#" + targetTab).addClass("active");
//         });
//     });


// })

document.addEventListener("DOMContentLoaded", function () {
    // const contactFormBox = document.getElementById('contact_form');
    // const successModal = new bootstrap.Modal(document.getElementById('successMsg'));

    // function validateForm() {
    //     let isValid = true;
    //     resetErrors();

    //     const cfname = document.querySelector("#contact_form #name").value.trim();
    //     if (cfname === "") {
    //         showError("nameError", "Please enter your full name.");
    //         isValid = false;
    //     }

    //     const cfnumber = document.querySelector("#contact_form #number").value.trim();
    //     const phonePattern = /^[6789][0-9]{9}$/;
    //     if (cfnumber === "" || !phonePattern.test(cfnumber)) {
    //         showError("numberError", "Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.");
    //         isValid = false;
    //     }

    //     const cfemail = document.querySelector("#contact_form #email").value.trim();
    //     const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    //     if (cfemail === "" || !emailPattern.test(cfemail)) {
    //         showError("emailError", "Please enter a valid email address.");
    //         isValid = false;
    //     }

    //     const cfcompany = document.querySelector("#contact_form #company").value.trim();
    //     if (cfcompany === "") {
    //         showError("companyError", "Please enter your company name.");
    //         isValid = false;
    //     }
    //     return isValid;
    // }
    // contactFormBox.addEventListener("submit", function (event) {
    //     if (!validateForm()) {
    //         event.preventDefault();
    //     } else {
    //         event.preventDefault();
    //         successModal.show();
    //         setTimeout(function () {
    //             contactFormBox.submit();
    //         }, 3000);
    //     }
    // });
    // function showError(elementId, message) {
    //     const errorElement = document.getElementById(elementId);
    //     errorElement.textContent = message;
    //     errorElement.classList.remove("d-none");
    // }

    // function resetErrors() {
    //     const errorElements = document.querySelectorAll("#contact_form small.text-white");
    //     errorElements.forEach((error) => {
    //         error.classList.add("d-none");
    //         error.textContent = "";
    //     });
    // }
    document.querySelectorAll('header .dropdown-menu').forEach(item => {
        item.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    });
})