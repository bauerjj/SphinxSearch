


jQuery(document).ready(function($) {
    $('a.SmallButton').click(function() {
            $(this).after('<span class="TinyProgress">&#160;</span>');
    });


//    $(".SearchBox").focus(function(){
//        $(".SecondaryControls").toggle();
//    });
//    $(".SearchBox").blurr(function(){
//        $(".SecondaryControls").toggle();
//    });

});