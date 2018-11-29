/**
 * Course curriculum.
 *
 * @author ThimPress
 * @package LearnPress/Js
 * @version 3.x.x
 */
(function ($) {
    // Global LP
    window.LP = window.LP || {};

    // BUS
    window.LP_Event_Bus = new Vue();

    LP.$vms = LP.$vms || {};

    LP.$vms['courseCurriculum'] = {
        el: '#learn-press-course-curriculum',
        data: function () {
            return {
                ready: false,
                delayNextItem: false,
                timerCountdownNextItem: false
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


            LP_Event_Bus.$on('complete-item', this._completeItem);
            LP_Event_Bus.$on('completed-item', this._onCompletedItem);
            LP_Event_Bus.$on('next-item', this._onNextItem);
            LP_Event_Bus.$on('move-to-item', this._onMoveToItem);
        },
        methods: {
            /**
             * Mark item is completed or remark is un-completed
             *
             * @param item
             */
            completeItem: function (item) {
                var $vm = this;
                item = item || this.currentItem;

                LP.$ajaxRequest(false, 'complete-course-item', {
                    itemId: item.id,
                    status: item.status !== 'completed'
                }).then(function (r) {
                    if (r.classes) {
                        item.classes = $(r.classes).filter(function (a, b) {
                            return -1 === $.inArray(b, ['current']);
                        }).get();
                    }
                    item.completed = r.completed;
                    item.status = r.status;

                    $vm.$courseStore().results = r.results;

                    LP_Event_Bus.$emit('completed-item', item);
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

                cls.push('course-item-' + item.type);
                cls.push('course-item-' + item.id);

                if (this.currentItem && this.currentItem.id == item.id) {
                    cls.push('current');
                }

                return cls;
            },
            getItem: function (itemId) {
                return this.$courseStore('getItem')(itemId);
            },
            getNextItem: function (current, onlyUncompleted) {
                var $vm = this,
                    allItems = this.$courseStore('allItems'),
                    nextItem,
                    at = allItems.findIndex(function (it) {
                        return it.id == (current || $vm.currentItem.id);
                    });

                if (at >= 0 && at < allItems.length - 1) {
                    if (onlyUncompleted) {
                        for (var i = at + 1; i < allItems.length; i++) {
                            if (allItems[i].status !== 'completed') {
                                nextItem = allItems[i];
                                break;
                            }
                        }
                    } else {
                        nextItem = allItems[at + 1];
                    }
                }

                return nextItem;
            },
            cancelNextItem: function () {
                this.delayNextItem && clearTimeout(this.delayNextItem);
            },
            countdownToNextItem: function (time, cb) {
                var $vm = this;
                this.timerCountdownNextItem && clearTimeout(this.timerCountdownNextItem);
                if (time) {
                    this.timerCountdownNextItem = setTimeout(function ($vm) {
                        $vm.countdownToNextItem(--time, cb);
                        cb({item: $vm.$courseStore().currentItem, time: time});
                    }, 1000, this);
                } else {
                    cb({item: $vm.$courseStore().currentItem, time: time});
                }
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
                this.cancelNextItem();
                this.$courseStore().currentItem = item;
                this.timerCountdownNextItem && clearTimeout(this.timerCountdownNextItem);

                if (undefined !== $(document).triggerHandler('LP.click-curriculum-item', {
                        $event: e,
                        item: item,
                        $vm: this
                    })) {
                    e && e.preventDefault();
                }
            },
            _completeItem: LP.debounce(function (data) {
                this.completeItem(data.item || this.currentItem);
            }, 300),
            _onCompletedItem: function (item) {
                console.log(item, this.$courseStore().autoNextItem)
                if (item.status === 'completed') {
                    this._nextItem();
                }
            },
            _onNextItem: function (data) {
                this._nextItem(data.$event, 0);
            },
            _onMoveToItem: function (data) {
                this.$courseStore().currentItem = data.item;
            },
            _nextItem: function (e, delay) {
                var isNext = this.$courseStore().autoNextItem,
                    onlyUncompleted;

                if (isNext) {
                    function x($vm, onlyUncompleted) {
                        var nextItem = $vm.getNextItem(false, onlyUncompleted);
                        if (nextItem) {
                            $vm._openItem(null, nextItem);
                        }
                    }

                    if (delay === undefined) {
                        if ($.isPlainObject(isNext)) {
                            delay = isNext.delay;
                            onlyUncompleted = isNext.onlyUncompleted;
                        } else {
                            delay = isNext > 0 ? isNext : 0;
                        }
                    }

                    if (onlyUncompleted === undefined && $.isPlainObject(isNext)) {
                        onlyUncompleted = isNext.onlyUncompleted;
                    }

                    if (delay) {
                        //var nextItem = $vm.getNextItem(false, onlyUncompleted);
                        //if(nextItem) {
                        this.countdownToNextItem(parseInt(delay / 1000), function (data) {
                            LP_Event_Bus.$emit('countdown-next-item', data);
                        });
                        //}
                    }

                    this.cancelNextItem();
                    this.delayNextItem = setTimeout(x, delay, this, onlyUncompleted);
                }
            },
            _toggleSectionDesc: function (e, section) {
                var $section = $(e.target).closest('.section-header'),
                    $desc = $section.find('.section-desc');

                $desc.slideToggle();
                Vue.set(section, 'showDesc', !section.showDesc);

                e.preventDefault();
                e.stopPropagation();
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

})(jQuery);
