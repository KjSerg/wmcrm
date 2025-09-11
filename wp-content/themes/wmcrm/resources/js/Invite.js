import {isObjectEmpty, isJsonString, getCookie, setCookie, getCurrentDate} from './_helpers';

export default class Invite {
    constructor() {
        this.users = {};
        this.loading = false;
        this.setUsersData();
        this.$doc = $(document);
    }

    setUsersData() {
        const t = this;
        const isLoading = t.loading;
        let storage = localStorage.getItem('users');
        let storageDate = localStorage.getItem('users-date') || '';
        const currentDate = getCurrentDate();
        if (currentDate === storageDate && storage) {
            if (isJsonString(storage)) {
                storage = JSON.parse(storage);
                if (storage && !isObjectEmpty(storage)) {
                    t.users = storage;
                    t.setUsersAvatar();
                    return;
                }
            }

        }
        if (isObjectEmpty(t.users) && !isLoading) {
            t.loading = true;
            $.ajax({
                type: "post",
                url: adminAjax,
                data: {
                    action: 'get_data_users'
                },
            }).done(function (r) {
                if (r && isJsonString(r)) t.users = JSON.parse(r);
                t.loading = false;
                localStorage.setItem('users', r);
                localStorage.setItem('users-date', currentDate);
                t.setUsersAvatar();
            });
        }
    }

    setUsersAvatar() {
        const t = this;
        const isLoading = t.loading;
        const $doc = t.$doc;
        if (!isLoading) {
            if (isObjectEmpty(t.users)) {
                t.setUsersData();
            } else {
                $(document).find('.invite').each(function () {
                    let $t = $(this);
                    if ($t.find('.invite__image').length === 0) {
                        let id = $t.attr('data-user-id');
                        if (id) {
                            let src = t.users[id].src;
                            if (src) $t.prepend('<span class="invite__image"><img class="cover" alt="" src="' + src + '"></span>');
                        }
                    }
                });
            }
        }
    }
}