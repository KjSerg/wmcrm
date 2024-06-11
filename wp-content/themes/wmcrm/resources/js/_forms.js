import {closeWindow, hidePreloader, isJsonString, renderMain, showMassage, showPreloader, openWindow} from "./_helpers";
import {setQiullText} from "./_quill-init";
import Invite from "./Invite";

let $doc = $(document);
$doc.ready(function () {
    $doc.on('submit', '.form-js', function (e) {
        e.preventDefault();
        let $form = $(this);
        let this_form = $form.attr('id');
        let test = true,
            thsInputs = $form.find('input, textarea'),
            $select = $form.find('select[required]');
        let $address = $form.find('input.address-js[required]');
        $select.each(function () {
            let $ths = $(this);
            let $label = $ths.closest('.form-group');
            let val = $ths.val();
            console.log(val);
            if (Array.isArray(val) && val.length === 0) {
                console.log(1);
                test = false;
                $label.addClass('error');
            } else {
                console.log(2);
                $label.removeClass('error');
                if (val === null || val === undefined) {
                    console.log(3);
                    test = false;
                    $label.addClass('error');
                }
            }
        });
        thsInputs.each(function () {
            let thsInput = $(this),
                $label = thsInput.closest('.form_element'),
                thsInputType = thsInput.attr('type'),
                thsInputVal = thsInput.val().trim(),
                inputReg = new RegExp(thsInput.data('reg')),
                inputTest = inputReg.test(thsInputVal);

            if (thsInput.attr('required')) {
                if (thsInputVal.length <= 0) {
                    test = false;
                    thsInput.addClass('error');
                    $label.addClass('error');
                    thsInput.focus();
                    if (thsInputType === 'file') {
                        $form.find('.cabinet-item__photo-item').eq(0).addClass('error');
                        $('html, body').animate({
                            scrollTop: $form.find('.cabinet-item__photo-item').eq(0).offset().top
                        });
                    }
                } else {
                    thsInput.removeClass('error');
                    $label.removeClass('error');
                    if (thsInput.data('reg')) {
                        if (inputTest === false) {
                            test = false;
                            thsInput.addClass('error');
                            $label.addClass('error');
                            thsInput.focus();
                        } else {
                            thsInput.removeClass('error');
                            $label.removeClass('error');
                        }
                    }
                    if (thsInputType === 'file') {
                        $form.find('.cabinet-item__photo-item').eq(0).removeClass('error');
                    }
                }
            }
            if (thsInput.hasClass('time-input')) {
                if (validateTime(thsInputVal)) {
                    thsInput.removeClass('error');
                    $label.removeClass('error');
                } else {
                    test = false;
                    thsInput.addClass('error');
                    $label.addClass('error');
                    thsInput.focus();
                }
            }
        });
        let $password = $form.find('input[name="password"]');
        let $passwordRepeat = $form.find('input[name="repeat_password"]');
        let $passwordOld = $form.find('input[name="old_password"]');
        let $passwordNew = $form.find('input[name="new_password"]');
        if (!$form.hasClass('login-form')) {
            if ($password.length > 0 && $passwordRepeat.length > 0) {
                if ($password.val() !== $passwordRepeat.val()) {
                    $password.addClass('error');
                    $passwordRepeat.addClass('error');
                    return;
                }
                if (!isValidPassword($password.val())) {
                    showMassage(errorPswMsg);
                    $password.addClass('error');
                    $passwordRepeat.addClass('error');
                    return;
                }
                $password.removeClass('error');
                $passwordRepeat.removeClass('error');
            } else if ($password.length > 0 && $password.val().length > 0) {
                if (!isValidPassword($password.val())) {
                    showMassage(errorPswMsg);
                    $password.addClass('error');
                    $passwordRepeat.addClass('error');
                    return;
                }
                $password.removeClass('error');
                $passwordRepeat.removeClass('error');
            }
            if ($passwordOld.length > 0 && $passwordNew.length > 0) {
                if (!isValidPassword($passwordNew.val())) {
                    showMassage(errorPswMsg);
                    $passwordNew.addClass('error');
                    return;
                }
                $passwordNew.removeClass('error');
            }
        }
        let $inp = $form.find('input[name="consent"]');
        if ($inp.length > 0) {
            if ($inp.prop('checked') === false) {
                $inp.closest('.form-consent').addClass('error');
                return;
            }
            $inp.closest('.form-consent').removeClass('error');
        }
        if ($address.length > 0) {
            let addressTest = true;
            $address.each(function (index) {
                let $el = $(this);
                let val = $el.val() || '';
                let selected = $el.attr('data-selected') || '';
                if (selected.trim() !== val.trim()) {
                    test = false;
                    addressTest = false;
                    $el.addClass('error');
                } else {
                    $el.removeClass('error');
                }
                if (val.length === 0) {
                    test = false;
                    $el.addClass('error');
                }
            });
            if (!addressTest) showMassage(locationErrorString);
        }
        if ($form.hasClass('comment-form')) {
            if ($form.find('.value-field').val().trim().length === 0) return;
        }
        if (test) {
            let thisForm = document.getElementById(this_form);
            let formData = new FormData(thisForm);
            showPreloader();
            $.ajax({
                type: $form.attr('method'),
                url: adminAjax,
                processData: false,
                contentType: false,
                data: formData,
            }).done(function (r) {
                const projectID = $form.find('[name="project_id"]').val();
                if (projectID !== undefined) localStorage.removeItem('comment-for-project-' + projectID);
                let _r = r || '';
                const resetTriggerTest = !$form.hasClass('profile-form') && !$form.hasClass('profile-notifications') && !$form.hasClass('change-user-form');
                if (!_r.includes('Error establishing a database connection')) {
                    if (resetTriggerTest) $form.trigger('reset');
                    $form.find('.form-files-result').html('');
                }
                if (r) {

                    if (isJsonString(r)) {
                        let res = JSON.parse(r);
                        if (res.avatar !== undefined) {
                            $doc.find('.profile-head-user__avatar img').attr('src', res.avatar);
                            $doc.find('.header-avatar img').attr('src', res.avatar);
                            $doc.find('.avatar-modal-image img').attr('src', res.avatar);
                        }
                        if ($form.hasClass('user-avatar-form')) {
                            if (res.avatar !== undefined) {
                                $form.addClass('uploaded-avatar');
                            } else {
                                $form.removeClass('uploaded-avatar');
                            }
                        }
                        if (res.change_data !== undefined) {
                            const changedData = res.change_data;
                            if (changedData.user_id === undefined) {
                                if (changedData.name) {
                                    $doc.find('.profile-head-user__name').text(changedData.name);
                                }
                                if (changedData.email) {
                                    $doc.find('.profile-email').text(changedData.email);
                                }
                                if (changedData.user_tel) {
                                    $doc.find('.profile-tel').text(changedData.user_tel);
                                }
                                if (changedData.position) {
                                    $doc.find('.profile-head-position').text(changedData.position);
                                }
                            } else {
                                const $row = $doc.find('.users-table-body-row[data-id="' + changedData.user_id + '"]');
                                if (changedData.name) {
                                    $row.find('.users-table-item__name').text(changedData.name);
                                }
                                if (changedData.email) {
                                    $row.find('.profile-email').text(changedData.email);
                                }
                                if (changedData.user_tel) {
                                    $row.find('.profile-tel').text(changedData.user_tel);
                                }
                                if (changedData.position) {
                                    $row.find('.users-table__position').text(changedData.position);
                                }
                            }

                        }
                        if (res.msg !== '' && res.msg !== undefined) {
                            if ($form.hasClass('report-footer-form')) {
                                $doc.find('.report-cart-changed').text(res.msg);
                            } else {
                                showMassage(res.msg);
                            }
                        }
                        if ($form.hasClass('login-form') && res.type === 'success' || res.is_reload === 'true') {
                            window.location.reload();
                            return;
                        }
                        if (res.comment_html !== '' && res.comment_html !== undefined) {
                            $doc.find('.section-comments-list').prepend(res.comment_html);
                        }
                        if (res.comments_html !== '' && res.comments_html !== undefined) {
                            $doc.find('.section-comments-content').html(res.comments_html);
                        }
                        if (res.events_html !== undefined) {
                            $doc.find('.events-section .container').html(res.events_html);
                        }
                        if (res.event_html !== undefined && res.event_id !== undefined) {
                            $doc.find('#event-' + res.event_id).replaceWith(res.event_html);
                        }
                        if (res.comment_html_update !== '' && res.comment_html_update !== undefined) {
                            let comment_id = res.comment_id;
                            $doc.find('#comment-' + comment_id).replaceWith(res.comment_html_update);
                        }
                        if ($form.hasClass('report-footer-form')) {
                            if (res.time !== '' && res.time !== undefined) {
                                $doc.find('.report-cart-sum').text(res.time);
                                $doc.find('.timer-result').text(res.time);
                                $doc.find('.timer-work-time span').text(res.time);
                                $form.remove();
                            }
                        }
                        if (res.url !== undefined) {
                            showPreloader();
                            renderMain({url: res.url, addToHistory: true});
                        }
                    } else {
                        showMassage(r);
                    }
                }
                hidePreloader();
                setQiullText();
                const invite = new Invite();
            });
        }
    });
    $doc.on('change', '.profile-notifications input', function (e) {
        $(this).closest('form').trigger('submit');
    });
});