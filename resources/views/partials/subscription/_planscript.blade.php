<script type="text/javascript">
$(document).ready(function() {
    $("input[name='package{{ $pillar->id }}']").click(function(){
        if($(this).attr("data-ischeck") == "yes") {
            $(this).prop('checked', false);
            $(this).removeAttr('checked');
            $(this).attr("data-ischeck", "no");
        } else {
            $("input[name='package{{ $pillar->id }}']").attr("data-ischeck", "no");
            $(this).attr("data-ischeck", "yes");
        }
    });
});
</script>
