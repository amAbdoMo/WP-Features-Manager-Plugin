(function () {
	'use strict';

	var players = new Map();

	function booleanData(element, name) {
		return element.dataset[name] === 'true';
	}

	function numberData(element, name, fallback) {
		var value = Number(element.dataset[name]);
		return Number.isFinite(value) ? value : fallback;
	}

	function formatTime(seconds) {
		if (!Number.isFinite(seconds) || seconds < 0) {
			return '0:00';
		}
		var wholeSeconds = Math.floor(seconds);
		var minutes = Math.floor(wholeSeconds / 60);
		var remainingSeconds = wholeSeconds % 60;
		var hours = Math.floor(minutes / 60);
		minutes %= 60;
		return hours ? hours + ':' + String(minutes).padStart(2, '0') + ':' + String(remainingSeconds).padStart(2, '0') : minutes + ':' + String(remainingSeconds).padStart(2, '0');
	}

	function labelsData(root) {
		try {
			return JSON.parse(root.dataset.labels || '{}');
		} catch (error) {
			return {};
		}
	}

	function CustomVideoPlayer(root) {
		this.root = root;
		this.video = root.querySelector('.widgets-manager-custom-video__media');
		this.controls = root.querySelector('.widgets-manager-custom-video__controls');
		this.status = root.querySelector('.widgets-manager-custom-video__status');
		this.seek = root.querySelector('.widgets-manager-custom-video__seek');
		this.volume = root.querySelector('.widgets-manager-custom-video__volume');
		this.currentTime = root.querySelector('.widgets-manager-custom-video__time--current');
		this.duration = root.querySelector('.widgets-manager-custom-video__time--duration');
		this.widgetWrapper = root.closest('.elementor-widget-widgets-manager-custom-video');
		this.abortController = new AbortController();
		this.hideTimer = 0;
		this.clickTimer = 0;
		this.progressFrame = 0;
		this.isSeeking = false;
		this.intersectionObserver = null;
		this.resizeObserver = null;
		this.volumeStorageKey = 'widgets-manager-custom-video-volume';
		this.labels = labelsData(root);
	}

	CustomVideoPlayer.prototype.message = function (key, fallback, replacement) {
		var message = this.labels[key] || fallback;
		return replacement === undefined ? message : message.replace('%s', replacement);
	};

	CustomVideoPlayer.prototype.syncWidgetHeight = function () {
		if (!this.widgetWrapper) {
			return;
		}
		if (this.root.classList.contains('is-fullscreen')) {
			this.widgetWrapper.style.removeProperty('--wm-video-widget-height');
			return;
		}
		var rootHeight = Math.ceil(this.root.getBoundingClientRect().height);
		if (rootHeight > 0) {
			this.widgetWrapper.style.setProperty('--wm-video-widget-height', rootHeight + 'px');
		}
	};

	CustomVideoPlayer.prototype.initialize = function () {
		if (!this.video || !this.controls) {
			return;
		}
		this.video.controls = false;
		this.controls.removeAttribute('aria-hidden');
		this.root.classList.add('is-ready', 'is-controls-visible');
		this.restoreVolume();
		this.bindEvents();
		this.updateAll();
		this.updateUnsupportedControls();
		this.syncWidgetHeight();
		this.observeVisibility();
		this.tryAutoplay();
	};

	CustomVideoPlayer.prototype.bindEvents = function () {
		var self = this;
		var signal = this.abortController.signal;
		this.root.addEventListener('click', function (event) {
			var action = event.target.closest('[data-wm-action]');
			var menu = event.target.closest('[data-wm-menu]');
			var speed = event.target.closest('[data-wm-speed]');
			var track = event.target.closest('[data-wm-track]');
			if (action && self.root.contains(action)) {
				event.preventDefault();
				self.runAction(action.dataset.wmAction);
				if (action.closest('.widgets-manager-custom-video__menu')) {
					self.closeMenus(true);
				}
				return;
			}
			if (menu && self.root.contains(menu)) {
				event.preventDefault();
				self.toggleMenu(menu);
				return;
			}
			if (speed && self.root.contains(speed)) {
				self.setSpeed(Number(speed.dataset.wmSpeed));
				return;
			}
			if (track && self.root.contains(track)) {
				self.setTrack(track.dataset.wmTrack);
				return;
			}
			self.closeMenus(false);
		}, { signal: signal });
		document.addEventListener('pointerdown', function (event) {
			if (!self.root.contains(event.target)) {
				self.closeMenus(false);
			}
		}, { signal: signal });
		this.video.addEventListener('play', function () { self.onPlay(); }, { signal: signal });
		this.video.addEventListener('pause', function () { self.onPause(); }, { signal: signal });
		this.video.addEventListener('ended', function () { self.onEnded(); }, { signal: signal });
		this.video.addEventListener('timeupdate', function () { self.scheduleProgress(); }, { signal: signal });
		this.video.addEventListener('progress', function () { self.updateBuffered(); }, { signal: signal });
		this.video.addEventListener('durationchange', function () { self.updateAll(); }, { signal: signal });
		this.video.addEventListener('loadedmetadata', function () { self.applyStartTime(); self.updateAll(); }, { signal: signal });
		this.video.addEventListener('volumechange', function () { self.updateVolume(); }, { signal: signal });
		this.video.addEventListener('ratechange', function () { self.updateSpeed(); }, { signal: signal });
		this.video.addEventListener('waiting', function () { self.announce(self.message('buffering', 'Video is buffering.')); }, { signal: signal });
		this.video.addEventListener('canplay', function () { self.announce(self.message('ready', 'Video is ready to play.')); }, { signal: signal });
		this.video.addEventListener('error', function () { self.announce(self.message('error', 'This video could not be played.')); }, { signal: signal });
		this.video.addEventListener('click', function () {
			if (!booleanData(self.root, 'clickToToggle')) {
				return;
			}
			if (booleanData(self.root, 'doubleClickFullscreen')) {
				window.clearTimeout(self.clickTimer);
				self.clickTimer = window.setTimeout(function () { self.togglePlay(); }, 250);
				return;
			}
			self.togglePlay();
		}, { signal: signal });
		this.video.addEventListener('dblclick', function () {
			if (booleanData(self.root, 'doubleClickFullscreen')) {
				window.clearTimeout(self.clickTimer);
				self.clickTimer = 0;
				self.toggleFullscreen();
			}
		}, { signal: signal });
		this.seek.addEventListener('input', function () {
			self.isSeeking = true;
			self.updateSeekPreview();
		}, { signal: signal });
		this.seek.addEventListener('change', function () {
			self.seekToPercent(Number(self.seek.value));
			self.isSeeking = false;
		}, { signal: signal });
		this.volume.addEventListener('input', function () { self.setVolume(Number(self.volume.value)); }, { signal: signal });
		this.root.addEventListener('mousemove', function () { self.revealControls(); }, { signal: signal });
		this.root.addEventListener('touchstart', function () { self.revealControls(); }, { signal: signal });
		this.root.addEventListener('focusin', function () { self.revealControls(); }, { signal: signal });
		this.root.addEventListener('keydown', function (event) { self.onKeydown(event); }, { signal: signal });
		document.addEventListener('fullscreenchange', function () { self.updateFullscreen(); }, { signal: signal });
		this.video.addEventListener('enterpictureinpicture', function () { self.announce(self.message('pip_started', 'Picture in Picture started.')); }, { signal: signal });
		this.video.addEventListener('leavepictureinpicture', function () { self.announce(self.message('pip_ended', 'Picture in Picture ended.')); }, { signal: signal });
		window.addEventListener('widgets-manager-custom-video-play', function (event) {
			if (event.detail.pauseOthers && event.detail.root !== self.root && !self.video.paused) {
				self.video.pause();
			}
		}, { signal: signal });
	};

	CustomVideoPlayer.prototype.runAction = function (action) {
		if (action === 'play') {
			this.togglePlay();
		} else if (action === 'rewind') {
			this.seekBy(-numberData(this.root, 'seekInterval', 10));
		} else if (action === 'forward') {
			this.seekBy(numberData(this.root, 'seekInterval', 10));
		} else if (action === 'mute') {
			this.video.muted = !this.video.muted;
		} else if (action === 'fullscreen') {
			this.toggleFullscreen();
		} else if (action === 'pip') {
			this.togglePictureInPicture();
		}
	};

	CustomVideoPlayer.prototype.togglePlay = function () {
		if (this.video.paused || this.video.ended) {
			this.play();
		} else {
			this.video.pause();
		}
	};

	CustomVideoPlayer.prototype.play = function () {
		var self = this;
		var playback = this.video.play();
		if (playback && typeof playback.catch === 'function') {
			playback.catch(function () {
				self.root.classList.remove('is-playing');
				self.root.classList.add('is-controls-visible');
				self.announce(self.message('playback_failed', 'Playback could not start. Select play to try again.'));
			});
		}
	};

	CustomVideoPlayer.prototype.onPlay = function () {
		this.root.classList.add('is-playing', 'is-overlay-dismissed');
		this.root.classList.remove('is-overlay-ended-hidden');
		this.root.classList.add('is-controls-visible');
		this.updatePlayButton();
		this.announce(this.message('playing', 'Video playing.'));
		window.dispatchEvent(new CustomEvent('widgets-manager-custom-video-play', { detail: { root: this.root, pauseOthers: booleanData(this.root, 'pauseOthers') } }));
		this.scheduleHide();
	};

	CustomVideoPlayer.prototype.onPause = function () {
		this.root.classList.remove('is-playing');
		this.root.classList.add('is-controls-visible');
		this.updatePlayButton();
		this.announce(this.message('paused', 'Video paused.'));
		this.clearHideTimer();
	};

	CustomVideoPlayer.prototype.onEnded = function () {
		this.root.classList.remove('is-playing');
		if (booleanData(this.root, 'restoreOverlay')) {
			this.root.classList.remove('is-overlay-dismissed', 'is-overlay-ended-hidden');
		} else {
			this.root.classList.add('is-overlay-ended-hidden');
		}
		this.root.classList.add('is-controls-visible');
		this.updatePlayButton();
		this.announce(this.message('ended', 'Video ended.'));
	};

	CustomVideoPlayer.prototype.scheduleProgress = function () {
		var self = this;
		if (!this.progressFrame) {
			this.progressFrame = window.requestAnimationFrame(function () {
				self.progressFrame = 0;
				self.updateProgress();
			});
		}
		this.applyEndTime();
	};

	CustomVideoPlayer.prototype.updateAll = function () {
		this.updateProgress();
		this.updateBuffered();
		this.updateVolume();
		this.updateSpeed();
		this.updateTrackButtons();
		this.updatePlayButton();
		this.updateFullscreen();
	};

	CustomVideoPlayer.prototype.updateProgress = function () {
		var duration = this.video.duration;
		var current = this.video.currentTime;
		var percent = Number.isFinite(duration) && duration > 0 ? current / duration * 100 : 0;
		if (!this.isSeeking) {
			this.seek.value = String(percent);
		}
		this.seek.style.setProperty('--wm-video-progress', percent + '%');
		this.seek.setAttribute('aria-valuenow', String(Math.round(percent)));
		this.seek.setAttribute('aria-valuetext', formatTime(current));
		this.currentTime.textContent = formatTime(current);
		this.duration.textContent = this.root.dataset.timeFormat === 'elapsed' ? '' : ' / ' + formatTime(duration);
	};

	CustomVideoPlayer.prototype.updateBuffered = function () {
		var duration = this.video.duration;
		var bufferedPercent = 0;
		if (Number.isFinite(duration) && duration > 0 && this.video.buffered.length) {
			bufferedPercent = this.video.buffered.end(this.video.buffered.length - 1) / duration * 100;
		}
		this.seek.style.setProperty('--wm-video-buffer', Math.max(bufferedPercent, Number(this.seek.value)) + '%');
	};

	CustomVideoPlayer.prototype.updateSeekPreview = function () {
		var percent = Number(this.seek.value);
		this.seek.style.setProperty('--wm-video-progress', percent + '%');
		this.currentTime.textContent = formatTime(this.video.duration * percent / 100);
	};

	CustomVideoPlayer.prototype.seekToPercent = function (percent) {
		if (!Number.isFinite(this.video.duration)) {
			return;
		}
		this.video.currentTime = Math.max(0, Math.min(this.video.duration, this.video.duration * percent / 100));
	};

	CustomVideoPlayer.prototype.seekBy = function (seconds) {
		if (Number.isFinite(this.video.duration)) {
			this.video.currentTime = Math.max(0, Math.min(this.video.duration, this.video.currentTime + seconds));
		}
	};

	CustomVideoPlayer.prototype.updateVolume = function () {
		var volume = this.video.muted ? 0 : this.video.volume;
		this.volume.value = String(volume);
		this.volume.style.setProperty('--wm-video-volume', volume * 100 + '%');
		this.volume.setAttribute('aria-valuenow', String(volume));
		this.volume.setAttribute('aria-valuetext', Math.round(volume * 100) + '%');
		var muteButton = this.root.querySelector('[data-wm-action="mute"]');
		if (muteButton) {
			muteButton.setAttribute('aria-pressed', this.video.muted ? 'true' : 'false');
			muteButton.setAttribute('aria-label', this.video.muted ? this.message('unmute', 'Unmute video') : this.message('mute', 'Mute video'));
			muteButton.querySelector('span').textContent = this.video.muted ? '🔇' : '🔊';
		}
		if (booleanData(this.root, 'rememberVolume')) {
			try {
				sessionStorage.setItem(this.volumeStorageKey, String(this.video.volume));
			} catch (error) {
				// Storage can be disabled; volume remains usable for this instance.
			}
		}
	};

	CustomVideoPlayer.prototype.setVolume = function (volume) {
		this.video.volume = Math.max(0, Math.min(1, volume));
		this.video.muted = this.video.volume === 0;
	};

	CustomVideoPlayer.prototype.restoreVolume = function () {
		if (!booleanData(this.root, 'rememberVolume')) {
			return;
		}
		try {
			var storedVolume = Number(sessionStorage.getItem(this.volumeStorageKey));
			if (Number.isFinite(storedVolume) && storedVolume >= 0 && storedVolume <= 1) {
				this.video.volume = storedVolume;
			}
		} catch (error) {
			// Browsers that block session storage continue with the configured volume.
		}
	};

	CustomVideoPlayer.prototype.updatePlayButton = function () {
		var button = this.root.querySelector('.widgets-manager-custom-video__control--play');
		if (!button) {
			return;
		}
		var playing = !this.video.paused && !this.video.ended;
		button.setAttribute('aria-pressed', playing ? 'true' : 'false');
		button.setAttribute('aria-label', playing ? this.message('pause', 'Pause video') : this.message('play', 'Play video'));
	};

	CustomVideoPlayer.prototype.updateSpeed = function () {
		var button = this.root.querySelector('[data-wm-menu="speed"]');
		var buttons = this.root.querySelectorAll('[data-wm-speed]');
		if (button) {
			button.textContent = this.video.playbackRate + '×';
		}
		buttons.forEach(function (speedButton) {
			speedButton.setAttribute('aria-checked', Number(speedButton.dataset.wmSpeed) === this.video.playbackRate ? 'true' : 'false');
		}, this);
	};

	CustomVideoPlayer.prototype.setSpeed = function (speed) {
		if (Number.isFinite(speed) && speed > 0) {
			this.video.playbackRate = speed;
			this.closeMenus(true);
			this.announce(this.message('speed_changed', 'Playback speed set to %s times.', speed));
		}
	};

	CustomVideoPlayer.prototype.updateTrackButtons = function () {
		var selectedTrack = 'off';
		for (var index = 0; index < this.video.textTracks.length; index += 1) {
			if (this.video.textTracks[index].mode === 'showing') {
				selectedTrack = String(index);
				break;
			}
		}
		this.root.querySelectorAll('[data-wm-track]').forEach(function (trackButton) {
			trackButton.setAttribute('aria-checked', trackButton.dataset.wmTrack === selectedTrack ? 'true' : 'false');
		});
	};

	CustomVideoPlayer.prototype.setTrack = function (trackIndex) {
		var tracks = this.video.textTracks;
		for (var index = 0; index < tracks.length; index += 1) {
			tracks[index].mode = String(index) === trackIndex ? 'showing' : 'disabled';
		}
		this.updateTrackButtons();
		this.closeMenus(true);
		this.announce(trackIndex === 'off' ? this.message('captions_off', 'Captions off.') : this.message('captions_changed', 'Captions changed.'));
	};

	CustomVideoPlayer.prototype.menuItems = function (menu) {
		return Array.prototype.slice.call(menu.querySelectorAll('[role="menuitem"], [role="menuitemradio"]')).filter(function (item) {
			return !item.disabled && item.getClientRects().length > 0;
		});
	};

	CustomVideoPlayer.prototype.toggleMenu = function (button) {
		var menu = button.parentNode.querySelector('.widgets-manager-custom-video__menu');
		var isOpen = button.getAttribute('aria-expanded') === 'true';
		this.closeMenus(false);
		if (!isOpen && menu) {
			menu.hidden = false;
			button.setAttribute('aria-expanded', 'true');
			var firstOption = this.menuItems(menu)[0];
			if (firstOption) {
				firstOption.focus();
			}
		}
	};

	CustomVideoPlayer.prototype.closeMenus = function (restoreFocus) {
		var expandedButton = this.root.querySelector('[data-wm-menu][aria-expanded="true"]');
		this.root.querySelectorAll('[data-wm-menu]').forEach(function (button) {
			button.setAttribute('aria-expanded', 'false');
		});
		this.root.querySelectorAll('.widgets-manager-custom-video__menu').forEach(function (menu) {
			menu.hidden = true;
		});
		if (restoreFocus && expandedButton) {
			expandedButton.focus();
		}
	};

	CustomVideoPlayer.prototype.applyStartTime = function () {
		var startTime = numberData(this.root, 'startTime', 0);
		if (startTime > 0 && Number.isFinite(this.video.duration)) {
			this.video.currentTime = Math.min(startTime, this.video.duration);
		}
	};

	CustomVideoPlayer.prototype.applyEndTime = function () {
		var startTime = numberData(this.root, 'startTime', 0);
		var endTime = numberData(this.root, 'endTime', 0);
		if (endTime <= startTime || this.video.currentTime < endTime || this.video.seeking) {
			return;
		}
		if (booleanData(this.root, 'restartAtEnd')) {
			this.video.currentTime = numberData(this.root, 'startTime', 0);
			this.play();
		} else {
			this.video.pause();
		}
	};

	CustomVideoPlayer.prototype.updateUnsupportedControls = function () {
		if (!document.pictureInPictureEnabled) {
			this.root.querySelectorAll('.widgets-manager-custom-video__control--pip').forEach(function (button) {
				button.hidden = true;
			});
		}
		var fullscreen = this.root.querySelector('.widgets-manager-custom-video__control--fullscreen');
		if (fullscreen && !this.root.requestFullscreen) {
			fullscreen.hidden = true;
		}
	};

	CustomVideoPlayer.prototype.togglePictureInPicture = function () {
		var self = this;
		if (!document.pictureInPictureEnabled || !this.video.requestPictureInPicture) {
			return;
		}
		var request = document.pictureInPictureElement ? document.exitPictureInPicture() : this.video.requestPictureInPicture();
		if (request && typeof request.catch === 'function') {
			request.catch(function () { self.announce(self.message('pip_unavailable', 'Picture in Picture is unavailable.')); });
		}
	};

	CustomVideoPlayer.prototype.toggleFullscreen = function () {
		var self = this;
		if (!document.fullscreenElement && this.root.requestFullscreen) {
			var request = this.root.requestFullscreen();
			if (request && typeof request.catch === 'function') {
				request.catch(function () { self.announce(self.message('fullscreen_unavailable', 'Fullscreen is unavailable.')); });
			}
		} else if (document.fullscreenElement && document.exitFullscreen) {
			document.exitFullscreen();
		}
	};

	CustomVideoPlayer.prototype.updateFullscreen = function () {
		var fullscreen = document.fullscreenElement === this.root;
		this.root.classList.toggle('is-fullscreen', fullscreen);
		this.syncWidgetHeight();
		var button = this.root.querySelector('.widgets-manager-custom-video__control--fullscreen');
		if (button) {
			button.setAttribute('aria-pressed', fullscreen ? 'true' : 'false');
			button.setAttribute('aria-label', fullscreen ? this.message('exit_fullscreen', 'Exit fullscreen') : this.message('fullscreen', 'Fullscreen'));
		}
	};

	CustomVideoPlayer.prototype.moveMenuFocus = function (target, key) {
		var menu = target.closest('.widgets-manager-custom-video__menu');
		if (!menu) {
			return false;
		}
		var options = this.menuItems(menu);
		var currentIndex = options.indexOf(target);
		var nextIndex = currentIndex;
		if (key === 'ArrowDown') {
			nextIndex = (currentIndex + 1) % options.length;
		} else if (key === 'ArrowUp') {
			nextIndex = (currentIndex - 1 + options.length) % options.length;
		} else if (key === 'Home') {
			nextIndex = 0;
		} else if (key === 'End') {
			nextIndex = options.length - 1;
		} else {
			return false;
		}
		options[nextIndex].focus();
		return true;
	};

	CustomVideoPlayer.prototype.onKeydown = function (event) {
		var target = event.target;
		if (target.matches('[role="menuitem"], [role="menuitemradio"]')) {
			if (event.key === 'Escape') {
				event.preventDefault();
				this.closeMenus(true);
			} else if (this.moveMenuFocus(target, event.key)) {
				event.preventDefault();
			}
			return;
		}
		if (target.matches('input, button, a')) {
			if (event.key === 'Escape') {
				this.closeMenus(true);
			}
			return;
		}
		if (event.key === ' ' || event.key.toLowerCase() === 'k') {
			event.preventDefault();
			this.togglePlay();
		} else if (event.key === 'ArrowLeft') {
			event.preventDefault();
			this.seekBy(-numberData(this.root, 'seekInterval', 10));
		} else if (event.key === 'ArrowRight') {
			event.preventDefault();
			this.seekBy(numberData(this.root, 'seekInterval', 10));
		} else if (event.key === 'ArrowUp') {
			event.preventDefault();
			this.setVolume(this.video.volume + 0.05);
		} else if (event.key === 'ArrowDown') {
			event.preventDefault();
			this.setVolume(this.video.volume - 0.05);
		} else if (event.key.toLowerCase() === 'm') {
			this.video.muted = !this.video.muted;
		} else if (event.key.toLowerCase() === 'f') {
			this.toggleFullscreen();
		} else if (event.key.toLowerCase() === 'c' && this.video.textTracks.length) {
			this.setTrack(this.video.textTracks[0].mode === 'showing' ? 'off' : '0');
		} else if (event.key === 'Escape') {
			this.closeMenus(true);
		}
	};

	CustomVideoPlayer.prototype.revealControls = function () {
		if (this.root.dataset.controlsVisibility !== 'hover') {
			return;
		}
		this.root.classList.add('is-controls-visible');
		this.scheduleHide();
	};

	CustomVideoPlayer.prototype.scheduleHide = function () {
		var self = this;
		this.clearHideTimer();
		if (this.root.dataset.controlsVisibility !== 'hover' || this.video.paused) {
			return;
		}
		this.hideTimer = window.setTimeout(function () {
			if (!self.root.matches(':focus-within')) {
				self.root.classList.remove('is-controls-visible');
			}
		}, numberData(this.root, 'controlsDelay', 2000));
	};

	CustomVideoPlayer.prototype.clearHideTimer = function () {
		if (this.hideTimer) {
			window.clearTimeout(this.hideTimer);
			this.hideTimer = 0;
		}
	};

	CustomVideoPlayer.prototype.observeVisibility = function () {
		var self = this;
		if (booleanData(this.root, 'pauseOffscreen') && 'IntersectionObserver' in window) {
			this.intersectionObserver = new IntersectionObserver(function (entries) {
				if (!entries[0].isIntersecting && !self.video.paused) {
					self.video.pause();
				}
			}, { threshold: 0.1 });
			this.intersectionObserver.observe(this.root);
		}
		if ('ResizeObserver' in window) {
			this.resizeObserver = new ResizeObserver(function () {
				self.updateProgress();
				self.syncWidgetHeight();
			});
			this.resizeObserver.observe(this.root);
		}
	};

	CustomVideoPlayer.prototype.tryAutoplay = function () {
		if (!booleanData(this.root, 'autoplay')) {
			return;
		}
		if (booleanData(this.root, 'disableEditorAutoplay') && window.elementorFrontend && typeof window.elementorFrontend.isEditMode === 'function' && window.elementorFrontend.isEditMode()) {
			return;
		}
		this.play();
	};

	CustomVideoPlayer.prototype.announce = function (message) {
		if (this.status) {
			this.status.textContent = message;
		}
	};

	CustomVideoPlayer.prototype.destroy = function () {
		this.abortController.abort();
		this.clearHideTimer();
		if (this.clickTimer) {
			window.clearTimeout(this.clickTimer);
		}
		if (this.progressFrame) {
			window.cancelAnimationFrame(this.progressFrame);
		}
		if (this.intersectionObserver) {
			this.intersectionObserver.disconnect();
		}
		if (this.resizeObserver) {
			this.resizeObserver.disconnect();
		}
		if (this.widgetWrapper) {
			this.widgetWrapper.style.removeProperty('--wm-video-widget-height');
		}
		this.video.controls = true;
		this.root.classList.remove('is-ready', 'is-playing', 'is-controls-visible', 'is-overlay-dismissed', 'is-overlay-ended-hidden');
		players.delete(this.root);
	};

	function prunePlayers() {
		players.forEach(function (player, root) {
			if (!document.documentElement.contains(root)) {
				player.destroy();
			}
		});
	}

	function initialize(root) {
		var existing = players.get(root);
		if (existing) {
			existing.destroy();
		}
		var player = new CustomVideoPlayer(root);
		players.set(root, player);
		player.initialize();
	}

	function initializeAll(scope) {
		var root = scope || document;
		prunePlayers();
		if (root.matches && root.matches('[data-widgets-manager-custom-video]')) {
			initialize(root);
		}
		if (root.querySelectorAll) {
			root.querySelectorAll('[data-widgets-manager-custom-video]').forEach(initialize);
		}
	}

	function observeDomChanges() {
		if (!('MutationObserver' in window)) {
			return;
		}
		var observer = new MutationObserver(function (mutations) {
			mutations.forEach(function (mutation) {
				mutation.addedNodes.forEach(function (node) {
					if (node.nodeType === 1) {
						initializeAll(node);
					}
				});
			});
			prunePlayers();
		});
		observer.observe(document.documentElement, { childList: true, subtree: true });
	}

	function registerElementorHook() {
		if (!window.elementorFrontend || !window.elementorFrontend.hooks || typeof window.elementorFrontend.hooks.addAction !== 'function') {
			return false;
		}
		window.elementorFrontend.hooks.addAction('frontend/element_ready/widgets-manager-custom-video.default', function (scope) {
			initializeAll(scope && scope[0] ? scope[0] : scope);
		});
		return true;
	}

	function start() {
		initializeAll(document);
		observeDomChanges();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}

	if (!registerElementorHook()) {
		window.addEventListener('elementor/frontend/init', registerElementorHook);
	}
}());
