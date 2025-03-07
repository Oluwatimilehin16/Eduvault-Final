
function toggleMenu() {
    const menu = document.querySelector('.dropdown-menu');
    menu.classList.toggle('active');
}


let currentVideo = 0;
const videos = document.querySelectorAll('.video-slide');

function showVideo(index) {
    videos.forEach((video, i) => {
        video.style.display = i === index ? "block" : "none";
    });
}

function prevVideo() {
    currentVideo = (currentVideo - 1 + videos.length) % videos.length;
    showVideo(currentVideo);
}

function nextVideo() {
    currentVideo = (currentVideo + 1) % videos.length;
    showVideo(currentVideo);
}