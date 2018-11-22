/**
 * Vue instance to load all items inside course.
 *
 * @author ThimPress
 * @package LearnPress/Js
 * @version 3.x.x
 */
(function ($) {
    window.LP = window.LP || {};

    LP.$vms = LP.$vms || {};

    LP.$vms['courseContent'] = {
        el: '#learn-press-content-item',
        data: function () {
            return {
                loaded: false,
                courseLoaded: false,
                currentItem: {},
                canNextItem: true,
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

            LP_Event_Bus.$on('completed-item', function (item) {
                $vm.canNextItem = true;
            });

            $(document).on('LP.click-curriculum-item', function (e, data) {
                data.$event && data.$event.preventDefault();
                $vm.currentItem = data.item;
            }).ready(function () {
                setTimeout(function () {
                    $vm.loaded = true;
                }, 100);
                //
            });

            $(document).on('LP.loaded-components', function () {
                $vm.courseLoaded = true;
            })

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
            /**
             * Complete lesson button
             *
             * @param e
             * @private
             */
            _completeItem: function (e) {
                this.canNextItem = false;
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
    };

    LP.$vComponents = LP.$vComponents || {};

    var componentDefaults = {
        props: ['item', 'isCurrent', 'currentItem', 'canNextItem', 'itemId'],
        methods: {
            _nextItem: function (e) {
                LP_Event_Bus.$emit('next-item', {$event: e});
            },
            $: function (selector) {
                return selector ? $(this.$el).find(selector) : $(this.$el);
            },
            $courseStore: function () {
                var $store = window.$courseStore;
                if (prop) {
                    if (arguments.length == 2) {
                        $store.getters[prop] = value;
                    } else {
                        return $store.getters[prop];
                    }
                }

                return $store.getters['all'];

                // var $store = window.$courseStore;
                //
                // if (!$store) {
                //     return undefined;
                // }
                //
                // if (prop) {
                //     if (arguments.length == 2) {
                //         $store.getters['all'][prop] = value;
                //     } else {
                //         return $store.getters['all'][prop]
                //     }
                // }
                //
                // return $store.getters['all'];
            }
        }
    }

    LP.$vComponents['lp-course-item'] = $.extend({}, componentDefaults, {
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
    });

    LP.$vComponents['lp-course-item-lp_lesson'] = $.extend(true, {}, componentDefaults, {
        data: function () {
            return {
                countdownNextItem: false
            }
        },
        mounted: function () {
            var $vm = this;

            LP_Event_Bus.$on('countdown-next-item', function (data) {
                if (!$vm.item) {
                    return;
                }
                if (data.item.id == $vm.itemId) {
                    if (!data.time) {
                        return $vm.countdownNextItem = false;
                    }
                    $vm.countdownNextItem = data.time + 1;
                }
            });

            LP_Event_Bus.$on('next-item', function () {
                $vm.countdownNextItem = false;
            });

            this.$('.button-completex').hover(function () {
                if ($vm.item.status === 'completed') {
                    $(this).html(LP.l10n.translate('Mark Uncompleted'));
                } else {
                    //$(this).html(LP.l10n.translate('Mark Completed'));
                }

            }, function () {
                if ($vm.item.status === 'completed') {
                    $(this).html(LP.l10n.translate('Completed'));
                } else {
                    $(this).html(LP.l10n.translate('Complete'));
                }
            })
        },
        methods: {
            isShowContent: function () {
                return true;
            }
        }
    });

    console.log(LP.$vComponents['lp-course-item-lp_lesson']);

    $(document).ready(function () {
        var c, $vms = LP.$vms, $vComponents = LP.$vComponents;

        window.$courseStore = new LP.Course_Store(lpVmCourseData);

        LP.$ajaxRequest = new LP.Request($courseStore, {courseId: $courseStore.getters['all'].courseId});

        for (c in $vComponents) {
            if (!$vComponents.hasOwnProperty(c)) {
                continue;
            }

            Vue.component(c, $vComponents[c]);
        }

        for (c in $vms) {
            if (!$vms.hasOwnProperty(c)) {
                continue;
            }

            $vms[c] = new Vue($vms[c]);
        }

        $(document).trigger('LP.loaded-components');
    });

    $(document).on('LP.loaded-components', function () {
        $('#learn-press-course').addClass('ready');
    });


    if (!window.$) {
        window.$ = jQuery;
    }

})(jQuery);
