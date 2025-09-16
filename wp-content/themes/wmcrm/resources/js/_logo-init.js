import './tm';

var LogoWM = (function () {
    function LogoWM(option) {
        var _ = this;
        _.elem = option.element;
        _.params = option;
        _.speed = option.speed || 1;
        _.reinitOnFocus = option.reinitOnFocus || false;
        _.callCallback = true;
        _.timelineArray = [];
        _.played = false;
        return _.inite();
    };
    LogoWM.prototype.forEachTL = function (event) {
        var _ = this;
        _.timelineArray.forEach(function (el, index, arr) {
            el[event]();
        });
    };
    LogoWM.prototype.reverse = function (func, index) {
        var _ = this;
        if (_.played === true) {
            return;
        }
        if (typeof func == "function") {
            TweenLite.delayedCall(_.timelineArray[_.timelineArray.length - 1].time() - (index !== undefined ? _.speed : .5), func);
        }
        if (index === undefined) {
            _.forEachTL("reverse");
            _.animateEnd = false;
        } else {
            _.timelineArray[index].reverse(-(_.speed / 3));
        }
    };
    LogoWM.prototype.play = function (index) {
        var _ = this;
        if (_.played === false) {
            if (index === undefined) {
                _.forEachTL("play");
                _.animateEnd = false;
            } else {
                window.setTimeout(function () {
                    _.animateEnd = true;
                }, _.speed * 1000)
                _.timelineArray[index].play((_.speed / 3));
            }
            return;
        }
        _.played = true;
    };
    LogoWM.prototype.inite = function () {
        var _ = this;
        if (_.reinitOnFocus) {
            _.elem.get(0).addEventListener("click", function () {
                if (!_.animateEnd) {
                    return;
                }
                _.callCallback = false;
                _.animateEnd = false;
                _.reverse(function () {
                    _.play(0);
                }, 0);
            })
        }
        _.shapes = _.elem.find("polygon");
        _.paths = _.elem.find('> path');
        _.polylion_stagger = 0.04;
        _.polylion_staggerFrom = {
            opacity: 0,
            scale: 0,
            transformOrigin: "center center"
        };
        _.polylion_staggerTo = {
            opacity: 1,
            scale: 1,
            transformOrigin: "center center",
            ease: Elastic.easeInOut,
        };
        _.path_staggerTo = {
            opacity: 1,
            scale: 1,
            delay: .4,
            transformOrigin: "center center",
            ease: Elastic.easeInOut
        };
        tMax.set(_.elem, {autoAlpha: 1});
        _.newPolylion = new TimelineMax({
            paused: _.params.paused
        });
        _.timelineArray.push(_.newPolylion);
        _.newPolylion.staggerFromTo(_.shapes, _.speed, _.polylion_staggerFrom, _.polylion_staggerTo, _.polylion_stagger);
        if (_.paths.length) {
            _.newPath = new TimelineMax({
                paused: _.params.paused
            });
            _.newPath.staggerFromTo(_.paths, _.speed, _.polylion_staggerFrom, _.path_staggerTo, _.polylion_stagger, .5);
            _.timelineArray.push(_.newPath);
        }
        _.timelineArray[_.timelineArray.length - 1].eventCallback("onComplete", function () {
            _.animateEnd = true;
            if (_.callCallback) {
                (_.params.onComplete || function () {
                })(this);
            } else {
                _.callCallback = true;
            }
        });
        if (!_.params.paused) {
            _.play();
        }
    };
    return LogoWM;
})();
var tMax = TweenMax;

$(document).ready(function () {
    if ($('.wm_logo').length > 0) {
        var joystick_logo = new LogoWM({
            element: $(".wm_logo"),
            paused: false,
            speed: 1,
            reinitOnFocus: true
        });
        setTimeout(function () {
            joystick_logo.play()
        }, 10000);
        setTimeout(function () {
            $('.preload').fadeOut(300);
        }, 4000);
    }
});