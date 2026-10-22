// Player functionality
document.addEventListener('DOMContentLoaded', function() {
    const playPauseBtn = document.getElementById('play-pause-btn');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const muteBtn = document.getElementById('mute-btn');
    const progressBar = document.querySelector('.progress-bar');
    const volumeBar = document.querySelector('.volume-bar');
    const currentTimeElement = document.querySelector('.current-time');
    const totalTimeElement = document.querySelector('.total-time');
    const trackTitleElement = document.querySelector('.track-info .title');
    const trackArtistElement = document.querySelector('.track-info .artist');
    const trackCoverElement = document.querySelector('.track-info .cover');

    let audio = new Audio();
    let isPlaying = false;
    let currentTrackIndex = -1;
    let trackList = [];
    let isMuted = false;
    let currentVolume = 0.7;

    // Set initial volume
    audio.volume = currentVolume;

    // Play/Pause functionality
    playPauseBtn.addEventListener('click', function() {
        if (isPlaying) {
            audio.pause();
            isPlaying = false;
            playPauseBtn.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 5V19L19 12L8 5Z" fill="currentColor"/></svg>';
        } else {
            if (currentTrackIndex === -1 && trackList.length > 0) {
                currentTrackIndex = 0;
                loadTrack(currentTrackIndex);
            }
            audio.play();
            isPlaying = true;
            playPauseBtn.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 19H10V5H6V19ZM14 5V19H18V5H14Z" fill="currentColor"/></svg>';
        }
    });

    // Previous track functionality
    prevBtn.addEventListener('click', function() {
        if (trackList.length === 0) return;
        currentTrackIndex = (currentTrackIndex - 1 + trackList.length) % trackList.length;
        loadTrack(currentTrackIndex);
        audio.play();
        isPlaying = true;
        playPauseBtn.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 19H10V5H6V19ZM14 5V19H18V5H14Z" fill="currentColor"/></svg>';
    });

    // Next track functionality
    nextBtn.addEventListener('click', function() {
        if (trackList.length === 0) return;
        currentTrackIndex = (currentTrackIndex + 1) % trackList.length;
        loadTrack(currentTrackIndex);
        audio.play();
        isPlaying = true;
        playPauseBtn.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 19H10V5H6V19ZM14 5V19H18V5H14Z" fill="currentColor"/></svg>';
    });

    // Mute functionality
    muteBtn.addEventListener('click', function() {
        isMuted = !isMuted;
        audio.muted = isMuted;
        if (isMuted) {
            muteBtn.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 4L9 7H6C5.44772 7 5 7.44772 5 8V16C5 16.5523 5.44772 17 6 17H9L12 20V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        } else {
            muteBtn.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 4L9 7H6C5.44772 7 5 7.44772 5 8V16C5 16.5523 5.44772 17 6 17H9L12 20V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        }
    });

    // Progress bar functionality
    audio.addEventListener('timeupdate', function() {
        if (audio.duration) {
            const progressPercent = (audio.currentTime / audio.duration) * 100;
            document.querySelector('.progress').style.width = progressPercent + '%';

            // Update current time
            const currentMinutes = Math.floor(audio.currentTime / 60);
            const currentSeconds = Math.floor(audio.currentTime % 60).toString().padStart(2, '0');
            currentTimeElement.textContent = `${currentMinutes}:${currentSeconds}`;
        }
    });

    // Click on progress bar to seek
    progressBar.addEventListener('click', function(e) {
        const width = this.clientWidth;
        const clickX = e.offsetX;
        const duration = audio.duration;
        audio.currentTime = (clickX / width) * duration;
    });

    // Volume bar functionality
    volumeBar.addEventListener('click', function(e) {
        const width = this.clientWidth;
        const clickX = e.offsetX;
        currentVolume = clickX / width;
        audio.volume = currentVolume;
        document.querySelector('.volume').style.width = (currentVolume * 100) + '%';
    });

    // When track ends, play next track
    audio.addEventListener('ended', function() {
        nextBtn.click();
    });

    // Load track function
    function loadTrack(index) {
        if (index < 0 || index >= trackList.length) return;

        const track = trackList[index];
        audio.src = track.url;
        trackTitleElement.textContent = track.title;
        trackArtistElement.textContent = track.artist;

        // Set cover image or placeholder
        if (track.cover) {
            trackCoverElement.style.backgroundImage = `url('${track.cover}')`;
            trackCoverElement.style.backgroundSize = 'cover';
        } else {
            // Generate gradient placeholder with initials
            const initials = track.title.substring(0, 2).toUpperCase();
            const hue = Math.floor(Math.random() * 360);
            trackCoverElement.style.backgroundImage = `linear-gradient(135deg, hsl(${hue}, 70%, 60%), hsl(${hue}, 70%, 40%))`;
            trackCoverElement.innerHTML = `<span>${initials}</span>`;
        }

        // Set total time
        audio.addEventListener('loadedmetadata', function() {
            const duration = audio.duration;
            const minutes = Math.floor(duration / 60);
            const seconds = Math.floor(duration % 60).toString().padStart(2, '0');
            totalTimeElement.textContent = `${minutes}:${seconds}`;
        });
    }

    // Keyboard shortcut for play/pause (space bar)
    document.addEventListener('keydown', function(e) {
        if (e.code === 'Space') {
            e.preventDefault();
            playPauseBtn.click();
        }
    });

    // Progressive enhancement for navigation
    document.addEventListener('click', function(e) {
        if (e.target.tagName === 'A' && e.target.href.startsWith(window.location.origin)) {
            e.preventDefault();
            const url = e.target.href;

            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.querySelector('main').innerHTML;
                    document.querySelector('main').innerHTML = newContent;
                    window.history.pushState({}, '', url);

                    // Update track list if needed
                    if (typeof updateTrackList === 'function') {
                        updateTrackList();
                    }
                });
        }
    });

    // Function to update track list (to be implemented by pages)
    window.updateTrackList = function(newTrackList) {
        trackList = newTrackList;
        currentTrackIndex = -1;
        isPlaying = false;
        playPauseBtn.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 5V19L19 12L8 5Z" fill="currentColor"/></svg>';
        trackTitleElement.textContent = 'No track playing';
        trackArtistElement.textContent = '-';
        trackCoverElement.style.backgroundImage = 'none';
        trackCoverElement.innerHTML = '';
        currentTimeElement.textContent = '0:00';
        totalTimeElement.textContent = '0:00';
        document.querySelector('.progress').style.width = '0%';
    };
});