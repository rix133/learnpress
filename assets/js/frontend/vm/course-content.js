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
                item: {a: 0},
                prevItem: {},
                nextItem: {}
            }
        },
        computed: {
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
                if (a != b && this.currentItem) {

                    //LP.debounce(function () {
                    var $target = $('#content-item-' + a + ' .item-login-register-form');
                    if ($target.length) {
                        $('#course-item-login-register-form').appendTo($target);
                    }
                    //}, 10, this)()


                    LP.setUrl(this.currentItem.permalink);
                    this.$('.content-item-scrollable').scrollTop(0);
                }

                this.getNavItems();
                return a;
            }
        },
        mounted: function () {
            var $vm = this;

            LP_Event_Bus.$on('completed-item', function (item) {
                $vm.canNextItem = true;
            });

            LP_Event_Bus.$on('next-item', function () {
                console.log($vm.currentItem)
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
                    console.log('Please implement the Vue component ' + component + '.');
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

                cls.push(this.currentItem.type);

                return cls;
            },
            getItem: function (itemId) {
                return this.$courseStore('getItem')(itemId) || {};
            },
            getNavItems: function () {
                var $vm = this,
                    items = this.$courseStore('allItems'),
                    currentAt = items.findIndex(function (it) {
                        return it.id == $vm.currentItem.id;
                    });

                if (currentAt > 0) {
                    this.prevItem = items[currentAt - 1];
                } else {
                    this.prevItem = false;
                }

                if (currentAt < items.length - 1) {
                    this.nextItem = items[currentAt + 1];
                } else {
                    this.nextItem = false;
                }
            },
            canCompleteItem: function (item) {
                console.log(item);
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
            _nextItem: function (e) {

            },
            _prevItem: function (e) {

            },
            _moveToItem: function (e, itemId) {
                e.preventDefault();
                var items = this.$courseStore('allItems'),
                    at = items.findIndex(function (it) {
                        return it.id == itemId;
                    });
                this.currentItem = items[at];
                LP_Event_Bus.$emit('move-to-item', {item: this.currentItem});
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
            }
        }
    };

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
        watch: {
            'item.id': function (id) {
                console.log(id)
            },
            itemId: function (id) {
                console.log(id)
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

            LP.$vComponents['course-item-' + this.item.id] = this;

            // this.$('.button-completex').hover(function () {
            //     if ($vm.item.status === 'completed') {
            //         $(this).html(LP.l10n.translate('Mark Uncompleted'));
            //     } else {
            //         //$(this).html(LP.l10n.translate('Mark Completed'));
            //     }
            //
            // }, function () {
            //     if ($vm.item.status === 'completed') {
            //         $(this).html(LP.l10n.translate('Completed'));
            //     } else {
            //         $(this).html(LP.l10n.translate('Complete'));
            //     }
            // })
        },
        methods: {
            isShowContent: function () {
                return true;
            },
            canCompleteItem: function (item) {
                return !item.preview;
            }
        }
    });

    LP.$vComponents['course-item-login-register-form'] = $.extend(true, {}, componentDefaults, {
        data: function () {
            return {
                message: '',
                error: false
            }
        },
        mounted: function () {
            this.$('form').on('submit', this.submit);
        },
        methods: {
            submit: function (e) {
                e.preventDefault();
                var $vm = this,
                    data = $(e.target).serializeJSON();

                this.message = '';
                this.error = false;

                return LP.apiRequest(data.api, '', $.extend({}, data)).then(function (response) {
                    if (response.result === 'success') {
                        $vm.message = response.message || 'Logged in';
                        $vm.error = false;

                        LP.reload();
                    } else {
                        $vm.message = response.message || 'Error';
                        $vm.error = true;
                    }
                }, function (response) {
                    $vm.message = response.message || 'Error';
                    $vm.error = true;
                });

            },
            getMessage: function () {
                return this.message;
            },
            _showForm: function (e, form) {
                e.preventDefault();
                switch (form) {
                    case 'login':
                        this.$().css('margin-left', 0);
                        break;
                    default:
                        this.$().css('margin-left', '-100%');
                        break;
                }
            }
        }
    });

    if (!window.$) {
        window.$ = jQuery;
    }

})(jQuery);
