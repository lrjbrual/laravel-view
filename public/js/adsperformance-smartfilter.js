$(document).ready(function() {
    getDropDownData();
    $('#filter_camp_name').change(function(){
      setAdgroupDropdown();
    });
    $("#filter_country, #filter_camp_type").change(function(){
      setCampaignDropdown();
    });
});
  var campaign_list;
  var cct;
  var adgrouplist;
  var cid_adg;
  var country_list;
  var targetingtype = ["Automatic","Manual"];
  var display_campaign = [];
  var display_campaignid = [];
  var display_adgroup = [];
  function getDropDownData(){
    $.ajax({
      url: 'getDropDownData', 
      type: 'GET', 
      success: function(result){
        var res = jQuery.parseJSON(result);
        campaign_list = res.campaign_list;
        cct = res.cct;
        //adgrouplist = res.adgrouplist;
        cid_adg = res.cid_adg;
        country_list = res.country_list;
        setCampaignDropdown();
      } 
    })
  }

  function setCampaignDropdown(){
    display_campaignid=[];
    display_campaign = [];
    
    var countries = $("#filter_country").val();
    var ttype = $("#filter_camp_type").val();
    if(countries == null){
      countries = country_list;
    }else{
      for(var x=0; x< countries.length; x++){
        var c = countries[x].split('|');
        countries[x] = c[0].toLowerCase();
      }
    }
    if(ttype == null) ttype = targetingtype;
    for(var x=0; x < ttype.length; x++){
      if(ttype[x] == 'Automatic') ttype[x]='auto';
      if(ttype[x] == 'Manual') ttype[x]='manual';
    }
    //console.log(countries);
    //console.log(ttype);
    for(x in countries){
      for (i in ttype) {
        if(cct[countries[x]][ttype[i]]){
          cct_list = cct[countries[x]][ttype[i]];
          for(c in cct_list){
            var cid = cct_list[c];
            display_campaignid.push(cid);
            display_campaign.push(campaign_list[cid]);
          }
        }
      }
    }
    //console.log(display_campaignid);
    display_campaign = Array.from(new Set(display_campaign));
    display_campaign.sort();
    //console.log(display_campaign);
    $('#filter_camp_name').html("");
    //$('#recommendation_campaign').append('<option value="select">Select All</option>');
    for(index in display_campaign){
      $('#filter_camp_name').append('<option value="'+display_campaign[index]+'">'+display_campaign[index]+'</option>');
    }
    $("#filter_camp_name").trigger("chosen:updated");
    setAdgroupDropdown(); 
  }
  function setAdgroupDropdown(){
    display_adgroup=[];
    var campaigns = $('#filter_camp_name').val();
    if(campaigns == null) campaigns = display_campaign;
    for(x in campaigns){
      cid = campaigns[x];
      for(a in cid_adg[cid])
        display_adgroup.push(cid_adg[cid][a]);
    }

    display_adgroup = Array.from(new Set(display_adgroup));
    display_adgroup.sort();
    //console.log(display_adgroup);
    $('#filter_ad_group').html("");
    for(index in display_adgroup){
      $('#filter_ad_group').append('<option value="'+display_adgroup[index]+'">'+display_adgroup[index]+'</option>');
    }
    $("#filter_ad_group").trigger("chosen:updated");
  }

  
function set_bid_graph(data){

  css1 = { 
    height: '0',
    width: '4px',
    border: '5px solid #0fb0c0' }

  css2 = { 
    height: '0',
    width: '4px',
    border: '5px solid #FF9933' }

  $('.bidLegend').find('.bidLegend1').css(css1)
  $('.bidLegend').find('.bidLegend2').css(css2)
  
  var data_imp = {
            labels: ['IMP'],
            series: [
                [data.imp.before],
                [data.imp.after]
            ]
    };
    var option_imp = {
        seriesBarDistance: 20,
        axisX: {
            offset: 60
        },
        axisY: {
            offset: 80,
            labelInterpolationFnc: function(value) {
                return value + ''
            },
            scaleMinSpace: 30
        }
    };
    var chart6= new Chartist.Bar('#impGraphProdCost', data_imp, option_imp);
    new Chartist.Bar('#impGraphProdCost', data_imp, option_imp);
    //end imp

    //click
  var data_click = {
            labels: ['Click'],
            series: [
                [data.click.before],
                [data.click.after]
            ]
    };
    var option_click = {
        seriesBarDistance: 20,
        axisX: {
            offset: 60
        },
        axisY: {
            offset: 80,
            labelInterpolationFnc: function(value) {
                return value + ''
            },
            scaleMinSpace: 30
        }
    };
    var chart6= new Chartist.Bar('#impClickProdCost', data_click, option_click);
    new Chartist.Bar('#impClickProdCost', data_click, option_click);
    //end click

    //acos
  var data_acos = {
            labels: ['Acos'],
            series: [
                [data.acos.before],
                [data.acos.after]
            ]
    };
    var option_acos = {
        seriesBarDistance: 20,
        axisX: {
            offset: 60
        },
        axisY: {
            offset: 80,
            labelInterpolationFnc: function(value) {
                return value + '%'
            },
            scaleMinSpace: 30
        }
    };
    var chart6= new Chartist.Bar('#impAcosProdCost', data_acos, option_acos);
    new Chartist.Bar('#impAcosProdCost', data_acos, option_acos);
    //end acos
}