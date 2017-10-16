<span id="tags-child"></span>
@foreach($tags as $tag)
  <i class="{{ $tag->icon }}" aria-hidden="true"></i> <span class="tags">{{ $tag->description }}</span><br/>
@endforeach

<script>
$(document).ready(function() {
    var textbox_id = $("#tags-child").parent().attr('data-template-subject-id');

    $(".tags").click(function(){
        $("#" + textbox_id).insertAtCaret('\{\{ ' + $(this).text() + ' \}\}');
    });

    jQuery.fn.extend({
        insertAtCaret: function(myValue){
          return this.each(function(i) {
            if (document.selection) {
              //For browsers like Internet Explorer
              this.focus();
              sel = document.selection.createRange();
              sel.text = myValue;
              this.focus();
            }
            else if (this.selectionStart || this.selectionStart == '0') {
              //For browsers like Firefox and Webkit based
              var startPos = this.selectionStart;
              var endPos = this.selectionEnd;
              var scrollTop = this.scrollTop;
              this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
              this.focus();
              this.selectionStart = startPos + myValue.length;
              this.selectionEnd = startPos + myValue.length;
              this.scrollTop = scrollTop;
            } else {
              this.value += myValue;
              this.focus();
            }
          })
        }
    });
});
</script>
