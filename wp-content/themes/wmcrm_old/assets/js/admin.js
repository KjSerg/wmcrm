var $doc = $(document);
var num = 0;
var parser = new DOMParser();
var interval;

$doc.ready(function () {
    $doc.on('submit', '.js-parser-form', function (e) {
        e.preventDefault();
        var $t = $(this);
        var isChecked = $t.find('#get-status').prop('checked') === true;
        var start = new Date();
        console.log('Start: ' + start);
        if ($t.hasClass('infinite-form')) {
            $doc.find('.result').append('Start: ' + start);
        } else {
            $doc.find('.result').html('Start: ' + start);
        }
        $doc.find('.preloader').addClass('active');
        setIcon();
        if (isChecked) {
            checkingStatus($t.find('[name="connect_id"]').val());
        }
        $.ajax({
            type: $t.attr('method'),
            url: admin_ajax,
            data: $t.serialize()
        }).done(function (r) {
            if (isChecked) {
                clearInterval(interval);
            }
            var html = '<br>';
            html += r + '<br><hr>';
            html += 'Finish: ' + new Date() + '<div class="separator"></div>';
            if ($t.hasClass('infinite-form')) {
                $doc.find('.result').append(html);
            } else {
                $doc.find('.result').html(html);
            }
            var offsetNum = Number($doc.find('.offset-num-js').last().text());
            console.log(offsetNum);
            console.log('Finish: ' + new Date());
            $doc.find('.preloader').removeClass('active');
            $doc.find('.sub-sum').remove();
            $doc.find('.result').append('<strong class="sub-sum">Всього коментарів: ' + $doc.find('.comment__item').length + '</strong>');
            removeIcon();
            if (r === 'done') return;
            if ($t.hasClass('infinite-form')) {
                if (!isNaN(offsetNum) && offsetNum > 0) {
                    $doc.find('#offset').val(offsetNum);
                } else {
                    offsetNum = Number($doc.find('#offset').val());
                    if (!isNaN(offsetNum) && offsetNum > 0) {
                        $doc.find('#offset').val(offsetNum);
                    }
                }
                $t.trigger('submit');
            }
        }).fail(function (r) {
            console.log(r);
            if (isChecked) {
                clearInterval(interval);
            }
            var html = 'Start: ' + start;
            html += '<hr><br>';
            html += r.responseText + '<br><hr>';
            html += 'Finish: ' + new Date();
            $doc.find('.result').html(html);
            var offsetNum = Number($doc.find('.offset-num-js').last().text());
            console.log(offsetNum);
            console.log('Finish: ' + new Date());
            $doc.find('.preloader').removeClass('active');
            $doc.find('.result').append('<br>Всього коментарів: ' + $doc.find('.comment__item').length);
            removeIcon();
            if ($t.hasClass('infinite-form')) {
                if (!isNaN(offsetNum) && offsetNum > 0) {
                    $doc.find('#offset').val(offsetNum);
                } else {
                    offsetNum = Number($doc.find('#offset').val());
                    if (!isNaN(offsetNum) && offsetNum > 0) {
                        $doc.find('#offset').val(offsetNum);
                    }
                }
                $t.trigger('submit');
            }
        });
    });
    $doc.on('click', '.send-request', function (e) {
        e.preventDefault();
        var $t = $(this);
        var start = new Date();
        console.log('Start: ' + start);
        $doc.find('.result').html('Start: ' + start);
        $doc.find('.preloader').addClass('active');
        $.ajax({
            type: 'POST',
            url: $t.attr('href'),
        }).done(function (r) {
            var html = 'Start: ' + start;
            html += '<hr><br>';
            html += r + '<br><hr>';
            html += 'Finish: ' + new Date();
            $doc.find('.result').html(html);
            console.log('Finish: ' + new Date());
            $doc.find('.preloader').removeClass('active');
            removeIcon();
        });
    });
    $doc.on('change', '#is-infinite', function (e) {
        var $t = $(this);
        var isChecked = $t.prop('checked') === true;
        if (isChecked) {
            $t.closest('form').addClass('infinite-form');
            $doc.find('#number').val(1);
        } else {
            $t.closest('form').removeClass('infinite-form');
        }
    });

});

function checkingStatus(connectID) {
    interval = setInterval(function () {
        $.ajax({
            type: 'POST',
            url: admin_ajax,
            data: {
                action: 'get_status_connect',
                connect: connectID
            },
        }).done(function (r) {
            $doc.find('.result').append(r);
        });
    }, 30000);
}

function setIcon() {
    var newFavicon = document.createElement('link');
    newFavicon.rel = 'icon';
    newFavicon.type = 'image/png';
    newFavicon.href = preloader;
    var currentFavicon = document.querySelector('link[rel="icon"]');
    if (currentFavicon) {
        currentFavicon.parentNode.removeChild(currentFavicon);
    }
    document.head.appendChild(newFavicon);
}

function removeIcon() {
    var currentFavicon = document.querySelector('link[rel="icon"]');
    if (currentFavicon) {
        currentFavicon.parentNode.removeChild(currentFavicon);
    }
}