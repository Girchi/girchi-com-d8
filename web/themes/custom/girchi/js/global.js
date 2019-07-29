$(document).ready(function() {
    $("#edit-field-politician-value").on("change", e => {
        if (e.target.checked) {
        $(".form-checkbox-input").addClass("checked");
    } else {
        $(".form-checkbox-input").removeClass("checked");
    }
});

    $(".search-submit").on("click", e => {
        if ($("#search-text").val()) {
        $(".navbar-search-input ")
            .fadeIn()
            .removeClass("border-white")
            .addClass("border-secondary")
            .addClass("w-lg-500");
        $(".navbar-search").submit();
    }
});

    if ($(".paragraph-yellow").length % 2 === 1) {
        $(".paragraph-yellow")
            .last()
            .addClass("w-100");
    }

    $("#favorite_news").click(e => {
        var nid = $("#favorite_news").attr("data-node-id");
    if ($("#favorite_news").is(":checked")) {
        $.ajax({
            type: "GET",
            url: "/api/add/favorite/news/" + nid,
            success: function(response) {
                console.log(response);
            },
            error: function(response) {
                console.log(response);
            }
        });
    } else {
        $.ajax({
            type: "GET",
            url: "/api/remove/favorite/news/" + nid,
            success: function(response) {
                console.log(response);
            },
            error: function(response) {
                console.log(response);
            }
        });
    }
});
});
