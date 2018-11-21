/**
 * Course curriculum.
 *
 * @author ThimPress
 * @package LearnPress/Js
 * @version 3.x.x
 */
(function ($) {
    // BUS
    window.LP_Event_Bus = new Vue();

    function xxx() {
        var $request = null;
        var vueConfig = {
            el: '#learn-press-course-curriculum',
            data: function () {
                return {
                    ready: false
                }
            },
            created: function () {
            },
            computed: {
                currentItem: function () {
                    return this.$courseStore('currentItem');
                }
            },
            watch: {},
            mounted: function () {
                this.totalItems = $.map(this.sections, function (a) {
                    return a.items.length;
                }).sum();
                this._$request = $request;
                LP_Event_Bus.$on('complete-item', this._completeItem);
            },
            methods: {
                completeItem: function (item) {
                    var $vm = this;
                    item = item || this.currentItem;

                    $request(false, 'complete-course-item', {itemId: item.id}).then(function (r) {
                        if (r.classes) {
                            item.classes = $(r.classes).filter(function (a, b) {
                                return -1 === $.inArray(b, ['current']);
                            }).get();
                        }
                        item.completed = r.completed;
                        $vm.$courseStore().results = r.results;
                    });
                },
                sectionClass: function (section) {
                    var cls = ['section'];

                    return cls;
                },
                sectionHtmlId: function (section) {
                    return 'section-' + section.id;
                },
                countItems: function (section) {
                    return this.$courseStore('countItems')(section);
                },
                getProgressStyles: function (section) {
                    return {
                        left: this.getPercentCompleted(section) * 100 + '%'
                    }
                },
                countCompletedItems: function (section) {
                    return this.$courseStore('countCompletedItems')(section);
                },
                getPercentCompleted: function (section) {
                    var completed = this.countCompletedItems(section),
                        total = this.countItems(section);

                    if (total) {
                        return completed / total;
                    }

                    return 0;
                },
                getSectionCountItemsHtml: function (section) {
                    return this.countCompletedItems(section) + '/' + this.countItems(section);
                },
                sectionItemClass: function (item, section) {
                    var cls = $(this.vmArray2Array(item.classes)).filter(function (a, b) {
                        return -1 === $.inArray(b, ['current']);
                    }).get();
                    console.log(item)

                    cls.push('course-item-' + item.type);
                    cls.push('course-item-' + item.id);

                    if (this.currentItem && this.currentItem.id == item.id) {
                        cls.push('current');
                    } else {

                    }

                    return cls;
                },
                getItem: function (itemId) {
                    return this.$courseStore('getItem')(itemId);
                },
                vmArray2Array: function (a) {
                    var r = [];
                    for (var i in a) {
                        if (isNaN(i)) {
                            break;
                        }
                        r.push(a[i])
                    }

                    return r;
                },
                _openItem: function (e, item) {
                    this.$courseStore().currentItem = item;
                    if (undefined !== $(document).triggerHandler('LP.click-curriculum-item', {
                            $event: e,
                            item: item,
                            $vm: this
                        })) {
                        e.preventDefault();
                    }
                },
                _completeItem: LP.debounce(function (data) {
                    this.completeItem(data.item || this.currentItem);
                }, 300),
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
                },
                endTime: function (sectionIndex, itemIndex) {
                    var sections = this.$courseStore().sections;
                    if (!this.ready && sectionIndex == sections.length - 1 && itemIndex == sections[sectionIndex].items.length - 1) {
                        this.ready = true;
                        $(document).trigger('course-ready', {$vm: this})
                    }
                }
            }
        };
        //window.LP_Course_Settings = <?php echo json_encode( learn_press_get_course_curriculum_for_js( $course->get_id() ) );?>

        var yyy = function (data) {
            window.$courseStore = (function (data) {
                var state = data;

                var getters = {
                    totalItems: function (state) {
                        return $.map(state.sections, function (a) {
                            return a.items.length;
                        }).sum();
                    },
                    countItems: (function (state) {
                        return function (section) {
                            if (!section) {
                                return state.totalItems;
                            }

                            return $.map([section], function (s) {
                                return s.items.length;
                            }).sum()
                        }
                    }),
                    countCompletedItems: (function (state) {
                        return function (section) {
                            if (!section) {
                                section = state.sections;
                            } else {
                                section = [section];
                            }

                            return $.map(section, function (s) {
                                return $.grep(s.items, function (i) {
                                    return i.completed;
                                }).length;
                            }).sum()
                        }
                    }),
                    currentItem: function (state) {
                        if (!$.isPlainObject(state.currentItem)) {
                            for (var i = 0, n = state.sections.length; i < n; i++) {
                                var item = state.sections[i].items.find(function (a) {
                                    return a.id == state.currentItem;
                                });

                                if (item) {
                                    state.currentItem = item;
                                    break;
                                }
                            }
                        }
                        return state.currentItem;
                    },
                    getItem: function (state) {
                        return function (itemId) {
                            for (var i = 0, n = state.sections.length; i < n; i++) {
                                var item = state.sections[i].items.find(function (a) {
                                    return a.id == state.currentItem;
                                });

                                if (item) {
                                    return item;
                                }
                            }
                        }
                    },
                    identify: function (state) {
                        return state.identify;
                    },
                    rootUrl: function (state) {
                        return state.rootUrl || '';
                    },
                    all: function (state) {
                        return state;
                    }
                };
                var mutations = {};
                var actions = {};


                return new Vuex.Store({
                    state: state,
                    getters: getters,
                    mutations: mutations,
                    actions: actions
                });
            })(data);

            $request = window.$request = new LP.Request($courseStore, {courseId: LP_Course_Settings.courseId});
            window.$vmCourse = new Vue(vueConfig);
        };
        yyy(LP_Course_Settings)
    }

    $(document).ready(function () {
        xxx();
    })

    if (!window.$) {
        window.$ = jQuery;
    }

})(jQuery);
