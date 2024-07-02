import {sendRequest} from "./_helpers";
import {setInterval} from "worker-timers";
import {Howl, Howler} from 'howler';

export default function checkingNotifications() {
    setNotificationsNumber();
    setInterval(setNotificationsNumber, 20000);
}
export const setNotificationsNumber = () => {
    let $selector = $(document).find('.header-notification-button span');
    if ($selector.length === 0) return;
    let oldNum = $selector.text() || 0;
    oldNum = Number(oldNum);
    sendRequest(adminAjax, {
        action: 'get_user_notifications'
    }, 'POST', false).then((res) => {
        let newNum = res.count || 0;
        let notifications = res.notifications || [];
        let hash = res.hash || '';
        let notificationsHash = localStorage.getItem('notificationsHash') || '';
        if (hash !== notificationsHash && newNum > 0) {
            setTimeout(newMessageSoundPlay, 1000);
            const title = localStorage.getItem('title') || $(document).find('title').text();
            $(document).find('title').text('(' + newNum + ') ' + title);
        }
        localStorage.setItem('notificationsHash', hash);
        newNum = Number(newNum);
        $selector.text(newNum);
    }).catch((error) => {
        console.error('Помилка:', error);
    });
}

export function newMessageSoundPlay() {
    const audioElement = document.getElementById('new-message-sound');
    const src = audioElement.getAttribute('src');
    if (src) {
        const audio = new Audio(src);
        audio.play();

        const sound = new Howl({
            src: [src]
        });

        sound.play();
    }
    // if (audioElement) {
    //     audioElement.muted = false;
    //     audioElement.play();
    //     audioElement.oncanplaythrough = function () {
    //         audioElement.play();
    //     };
    //     audioElement.load();
    // }
}