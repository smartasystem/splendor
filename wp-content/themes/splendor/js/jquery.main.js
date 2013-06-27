/**
 * General jQuery for smakformat.se
 * By: Kristain Erendi, http://reptilo.se
 * Version: 1.0
 * Date: 2013-03-07
 */
jQuery(document).ready(function($){

  //media queries
  $(window).resize(function() {
    var width = $(window).width();
    if (width < 900) {
      $('#head-slogan').hide();
    }
    else {
      $('#head-slogan').show();
    }
    if (width < 780) {
      $('#head-img').hide();
    }
    else {
      $('#head-img').show();
    }
  });    




});