$(document).ready()
{
    $(".keyword_holder").each(function () {
        $(this).children().each(function () {
            append_delete_button($(this));
        });

        generate_hidden_keyword_inputs($(this));
    });
    $(".keywords_input").on("keyup", function (e) {
        if (e.key === ",") {
            if ($(this).val().replace(",", "").trim().length > 1) {
                keyword_holder = $(this).parent().find(".keyword_holder");

                keyword_element_dom = $(`<div class="keyword"> ${$(this).val().replace(",", "").trim()} </div>`);
                keyword_holder.append(keyword_element_dom);

                append_delete_button(keyword_element_dom);

                generate_hidden_keyword_inputs(keyword_holder);
            }

            $(this).val("");
        }
    });
}

function generate_hidden_keyword_inputs(keyword_holder) {
    keyword_input_holder = $(keyword_holder).parent().find(".keyword_input_holder");

    keyword_input_holder.empty();

    i = 0;

    keyword_holder.children().each(function () {
        text = $(this).contents().filter(function () {
            return this.nodeType == 3;
        })[0].nodeValue.trim();

        keyword_input_holder.append(`<input type="hidden" id="keyword-${i}" name="keyword_${i++}" value="${text}">`);
    });

    keyword_input_holder.append(`<input type="hidden" id="keyword-sum" name="keyword_sum" value="${i}">`);
}

function append_delete_button(keyword_element_dom) {
    keyword_delete_button_dom = $('<span class="keyword_delete">&#10005</span>');

    keyword_element_dom.append(keyword_delete_button_dom);

    keyword_delete_button_dom.click({ param1: keyword_element_dom.parent() }, function (e) {
        $(this).parent().remove();
        generate_hidden_keyword_inputs(e.data.param1);
    });
}