// SOUND - Modern Audio Player & Public SPA Navigation
document.addEventListener('DOMContentLoaded', function() {
    const playPauseBtn = document.getElementById('play-pause-btn');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const muteBtn = document.getElementById('mute-btn');
    const progressBar = document.querySelector('.progress-bar');
    const progress = document.querySelector('.progress');
    const volumeBar = document.querySelector('.volume-bar');
    const volume = document.querySelector('.volume');
    const currentTimeElement = document.querySelector('.current-time');
    const totalTimeElement = document.querySelector('.total-time');
    const trackTitleElement = document.getElementById('player-track-title') || document.querySelector('.track-info .title');
    const trackArtistElement = document.getElementById('player-track-artist') || document.querySelector('.track-info .artist');
    const trackCoverElement = document.getElementById('player-track-cover') || document.querySelector('.track-info .cover');

    let audio = new Audio();
    let isPlaying = false;
    let currentTrackIndex = -1;
    let trackList = [];
    let isMuted = false;
    let currentVolume = 0.7;

    // SVG Icons
    const playIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="8 5 19 12 8 19 8 5"></polygon></svg>';
    const pauseIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>';
    const volumeHighIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>';
    const volumeMuteIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><line x1="23" y1="9" x2="17" y2="15"></line><line x1="17" y1="9" x2="23" y2="15"></line></svg>';

    // Initial volume
    audio.volume = currentVolume;
    if (volume) volume.style.width = (currentVolume * 100) + '%';

    // Play/Pause functionality
    if (playPauseBtn) {
        playPauseBtn.addEventListener('click', function() {
            if (!audio.src) {
                // If there are tracks on the page, play the first one
                if (trackList.length > 0) {
                    currentTrackIndex = 0;
                    loadTrack(currentTrackIndex);
                    audio.play().catch(function() {});
                    isPlaying = true;
                    playPauseBtn.innerHTML = pauseIcon;
                    return;
                }
                return;
            }

            if (isPlaying) {
                audio.pause();
                isPlaying = false;
                playPauseBtn.innerHTML = playIcon;
            } else {
                audio.play().catch(function(e) {
                    console.warn('Playback error:', e);
                });
                isPlaying = true;
                playPauseBtn.innerHTML = pauseIcon;
            }
        });
    }

    // Previous track
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            if (trackList.length === 0) return;
            currentTrackIndex = (currentTrackIndex - 1 + trackList.length) % trackList.length;
            loadTrack(currentTrackIndex);
            audio.play().catch(function() {});
            isPlaying = true;
            if (playPauseBtn) playPauseBtn.innerHTML = pauseIcon;
        });
    }

    // Next track
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            if (trackList.length === 0) return;
            currentTrackIndex = (currentTrackIndex + 1) % trackList.length;
            loadTrack(currentTrackIndex);
            audio.play().catch(function() {});
            isPlaying = true;
            if (playPauseBtn) playPauseBtn.innerHTML = pauseIcon;
        });
    }

    // Mute toggle
    if (muteBtn) {
        muteBtn.addEventListener('click', function() {
            isMuted = !isMuted;
            audio.muted = isMuted;
            muteBtn.innerHTML = isMuted ? volumeMuteIcon : volumeHighIcon;
            if (volume) volume.style.width = isMuted ? '0%' : (currentVolume * 100) + '%';
        });
    }

    // Progress bar update
    audio.addEventListener('timeupdate', function() {
        if (audio.duration && !isNaN(audio.duration)) {
            const progressPercent = (audio.currentTime / audio.duration) * 100;
            if (progress) progress.style.width = progressPercent + '%';

            const currentMinutes = Math.floor(audio.currentTime / 60);
            const currentSeconds = Math.floor(audio.currentTime % 60).toString().padStart(2, '0');
            if (currentTimeElement) currentTimeElement.textContent = `${currentMinutes}:${currentSeconds}`;
        }
    });

    function formatDuration(sec) {
        if (!sec || isNaN(sec) || !isFinite(sec)) return '0:00';
        const m = Math.floor(sec / 60);
        const s = Math.floor(sec % 60).toString().padStart(2, '0');
        return `${m}:${s}`;
    }

    function syncDuration() {
        if (audio.duration && !isNaN(audio.duration) && isFinite(audio.duration)) {
            if (totalTimeElement) totalTimeElement.textContent = formatDuration(audio.duration);
        }
    }

    // Loaded metadata & duration update events
    audio.addEventListener('loadedmetadata', syncDuration);
    audio.addEventListener('durationchange', syncDuration);
    audio.addEventListener('canplay', syncDuration);

    // Seek bar click
    if (progressBar) {
        progressBar.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const width = rect.width;
            if (audio.duration && !isNaN(audio.duration)) {
                audio.currentTime = (clickX / width) * audio.duration;
            }
        });
    }

    // Volume bar click
    if (volumeBar) {
        volumeBar.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const clickX = Math.max(0, Math.min(e.clientX - rect.left, rect.width));
            currentVolume = clickX / rect.width;
            audio.volume = currentVolume;
            isMuted = false;
            audio.muted = false;
            if (muteBtn) muteBtn.innerHTML = volumeHighIcon;
            if (volume) volume.style.width = (currentVolume * 100) + '%';
        });
    }

    // On track ended
    audio.addEventListener('ended', function() {
        if (trackList.length > 1) {
            if (nextBtn) nextBtn.click();
        } else {
            isPlaying = false;
            if (playPauseBtn) playPauseBtn.innerHTML = playIcon;
            if (progress) progress.style.width = '0%';
            if (currentTimeElement) currentTimeElement.textContent = '0:00';
        }
    });

    function normalizeAssetUrl(url) {
        if (!url) return '';
        url = url.trim();
        if (url.startsWith('http://') || url.startsWith('https://')) return url;
        if (url.startsWith('/sound/')) return url;
        if (url.startsWith('sound/')) return '/' + url;
        return '/sound/' + url.replace(/^\/+/, '');
    }

    function renderPlayerCover(title, cover) {
        if (!trackCoverElement) return;
        const initials = (title || 'SO').substring(0, 2).toUpperCase();

        if (cover && cover.trim() !== '') {
            const normalizedCover = normalizeAssetUrl(cover);
            const testImg = new Image();
            testImg.onload = function() {
                trackCoverElement.style.backgroundImage = `url('${normalizedCover}')`;
                trackCoverElement.style.backgroundColor = 'transparent';
                trackCoverElement.innerHTML = '';
            };
            testImg.onerror = function() {
                trackCoverElement.style.backgroundImage = 'none';
                trackCoverElement.style.backgroundColor = '#242424';
                trackCoverElement.innerHTML = `<span style="color: #FF5A3C; font-weight: 800; font-size: 14px;">${initials}</span>`;
            };
            testImg.src = normalizedCover;
        } else {
            trackCoverElement.style.backgroundImage = 'none';
            trackCoverElement.style.backgroundColor = '#242424';
            trackCoverElement.innerHTML = `<span style="color: #FF5A3C; font-weight: 800; font-size: 14px;">${initials}</span>`;
        }
    }

    // Load track function
    function loadTrack(index) {
        if (index < 0 || index >= trackList.length) return;

        const track = trackList[index];
        audio.src = normalizeAssetUrl(track.url);
        if (trackTitleElement) trackTitleElement.textContent = track.title || 'Unknown Track';
        if (trackArtistElement) trackArtistElement.textContent = track.artist || 'Unknown Artist';
        if (currentTimeElement) currentTimeElement.textContent = '0:00';
        if (totalTimeElement) totalTimeElement.textContent = '0:00';
        if (progress) progress.style.width = '0%';

        renderPlayerCover(track.title, track.cover);
    }

    // Global helper to play a track immediately from cards / buttons
    window.playTrackDirect = function(url, title, artist, cover) {
        if (!url) return;
        audio.src = normalizeAssetUrl(url);
        if (trackTitleElement) trackTitleElement.textContent = title || 'Unknown Track';
        if (trackArtistElement) trackArtistElement.textContent = artist || 'Unknown Artist';
        if (currentTimeElement) currentTimeElement.textContent = '0:00';
        if (totalTimeElement) totalTimeElement.textContent = '0:00';
        if (progress) progress.style.width = '0%';

        renderPlayerCover(title, cover);

        audio.play().then(function() {
            isPlaying = true;
            if (playPauseBtn) playPauseBtn.innerHTML = pauseIcon;
        }).catch(function(err) {
            console.warn('Playback error:', err);
        });
    };

    // Global tracklist updater
    window.updateTrackList = function(newTrackList) {
        if (Array.isArray(newTrackList) && newTrackList.length > 0) {
            trackList = newTrackList;
        } else {
            // Scan current DOM for any data-track elements
            scanPageForTracks();
        }
    };

    function scanPageForTracks() {
        const trackElements = document.querySelectorAll('[data-track-url]');
        if (trackElements.length > 0) {
            const found = [];
            trackElements.forEach(function(el) {
                found.push({
                    url: el.getAttribute('data-track-url'),
                    title: el.getAttribute('data-track-title') || 'Unknown',
                    artist: el.getAttribute('data-track-artist') || 'Unknown',
                    cover: el.getAttribute('data-track-cover') || ''
                });
            });
            trackList = found;
        }
    }

    scanPageForTracks();

    // Event delegation for play buttons on cards or lists
    document.addEventListener('click', function(e) {
        const playBtn = e.target.closest('.card-play-overlay, .btn-round-play, [data-action="play-track"]');
        if (playBtn) {
            const card = playBtn.closest('[data-track-url]') || playBtn;
            const url = card.getAttribute('data-track-url');
            if (url) {
                e.preventDefault();
                e.stopPropagation();
                const title = card.getAttribute('data-track-title') || 'Track';
                const artist = card.getAttribute('data-track-artist') || 'Artist';
                const cover = card.getAttribute('data-track-cover') || '';
                window.playTrackDirect(url, title, artist, cover);
            }
        }
    });

    // Spacebar keyboard shortcut for play/pause (when not typing in an input/textarea)
    document.addEventListener('keydown', function(e) {
        if (e.code === 'Space') {
            const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
            if (activeTag === 'input' || activeTag === 'textarea' || activeTag === 'select') {
                return;
            }
            e.preventDefault();
            if (playPauseBtn) playPauseBtn.click();
        }
    });

    // =========================================================================
    // SPA Pjax Navigation for Public Pages
    // =========================================================================
    function isExcludedUrl(urlStr) {
        try {
            const parsed = new URL(urlStr, window.location.origin);
            const path = parsed.pathname.toLowerCase();
            return path.includes('/admin') ||
                   path.endsWith('/login.php') ||
                   path.endsWith('/register.php') ||
                   path.endsWith('/logout.php');
        } catch (e) {
            return true;
        }
    }

    function updatePublicActiveNav(url) {
        try {
            const currentPath = new URL(url, window.location.origin).pathname;
            const navLinks = document.querySelectorAll('.sidebar nav a');
            navLinks.forEach(link => {
                const linkPath = new URL(link.href, window.location.origin).pathname;
                const isMatch = (linkPath === currentPath) ||
                                (currentPath.endsWith('/') && linkPath.endsWith('/index.php')) ||
                                (currentPath.endsWith('/index.php') && linkPath.endsWith('/'));

                if (isMatch) {
                    link.parentElement.classList.add('active');
                } else {
                    link.parentElement.classList.remove('active');
                }
            });
        } catch (e) {}
    }

    function loadPjaxPage(url, pushState = true) {
        if (isExcludedUrl(url) || isExcludedUrl(window.location.href)) {
            window.location.href = url;
            return;
        }

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error ' + response.status);
                }
                if (response.redirected && isExcludedUrl(response.url)) {
                    window.location.href = response.url;
                    return null;
                }
                return response.text();
            })
            .then(html => {
                if (!html) return;
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newMain = doc.querySelector('main');
                const currentMain = document.querySelector('main');

                if (!newMain || !currentMain) {
                    window.location.href = url;
                    return;
                }

                currentMain.innerHTML = newMain.innerHTML;
                if (doc.title) {
                    document.title = doc.title;
                }

                if (pushState) {
                    window.history.pushState({ pjax: true, url: url }, doc.title || '', url);
                }

                updatePublicActiveNav(url);
                currentMain.scrollTop = 0;
                window.scrollTo(0, 0);

                // Scan page for new tracks
                scanPageForTracks();

                if (typeof updateTrackList === 'function') {
                    updateTrackList();
                }
            })
            .catch(err => {
                console.warn('Navigation fetch failed, falling back to full page load:', err);
                window.location.href = url;
            });
    }

    // Link click interception for SPA navigation
    if (!isExcludedUrl(window.location.href)) {
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link || !link.href) return;

            // Don't intercept external, target="_blank", modifier keys, download, or anchor hashes
            if (!link.href.startsWith(window.location.origin)) return;
            if (link.target === '_blank' || link.target === '_top' || link.target === '_parent') return;
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            if (link.hasAttribute('download')) return;

            try {
                const targetUrl = new URL(link.href);
                if (targetUrl.pathname === window.location.pathname && targetUrl.search === window.location.search && targetUrl.hash) {
                    return;
                }
            } catch (err) {}

            // Must NOT run on admin or login/register/logout
            if (isExcludedUrl(link.href) || isExcludedUrl(window.location.href)) {
                return;
            }

            e.preventDefault();
            loadPjaxPage(link.href, true);
        });

        // Handle popstate for browser Back/Forward navigation
        window.addEventListener('popstate', function(e) {
            if (isExcludedUrl(window.location.href)) {
                window.location.reload();
                return;
            }
            loadPjaxPage(window.location.href, false);
        });
    }
});