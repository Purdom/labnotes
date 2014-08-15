var nav = responsiveNav(
    "header nav",
    {
        label: "☰ Menu"
    }
);

jQuery(function($){
    var header, windowHeight, adminBarHeight, bannerHeight, headerHeight;

    header = $('.singular [role=main] article header');
    $(header).wrapInner('<div>');

    adminBarHeight = $('#wpadminbar').height();
    bannerHeight = $('[role=banner]').height();

    $(window).bind("load resize", function(){
        winHeight = $(window).height();

        headerHeight = winHeight - (adminBarHeight + bannerHeight);
        $(header).css({'height':headerHeight});
    });
});