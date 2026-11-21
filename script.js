let lastScroll = 0;
const header = document.querySelector("header");
window.addEventListener("scroll", function () {

    let currentScroll = window.scrollY;
    if (currentScroll > lastScroll && currentScroll > 100) {
        header.classList.add("hide");
    } else {
        header.classList.remove("hide");
    }
    lastScroll = currentScroll;
});

const slider = document.querySelector(".slider");

if (slider) {
    const slides = slider.querySelectorAll("img");
    let currentIndex = 0;

    setInterval(() => {
        currentIndex++;
        if (currentIndex >= slides.length) {
            currentIndex = 0;
        }
        slider.scrollTo({
            left: slides[currentIndex].offsetLeft - slides[0].offsetLeft,
            behavior: "smooth"
        });

    }, 5000);
}
