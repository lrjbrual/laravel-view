// function deleteCampaign(value){
//   var id = value.getAttribute('data-campaign-id');
//   // disable button to avoid double clicking
//   $("#deleteCampaign" + id).prop('disabled', true);
//   htmlStr = '<br />'
//   htmlStr += '<div class="alert alert-success alert-dismissable">';
//   htmlStr += '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
//   htmlStr += 'Successfully deleted campaign #'+id+'.';
//   htmlStr += '</div>';
//
//   $.ajax({
//       url: "/campaign/deleteCampaign",
//       type: "post",
//       data: {
//         'id': id,
//       },
//       success: function(response){
//         $("#campaign" + id).remove();
//         $("#for-alert").html(htmlStr);
//       }
//   });
// }
