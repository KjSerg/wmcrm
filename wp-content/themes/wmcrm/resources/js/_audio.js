import {Howl} from "howler";

export default function playSound(elementID) {
    const audioElement = document.getElementById(elementID);
    const src = audioElement.getAttribute('src');
    if (src) {
        const audio = new Audio(src);
        audio.play();
        const sound = new Howl({
            src: [src]
        });
        sound.play();
    }
}