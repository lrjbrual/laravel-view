'use strict';
$(document).ready(function(){
    // Bootstrap switch
    $.each($('.make-switch-radio'), function () {
        $(this).bootstrapSwitch({
            onText: $(this).data('onText'),
            offText: $(this).data('offText'),
            onColor: $(this).data('onColor'),
            offColor: $(this).data('offColor'),
            size: $(this).data('size'),
            labelText: $(this).data('labelText')
        });
    });

    // Switchery
    new Switchery(document.querySelector('.sm_toggle_checked'), { size: 'small', color: '#ff5722', jackColor: '#fff' });
    new Switchery(document.querySelector('#refunds-switch'), { size: 'small', color: '#ff5722', jackColor: '#fff' });
});
