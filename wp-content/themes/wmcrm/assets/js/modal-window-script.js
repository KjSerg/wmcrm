function openWindow($window){
    $window.addClass('active');
    $('body').addClass('open-window');
}
function closeWindow($window){
    $window.removeClass('active');
    $('body').removeClass('open-window');
}