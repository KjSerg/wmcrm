import {sendRequest} from "./_helpers";
import {setInterval} from "worker-timers";

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
        if(hash !== notificationsHash && newNum > 0 ) {
            setTimeout(newMessageSoundPlay, 1000);
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
    if (audioElement) {
        audioElement.muted = false;
        audioElement.play();
        audioElement.oncanplaythrough = function () {
            audioElement.play();
        };
        audioElement.load();
    }
}