/**
 * Vue instance to load all items inside course.
 *
 * @author ThimPress
 * @package LearnPress/Js
 * @version 3.x.x
 */
(function ($) {
    function xxx() {
        return new Vue({
            el: '#learn-press-content-item',
            data: function () {
                return {
                    loaded: false,
                    courseLoaded: false,
                    currentItem: {},
                    item: {a: 0}
                }
            },
            computed: {
//                    currentItem: function () {
//                        console.log('currentItem')
//                        return this.$courseStore() ? this.$courseStore().currentItem : {};
//                    },
                abcx: function () {
                    return this.abc();
                }
            },
            watch: {
                courseLoaded: function (newValue) {
                    this.currentItem = this.$courseStore('currentItem');

                    return newValue;
                },
                'currentItem.id': function (a, b) {
                    if (a != b) {
                        LP.setUrl(this.currentItem.permalink);
                        this.$('.content-item-scrollable').scrollTop(0);
                    }
                    return a;
                }
            },
            mounted: function () {
                var $vm = this;
                //this.loaded = true;
                $(document).on('LP.click-curriculum-item', function (e, data) {
                    data.$event.preventDefault();
                    $vm.currentItem = data.item;
                }).ready(function () {
                    setTimeout(function () {
                        $vm.loaded = true;
                    }, 100);
                    //
                });
            },
            methods: {
                getComponent: function (type) {
                    var component = 'lp-course-item-' + type,
                        refComponent = $(document).triggerHandler('LP.get-course-item-component', component, {type: type});

                    if (refComponent) {
                        component = refComponent;
                    }
                    if (!Vue.options.components[component]) {
                        component = 'lp-course-item';
                        console.log('Vue component ' + component + ' does not exist.');
                    }
                    return component;
                },
                abc: function () {
                    return Math.random();
                },
                isShowItem: function (itemId) {

                    if (!this.loaded) {
                        return false;
                    }

                    return this.currentItem.id == itemId;
                },
                mainClass: function () {
                    var cls = [this.$().attr('data-classes') || '']

                    if (this.loaded) {
                        cls.push('ready');
                    }

                    cls.push(this.currentItem.type)

                    return cls;
                },
                getItem: function (itemId) {
                    return this.$courseStore('getItem')(itemId) || {};
                },
                _completeItem: function (e) {
                    //$(document).trigger('LP.complete-item', {$event: e, item: this.currentItem});
                    LP_Event_Bus.$emit('complete-item', {$event: e, item: this.currentItem});
                },
                $: function (selector) {
                    return selector ? $(this.$el).find(selector) : $(this.$el);
                },
                $courseStore: function (prop, value) {

                    var $store = window.$courseStore;
                    if (prop) {
                        if (arguments.length == 2) {
                            $store.getters[prop] = value;
                        } else {
                            return $store.getters[prop];
                        }
                    }

                    return $store.getters['all'];

                    var $store = window.$courseStore;

                    if (!$store) {
                        return undefined;
                    }

                    if (prop) {
                        if (arguments.length == 2) {
                            $store.getters['all'][prop] = value;
                        } else {
                            return $store.getters['all'][prop]
                        }
                    }

                    return $store.getters['all'];
                }
            }
        });
    }

    // <
    //     ? php readfile(LP_PLUGIN_PATH.
    // '/assets/js/frontend/vm/quiz.js'
    // )
    // ;
    //     ?
    // >
    var lpQuizQuestions = {};
    var componentDefaults = {
        props: ['item', 'isCurrent', 'currentItem'],
    }
    Vue.component('lp-course-item', $.extend({}, componentDefaults, {
        getComponent: function (type) {
            var component = 'lp-course-item-' + type,
                refComponent = $(document).triggerHandler('LP.get-course-item-component', component, {type: type});

            if (refComponent) {
                component = refComponent;
            }
            if (!Vue.options.components[component]) {
                component = 'lp-course-item';
                console.log('Vue component ' + component + ' does not exist.');
            }
            return component;
        },
    }));
    Vue.component('lp-course-item-lp_lesson', $.extend({}, componentDefaults, {
        methods: {
            isShowContent: function () {
                return true;
            }
        }
    }));


    var $vm = xxx();

    $(document).on('course-ready', function () {
        $vm.courseLoaded = true;
    });

    window.$vmContentItem = $vm;

})(jQuery);
