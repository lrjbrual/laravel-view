<div class="col-md-4 col-sm-12 m-t-25 col-lg-4" style="clear:both;">
  <div class="card to_do">
      <div class="card-header bg-white">
          To Do List
      </div>
      <div class="card-block no-padding to_do_section">
          <div class="row">
              <div class="todo_section">
                  <form class="list_of_items">
                      @foreach($todos as $todo)
                      <div class="todolist_list showactions" data-row-id="todo{{ $todo->id }}" data-id="{{ $todo->id }}">
                          <div class="col-xs-8 nopad custom_textbox1">
                              <div class="todo_mintbadge {{ $todo->color_class }}"></div>
                              <div class="todoitemcheck checkbox-custom">
                                  <input type="checkbox" class="striked large" {{ $todo->is_striked == 1 ? 'checked' : '' }} />
                              </div>
                              <div class="todotext todoitem todo_width {{ $todo->is_striked == 1 ? 'strikethrough' : '' }}">{{ $todo->item }}</div>
                          </div>
                          <div class="col-xs-3 showbtns todoitembtns" style="{{ $todo->is_striked == 1 ? 'display: none;' : '' }}">
                              <a href="#" class="todoedit">
                                  <span class="fa fa-pencil"></span>
                              </a>
                              <span class='dividor'>|</span>
                              <a href="#" class="tododelete redcolor">
                                  <span class="fa fa-trash"></span>
                              </a>
                          </div>
                          <span class="seperator"></span>
                      </div>
                      @endforeach
                  </form>
              </div>
              <form id="main_input_box" class="form-inline">
                  <div class="input-group todo">
                                  <span class="input-group-btn">
                                  <a class="btn btn-primary" tabindex="0" role="button"
                                     data-toggle="popover" data-trigger="focus"
                                     data-contentwrapper=".mycontent" id="btn_color"
                                     data-badge="todo_mintbadge">Color&nbsp;&nbsp; <i
                                              class="fa fa-caret-right"> </i></a>
                                  </span>
                      <input id="custom_textbox" name="Item" type="text" required
                             placeholder="Write and hit enter"
                             class="input-md form-control" size="75"/>
                  </div>
              </form>
          </div>
          <div class="mycontent">
              <div class="border_color bg-danger border_danger"
                   data-color="btn-danger" data-badge="bg-danger"></div>
              <div class="border_color bg-primary border_primary"
                   data-color="btn-primary" data-badge="bg-primary"></div>
              <div class="border_color bg-info border_info" data-color="btn-info"
                   data-badge="bg-info"></div>
              <div class="border_color bg-mint border_mint" data-color="btn-mint"
                   data-badge="bg-mint"></div>
          </div>
      </div>
  </div>
</div>
<script src="{{ url('js/todo.js') }}" charset="utf-8"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $('#dashboardErrorModal').modal('show');
  })
</script>
