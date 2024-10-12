$(document).ready(function() {
  var multipleCardCarousel = document.querySelector("#carousel-Municipalities");
  if (window.matchMedia("(min-width: 768px)").matches) {
    var carousel = new bootstrap.Carousel(multipleCardCarousel, {
      interval: false,
    });

    var $carouselInner = $("#carousel-Municipalities .carousel-inner");
    var $carouselItems = $(".carousel-item");
    var cardWidth = $carouselItems.outerWidth(true);
    var scrollPosition = 0;

    function updateDimensions() {
      var carouselWidth = $carouselInner[0].scrollWidth;
      var containerWidth = $carouselInner.width();
      // Set new cardWidth after AJAX load or resize
      cardWidth = $carouselItems.outerWidth(true);
      return { carouselWidth, containerWidth };
    }

    function scrollToPosition(position) {
      $carouselInner.animate({ scrollLeft: position }, 600);
    }

    $("#carousel-Municipalities .carousel-control-next").on("click", function() {
      var { carouselWidth, containerWidth } = updateDimensions();
      if (scrollPosition + containerWidth >= carouselWidth) {
        scrollPosition = 0; // Wrap around
      } else {
        scrollPosition += cardWidth;
      }
      scrollToPosition(scrollPosition);
    });

    $("#carousel-Municipalities .carousel-control-prev").on("click", function() {
      var { carouselWidth } = updateDimensions();
      if (scrollPosition - cardWidth < 0) {
        scrollPosition = carouselWidth - cardWidth; // Wrap around
      } else {
        scrollPosition -= cardWidth;
      }
      scrollToPosition(scrollPosition);
    });

    // Optional: Handle AJAX content updates
    $(document).on('ajaxComplete', function() {
      // Recalculate dimensions after AJAX content is loaded
      var { carouselWidth } = updateDimensions();
      scrollToPosition(scrollPosition);
    });

  } else {
    $(multipleCardCarousel).addClass("slide");
  }
});




