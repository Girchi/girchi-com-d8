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

    if ($(window).width() < 1200) {
        $('.nav-item.is-dropdown .nav-link i').on('click', (e) => {
          e.preventDefault()
          const item = $(e.target).parents('.nav-item')
          const dropdown = $(e.target).parents('.nav-item').find('.nav-dropdown')
          if (dropdown.is(':visible')) {
            dropdown.hide()
            item.removeClass('collapsed')
          } else {
            dropdown.show()
            item.addClass('collapsed')
          }
        })
      } else {
        $('.nav-item.is-dropdown').on('mouseenter', function (e) {
          $(this).addClass('collapsed')
        }).on('mouseleave', function (e) {
          $(this).removeClass('collapsed')
        })
      }

    $('.search-submit').on('click',e => {
        if($('#search-text').val()){
            $('.navbar-search-input ')
                .fadeIn()
                .removeClass('border-white')
                .addClass('border-secondary')
                .addClass('w-lg-500');
            $('.navbar-search').submit();
        }
    })
});