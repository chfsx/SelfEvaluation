printFeedback = function () {
    this.originalBodyWidht = $("body").width();

    window.onafterprint = function (e) {
        $(window).off('mousemove', window.onafterprint);
        $("body").width(this.originalBodyWidht);
        console.log("On After Print");
    };
   //make sure mainbar-slates not too big
  $(".il-maincontrols-mainbar").css("width", "80px");

    $.when($("body").width(800)).then(
        function () {
            setTimeout(
                function () {
                    window.print();
                    setTimeout(function () {
                        $(window).one('mousemove', window.onafterprint);
                        console.log("On After One");

                    }, 1)
                  $(".il-maincontrols-mainbar").css("width", "");// normal size again
                }, 500)
        }
    );
};