<span id="tags-child"></span>
@foreach($tags as $tag)
  <i class="{{ $tag->icon }}" aria-hidden="true"></i> <span class="tags">{{ $tag->description }}</span><br/>
@endforeach

<script>
$(document).ready(function() {
  var textarea_id = $("#tags-child").parent().attr('data-body-id');
  var divbody = $("#" + textarea_id);

  $(".tags").click(function(){
      // restore cursor position
      divbody.summernote('editor.restoreRange');
      divbody.summernote('editor.focus');

      divbody.summernote('editor.insertText', '\{\{ ' + $(this).text() + ' \}\}');
      divbody.summernote('editor.saveRange');
  });

  $(function(){
      // Save cursor position
      divbody.summernote('editor.saveRange');
  });
});
</script>
