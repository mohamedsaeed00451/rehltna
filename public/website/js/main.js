// start nav
const openBtn = document.getElementById("open-menu");
const closeBtn = document.getElementById("close-menu");
const overlay = document.getElementById("menu-overlay");
const sidebar = document.getElementById("mobile-sidebar");
const content = document.getElementById("menu-content");

function toggleMenu() {
    sidebar.classList.toggle("invisible");
    overlay.classList.toggle("opacity-100");
    content.classList.toggle("mobile-menu-closed");
    content.classList.toggle("mobile-menu-open");
}

if (openBtn) openBtn.addEventListener("click", toggleMenu);
if (closeBtn) closeBtn.addEventListener("click", toggleMenu);
if (overlay) overlay.addEventListener("click", toggleMenu);

function toggleMobileDropdown(id, iconId) {
    const dropdown = document.getElementById(id);
    const icon = document.getElementById(iconId);
    dropdown.classList.toggle("hidden");
    icon.classList.toggle("rotate-180");
}

// end nav
// start slider
// ---------------------------------------------------------
// START SLIDER LOGIC (UPDATED WITH VIDEO SUPPORT)
// ---------------------------------------------------------
if (document.querySelector(".mySwiper")) {
    var swiper = new Swiper(".mySwiper", {
        spaceBetween: 0,
        effect: "fade",
        fadeEffect: {crossFade: true},
        speed: 1000,
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        navigation: {
            nextEl: ".swiper-button-next-custom",
            prevEl: ".swiper-button-prev-custom",
        },
        on: {
            init: function () {
                handleVideoSlide(this);
            },
            slideChangeTransitionEnd: function () {
                handleVideoSlide(this);
            }
        }
    });

    function handleVideoSlide(swiperInstance) {
        document.querySelectorAll('.slider-video').forEach(vid => {
            vid.pause();
            vid.currentTime = 0;
        });

        let activeSlide = swiperInstance.slides[swiperInstance.activeIndex];
        let video = activeSlide.querySelector('video');

        if (video) {
            swiperInstance.autoplay.stop();
            let playPromise = video.play();
            if (playPromise !== undefined) {
                playPromise.catch(error => {
                    console.log("Autoplay prevented by browser:", error);
                    swiperInstance.autoplay.start();
                });
            }

            video.onended = function () {
                swiperInstance.slideNext();
                swiperInstance.autoplay.start();
            };
        } else {
            swiperInstance.autoplay.start();
        }
    }
}
// end slider

// start dropdown
const dropdownBtn = document.getElementById("dropdown-btn");
const dropdownMenu = document.getElementById("dropdown-menu");
const selectedOption = document.getElementById("selected-option");
const dropdownItems = document.querySelectorAll(".dropdown-item");

if (dropdownBtn) {
    const dropdownIcon = dropdownBtn.querySelector(".fa-chevron-down"); // Ensure this exists if used
    dropdownBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        dropdownMenu.classList.toggle("invisible");
        dropdownMenu.classList.toggle("opacity-0");
        dropdownMenu.classList.toggle("-translate-y-2");
        dropdownMenu.classList.toggle("translate-y-0");
        if (dropdownIcon) dropdownIcon.classList.toggle("rotate-180");
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", (e) => {
        if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.classList.add("invisible", "opacity-0", "-translate-y-2");
            dropdownMenu.classList.remove("translate-y-0");
            if (dropdownIcon) dropdownIcon.classList.remove("rotate-180");
        }
    });

    // Handle option selection
    dropdownItems.forEach((item) => {
        item.addEventListener("click", () => {
            const value = item.dataset.value;
            const text = item.querySelector("span").textContent;

            selectedOption.textContent = text;

            // Visual selection state
            dropdownItems.forEach((i) =>
                i.querySelector(".fa-check").classList.add("opacity-0"),
            );
            item.querySelector(".fa-check").classList.remove("opacity-0");

            // Close dropdown
            dropdownMenu.classList.add("invisible", "opacity-0", "-translate-y-2");
            dropdownMenu.classList.remove("translate-y-0");
            if (dropdownIcon) dropdownIcon.classList.remove("rotate-180");

            // Here you would typically trigger the filtering logic
            console.log("Selected category:", value);
        });
    });
}
// end dropdown

// Start Before/After Slider Logic
const compareContainer = document.getElementById("compare-container");
if (compareContainer) {
    const compareOverlay = document.getElementById("compare-overlay");
    const compareSlider = document.getElementById("compare-slider");
    const compareHandle = document.getElementById("compare-handle");
    let isDragging = false;

    // Mouse Events
    compareSlider.addEventListener("mousedown", () => (isDragging = true));
    document.addEventListener("mouseup", () => (isDragging = false));
    document.addEventListener("mousemove", (e) => {
        if (!isDragging) return;
        const rect = compareContainer.getBoundingClientRect();
        let x = e.clientX - rect.left;
        let percent = (x / rect.width) * 100;
        percent = Math.max(0, Math.min(100, percent));
        compareOverlay.style.width = `${percent}%`;
        compareSlider.style.left = `${percent}%`;
    });

    // Touch Events for Mobile
    compareSlider.addEventListener("touchstart", () => (isDragging = true));
    document.addEventListener("touchend", () => (isDragging = false));
    document.addEventListener("touchmove", (e) => {
        if (!isDragging) return;
        const rect = compareContainer.getBoundingClientRect();
        let x = e.touches[0].clientX - rect.left;
        let percent = (x / rect.width) * 100;
        percent = Math.max(0, Math.min(100, percent));
        compareOverlay.style.width = `${percent}%`;
        compareSlider.style.left = `${percent}%`;
    });
}
// End Before/After Slider Logic

// Testimonial Swiper
if (document.querySelector(".testimonial-swiper")) {
    var testimonialSwiper = new Swiper(".testimonial-swiper", {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        speed: 1000,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        breakpoints: {
            640: {
                slidesPerView: 1,
            },
            768: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 3,
            },
        },
    });
}

document.addEventListener('DOMContentLoaded', function () {

    const gallerySliders = document.querySelectorAll('.gallery-swiper');

    gallerySliders.forEach((sliderElement) => {
        new Swiper(sliderElement, {
            slidesPerView: "auto",
            spaceBetween: 20,
            loop: true,
            speed: 5000,
            allowTouchMove: false,

            autoplay: {
                delay: 0,
                disableOnInteraction: false,
                pauseOnMouseEnter: false,
            },

            freeMode: false,

            breakpoints: {
                640: {
                    spaceBetween: 20,
                },
                1024: {
                    spaceBetween: 30,
                },
            },
        });
    });

});
