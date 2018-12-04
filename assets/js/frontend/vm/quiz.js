/**
 * Quiz Component for LearnPress
 *
 * @author ThimPress
 * @version 3.2.0
 */
;(function ($) {

    /**
     * Shortcut function for translating texts
     *
     * @param text
     * @param a
     * @param b
     * @param c
     * @param d
     * @param e
     * @param f
     * @returns {*}
     */
    var translate = function (text, a, b, c, d, e, f) {
        var args = [];

        for (var i = 0; i < arguments.length; i++) {
            args.push(arguments[i]);
        }
        return LP.l10n ? LP.l10n.translate(text, a, b, c, d, e, f) : text;
    };

    window.LP = window.LP || {};

    LP.$vComponents = LP.$vComponents || {};

    /**
     * Default type of question
     */
    LP.$vComponents['lp-question-type-__default-answers'] = {
        props: ['question', 'answers', 'item'],
        data: function () {
            return {
                answersX: []
            }
        },
        computed: {
            myAnswers: {
                set: function (answer) {
                    this.$emit('update-answer', {id: this.question.id, answer: answer}, 100, 200);
                },
                get: function () {
                    return this.answers[this.question.id] || [];
                }
            }
        },
        methods: {
            getAnswerClass: function (answer) {
                var classes = answer.classes || ['answer-option'];

                return classes;
            },
            _triggerEvent: function (e) {
            },
            disableOption: function () {
                if (this.item.status === 'completed') {
                    return true;
                }

                if (this.question.checked) {
                    return true;
                }

                return false;
            }
        }
    };

    /**
     * Vue Quiz
     *
     * @version 3.x.x
     */
    LP.$vComponents['lp-course-item-lp_quiz'] = {
        props: ['isCurrent', 'currentItem', 'itemId', 'item'],
        data: function () {
            return $.extend({}, {
                // status: '',
                // currentQuestion: 0,
                // childAnswers: {},
                // questions: [],
                // hintCount: 0,
                // checkCount: 0,
                // totalTime: 0,
                // timeRemaining: 0,
                // passingGrade: 0,
                // quizData: '',
                // results: {
                //     result: 0,
                //     time_spend: 0,
                //     question_correct: 0,
                //     question_wrong: 0,
                //     question_empty: 0,
                //     grade_text: ''
                // },

                ///////
                isLoading: true,
                questionIds: [],
                isFirst: false,
                isLast: false,
                isReviewing: false,
                //answers: {},
                clock: {
                    h: '00', m: '00', s: '00'
                }
            })
        },
        watch: {
            questions: {
                handler: function (v) {
                    return v || [];
                },
                deep: true
            },
            isCurrent: function (a, b) {
                if (a) {
                    if (this.item.status === '') {
                        this.load();
                    }

                    this.onActivate();
                } else {
                    this.onDeactivate();
                }

                return a;
            },
            'item.timeRemaining': function (v) {
                this.clock = this.secondsToTime(v);
                return v;
            },
            currentQuestion: function (v, p) {
                if (this.item.status === 'completed') {
                    this.checkAnswers(v);
                }

                this.updateCurrentQuestion();
            }
        },
        computed: {
            currentQuestion: {
                set: function (v) {
                    this.item.currentQuestion = v;
                },
                get: function () {
                    return this.item.currentQuestion;
                }
            },
            answers: {
                set: function (v) {
                    this.item.answers = v;
                },
                get: function () {
                    return this.item.answers;
                }
            },
            isActive: function () {
                return v;
            },
            questionContent: function () {

            }
        },
        mounted: function () {
            if (this.item.id) {
                this.init();
            }

            if (!LP.$vms) {
                LP.$vms = {};
            }

            LP.$vms['Quiz_' + this.item.id] = this;
        },
        methods: {
            timeWarningClass: function () {
                return [this.item.timeRemaining <= 5 ? 'timeover' : ''];
            },
            getItem: function () {

            },
            secondsToTime: function (seconds) {
                var MINUTE_IN_SECONDS = 60,
                    HOUR_IN_SECONDS = 3600,
                    DAY_IN_SECONDS = 24 * 3600;

                if (seconds > DAY_IN_SECONDS) {
                    var days = Math.ceil(seconds / DAY_IN_SECONDS);
                    return {d: days + ( days > 1 ? ' days left' : ' day left' )};
                } else if (seconds) {
                    var hours = Math.floor(seconds / HOUR_IN_SECONDS), minutes;

                    seconds = hours ? seconds % (hours * HOUR_IN_SECONDS) : seconds;
                    minutes = Math.floor(seconds / MINUTE_IN_SECONDS);
                    seconds = minutes ? seconds % (minutes * MINUTE_IN_SECONDS) : seconds;

                    if (hours < 10) {
                        hours = '0' + hours;
                    }

                    if (minutes < 10) {
                        minutes = '0' + minutes;
                    }

                    if (seconds < 10) {
                        seconds = '0' + seconds;
                    }
                    return {
                        h: hours,
                        m: minutes,
                        s: seconds
                    }
                }

                return {
                    h: '00',
                    m: '00',
                    s: '00'
                }
            },
            loadScript: function (url) {
                var script = document.createElement('script');
                script.onload = function () {
                };
                script.src = url;

                document.head.appendChild(script);
            },
            getQuestionTypeAnswers: function (type) {
                type = this.isDefaultQuestionType(type) ? '__default' : type;
                return 'lp-question-type-' + type + '-answers';
            },
            isDefaultQuestionType: function (type) {
                return $.inArray(type, ['single_choice', 'true_or_false', 'multi_choice']) !== -1;
            },
            init: function () {

                var $vm = this;
                this.questionIds = $(this.item.questions).map(function () {
                    return this.id;
                }).get();

                this.toggleButtons();
                this.$('.quiz-question').each(function () {
                    var $q = $(this),
                        id = $q.attr('data-id');
                    $vm.fillAnswers($q);
                });

                $(document).ready(LP.debounce(function () {
                    if (!$vm.currentQuestion) {
                        $vm.currentQuestion = $vm.questionIds[0];
                    }

                    $vm.isLoading = false;

                    var $inputs = $vm.$('.answer-option').on('change', 'input, textarea, select', function () {
                        var $q = $(this).closest('.quiz-question');
                        $vm.fillAnswers($q);
                    });

                    if ($.isEmptyObject($vm.answers)) {
                        //$inputs.filter('input[type="radio"], input[type="checkbox"]').prop('disabled', false).prop('checked', false);
                        //$inputs.filter(':not(input[type="radio"]), :not(input[type="checkbox"])').prop('disabled', false).val('');
                    }

                    var scripts = [];
                    $vm.$('#learn-press-quiz-' + $vm.item.id).find('script').each(function () {
                        var $script = $(this);

                        if ($script.attr('src')) {
                            $vm.loadScript($script.attr('src'));
                            $script.remove();
                        } else {
                            scripts.push($(this).text());
                        }
                    });

                    if (scripts) {
                        eval.apply(window, [scripts.join("\n\n")]);
                    }

                }, 300));

                if (this.item.status === 'started') {
                    this.startCounter();
                }
            },
            isShowQuestion: function (question) {
                return this.isLoading || this.currentQuestion == question.id;
            },
            load: function () {
                var $vm = this,
                    _then = function (r) {
                        var assignFields = $vm.getAjaxFields();
                        $vm.$set($vm.item, 'quizData', r);

                        $.each(assignFields, function (a, b) {
                            $vm[b] = r[b];
                        });

                        $vm.init();
                    };

                // if (this.item.quizData) {
                //     _then(this.item.quizData);
                // } else {
                //     LP.$ajaxRequest(false, 'get-quiz', {itemId: this.item.id, xxx: 1}).then(_then);
                // }

                $vm.init();
            },
            complete: function () {
                var $vm = this;
                this.stopCounter();

                return LP.apiRequest('quiz/complete', '', {
                    itemId: this.item.id,
                    answers: this.answers,
                    timeSpend: this.timeSpend
                }).then(function (response) {
                    $vm.setResponseData(response.quiz);
                });

                // LP.$ajaxRequest(false, 'complete-quiz', {
                //     itemId: this.item.id,
                //     answers: this.answers,
                //     timeSpend: this.timeSpend
                // }).then(function (response) {
                //     $vm.setResponseData(response.quiz);
                //     $vm.init();
                // })
            },
            startCounter: function () {
                this.timer && clearInterval(this.timer);
                this.timer = setInterval(function () {
                    this.item.timeRemaining > 0 ? this.item.timeRemaining-- : this.complete();
                    this.item.timeSpend++;

                    //console.log('Counting #', this.itemId, '...', this.timeRemaining, ':', this.timeSpend)
                }.bind(this), 1000);
            },
            stopCounter: function () {
                this.timer && clearInterval(this.timer);
            },
            getResultMessage: function () {

                if (!LP.l10n) {
                    return '';
                }
                return translate('Your grade is <strong>%s</strong>', !this.item.results.grade_text ? translate('Ungraded') : this.item.results.grade_text);
            },
            isActivate: function () {
                return this.item.id === this.itemId;
            },
            hasQuestions: function () {
                return this.item.questions && this.item.questions.length;
            },
            isShowContent: function () {
                return false;//this.item.quiz ? !this.item.quiz.status : true;
            },
            applyFilters: function (action, args) {
                var filteredArgs = $(document).triggerHandler(action, args);

                return filteredArgs !== undefined ? filteredArgs : args;
            },
            fillAnswers: function ($q) {
                var $vm = this,
                    id = $q.attr('data-id'),
                    answers = [];

                var type = $q.attr('data-type');

                $q.find('.answer-option').find('input[type="checkbox"], input[type="radio"]').filter(':checked').each(function () {
                    if ($(this).attr('type') === 'radio') {
                        answers = $(this).val();
                    } else {
                        answers.push($(this).val());
                    }
                });

                $q.find('.answer-option').find('input, select, textarea').each(function () {
                    if ($.inArray($(this).attr('type'), ['checkbox', 'radio']) !== -1) {
                        return;
                    }
                    answers.push($(this).val());
                });

                //Vue.set($vm.answers, id, answers);
            },
            toggleButtons: function () {
                var $vm = this;
                if (this.questionIds.length > 1) {
                    this.isFirst = this.questionIds.findIndex(function (e) {
                            return e == $vm.currentQuestion;
                        }) === 0;

                    this.isLast = this.questionIds.findIndex(function (e) {
                            return e == $vm.currentQuestion;
                        }) === this.questionIds.length - 1;
                }
            },
            getQuestionIndex: function (id) {
                return (function (theQuestions, theId) {
                    return theQuestions.findIndex(function (q) {
                        return q == theId;
                    })
                })(this.questionIds, id || this.currentQuestion);
            },
            getQuestion: function (id) {
                return this.item.questions.find(function (a) {
                    return a.id == id
                })
            },
            getResultFormatted: function (decimal) {
                return this.item.results ? this.item.results.result.toFixed(2) : 0;
            },
            $: function (selector) {
                return selector ? $(this.$el).find(selector) : $(this.$el)
            },
            getQuestionContent: function (questionId) {
                var q = this.getQuestionById(questionId);

                return q ? q.content : '';
            },
            mainClass: function () {
                var cls = [this.isLoading ? '' : 'is-loaded', 'learn-press-quiz-content'];

                return cls;
            },
            hasHint: function (questionId) {
                var q = this.getQuestionById(questionId);

                return q && q.hasHint;
            },
            hasExplanation: function (questionId) {
                var q = this.getQuestionById(questionId);

                return q && q.hasExplanation;
            },
            canHintQuestion: function (questionId) {
                var q = this.getQuestionById(questionId);

                return this.hintCount && (q && !q.hinted && q.hasHint);
            },
            canCheckQuestion: function (questionId) {

                questionId = questionId || this.currentQuestion;

                // Option is turn of in quiz
                if (this.item.showCheckAnswer !== 'yes') {
                    LP.log('showCheckAnswer is disabled');
                    return false;
                }

                // There is no more checks
                if (!this.item.checkCount) {
                    LP.log('checkCount = 0');
                    return false;
                }

                // Did not check any option
                if (!this.answers[questionId]) {
                    LP.log('No option selected');
                    return false;
                }

                var q = this.getQuestionById(questionId);

                return this.item.checkCount && (this.answers[questionId] && this.answers[questionId].length) && (q && !q.checked /*&& q.hasExplanation*/);
            },
            canRetake: function () {
                return true;
            },
            buttonHintLabel: function (questionId) {
                var q = this.getQuestionById(questionId);
                return translate(q && !q.hinted ? 'Hint' : 'Hinted');
            },
            buttonCheckLabel: function (questionId) {
                var q = this.getQuestionById(questionId);
                return translate(q && !q.checked ? 'Check' : 'Checked');
            },
            isCheckedQuestion: function (questionId) {
                var q = this.getQuestionById(questionId);
                return q && q.checked;
            },
            isHintedQuestion: function (questionId) {
                var q = this.getQuestionById(questionId);
                return q && q.hinted;
            },
            getQuestionExplanation: function (questionId) {
                var q = this.getQuestionById(questionId);
                return q ? q.explanation : '';
            },
            getQuestionHint: function (questionId) {
                var q = this.getQuestionById(questionId);
                return q ? q.hint : '';
            },
            getQuestionData: function (data, questionId) {
                questionId = questionId || this.currentQuestion;
                return LP.$ajaxRequest(false, 'get-question-data',
                    $.extend({}, data || {}, {
                        itemId: this.itemId,
                        question_id: questionId
                    })
                );
            },
            countQuestions: function () {
                return this.questionIds ? this.questionIds.length : 0;
            },
            checkAnswers: function (questionId, userAnswers) {
                var q = isNaN(questionId) ? questionId : this.getQuestionById(questionId);

                if (q) {

                    if (userAnswers !== undefined) {
                        q.userAnswers = userAnswers;
                    } else {
                        userAnswers = q.userAnswers;
                    }

                    var $answers = this.$('.quiz-question').filter('#quiz-question-' + q.id).find('.answer-option').addClass('disabled');
                    $.each(userAnswers, function (i, answer) {

                        var answerClass = [];

                        if (answer.is_true) {
                            answerClass.push('answer-correct');
                        }

                        if (answer.checked) {
                            answerClass.push('answer-selected');
                        }

                        if (answer.checked && answer.is_true) {
                            answerClass.push('answered-correct');
                        } else if (answer.checked && !answer.is_true) {
                            answerClass.push('answered-wrong');
                        } else if (!answer.checked && answer.is_true) {
                            answerClass.push('answered-wrong');
                        }

                        $answers.eq(i).addClass(answerClass.join(' ')).find('input.option-check').prop('checked', answer.checked).prop('disabled', true);
                    })
                }
            },
            getQuestionById: function (questionId) {
                questionId = questionId || this.currentQuestion;
                var at = this.getQuestionIndex(questionId);
                return this.item.questions ? this.item.questions[at] : false;
            },
            getAccessLevel: function () {
                switch (this.item.status) {
                    case 'completed':
                        return 30;
                    case 'started':
                        return 20;
                    default:
                        // enrolled course
                        return 10;
                }

                return 0;
            },
            hasAccessLevel: function (levels, cp) {
                if (!isNaN(levels)) {
                    levels = [levels];
                }

                switch (cp) {
                    case '>':
                        return this.getAccessLevel() > levels[0];
                    case '<':
                        return this.getAccessLevel() < levels[0];
                    case '!':
                        return this.getAccessLevel() != levels[0];
                    case '=':
                        return this.getAccessLevel() == levels[0];
                }

                return $.inArray(this.getAccessLevel(), levels) !== -1;
            },
            getAjaxFields: function () {
                var fields = 'totalTime timeRemaining checkCount hintCount currentQuestion status questions answers passingGrade results timeSpend'.split(' ');
                return this.applyFilters('LP.quiz-ajax-fields', fields);
            },
            onActivate: function () {
                if (this.item.status === 'started') {
                    this.startCounter();
                }
            },
            onDeactivate: function () {
                if (this.item.status === 'started') {
                    this.stopCounter();
                }
            },
            updateCurrentQuestion: function () {
                LP.$ajaxRequest('', 'update-current-question', {
                    itemId: this.item.id,
                    questionId: this.currentQuestion
                }).then(function (r) {
                })
            },
            /**
             * Set data from ajax response to item of this quiz.
             *
             * @since 3.x.x
             *
             * @param {Object} response     Response data to assigns.
             * @param {Array} excludeProps  Properties will ignore.
             */
            setResponseData: function (response, excludeProps) {
                var prop, i, n, id, newQuestion;

                for (prop in response) {

                    if (!response.hasOwnProperty(prop)) {
                        continue;
                    }

                    // Exclude the prop in list
                    if ($.isArray(excludeProps) && -1 !== $.inArray(prop, excludeProps)) {
                        continue;
                    }

                    if (prop === 'questions') {
                        for (i = 0, n = this.item.questions.length; i < n; i++) {
                            id = this.item.questions[i].id;
                            newQuestion = response.questions[id];

                            if (newQuestion) {
                                LP.assignObject(this.item.questions[i], newQuestion);
                            }
                        }
                    } else {
                        this.item[prop] = response[prop];
                    }
                }
            },
            _updateQuestionAnswer: function (data) {
                Vue.set(this.item.answers, data.id, data.answer);
            },
            _questionsNav: function ($event) {
                switch ($event.keyCode) {
                    case 37:
                        this._prev();
                        break;
                    case 39:
                        this._next();
                        break;
                }
            },
            _prev: function () {
                var at = this.getQuestionIndex();

                if (at > 0) {
                    at--;
                }
                this._moveToQuestion(null, at)
            },
            _next: function () {
                var at = this.getQuestionIndex();

                if (at < this.questionIds.length - 1) {
                    at++;
                }

                this._moveToQuestion(null, at)
            },
            _moveToQuestion: function ($e, at) {

                this.currentQuestion = this.questionIds[at];
                this.toggleButtons();

                var q = this.item.questions[at];

                if (q && q.permalink) {
                    LP.setUrl(q.permalink)
                }
            },
            _reviewQuestions: function () {
                this.isReviewing = !this.isReviewing;
            },
            _complete: function () {
                var $vm = this;

                jConfirm(translate('Do you want to finish quiz %s?', this.item.name), '', $.proxy(function (confirm) {
                    $vm.complete();
                }, this));

                setTimeout(function () {
                    $.alerts._reposition();
                    $('#popup_container').addClass('ready')
                }, 30)

                var $a = $('<a href="" class="close"><i class="fa fa-times"></i></a>')
                $('#popup_container').append($a);
                $a.on('click', function () {
                    $.alerts._hide();
                    return false;
                });

                $(document.body).toggleClass('confirm', true);


            },
            /**
             * Event handler for button 'Check Answer'
             *
             * @since 3.x.x
             * @private
             */
            _doCheckAnswer: function () {
                var $vm = this,
                    q = this.item.questions[this.getQuestionIndex()],
                    answers = this.answers[q.id] || false;/// LP.listPluck(q.optionAnswers, 'value', {checked: true});
                q.checked = true;

                if (q.type !== 'multi_choice' && $.isArray(answers)) {
                    answers = answers[0];
                }

                return LP.apiRequest('question/check', '', {
                    itemId: this.itemId,
                    questionId: this.item.currentQuestion,
                    answers: answers
                }).then(function (response) {
                    $vm.setResponseData(response);
                });
            },
            _doHintAnswer: function () {
                var q = this.item.questions[this.getQuestionIndex()];
                q.hinted = true;
                q.hint = 'Hint: ' + Math.random();
                this.hintCount--;
            },
            /**
             * Start quiz action when user clicking on button
             * @private
             */
            _startQuiz: function () {
                var $vm = this;

                return LP.apiRequest('quiz/start', '', {
                    itemId: this.itemId,
                }).then(function (response) {
                    var assignFields = $vm.getAjaxFields();

                    $.each(assignFields, function (a, b) {
                        if (typeof response.quizData[b] === 'undefined') {
                            return;
                        }

                        $vm.item[b] = response.quizData[b];
                    });
                    $vm.answers = {};
                    $vm.init();
                });

                // LP.$ajaxRequest('', 'start-quiz', {itemId: this.item.id}).then(function (r) {
                //     //LP.$vms['notifications'].add(r.notifications);
                //
                //     var assignFields = this.getAjaxFields();
                //     $vm.$set($vm.item, 'quizData', r.quizData);
                //
                //     $.each(assignFields, function (a, b) {
                //         $vm[b] = r.quizData[b];
                //     });
                //
                //     $vm.init();
                //
                // })
            },
            /**
             * Retake quiz action when user clicking on button.
             *
             * @since 3.x.x
             * @private
             */
            _retakeQuiz: function () {
                var $vm = this;
                this.answers = {};

                return LP.apiRequest('quiz/retake', '', {
                    itemId: this.itemId
                }).then(function (response) {
                    LP.assignObject($vm.item, response.quiz);

                    $vm.answers = {};
                    $vm.init();
                });
            },
            _transitionEnter: LP.debounce(function () {
//                    var $el = this.$('.quiz-question:visible');
//                    $el.parent().height($el.height());
//                    console.log('enter', this.currentQuestion)
            }, 10)
        }
    };

})(jQuery);
