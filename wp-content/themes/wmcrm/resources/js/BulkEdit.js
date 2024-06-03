import {setCookie, getCookie, removeArrayElement, sendRequest, renderMain} from './_helpers';

export default class BulkEdit {
    constructor() {
        this.$doc = $(document);
        this.cookieName = 'selected_project';
        this.init();
    }

    test() {
        const _this = this;
        const $doc = _this.$doc;
        return $doc.find('body').hasClass('user-admin');
    }

    setSelectedProjects() {
        const _this = this;
        const $doc = _this.$doc;
        $doc.find('.project-item').addClass('selected');
    }

    unselectedProjects() {
        const _this = this;
        const $doc = _this.$doc;
        $doc.find('.project-item.selected').removeClass('selected');
    }

    toggleArchiveButton() {
        const _this = this;
        const $doc = _this.$doc;
        const cookieName = _this.cookieName;
        let selectedProject = getCookie(cookieName);
        if (selectedProject) {
            selectedProject = selectedProject.split(',');
            $doc.find('.archive-projects').removeClass('show');
        } else {
            selectedProject = [];
        }
        if (selectedProject.length === 0) {
            $doc.find('.archive-projects').removeClass('show');
            _this.unselectedProjects();
        } else {
            $doc.find('.archive-projects').addClass('show');
        }
    }

    init() {
        const _this = this;
        const $doc = _this.$doc;
        const test = _this.test();
        const cookieName = _this.cookieName;
        if (test) {
            $doc.on('click', '.project-item.select-edit .project-item-icon', function (e) {
                e.preventDefault();
                let $t = $(this);
                $t = $t.closest('.project-item');
                let selectedProject = getCookie(cookieName);
                if (selectedProject) {
                    selectedProject = selectedProject.split(',');
                } else {
                    selectedProject = [];
                }
                const id = $t.attr('data-id');
                const isSelected = $t.hasClass('selected');
                if (isSelected) {
                    $t.removeClass('selected');
                    if (selectedProject.includes('-1')) {
                        selectedProject = [];
                    } else {
                        if (selectedProject) selectedProject = removeArrayElement(id, selectedProject);
                    }
                } else {
                    $t.addClass('selected');
                    selectedProject.push(id);
                }
                setCookie(cookieName, selectedProject ? selectedProject.join(',') : '', 1);
                _this.toggleArchiveButton();
            });
            $doc.on('click', '.select-all-project', function (e) {
                e.preventDefault();
                let selectedProject = [];
                const $t = $(this);
                const activeSting = $t.attr('data-active');
                const notActiveString = $t.attr('data-not-active');
                if ($t.hasClass('active')) {
                    $t.removeClass('active');
                    selectedProject = [];
                    $t.text(activeSting);
                    _this.unselectedProjects();
                } else {
                    $t.addClass('active');
                    selectedProject = ['-1'];
                    $t.text(notActiveString);
                    _this.setSelectedProjects();
                }
                setCookie(cookieName, selectedProject ? selectedProject.join(',') : '', 1);
                _this.toggleArchiveButton();
            });
            $doc.on('click', '.archive-projects', function (e) {
                e.preventDefault();
                const query = document.query || false;
                sendRequest(adminAjax, {
                    'action': 'archive_projects',query:query
                }).then((res) => {
                    setCookie(cookieName, '', 1);
                    _this.toggleArchiveButton();
                    renderMain({
                        url: res.url
                    });
                });
            });
        }
    }
}