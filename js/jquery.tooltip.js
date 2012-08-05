/*
 * tooltip that shows the discussion body text (orignal poster's text)
 */

jQuery(document).ready(function($) {

        function hoverIntent() {
            var offset = $(this).offset();
            $(this).find('.ToolTip').fadeIn(200).addClass('ShowToolTip');
            $(this).find('.ToolTip').css('left', offset.left + 'px');
        }
        function hoverHide(){
            $(this).find('.ToolTip').fadeOut(200);
        }

        $(".HasToolTip").hoverIntent({
            sensitivity: 3, // How sensitive the hoverIntent should be
            interval: 200, // How often in milliseconds the onmouseover should be checked
            over: hoverIntent, // Function to call when mouseover is called
            timeout: 300, // How often in milliseconds the onmouseout should be checked
            out: hoverHide // Function to call when mouseout is called

        });

});
