(function () {
	'use strict';

	var configuration = window.WPFeaturesManagerAdmin || {};
	var form = document.getElementById('wp-features-manager-form');
	var searchField = document.getElementById('wp-features-manager-search');
	var statusField = document.getElementById('wp-features-manager-status');
	var statusFilter = document.querySelector('[data-wp-features-manager-status-filter]');
	var statusTrigger = document.getElementById('wp-features-manager-status-trigger');
	var statusMenu = document.getElementById('wp-features-manager-status-menu');
	var statusValue = document.getElementById('wp-features-manager-status-value');
	var statusOptions = document.querySelectorAll('[data-status-value]');
	var saveState = document.getElementById('wp-features-manager-save-state');
	var summary = document.getElementById('wp-features-manager-summary');
	var noResults = document.getElementById('wp-features-manager-no-results');
	var tabs = document.querySelectorAll('[data-wp-features-manager-tab]');
	var panels = document.querySelectorAll('[data-wp-features-manager-panel]');
	var inputs = document.querySelectorAll('.wp-features-manager__toggle');
	var dirty = false;

	if (!form) {
		return;
	}

	function updateSaveState() {
		if (!saveState) {
			return;
		}

		saveState.textContent = dirty ? configuration.unsaved : configuration.saved;
		saveState.classList.toggle('is-dirty', dirty);
	}

	function markDirty() {
		dirty = true;
		updateSaveState();
	}

	function closeStatusMenu(restoreFocus) {
		if (!statusMenu || !statusTrigger) {
			return;
		}

		statusMenu.hidden = true;
		statusTrigger.setAttribute('aria-expanded', 'false');
		if (restoreFocus) {
			statusTrigger.focus();
		}
	}

	function openStatusMenu(focusLast) {
		var selectedOption;

		if (!statusMenu || !statusTrigger || statusTrigger.disabled) {
			return;
		}

		statusMenu.hidden = false;
		statusTrigger.setAttribute('aria-expanded', 'true');
		selectedOption = statusMenu.querySelector('[aria-selected="true"]');
		if (focusLast && statusOptions.length) {
			statusOptions[statusOptions.length - 1].focus();
		} else if (selectedOption) {
			selectedOption.focus();
		}
	}

	function selectStatusOption(option) {
		var index;

		if (!option || !statusField || !statusValue) {
			return;
		}

		statusField.value = option.getAttribute('data-status-value');
		statusValue.textContent = option.textContent;
		for (index = 0; index < statusOptions.length; index += 1) {
			statusOptions[index].classList.toggle('is-selected', statusOptions[index] === option);
			statusOptions[index].setAttribute('aria-selected', statusOptions[index] === option ? 'true' : 'false');
		}
		closeStatusMenu(true);
		filterCards();
	}

	function moveStatusFocus(currentIndex, direction) {
		var nextIndex = currentIndex + direction;

		if (nextIndex < 0) {
			nextIndex = statusOptions.length - 1;
		} else if (nextIndex >= statusOptions.length) {
			nextIndex = 0;
		}
		statusOptions[nextIndex].focus();
	}

	function updateSummary() {
		if (!summary) {
			return;
		}

		var enabledCount = document.querySelectorAll('.wp-features-manager__toggle:checked').length;
		var totalCount = parseInt(summary.getAttribute('data-total'), 10) || 0;
		summary.textContent = configuration.summary
			.replace('%1$d', enabledCount)
			.replace('%2$d', totalCount);
	}

	function activePanel() {
		for (var index = 0; index < panels.length; index += 1) {
			if (!panels[index].hidden) {
				return panels[index];
			}
		}
		return null;
	}

	function activateTab(tab, moveFocus) {
		if (!tab) {
			return;
		}

		var target = tab.getAttribute('data-wp-features-manager-tab');
		for (var tabIndex = 0; tabIndex < tabs.length; tabIndex += 1) {
			var selected = tabs[tabIndex] === tab;
			tabs[tabIndex].classList.toggle('is-active', selected);
			tabs[tabIndex].setAttribute('aria-selected', selected ? 'true' : 'false');
			tabs[tabIndex].tabIndex = selected ? 0 : -1;
		}
		for (var panelIndex = 0; panelIndex < panels.length; panelIndex += 1) {
			panels[panelIndex].hidden = panels[panelIndex].getAttribute('data-wp-features-manager-panel') !== target;
		}
		if (moveFocus) {
			tab.focus();
		}
		closeStatusMenu(false);
		filterCards();
	}

	function moveTabFocus(currentIndex, direction) {
		var nextIndex = currentIndex + direction;
		if (nextIndex < 0) {
			nextIndex = tabs.length - 1;
		} else if (nextIndex >= tabs.length) {
			nextIndex = 0;
		}
		activateTab(tabs[nextIndex], true);
	}

	function filterCards() {
		var searchTerm = searchField ? searchField.value.toLowerCase().trim() : '';
		var status = statusField ? statusField.value : 'all';
		var panel = activePanel();
		var cards = panel ? panel.querySelectorAll('[data-wp-features-manager-card]') : [];
		var visibleCount = 0;
		var index;

		for (index = 0; index < cards.length; index += 1) {
			var card = cards[index];
			var toggle = card.querySelector('.wp-features-manager__toggle');
			var statusText = card.querySelector('[data-wp-features-manager-status-text]');
			var unavailable = card.getAttribute('data-availability') === 'unavailable';
			var cardStatus = unavailable ? 'unavailable' : (toggle && toggle.checked ? 'enabled' : 'disabled');
			var matchesSearch = card.getAttribute('data-search').indexOf(searchTerm) !== -1;
			var matchesStatus = status === 'all' || cardStatus === status;
			var visible = matchesSearch && matchesStatus;

			card.hidden = !visible;
			card.setAttribute('data-status', cardStatus);
			if (statusText) {
				statusText.textContent = unavailable ? configuration.unavailable : (cardStatus === 'enabled' ? configuration.enabled : configuration.disabled);
			}
			if (visible) {
				visibleCount += 1;
			}
		}

		if (noResults) {
			noResults.hidden = cards.length === 0 || visibleCount !== 0;
		}

		updateSummary();
	}

	function applyBulkAction(action) {
		var panel = activePanel();
		var cards = panel ? panel.querySelectorAll('[data-wp-features-manager-card]') : [];
		var index;
		var changed = false;

		for (index = 0; index < cards.length; index += 1) {
			var card = cards[index];
			var toggle = card.querySelector('.wp-features-manager__toggle');

			if (card.hidden || !toggle || toggle.disabled) {
				continue;
			}

			if (toggle.checked !== (action === 'enable')) {
				toggle.checked = action === 'enable';
				changed = true;
			}
		}

		if (changed) {
			markDirty();
			filterCards();
		}
	}

	for (var tabIndex = 0; tabIndex < tabs.length; tabIndex += 1) {
		tabs[tabIndex].addEventListener('click', function () {
			activateTab(this, false);
		});
		tabs[tabIndex].addEventListener('keydown', function (event) {
			var currentIndex = Array.prototype.indexOf.call(tabs, this);
			if (event.key === 'ArrowRight') {
				event.preventDefault();
				moveTabFocus(currentIndex, 1);
			} else if (event.key === 'ArrowLeft') {
				event.preventDefault();
				moveTabFocus(currentIndex, -1);
			} else if (event.key === 'Home') {
				event.preventDefault();
				activateTab(tabs[0], true);
			} else if (event.key === 'End') {
				event.preventDefault();
				activateTab(tabs[tabs.length - 1], true);
			}
		});
	}

	if (searchField) {
		searchField.addEventListener('input', filterCards);
	}

	if (statusTrigger) {
		statusTrigger.addEventListener('click', function () {
			if (statusMenu.hidden) {
				openStatusMenu(false);
			} else {
				closeStatusMenu(false);
			}
		});
		statusTrigger.addEventListener('keydown', function (event) {
			if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
				event.preventDefault();
				openStatusMenu(event.key === 'ArrowUp');
			} else if (event.key === 'Escape') {
				closeStatusMenu(false);
			}
		});
	}

	for (var statusIndex = 0; statusIndex < statusOptions.length; statusIndex += 1) {
		statusOptions[statusIndex].addEventListener('click', function () {
			selectStatusOption(this);
		});
		statusOptions[statusIndex].addEventListener('keydown', function (event) {
			var optionIndex = Array.prototype.indexOf.call(statusOptions, this);

			if (event.key === 'ArrowDown') {
				event.preventDefault();
				moveStatusFocus(optionIndex, 1);
			} else if (event.key === 'ArrowUp') {
				event.preventDefault();
				moveStatusFocus(optionIndex, -1);
			} else if (event.key === 'Home') {
				event.preventDefault();
				statusOptions[0].focus();
			} else if (event.key === 'End') {
				event.preventDefault();
				statusOptions[statusOptions.length - 1].focus();
			} else if (event.key === 'Enter' || event.key === ' ') {
				event.preventDefault();
				selectStatusOption(this);
			} else if (event.key === 'Escape') {
				event.preventDefault();
				closeStatusMenu(true);
			} else if (event.key === 'Tab') {
				closeStatusMenu(false);
			}
		});
	}

	document.addEventListener('click', function (event) {
		if (statusFilter && !statusFilter.contains(event.target)) {
			closeStatusMenu(false);
		}
	});

	for (var index = 0; index < inputs.length; index += 1) {
		inputs[index].addEventListener('change', function () {
			markDirty();
			filterCards();
		});
	}

	var bulkButtons = document.querySelectorAll('[data-wp-features-manager-bulk]');
	for (var buttonIndex = 0; buttonIndex < bulkButtons.length; buttonIndex += 1) {
		bulkButtons[buttonIndex].addEventListener('click', function () {
			applyBulkAction(this.getAttribute('data-wp-features-manager-bulk'));
		});
	}

	form.addEventListener('submit', function () {
		dirty = false;
	});

	window.addEventListener('beforeunload', function (event) {
		if (!dirty) {
			return undefined;
		}

		event.preventDefault();
		event.returnValue = '';
		return '';
	});

	updateSaveState();
	if (tabs.length) {
		activateTab(tabs[0], false);
	} else {
		filterCards();
	}
}());
