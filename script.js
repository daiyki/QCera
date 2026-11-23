const slider = document.querySelector(".slider");

if (slider) {
    const slides = slider.querySelectorAll(".slide");
    const dots = document.querySelectorAll(".slidernav a");
    let currentIndex = 0;
    let timer;

    const goTo = (i) => {
        currentIndex = (i + slides.length) % slides.length;
        slider.scrollTo({ left: currentIndex * slider.clientWidth, behavior: "smooth" });
    };

    const start = () => { timer = setInterval(() => goTo(currentIndex + 1), 5000); };

    dots.forEach((dot, i) => {
        dot.addEventListener("click", (e) => {
            e.preventDefault();
            goTo(i);
            clearInterval(timer);
            start();
        });
    });

    // keep the index in sync if the user swipes/scrolls manually
    slider.addEventListener("scroll", () => {
        currentIndex = Math.round(slider.scrollLeft / slider.clientWidth);
    });

    start();
}

/* Mobile menu (only visible on small screens) */
const siteHeader = document.querySelector("header");
const menuToggle = document.querySelector(".menu-toggle");

if (siteHeader && menuToggle) {
    const setMenu = (open) => {
        siteHeader.classList.toggle("open", open);
        menuToggle.setAttribute("aria-expanded", open ? "true" : "false");
    };

    menuToggle.addEventListener("click", () => setMenu(!siteHeader.classList.contains("open")));
    siteHeader.querySelectorAll(".bar a").forEach((a) => a.addEventListener("click", () => setMenu(false)));
}
