(function () {
	'use strict';

	var configuration = window.WidgetsManagerAdmin || {};
	var form = document.getElementById('widgets-manager-form');
	var searchField = document.getElementById('widgets-manager-search');
	var statusField = document.getElementById('widgets-manager-status');
	var saveState = document.getElementById('widgets-manager-save-state');
	var summary = document.getElementById('widgets-manager-summary');
	var noResults = document.getElementById('widgets-manager-no-results');
	var cards = document.querySelectorAll('[data-widgets-manager-card]');
	var inputs = document.querySelectorAll('.widgets-manager__toggle');
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

	function updateSummary() {
		if (!summary) {
			return;
		}

		var enabledCount = document.querySelectorAll('.widgets-manager__toggle:checked').length;
		var totalCount = parseInt(summary.getAttribute('data-total'), 10) || 0;
		summary.textContent = configuration.summary
			.replace('%1$d', enabledCount)
			.replace('%2$d', totalCount);
	}

	function filterCards() {
		var searchTerm = searchField ? searchField.value.toLowerCase().trim() : '';
		var status = statusField ? statusField.value : 'all';
		var visibleCount = 0;
		var index;

		for (index = 0; index < cards.length; index += 1) {
			var card = cards[index];
			var toggle = card.querySelector('.widgets-manager__toggle');
			var statusText = card.querySelector('[data-widgets-manager-status-text]');
			var cardStatus = toggle && toggle.checked ? 'enabled' : 'disabled';
			var matchesSearch = card.getAttribute('data-search').indexOf(searchTerm) !== -1;
			var matchesStatus = status === 'all' || cardStatus === status;
			var visible = matchesSearch && matchesStatus;

			card.hidden = !visible;
			card.setAttribute('data-status', cardStatus);
			if (statusText) {
				statusText.textContent = cardStatus === 'enabled' ? configuration.enabled : configuration.disabled;
			}
			if (visible) {
				visibleCount += 1;
			}
		}

		if (noResults) {
			noResults.hidden = visibleCount !== 0;
		}

		updateSummary();
	}

	function applyBulkAction(action) {
		var index;
		var changed = false;

		for (index = 0; index < cards.length; index += 1) {
			var card = cards[index];
			var toggle = card.querySelector('.widgets-manager__toggle');

			if (card.hidden || !toggle) {
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

	if (searchField) {
		searchField.addEventListener('input', filterCards);
	}

	if (statusField) {
		statusField.addEventListener('change', filterCards);
	}

	for (var index = 0; index < inputs.length; index += 1) {
		inputs[index].addEventListener('change', function () {
			markDirty();
			filterCards();
		});
	}

	var bulkButtons = document.querySelectorAll('[data-widgets-manager-bulk]');
	for (var buttonIndex = 0; buttonIndex < bulkButtons.length; buttonIndex += 1) {
		bulkButtons[buttonIndex].addEventListener('click', function () {
			applyBulkAction(this.getAttribute('data-widgets-manager-bulk'));
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
	filterCards();
}());
