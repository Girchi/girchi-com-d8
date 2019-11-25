$("document").ready(function () {
    $.each($(".amount"), function (key, input) {
        let amount = input.value;
        let value = Math.floor((amount / currency) * 100);
        $(input).closest('.d-flex').find('.ged-output-add').html(value);
    });
});
// GED Count
let currency = $("#currency_girchi").val();

$("#edit-amount").on("keyup", e => {
    let amount = e.target.value;
    let value = Math.floor((amount / currency) * 100);
    $("#ged-place1").html(value);
    $("#ged-place-2").html(value);
});

$(".amount").on("keyup", e => {
    let amount = e.target.value;
    let value = Math.floor((amount / currency) * 100);
    $(e.target).closest('.d-flex').find('.ged-output-add').html(value);
});


$("#edit-amount--2").on("keyup", e => {
    let amount = e.target.value;
    let value = Math.floor((amount / currency) * 100);
    $("#ged-place-2").html(value);
});

// Front validation
$("#politicians-donation").on("change", e => {
    if (e.target.value) {
        $("#edit-donation-aim").attr("disabled", "disabled");
    } else {
        $("#edit-donation-aim").removeAttr("disabled");
    }
});

$("#edit-donation-aim").on("change", e => {
    if (e.target.value) {
        $("#politicians-donation").attr("disabled", "disabled");
    } else {
        $("#politicians-donation").removeAttr("disabled");
    }
});

$("#reg-politicians-donation").on("change", e => {
    if (e.target.value) {
        $("#edit-donation-aim--2").attr("disabled", "disabled");
    } else {
        $("#edit-donation-aim--2").removeAttr("disabled");
    }
});

$("#edit-donation-aim--2").on("change", e => {
    if (e.target.value) {
        $("#reg-politicians-donation").attr("disabled", "disabled");
    } else {
        $("#reg-politicians-donation").removeAttr("disabled");
    }
});

$('body').on('click', '.pauseDonation', e => {
    var entity_id = $(e.target).attr('data-id');
    var user_id = $(e.target).attr('user-id');
    $.ajax({
        type: "POST",
        url: "/donate/update_donation_status",
        data: {"action": "pause", "id": entity_id, "user_id": user_id}
    })
        .done((data) => {
            if (data.statusCode == 200) {
                $(e.target).replaceWith(`<button
                                   data-id = "${entity_id}"
                                   user-id = "${user_id}"
                                   class="btn btn-success mr-sm-1 text-uppercase px-2 d-block d-sm-inline-block mx-0 w-100 w-sm-auto mt-1 mt-sm-0 resumeDonation">
                                   ${Drupal.t("Resume")}
                                   </button>`);

                $(`[data-wrapper-id=${entity_id}]`).removeClass('bg-gradient-green').addClass('bg-gradient-warning');
                $(`.donation-status-${entity_id}`).text(Drupal.t('PAUSED'));
            }
        });

});
$('body').on('click', '.resumeDonation', e => {
    var entity_id = $(e.target).attr('data-id');
    var user_id = $(e.target).attr('user-id');
    $.ajax({
        type: "POST",
        url: "/donate/update_donation_status",
        data: {"action": "resume", "id": entity_id, "user_id": user_id}
    })
        .done((data) => {
            if (data.statusCode == 200) {
                $(e.target).replaceWith(`<button
                                     data-id = "${entity_id}"
                                     user-id = "${user_id}"
                                     class="btn btn-outline-light-silver mr-sm-1 text-grey text-uppercase px-2 d-block d-sm-inline-block mx-0 w-100 w-sm-auto mt-1 mt-sm-0 pauseDonation">
                                     ${Drupal.t('Pause')}
                                     </button>`);
                $(`[data-wrapper-id=${entity_id}]`).removeClass('bg-gradient-warning').addClass('bg-gradient-green');
                $(`.donation-status-${entity_id}`).text(Drupal.t("ACTIVE"));
            }
        });


});

// get 10 top politicians on focus FOR SINGLE DONATION.
$("#politicians-donation").on("focus", e => {
    getTopPoliticians($('.politiciansList'),'politiciansList');
});

// get 10 top politicians on focus FOR REGULAR DONATION.
$("#reg-politicians-donation").on("focus", e => {
    getTopPoliticians($('.regPoliticiansList'),'regPoliticiansList');
});

// get politicians on key up FOR SINGLE DONATION.
$("#politicians-donation").on("keyup", e => {
    let keyword = e.target.value;
    getPoliticiansOnKeyUp($('.politiciansList'),'politiciansList', keyword);
});

// get politicians on key up FOR REGULAR DONATION.
$("#reg-politicians-donation").on("keyup", e => {
    let keyword = e.target.value;
    getPoliticiansOnKeyUp($('.regPoliticiansList'),'regPoliticiansList', keyword);
});

// Choose the politician FOR SINGLE DONATION.
$(".politiciansList").on("click", "a", e => {
    let item = $(e.target).closest("a");
    chooseThePolitician($("#politician-autocomplete"),$("#autocomplete-result"),$("#politician_id"),item, "delete-politician");
});

// Choose the politician FOR REGULAR DONATION.
$(".regPoliticiansList").on("click", "a", e => {
    let item = $(e.target).closest("a");
    chooseThePolitician($("#reg-politician-autocomplete"),$("#reg-autocomplete-result"),$("#reg-politician_id"),item, "reg-delete-politician");
});

//Delete choosen politician FOR SINGLE DONATION.
$(document).on("click", ".delete-politician", e => {
    deleteChoosenPolitician($("#autocomplete-result"),$("#politician-autocomplete"),$("#politicians-donation"),$("#edit-donation-aim"));
});

//Delete choosen politician FOR REGULAR DONATION.
$(document).on("click", ".reg-delete-politician", e => {
    deleteChoosenPolitician($("#reg-autocomplete-result"),$("#reg-politician-autocomplete"),$("#reg-politicians-donation"),$("#edit-donation-aim--2"));
});



$(document).ready(function() {
    getCurrentPolitician($("#politician-autocomplete"), $("#autocomplete-result"), $("#politician_id"), "delete-politician");
    getCurrentPolitician($("#reg-politician-autocomplete"), $("#reg-autocomplete-result"), $("#reg-politician_id"), "reg-delete-politician");
});

// Function to get 10 top politicians
function getTopPoliticians(showPoliticiansList, politiciansListClass){
    showPoliticiansList.show();
    let politiciansList = $("ul."+politiciansListClass);
    let elementsCounter = 0;
    $.ajax({
        type: "GET",
        url: "/api/donations/top-politicians"
    }).done(data => {
        politiciansList.html("");
        $.each(data, function(i, user) {
            let dataLength = data.length;
            elementsCounter++;
            let newElement = $(`<li style="border-top: 1px solid #ecf1f5" class="last bg-dark-white politiciansListItem">
                    <a class="dropdown-item" role="option" style="border-radius: 0" id="${user.id}">
                    <span class="text">
                    <div class="d-flex w-100 align-items-center p-1">
                    <span class="rounded-circle overflow-hidden d-inline-block">
                        <img
                                src="${user.imgUrl}"
                                width="35"
                                class="rounded pl-politician-avatar"
                                alt="..."
                        />
                    </span>
                        <h6 class="text-uppercase line-height-1-2 font-size-3 font-size-xl-3 mb-0 mx-2">
                            <span
                                    class="text-decoration-none d-inline-block text-hover-success"
                            >
                            <span class="pl-politician-first-name">${user.firstName}</span>
                            <span class="font-weight-bold pl-politician-last-name">${user.lastName}</span>
                            </span>
                            <span class="d-flex font-size-1 text-grey pl-politician-position">
                                პოლიტიკოსი
                            </span>
                        </h6>
                    </div>
                    </span>
                    </a>
                </li>`);
            if (elementsCounter === 1) {
                newElement.css("border-top-left-radius", "20px");
                newElement.css("border-top-right-radius", "20px");
                let a = newElement.find("a").first();
                a.css("border-top-left-radius", "20px");
                a.css("border-top-right-radius", "20px");
            }

            if (elementsCounter === dataLength) {
                let lastElement = newElement.last();
                lastElement.css("border-bottom-left-radius", "20px");
                lastElement.css("border-bottom-right-radius", "20px");
                let a = lastElement.find("a").first();
                a.css("border-bottom-left-radius", "20px");
                a.css("border-bottom-right-radius", "20px");
            }

            politiciansList.append(newElement);
        });
        showPoliticiansList.selectpicker("refresh");
    });

}

// Function to get politicians on key up.
function getPoliticiansOnKeyUp(showPoliticiansList, politiciansListClass, keyword){
    showPoliticiansList.show();
    let politiciansList = $("ul." + politiciansListClass);
    let elementsCounter = 0;
    $.ajax({
        type: "GET",
        url: "/api/party-list/my-supported-members?user=" + keyword
    }).done(data => {
        politiciansList.html("");
        $.each(data, function(i, user) {
            let dataLength = data.length;
            elementsCounter++;
            let newElement = $(`<li style="border-top: 1px solid #ecf1f5" class="last bg-dark-white politiciansListItem">
                    <a class="dropdown-item" role="option" style="border-radius: 0" id="${user.id}">
                    <span class="text">
                    <div class="d-flex w-100 align-items-center p-1">
                      <span class="rounded-circle overflow-hidden d-inline-block">
                        <img
                                src="${user.imgUrl}"
                                width="35"
                                class="rounded pl-politician-avatar"
                                alt="..."
                        />
                      </span>
                        <h6 class="text-uppercase line-height-1-2 font-size-3 font-size-xl-3 mb-0 mx-2">
                            <span
                                    class="text-decoration-none d-inline-block text-hover-success"
                            >
                              <span class="pl-politician-first-name">${user.firstName}</span>
                              <span class="font-weight-bold pl-politician-last-name">${user.lastName}</span>
                            </span>
                            <span class="d-flex font-size-1 text-grey pl-politician-position">
                                პოლიტიკოსი
                            </span>
                        </h6>
                    </div>
                    </span>
                    </a>
                </li>`);

            if (elementsCounter === 1) {
                newElement.css("border-top-left-radius", "20px");
                newElement.css("border-top-right-radius", "20px");
                let a = newElement.find("a").first();
                a.css("border-top-left-radius", "20px");
                a.css("border-top-right-radius", "20px");
            }

            if (elementsCounter === dataLength) {
                let lastElement = newElement.last();
                lastElement.css("border-bottom-left-radius", "20px");
                lastElement.css("border-bottom-right-radius", "20px");
                let a = lastElement.find("a").first();
                a.css("border-bottom-left-radius", "20px");
                a.css("border-bottom-right-radius", "20px");
            }

            politiciansList.append(newElement);
        });

        showPoliticiansList.selectpicker("refresh");
    });
};

// Funtion to Choose the politician.
function chooseThePolitician(politician_autocomplete, autocomplete_result, politician_id, item, delete_politician){
    politician_autocomplete.hide();
    autocomplete_result.show();
    let politicianId = item.attr("id");
    let img = item.find("img").attr("src");
    let firstName = item.find("span.pl-politician-first-name")[0].innerText;
    let lastName = item.find("span.pl-politician-last-name")[0].innerText;
    autocomplete_result.html(`
    <button type="button" class="btn btn-white border btn-block bg-hover-white rounded-oval"title="${firstName}
    ${lastName}">
        <div class="d-flex w-100 align-items-center p-1">
            <span class="rounded-circle overflow-hidden d-inline-block">
                <img src="${img}" width="35" class="rounded" alt="...">
            </span>
            <h6 class="text-uppercase line-height-1-2 font-size-3 font-size-xl-3 mb-0 mx-2">
            <span class="text-decoration-none d-inline-block text-hover-success">
            ${firstName}
            </span>
            <span class="font-weight-bold">${lastName}</span>
            </span>
            <span class="d-flex font-size-1 text-grey justify-content-center justify-content-sm-start">
                პოლიტიკოსი
            </span>
            </h6>
            <span class="${delete_politician} font-size-4 p-0 shadow-none text-dark-silver text-hover-danger float-right ml-auto" >
                    <i class="icon-delete"></i>
            </span>
        </div>
    </button>
    `);

   politician_id.val(politicianId);
};

// Function to get current politician from URL
function getCurrentPolitician(politician_autocomplete, autocomplete_result, politician_id,delete_politician) {
    let id = location.search.substring(location.search.lastIndexOf("=") + 1);
    if (id) {       
        politician_autocomplete.hide();   
        $("#autocomplete-result").show();
        let firstName = document.getElementsByClassName('hidden-first-name')[0].value;
        let lastName = document.getElementsByClassName('hidden-last-name')[0].value;
        let image = document.getElementsByClassName('hidden-image')[0].src;
        autocomplete_result.html(`
        <button type="button" class="btn btn-white border btn-block bg-hover-white rounded-oval"title="${firstName}
        ${lastName}">
            <div class="d-flex w-100 align-items-center p-1">
                <span class="rounded-circle overflow-hidden d-inline-block">
                    
                    <img src='${image}' width="35" class="rounded" alt="...">
                </span>
                <h6 class="text-uppercase line-height-1-2 font-size-3 font-size-xl-3 mb-0 mx-2">
                <span class="text-decoration-none d-inline-block text-hover-success">
                ${firstName}
                </span>
                <span class="font-weight-bold">${lastName}</span>
                </span>
                <span class="d-flex font-size-1 text-grey justify-content-center justify-content-sm-start">
                    პოლიტიკოსი
                </span>
                </h6>
                <span class="${delete_politician} font-size-4 p-0 shadow-none text-dark-silver text-hover-danger float-right ml-auto" >
                        <i class="icon-delete"></i>
                </span>
            </div>
        </button>
        `);
    }
    politician_id.val(id);
};

$("#politicians-donation").on("focusout", e => {
    document.getElementById('politicians-donation').value = "";
});

$("#reg-politicians-donation").on("focusout", e => {
    document.getElementById('reg-politicians-donation').value = "";
});

//Function to delete choosen politician.
function deleteChoosenPolitician(autocomplete_result,politician_autocomplete, politicians_donation, edit_donation_aim){
    autocomplete_result.hide();
    politician_autocomplete.show();
    politicians_donation.val("");
    edit_donation_aim.removeAttr("disabled");
};

//hide list.
$("body").on("click", e => {
    if(!$(e.target).is('#politicians-donation')){
        $(".politiciansList").hide();
    }
    if(!$(e.target).is('#reg-politicians-donation')){
        $(".regPoliticiansList").hide();
    }
});

