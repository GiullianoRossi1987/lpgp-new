// jquery only functions

$(window).on("load", function(){ // maybe replace with a ready function
    $(window).scroll(function(){
        const fadeTime = 500;
        const diff = $(".header-container").offset().top;
        var wBottom = $(this).scrollTop() + $(this).innerHeight() + diff;
        $(".fade-on-scroll").each(function(){
            var objBottom = $(this).offset().top + $(this).outerHeight();
            // debug
            // console.log("obj: "
            //     + Math.ceil(objBottom)
            //     + " | Window: "
            //     + Math.ceil(wBottom)
            // );
            if(objBottom < wBottom){
                if($(this).css("opacity") == 0)
                    $(this).fadeTo(fadeTime, 1);
            }
            else{
                if($(this).css("opacity") == 1)
                    $(this).fadeTo(fadeTime, 0);
            }
        });
        $(".in-fade-on-scroll").children().each(function(){
            var objBottom = $(this).offset().top + $(this).outerHeight();
            // debug
            // console.log("obj: "
            //     + Math.ceil(objBottom)
            //     + " | Window: "
            //     + Math.ceil(wBottom)
            // );
            if(objBottom < wBottom){
                if($(this).css("opacity") == 0)
                    $(this).fadeTo(fadeTime, 1);
            }
            else{
                if($(this).css("opacity") == 1)
                    $(this).fadeTo(fadeTime, 0);
            }
        });
    });
});
