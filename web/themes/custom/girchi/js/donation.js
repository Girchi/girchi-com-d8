// GED Count
$("#edit-amount").on("focus", async e => {
    let currency = await getCurrency();
    $(this).on("keyup", e => {
        let amount = e.target.value;
        let value = Math.ceil(amount * currency * 100);
        $("#ged-place1").html(value);
    });
});

$("#edit-amount--2").on("focus", async e => {
    let currency = await getCurrency();
    $(this).on("keyup", e => {
        let amount = e.target.value;
        let value = Math.ceil(amount * currency * 100);
        $("#ged-place-2").html(value);
    });
});

// Front validation 
$("#edit-politicians").on("change", e => {
    if (e.target.value) {
        $("#edit-donation-aim").attr("disabled", "disabled");
    } else {
        $("#edit-donation-aim").removeAttr("disabled");
    }
});

$("#edit-donation-aim").on("change", e => {
    if (e.target.value) {
        $("#edit-politicians").attr("disabled", "disabled");
    } else {
        $("#edit-politicians").removeAttr("disabled");
    }
});

$("#edit-politicians--2").on("change", e => {
    if (e.target.value) {
        $("#edit-donation-aim--2").attr("disabled", "disabled");
    } else {
        $("#edit-donation-aim--2").removeAttr("disabled");
    }
});

$("#edit-donation-aim--2").on("change", e => {
    if (e.target.value) {
        $("#edit-politicians--2").attr("disabled", "disabled");
    } else {
        $("#edit-politicians--2").removeAttr("disabled");
    }
});



async function getCurrency() {
    let response = await fetch(
        "https://free.currconv.com/api/v7/convert?q=USD_GEL&compact=ultra&apiKey=eb0b17da4ee0b2728877"
    );
    let data = await response.json();
    let { USD_GEL: currency } = data;

    return currency;
}
