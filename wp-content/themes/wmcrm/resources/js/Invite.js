import {isObjectEmpty, isJsonString} from './_helpers';

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
                $doc.find('.invite').each(function () {
                    let $t = $(this);
                    if($t.find('.invite__image').length === 0){
                        let id = $t.attr('data-user-id');
                        if (id) {
                            let src = t.users[id].src;
                            if(src) $t.prepend('<span class="invite__image"><img class="cover" alt="" src="' + src + '"></span>');
                        }
                    }
                });
            }
        }
    }
}