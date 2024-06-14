import {
    hidePreloader,
    isJsonString,
    renderMain,
    sendRequest,
    setCookie,
    showMassage,
    getCurrentDate,
    renderInModalHTML,
    openWindow,
    closeWindow, showPreloader
} from "./_helpers";
import {setInterval} from "worker-timers";

export default class Stopwatch {
    constructor() {
        this.loading = false;
        this.date = false;
        this.stopwatches = [];
        this.workTimes = [];
        this.startTimestamp = 0;
        this.finishTimestamp = 0;
        this.sum = 0;
        this.interval = null;
        this.timersInterval = null;
        this.status = 0;
        this.$doc = $(document);
        this.listenEvents();
        this.getCurrentData();
        this.cyclicallyUpdated();
    }

    clearStorage() {
        const _this = this;
        _this.stopwatches = [];
        _this.workTimes = [];
        _this.startTimestamp = 0;
        _this.finishTimestamp = 0;
        _this.sum = 0;
        clearInterval(_this.interval);
        _this.interval = null;
        _this.status = 0;
        _this.$doc.find('.timer').removeClass('play').removeClass('pause');
    }

    getTimers() {
        const _this = this;
        const _stopwatches = _this.stopwatches;
        $.ajax({
            type: 'POST',
            url: adminAjax,
            data: {
                action: 'get_timers_html',
            },
        }).done(function (r) {
            if (r) {
                _this.$doc.find('.timer-control').html(r);
            }
        });
    }

    listenEvents() {
        const _this = this;
        const _stopwatches = _this.stopwatches;
        _this.$doc.on('click', '.timer-button-start', function (e) {
            e.preventDefault();
            let $t = $(this);
            let $timer = $t.closest('.timer');
            _this.status = 1;
            $timer.addClass('play');
            $timer.removeClass('pause');
            _this.start();

        });
        _this.$doc.on('click', '.timer-button-finish', function (e) {
            e.preventDefault();
            let $t = $(this);
            let $timer = $t.closest('.timer');
            _this.status = 0;
            $timer.removeClass('play');
            $timer.removeClass('pause');
            _this.finish();

        });
        _this.$doc.on('click', '.timer-button-pause', function (e) {
            e.preventDefault();
            let $t = $(this);
            let $timer = $t.closest('.timer');
            _this.status = -1;
            $timer.removeClass('play');
            $timer.addClass('pause');
            _this.pauseEvent();

        });
        _this.$doc.on('click', '.timer-button-play-pause', function (e) {
            e.preventDefault();
            let $t = $(this);
            let $timer = $t.closest('.timer');
            _this.status = 1;
            $timer.addClass('play');
            $timer.removeClass('pause');
            _this.start();

        });
        _this.$doc.on('click', '.timer-result', function (e) {
            e.preventDefault();
            let $t = $(this);
            let $timer = $t.closest('.timer');
            let isOpen = $timer.hasClass('open-controls');
            if (!isOpen) {
                $timer.addClass('open-controls');
                $('body').addClass('open-timer');
                if (_this.status === 0 && _this.workTimes.length === 0) {
                    _this.$doc.find('.timer-button-start').trigger('click');
                }
            } else {
                $timer.removeClass('open-controls');
                $('body').removeClass('open-timer');
            }
            if ($t.hasClass('admin-timers')) {
                _this.getTimers();
                clearInterval(_this.timersInterval);
                _this.timersInterval = setInterval(function () {
                    _this.getTimers();
                }, 60000);
            }
        });
        _this.$doc.on('click', '.report-button-trigger', function (e) {
            e.preventDefault();
            let $t = $(this);
            _this.$doc.find('.report-footer-control').hide();
            _this.$doc.find('.report-footer-form').show();
        });
        _this.$doc.mouseup(function (e) {
            let div = $(".timer");
            if (!div.is(e.target)
                && div.has(e.target).length === 0) {
                div.removeClass('open-controls');
                $('body').removeClass('open-timer');
                clearInterval(_this.timersInterval);
            }
        });
        document.onvisibilitychange = () => {
            if (document.visibilityState === "hidden") {
                console.log("tab inactive");
                _this.$doc.find('.timer').addClass('not-active');
            }
            if (document.visibilityState === "visible") {
                console.log("tab active");
                _this.getCurrentData();
            }
        };
    }

    tick(data) {
        const _this = this;
        if (_this.$doc.find('.test-timer').length > 0) {
            const status = Number(data.status);
            const results = data.results;
            const work = results.work;
            const pause = results.pause;
            let pauseSeconds = pause.seconds;
            let workSeconds = work.seconds;

            setInterval(function () {
                if (status === 1) {
                    workSeconds = workSeconds + 1;
                } else if (status === -1) {
                    pauseSeconds = pauseSeconds + 1;
                }
                let formatedTime = _this.convertMillisecondsToTime(workSeconds * 1000);
                let formatedPauseTime = _this.convertMillisecondsToTime(pauseSeconds * 1000);
                const formatedTimeString = formatedTime['hours'] + ':' + formatedTime['minutes'] + ':' + formatedTime['seconds'];
                const formatedPauseTimeString = formatedPauseTime['hours'] + ':' + formatedPauseTime['minutes'] + ':' + formatedPauseTime['seconds'];
                _this.$doc.find('.timer-work-time span').text(formatedTimeString);
                _this.$doc.find('.timer-pause-time span').text(formatedPauseTimeString);
            }, 1000);
        }

    }

    runTick() {
        const _this = this;
        const _status = _this.status;
        console.log(_status)
        // if (_this.$doc.find('.test-timer').length > 0) return;
        if (_status === 0) {
            if (_this.interval !== null) {
                clearInterval(_this.interval);
            }
        } else {
            console.log(_this.interval)
            if (_this.interval === null) {
                _this.interval = setInterval(function () {
                    _this.renderResults();
                }, 1000);
            }

        }
    }

    renderResults() {
        const _this = this;
        const _stopwatches = _this.stopwatches;
        const _startTimestamp = _this.startTimestamp;
        const _finishTimestamp = _this.finishTimestamp;
        const _status = _this.status;
        const currentTime = _this.getCurrentTimestamp();
        const stopwatchSum = _this.getStopwatchSum();
        const workTimesSum = _this.getWorkTimesSum();
        const finish = _finishTimestamp > 0 ? _finishTimestamp : currentTime;
        let sum = workTimesSum - stopwatchSum;
        const formatedTime = _this.convertMillisecondsToTime(workTimesSum);
        const formatedPauseTime = _this.convertMillisecondsToTime(stopwatchSum);
        const formatedTimeString = formatedTime['hours'] + ':' + formatedTime['minutes'] + ':' + formatedTime['seconds'];
        const formatedPauseTimeString = formatedPauseTime['hours'] + ':' + formatedPauseTime['minutes'] + ':' + formatedPauseTime['seconds'];
        if (workTimesSum > 0) _this.$doc.find('.timer-result').text(formatedTimeString);
        _this.$doc.find('.timer-work-time span').text(formatedTimeString);
        _this.$doc.find('.timer-pause-time span').text(formatedPauseTimeString);
    }

    getStopwatchSum() {
        const _this = this;
        const _stopwatches = _this.stopwatches;
        let _sum = 0;
        const l = _stopwatches.length - 1;
        for (let a = 0; a < _stopwatches.length; a++) {
            let item = _stopwatches[a];
            let start = Number(item.start || 0);
            let finish = item.finish ? Number(item.finish) : _this.getCurrentTimestamp();
            finish = Number(finish);
            if (finish === 0) {
                if (a < l) {
                    finish = start;
                } else {
                    finish = finish === 0 ? Number(_this.getCurrentTimestamp()) : finish;
                }
            }
            let subSum = finish - start;
            _sum = _sum + subSum;
        }
        return _sum;
    }

    getWorkTimesSum() {
        const _this = this;
        const _workTimes = _this.workTimes;
        const _stopwatches = _this.stopwatches;
        const _status = _this.status;
        const l = _workTimes.length - 1;
        let _sum = 0;
        let test = 0;
        for (let a = 0; a < _workTimes.length; a++) {
            let item = _workTimes[a];
            let start = Number(item.start || 0);
            let finish = Number(item.finish);

            if (finish === 0) {
                if (a < l) {
                    finish = start;
                } else {
                    finish = finish === 0 ? _this.getCurrentTimestamp() : finish;
                }
            }

            let subSum = finish - start;
            _sum = _sum + subSum;
        }
        return _sum;
    }

    start() {
        const _this = this;
        const _status = _this.status;
        const _finishTimestamp = _this.finishTimestamp;
        const _stopwatches = _this.stopwatches;
        const _workTimes = _this.workTimes;
        const unix = Number(_this.getCurrentTimestamp());
        const lastIndexWorkTimes = _workTimes.length - 1;
        if (lastIndexWorkTimes >= 0) {
            const lastFinishWorkTimes = Number(_workTimes[lastIndexWorkTimes].finish) || 0;
            if (lastFinishWorkTimes === 0) {
                _workTimes[lastIndexWorkTimes].finish = unix;
            }
        }
        if (_status === 1) {
            _workTimes.push({
                start: unix,
                finish: 0,
            });
        }
        _this.workTimes = _workTimes;
        if (_this.startTimestamp === 0) {
            _this.startTimestamp = unix;
        } else {
            const lastIndex = _stopwatches.length - 1;
            if (lastIndex >= 0) {
                const lastFinish = Number(_stopwatches[lastIndex].finish);
                if (lastFinish === 0) {
                    _stopwatches[lastIndex].finish = unix;
                    _this.stopwatches = _stopwatches;
                }
            }
            if (_finishTimestamp > 0) {
                _this.finishTimestamp = 0;
            }
        }
        _this.saveData(false, true);
    }

    pauseEvent() {
        const _this = this;
        const _stopwatches = _this.stopwatches;
        const unix = Number(_this.getCurrentTimestamp());
        const _workTimes = _this.workTimes;
        const lastIndex = _stopwatches.length - 1;
        _stopwatches.push({
            start: unix,
            finish: 0,
        });
        _this.stopwatches = _stopwatches;
        const workTimesLastIndex = _workTimes.length - 1;
        if (workTimesLastIndex >= 0) {
            _workTimes[workTimesLastIndex].finish = unix;
            _this.workTimes = _workTimes;
        }
        _this.saveData(false, true);
    }

    finish() {
        const _this = this;
        const _stopwatches = _this.stopwatches;
        const _workTimes = _this.workTimes;
        const lastIndex = _stopwatches.length - 1;
        const unix = Number(_this.getCurrentTimestamp());
        if (lastIndex >= 0) {
            if (Number(_stopwatches[lastIndex].finish) === 0) {
                _stopwatches[lastIndex].finish = unix;
                _this.stopwatches = _stopwatches;
            }
        }
        _this.finishTimestamp = unix;
        const workTimesLastIndex = _workTimes.length - 1;
        if (workTimesLastIndex >= 0) {
            _workTimes[workTimesLastIndex].finish = unix;
            _this.workTimes = _workTimes;
        }
        clearInterval(_this.interval);
        _this.renderResults();
        _this.saveData(true, true);
    }

    getCurrentTimestamp() {
        return Number(Date.now());
    }

    getTimestampInSeconds() {
        return Math.floor(Date.now() / 1000);
    }

    convertMillisecondsToTime(milliseconds) {
        let date = new Date(milliseconds);
        let hours = date.getUTCHours();
        let minutes = date.getUTCMinutes();
        let seconds = date.getUTCSeconds();
        hours = (hours < 10) ? "0" + hours : hours;
        minutes = (minutes < 10) ? "0" + minutes : minutes;
        seconds = (seconds < 10) ? "0" + seconds : seconds;
        return {
            hours: hours,
            minutes: minutes,
            seconds: seconds,
        };
    }

    getCurrentDate() {
        let today = new Date();
        let day = today.getDate();
        let month = today.getMonth() + 1;
        let year = today.getFullYear();
        day = (day < 10) ? "0" + day : day;
        month = (month < 10) ? "0" + month : month;
        return day + "-" + month + "-" + year;
    }

    saveData(getResultModal = false, showLoader = false) {
        const _this = this;
        if (_this.date === false) _this.date = getCurrentDate();
        if (_this.date !== getCurrentDate()) _this.clearStorage();
        if (_this.loading === true) return;
        const _finishTimestamp = _this.finishTimestamp;
        const _startTimestamp = _this.startTimestamp;
        const _stopwatches = _this.stopwatches;
        const _workTimes = _this.workTimes;
        const _status = _this.status;
        const sum = _this.getWorkTimesSum();
        const sumPauses = _this.getStopwatchSum();
        const currentDate = getCurrentDate();
        _this.loading = true;
        const data = {
            'action': 'save_user_time',
            get_result_modal: getResultModal ? '1' : '0',
            date: currentDate,
            work_times: _workTimes,
            stopwatches: _stopwatches,
            start: _startTimestamp,
            finish: _finishTimestamp,
            status: _status,
            costs_sum: sum,
            costs_sum_hour: _this.convertMillisecondsToTime(sum),
            pause_time: sumPauses,
            pause_time_hour: _this.convertMillisecondsToTime(sumPauses),
        };
        if (showLoader) {
            showPreloader();
        }
        $.ajax({
            type: 'POST',
            url: adminAjax,
            data: data,
        }).done(function (r) {
            hidePreloader();
            if (isJsonString(r)) {
                const res = JSON.parse(r);
                console.log(res);
                _this.runTick();
                const html = res.timer_modal_html;
                _this.loading = false;
                if (html !== undefined && html !== '') {
                    if (_this.$doc.find('#report-window').length > 0) closeWindow(_this.$doc.find('#report-window'));
                    $('body').append(html);
                    setTimeout(function () {
                        openWindow(_this.$doc.find('#report-window'));
                    }, 500);
                }
            } else {
                alert(r);
            }

        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR)
            console.log(textStatus)
            console.log(errorThrown)
            _this.saveData(getResultModal, showLoader);
        });

        // sendRequest(adminAjax, data, 'POST', showLoader).then((res) => {
        //     console.log(res);
        //     _this.runTick();
        //     const html = res.timer_modal_html;
        //     _this.loading = false;
        //     if (html !== undefined && html !== '') {
        //         if (_this.$doc.find('#report-window').length > 0) closeWindow(_this.$doc.find('#report-window'));
        //         $('body').append(html);
        //         setTimeout(function () {
        //             openWindow(_this.$doc.find('#report-window'));
        //         }, 500);
        //     }
        // }).catch(function (e) {
        //     console.log(e)
        // });
    }

    getCurrentData(saveData = false) {
        const _this = this;
        const date = _this.getCurrentDate();
        if (_this.date !== date) _this.clearStorage();
        const data = {
            action: 'get_user_time',
            date: date
        }
        $.ajax({
            type: 'POST',
            url: adminAjax,
            data: data,
        }).done(function (r) {
            hidePreloader();
            if (isJsonString(r)) {
                const res = JSON.parse(r);
                console.log(res)
                if (res) {
                    _this.$doc.find('.timer').removeClass('not-active');
                    let pauses = res.pauses || [];
                    let costs_data = res.costs_data || [];
                    const costs_status = res.costs_status;
                    const costs_start = res.costs_start || 0;
                    const costs_finish = res.costs_finish || 0;
                    const timer_modal_html = res.timer_modal_html;
                    const costs_sum_hour = res.costs_sum_hour;
                    const costs_sum = res.costs_sum;
                    const costs_sum_hour_pause = res.costs_sum_hour_pause;
                    const costs_sum_pause = res.costs_sum_pause;
                    if (isJsonString(pauses)) pauses = JSON.parse(pauses);
                    if (isJsonString(costs_data)) costs_data = JSON.parse(costs_data);
                    _this.stopwatches = pauses;
                    _this.workTimes = costs_data;
                    if (costs_status) {
                        _this.status = Number(costs_status);
                    }
                    _this.startTimestamp = Number(costs_start || 0);
                    _this.finishTimestamp = Number(costs_finish || 0);
                    _this.renderResults();
                    if (_this.status === 1) {
                        _this.$doc.find('.timer').removeClass('pause');
                        _this.$doc.find('.timer').addClass('play');
                        _this.runTick();
                    } else if (_this.status === -1) {
                        _this.$doc.find('.timer').addClass('pause');
                        _this.$doc.find('.timer').removeClass('play');
                        _this.runTick();
                    } else {
                        _this.$doc.find('.timer').removeClass('pause');
                        _this.$doc.find('.timer').removeClass('play');
                    }
                    if (res.reload) {
                        window.location.reload();
                        return;
                    }
                    if (saveData) _this.saveData();
                } else {
                    window.location.reload();
                }
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR)
            console.log(textStatus)
            console.log(errorThrown)
            _this.getCurrentData();
        });

    }

    cyclicallyUpdated() {
        const _this = this;
        setInterval(function () {
            _this.getCurrentData(true);
        }, 60000);
    }
}

