/**
 * Vuex Store for LP Course
 *
 * @author ThimPress
 * @package LearnPress/Js
 * @version 3.x.x
 */
(function ($) {
    window.LP = window.LP || {};

    window.LP.Course_Store = function (data) {
        var state = data;
        var allItems = false;

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
                            return a.id == itemId;
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
            courseId: function (state) {
                return state.courseId;
            },
            allItems: function () {
                if (allItems === false) {
                    allItems = [];
                    for (var i = 0, n = state.sections.length; i < n; i++) {
                        allItems = allItems.concat(state.sections[i].items || []);
                    }
                }
                return allItems;
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
    }
})(jQuery);