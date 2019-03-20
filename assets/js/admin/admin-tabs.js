/**
 * Plugin: Tabs
 */
;(function ($) {
    var adminTabs = function ($el, options) {
        var $tabs = $el.find('.tabs-nav').find('li'),
            $tabsWrap = $tabs.parent(),
            $contents = $el.find('.tabs-content-container > li'),
            $currentTab = null,
            $currentContent = null;

        function selectTab($tab) {
            var index = $tabs.index($tab),
                url = $tab.find('a').attr('href');

            $currentContent = $contents.eq(index);

            $tab.addClass('active').siblings('li.active').removeClass('active');
            $currentContent.show().css({visibility: 'hidden'});
            calculateHeight($currentContent);
            $currentContent.hide();
            $currentContent.show();
            $currentContent.siblings('li.active').fadeOut(0, function () {
                $currentContent.addClass('active').siblings('li.active').removeClass('active');
            });
            if ($tab.hasClass('lp-virtual-functions-adv-tab')) {
                $tab.parent().find('.lp-virtual-functions-li').show();
                $('.lp-first-in-list').addClass('active');
            } else if (!$tab.hasClass('lp-virtual-functions-li')) {
                $tab.parent().find('.lp-virtual-functions-li').hide();
            }
            LP.setUrl(url);
        }

        function calculateHeight($currentContent) {

            var contentHeight = $currentContent.height(),
                tabsHeight = $tabsWrap.outerHeight();

            if (contentHeight < tabsHeight) {
                contentHeight = tabsHeight + parseInt($tabsWrap.css('margin')) * 2;
            } else {
                contentHeight = undefined;
            }
            $currentContent.css('visibility', '').css({height: contentHeight});
        }

        function selectDefaultTab() {
            $currentTab = $tabs.filter('.active');
            if (!$currentTab.length) {
                $currentTab = $tabs.first();
            }
            console.log($currentTab);
            $currentTab.find('a').trigger('click');
            if ($currentTab.hasClass('lp-virtual-functions-adv-tab')) {
                $('.lp-first-in-list').addClass('active');
            }
            if ($currentTab.hasClass('lp-virtual-functions-adv-tab') || $currentTab.hasClass('lp-virtual-functions-li')) {
                $currentTab.parent().find('.lp-virtual-functions-li').show();
            }
        }

        function initEvents() {
            $el.on('click', '.tabs-nav a', function (event) {
                event.preventDefault();
                $currentTab = $(this).parent();
                selectTab($currentTab);
            });
        }

        function init() {
            initEvents();
            selectDefaultTab();
            $(window).on('resize.calculate-tab', function () {
                var $currentContent = $el.find('.tabs-content-container .active').css('height', '');
                calculateHeight($currentContent);
            })
        }

        init();
    };
    $.fn.lpAdminTab = function (options) {
        options = $.extend({}, options || {});
        return $.each(this, function () {
            var $el = $(this),
                tabs = $el.data('learn-press/tabs');

            if (!tabs) {
                tabs = new adminTabs($el, options);
                $el.data('learn-press/tabs', tabs)
            }
            return $el;
        })
    };

    var $doc = $(document);
    $doc.ready(function () {
        $('.learn-press-tabs').lpAdminTab();
    });
})(jQuery);