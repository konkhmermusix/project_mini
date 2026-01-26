const sliderWrapper = document.querySelector(".slider-wrapper");

function slideLeft() {
  sliderWrapper.scrollBy({ left: -280, behavior: "smooth" });
}

function slideRight() {
  sliderWrapper.scrollBy({ left: 280, behavior: "smooth" });
}
