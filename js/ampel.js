$(document).ready()
{
    console.log("hi");

    $(".keywords_input").on("keyup", function(e)
    {
        if(e.key === ",")
        {
            console.log($(this).val());
            $(this).parent().find(".keyword_holder").append(`<div class="keyword"> ${ $(this).val().slice(0,-1) } </div>`);
            $(this).val("");
        }
    });
}