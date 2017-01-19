(function($, dom, w, BS){

    function adjustHPCIIframe(){
        var _iframe_el= jQuery('iframe[name='+hpciCCFrameName+']');
        if(!_iframe_el.length) return false;
        console.log(666);
        console.log(BS.isBreakpoint('xs'));
        console.log(hpciCCFrameFullMobileUrl);
        console.log(_iframe_el);

        if(BS.isBreakpoint('xs')|| BS.isBreakpoint('xs')){
            _iframe_el.attr('src',hpciCCFrameFullMobileUrl);
            _iframe_el.css('height', '100px');

        }
        else
        {            _iframe_el.attr('src',hpciCCFrameFullDesktopUrl);
            _iframe_el.css('height', '70px');
        }

    }
    // DOM has been loaded
    $(dom).ready(function() {
    adjustHPCIIframe();

    });


    // Insert scripts that are supposed to be executed upon each window resize
    $(w).bind('resize', function() {

    BS.waitForFinalEvent(function() {

    adjustHPCIIframe();

    }, BS.clock.fast, BS.timeString.getTime())
    });


    // Insert scripts that are supposed to be executed upon each orientation change.
    $(w).bind('orientationchange', function() {
    BS.waitForFinalEvent(function() {

    adjustHPCIIframe();

    }, BS.clock.fast, BS.timeString.getTime())
    });


    })(jQuery, document, window, ResponsiveBootstrapToolkit);