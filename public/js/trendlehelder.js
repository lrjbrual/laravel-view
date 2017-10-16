/*
Author jason 07-10-17
*/

/*
added by jason 07-10-17
param 
 - inputType (string)
 - value (array)
 - elemClass (string)
 - textToDisplay (string)
 - nthChild (string)
 - url (string)
 - data (array)
return html
*/
function generateInput(inputType,value,elemClass,textToDisplay,nthChild,url,data,column,from){
  switch (inputType){
    case 'textbox':
        textToDisplay = ($(elemClass).text() != textToDisplay ) ? $(elemClass).text() : ''
        nthChild.append('<input type="text" class="inputTb" style="width:100%" value="'+textToDisplay+'">')
        $('.inputTb').focus()

        $('.inputTb').keypress(function (e) {
          if (e.which == 13) {
            newVal = { newval: $(this).val() }
            newdata = Object.assign({}, data, newVal);
            saveEditCell(url,newdata,elemClass,$(this),nthChild,column,from)
              
            e.preventDefault();   
          }
        });

        $('.inputTb').focusout(function() {
          $(this).remove()
          $(elemClass).show();
        })
    break;
  }
}

function saveEditCell(url,data,elemClass,elemTb,nthChild,column,from){
  $.ajax({ url: url, type: 'POST', data: data , success:function(result){
    var response = jQuery.parseJSON(result);

      if( ( column == 13 || column == 14 ) && from == 'order'){
        nthChild.parent().children(':nth-child(19)').html(response.rid1)
        nthChild.parent().children(':nth-child(20)').html(response.rid2)
        nthChild.parent().children(':nth-child(21)').html(response.rid3)
        nthChild.parent().children(':nth-child(22)').html(response.tar)
        nthChild.parent().children(':nth-child(23)').html(response.dif)
      }

      if( ( column == 19 || column == 21 ) && from == 'fnsku'){
        nthChild.parent().children(':nth-child(24)').html(response.rid1)
        nthChild.parent().children(':nth-child(25)').html(response.rid2)
        nthChild.parent().children(':nth-child(26)').html(response.rid3)
        nthChild.parent().children(':nth-child(27)').html(response.tar)
        nthChild.parent().children(':nth-child(28)').html(response.dif)
      }

      $(elemClass).text(response.value);
      $(elemTb).hide()
      $(elemClass).show()
  } })
}

function ValidateMinMax(element,digit){
    pattern =/^[0-9]{1,10}$/;
    validatate = pattern.test(+$(element).val());
    if (!validatate) {
        $(element).css('border', '1px solid red');
        element.value = element.value.replace(/[^0-9]/g, '');
        if ($(element).val().length >= digit ) {
            element.value = element.value.substring(0, 11);
            $(element).css('border', '1px solid #D9D9D9');
        }
        sweetAlert("Invalid Input", "Please input number only and not greater than of 12 digits", "error");
    }else{
        $(element).css('border', '1px solid #D9D9D9');
    }
}

function validateNumberOnly(val){
    pattern = /^\d+$/;
    validatate = pattern.test(val);
    if (!validatate) {
        return true
    }else{
        return false
    }
}

function roundNumber(num, scale) {
  var number = Math.round(num * Math.pow(10, scale)) / Math.pow(10, scale);
  if(num - number > 1) {
    return (number + Math.floor(2 * Math.round((num - number) * Math.pow(10, (scale + 1))) / 10) / Math.pow(10, scale));
    }else{
    return number;
    }
}


function validateNumberPositiveOnly(val){
    pattern = /^(?:\d*\.\d{1,2}|\d+)$/;
    validatate = pattern.test(val);
    if (!validatate) {
        return false;
    }else{
        return true;
    }
}

function validateNumberPositiveNegative(val){
    pattern = /^-?[0-9]\d*(\.\d{1,2}|\d+)?$/;
    validatate = pattern.test(val);
    if (!validatate) {
        return false;
    }else{
        return true;
    }
}
