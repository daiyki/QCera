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