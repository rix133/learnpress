import {dispatch, select, apiFetch} from '@learnpress/data-controls';
import {select as wpSelect, dispatch as wpDispatch} from '@wordpress/data';

const {camelCaseDashObjectKeys, Hook} = LP;
/**
 * Set user data for app.
 * @param key
 * @param data
 * @return {{type: string, data: *}}
 */
export function setQuizData(key, data) {
    if (typeof key === 'string') {
        data = {[key]: data}
    } else {
        data = key;
    }

    return {
        type: 'SET_QUIZ_DATA',
        data: camelCaseDashObjectKeys(data)
    }
}

/**
 * Set question will display.
 *
 * @param questionId
 * @return {{type: string, data: *}}
 */
export function setCurrentQuestion(questionId) {
    return {
        type: 'SET_CURRENT_QUESTION',
        questionId
    }
}

export function setCurrentPage(currentPage) {
    return {
        type: 'SET_CURRENT_PAGE',
        currentPage
    }
}

export function __requestBeforeStartQuiz(quizId, courseId, userId) {


    return {
        type: 'BEFORE_START_QUIZ'
    }
}

export function __requestStartQuizSuccess(results, quizId, courseId, userId) {

    Hook.doAction('quiz-started', results, quizId, courseId, userId);

    return {
        type: 'START_QUIZ_SUCCESS',
        quizId,
        courseId,
        userId,
        results
    }
}

/**
 * Request to api for starting a quiz.
 */
const startQuiz = function*() {

    const {
        itemId,
        courseId
    } = wpSelect('learnpress/quiz').getDefaultRestArgs();

    const doStart = Hook.applyFilters('before-start-quiz', true, itemId, courseId);

    // Allow third-party can ignore core action
    if (true !== doStart) {
        return;
    }

    let response = yield apiFetch({
        path: 'lp/v1/users/start-quiz',
        method: 'POST',
        data: {
            item_id: itemId,
            course_id: courseId
        }
    });

    response = Hook.applyFilters('request-start-quiz-response', response, itemId, courseId);

    yield dispatch('learnpress/quiz', '__requestStartQuizSuccess', camelCaseDashObjectKeys(response['results']), itemId, courseId);
};

export {startQuiz}

export function __requestSubmitQuiz() {

    return {
        type: 'SUBMIT_QUIZ'
    }
}

export function __requestSubmitQuizSuccess(results, quizId, courseId) {

    Hook.doAction('quiz-submitted', results, quizId, courseId);

    return {
        type: 'SUBMIT_QUIZ_SUCCESS',
        results
    }
}

export function* submitQuiz() {

    const {
        getDefaultRestArgs,
        getQuestionsSelectedAnswers
    } = wpSelect('learnpress/quiz');

    const {
        itemId,
        courseId
    } = getDefaultRestArgs();

    const doSubmit = Hook.applyFilters('before-submit-quiz', true);

    if (true !== doSubmit) {
        return;
    }

    const answered = getQuestionsSelectedAnswers();
    let response = yield apiFetch({
        path: 'lp/v1/users/submit-quiz',
        method: 'POST',
        data: {
            item_id: itemId,
            course_id: courseId,
            answered
        }
    });

    response = Hook.applyFilters('request-submit-quiz-response', response, itemId, courseId);

    if (response.success) {
        yield dispatch('learnpress/quiz', '__requestSubmitQuizSuccess', camelCaseDashObjectKeys(response.results), itemId, courseId);
    }
}

export function updateUserQuestionAnswers(questionId, answers, quizId, courseId = 0, userId = 0) {
    return {
        type: 'UPDATE_USER_QUESTION_ANSWERS',
        questionId,
        answers,
    }
}

export function __requestShowHintSuccess(id, showHint) {
    return {
        type: 'SET_QUESTION_HINT',
        questionId: id,
        showHint
    }
}

export function* showHint(id, showHint) {
    yield dispatch('learnpress/quiz', '__requestShowHintSuccess', id, showHint);

    // const {
    //     getDefaultRestArgs,
    //     getData
    // } = wpSelect('learnpress/quiz');
    //
    // const {
    //     itemId,
    //     courseId
    // } = getDefaultRestArgs();
    //
    // const result = yield apiFetch({
    //     path: 'lp/v1/users/hint-answer',
    //     method: 'POST',
    //     data: {
    //         itemId,
    //         courseId,
    //         question_id: id
    //     }
    // });
    //
    // yield dispatch('learnpress/quiz', '__requestShowHintSuccess', id, camelCaseDashObjectKeys(result));
}

export function __requestCheckAnswerSuccess(id, result) {
    return {
        type: 'CHECK_ANSWER',
        questionId: id,
        ...result
    }
}

export function* checkAnswer(id) {
    console.time('checkAnswer');
    const {
        getDefaultRestArgs,
        getQuestionAnswered,
    } = wpSelect('learnpress/quiz');

    const {
        itemId,
        courseId
    } = getDefaultRestArgs();


    const result = yield apiFetch({
        path: 'lp/v1/users/check-answer',
        method: 'POST',
        data: {
            item_id: itemId,
            course_id: courseId,
            question_id: id,
            answered: getQuestionAnswered(id) || ''
        }
    });

    yield dispatch('learnpress/quiz', '__requestCheckAnswerSuccess', id, camelCaseDashObjectKeys(result));
    console.timeEnd('checkAnswer');

}

export function markQuestionRendered(questionId) {
    return {
        type: 'MARK_QUESTION_RENDERED',
        questionId
    }
}

export function setQuizMode(mode) {
    return {
        type: 'SET_QUIZ_MODE',
        mode
    }
}