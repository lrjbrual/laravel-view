$(document).ready(function(){
    $('.todo_section').slimscroll({
         height: '213px',
         size: '5px',
         opacity: 0.2
    });

    $('body').on('click', '.border_color', function() {
       $('#btn_color').removeClass('btn-secondary btn-danger btn-primary btn-info btn-mint').addClass($(this).data('color'));
       $('#btn_color').data('badge', $(this).data('badge'));
       $('#btn_color').css("color", "#fff");
       return false;
    });

    $('[data-toggle="popover"]').popover({
        html: true,
        placement: 'right',
        content: function() {
            return $($(this).data('contentwrapper')).html();
        }
    });

    $(".border_danger").on('click',function() {
        $(".todo_mintbadge").addClass('border_danger')
    });

    $("form#main_input_box").submit(function(event) {
        event.preventDefault();

        var deleteButton = " <a href='#' class='tododelete redcolor'><span class='fa fa-trash'></span></a>";
        var striks = " <span class='dividor'>|</span> ";
        var editButton = " <a href='#' class='todoedit'><span class='fa fa-pencil'></span></a>";
        var checkBox = "<input type='checkbox' class='striked large' autocomplete='off' />";
        var twoButtons = "<div class='col-xs-3 showbtns todoitembtns'>" + editButton + striks + deleteButton + "</div>";
        var badgeClass = $('#btn_color').data('badge');

        $.ajax({
            type: "POST",
            "url": "todo/create",
            'data': {
                "color_class" : $('#btn_color').data('badge'),
                "item": $("#custom_textbox").val(),
                "is_striked": 0,
            },
            cache: false,
            success: function(r)
            {

            }
        });

        $(".list_of_items").prepend("<div class='todolist_list showactions'>  " + "<div class='col-xs-8 nopad custom_textbox1'> <div class='todo_mintbadge " + badgeClass + "'> </div> <div class='todoitemcheck checkbox-custom'>" + checkBox + "</div>" + "<div class='todotext todoitem todo_width'>" + $("#custom_textbox").val() + "</div> </div>" +   twoButtons + "<span class='seperator'></span>");
        $("#custom_textbox").val('');
        $('#btn_color').css("color", "#fff");
        return false;
    });

    $(".todo_section").on('click','.tododelete', function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            "url": "todo/delete",
            'data': {
                "id":  $(this).closest('.todolist_list').data('id'),
            },
            cache: false,
            success: function(r)
            {

            }
        });
        $(this).closest('.todolist_list').remove();
    });

    $(".todo_section").on('click','.striked', function(e) {
        $.ajax({
            type: "POST",
            "url": "todo/status",
            'data': {
                "id":  $(this).closest('.todolist_list').data('id'),
                "is_striked":  $(this).closest('.todolist_list').find('.striked').is(':checked') == true ? 1 : 0,
            },
            cache: false,
            success: function(r)
            {

            }
        });
        $(this).closest('.todolist_list').find('.todotext').toggleClass('strikethrough');
        $(this).closest('.todolist_list').find('.todoedit').toggle();
        $(this).closest('.todolist_list').find('.dividor').toggle();
    });

    $(".todo_section").on('click',".todoedit", function(e) {
        var editButton = " <a href='#' class='todoedit'><span class='fa fa-pencil'></span></a>";
        e.preventDefault();
        $(this).closest('.todolist_list').find('.striked').toggle();
        if ($(this).text() == " ") {
            $(this).closest('.todolist_list').find(".showbtns").toggleClass("opacityfull");
            var text1 = $(this).closest('.todolist_list').find("input[type='text'][name='text']").val().trim();
            if (text1 === '') {
                $(this).closest('.todolist_list').find("input[type='text'][name='text']").focus();
                $(this).closest('.todolist_list').find(".striked").hide();
                swal({
                    title: 'This field cannot be blank.',
                    confirmButtonColor: '#00ADB5'
                });
                return;
            }

            $.ajax({
                type: "POST",
                "url": "todo/update",
                'data': {
                    "id":  $(this).closest('.todolist_list').data('id'),
                    "item": text1,
                },
                cache: false,
                success: function(r)
                {

                }
            });
            $(this).closest('.todolist_list').find('.todotext').html(text1);
            $(this).html("<span class='fa fa-pencil'></span>");
            $(this).closest('.todolist_list').find(".showbtns").toggleClass("opacityfull");
            return;
        }
        var text = '';
        text = $(this).closest('.todolist_list').find('.todotext').text();
        text = "<input type='text' name='text' value='" + text + "' onkeypress='return event.keyCode != 13;' />";
        $(this).closest('.todolist_list').find('.todotext').html(text);
        $(this).html("<span class='fa fa-check'></span> ");
        text = '';
        return;
    });

    $("#custom_textbox").on("keypress", function(e) {
        if (e.which === 32 && !this.value.length)
            e.preventDefault();
    });
});
