$(document).ready(function() {
    $("#edit-amount").on("focus", async e => {
        let response = await fetch(
            "https://free.currconv.com/api/v7/convert?q=USD_GEL&compact=ultra&apiKey=eb0b17da4ee0b2728877"
        );
        let data = await response.json();
        $(this).on("keyup", e => {
            let amount = e.target.value;
            let { USD_GEL: currency } = data;
            let value = Math.ceil(amount * currency * 100);
            $("#ged-place1").html(value);
        });
    });

    $("#edit-politicians").on("change", (e) => {
        console.log(e.target.value);
        if (e.target.value) {
            $("#edit-donation-aim").attr("disabled", "disabled");
        }else{
            // console.log('test');
             $("#edit-donation-aim").removeAttr("disabled");
        }
    });

     $("#edit-donation-aim").on("change", e => {
         console.log(e.target.value);
         if (e.target.value) {
             $("#edit-politicians").attr("disabled", "disabled");
         } else {
            //  console.log("test");
             $("#edit-politicians").removeAttr("disabled");
         }
     });
});
