$(document).ready(function () {
    // Video blog carousel
    const videoCarousel = $('.front-news-carousel').owlCarousel({
        margin: 10,
        dotsContainer: $('.front-news-carousel-dots'),
        responsive: {
            0: {
                items: 2,
                slideBy: 2
            },
            480: {
                items: 2,
                slideBy: 2
            },
            768: {
                items: 2,
                slideBy: 2
            },
            992: {
                items: 5,
                slideBy: 5
            }
        }
    })
    $('.front-news-carousel-next').on('click', () => {
        videoCarousel.trigger('next.owl.carousel')
    })
    $('.front-news-carousel-prev').on('click', () => {
        videoCarousel.trigger('prev.owl.carousel')
    })
    $('#edit-field-politician-value').on('change', (e) => {
         if (e.target.checked) {
            $('.form-checkbox-input').addClass('checked');
        } else {
            $('.form-checkbox-input').removeClass('checked');
        }

    });
});