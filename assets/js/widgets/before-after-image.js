(function (window, document) {
	'use strict';

	if (window.WidgetsManagerBeforeAfter) {
		return;
	}

	var instances = [];

	function clampPosition(position) {
		return Math.max(0, Math.min(100, position));
	}

	function isRtl(element) {
		return window.getComputedStyle(element).direction === 'rtl';
	}

	function pointToPosition(component, event) {
		var bounds = component.getBoundingClientRect();
		var horizontal = component.classList.contains('widgets-manager-before-after--horizontal');
		var ratio;

		if (horizontal) {
			ratio = bounds.width ? (event.clientX - bounds.left) / bounds.width : 0;
			if (isRtl(component)) {
				ratio = 1 - ratio;
			}
		} else {
			ratio = bounds.height ? (event.clientY - bounds.top) / bounds.height : 0;
		}

		return clampPosition(ratio * 100);
	}

	function createInstance(component) {
		var handle = component.querySelector('.widgets-manager-before-after__handle');
		var dragEnabled = component.getAttribute('data-drag-to-position') === 'true';
		var clickEnabled = component.getAttribute('data-click-to-position') === 'true';
		var keyboardStep = parseInt(component.getAttribute('data-keyboard-step'), 10) || 5;
		var position = 50;
		var hasInteracted = false;
		var activePointer = null;
		var resizeObserver = null;
		var listeners = [];

		if (!handle) {
			return null;
		}

		function responsiveStartPosition() {
			var computedPosition = parseFloat(window.getComputedStyle(component).getPropertyValue('--wm-start-position'));
			var fallbackPosition = parseFloat(component.getAttribute('data-start-position'));

			if (!isNaN(computedPosition)) {
				return clampPosition(computedPosition);
			}

			return isNaN(fallbackPosition) ? 50 : clampPosition(fallbackPosition);
		}

		function setPosition(nextPosition) {
			var accessiblePosition;

			position = Math.round(clampPosition(nextPosition) * 100) / 100;
			accessiblePosition = position.toFixed(2).replace(/\.?0+$/, '');
			component.style.setProperty('--wm-position', position.toFixed(2) + '%');
			handle.setAttribute('aria-valuenow', accessiblePosition);
			handle.setAttribute('aria-valuetext', accessiblePosition + '%');
		}

		function addListener(target, eventName, callback, options) {
			target.addEventListener(eventName, callback, options);
			listeners.push([target, eventName, callback, options]);
		}

		function stopDragging(event) {
			if (activePointer !== event.pointerId) {
				return;
			}

			if (component.hasPointerCapture && component.hasPointerCapture(event.pointerId)) {
				component.releasePointerCapture(event.pointerId);
			}
			activePointer = null;
			component.classList.remove('is-wm-dragging');
		}

		function beginDragging(event) {
			if (!dragEnabled || event.button > 0) {
				return;
			}

			hasInteracted = true;
			activePointer = event.pointerId;
			component.classList.add('is-wm-dragging');
			component.setPointerCapture(event.pointerId);
			setPosition(pointToPosition(component, event));
			event.preventDefault();
		}

		function updateDragging(event) {
			if (activePointer !== event.pointerId) {
				return;
			}

			setPosition(pointToPosition(component, event));
			event.preventDefault();
		}

		function positionFromPointer(event) {
			if (handle.contains(event.target) && dragEnabled) {
				beginDragging(event);
				return;
			}

			if (clickEnabled && event.button <= 0) {
				hasInteracted = true;
				setPosition(pointToPosition(component, event));
			}
		}

		function adjustWithKeyboard(event) {
			var horizontal = component.classList.contains('widgets-manager-before-after--horizontal');
			var rtl = isRtl(component);
			var nextPosition = position;

			if (event.key === 'Home') {
				nextPosition = 0;
			} else if (event.key === 'End') {
				nextPosition = 100;
			} else if (horizontal && event.key === 'ArrowLeft') {
				nextPosition += rtl ? keyboardStep : -keyboardStep;
			} else if (horizontal && event.key === 'ArrowRight') {
				nextPosition += rtl ? -keyboardStep : keyboardStep;
			} else if (!horizontal && event.key === 'ArrowUp') {
				nextPosition -= keyboardStep;
			} else if (!horizontal && event.key === 'ArrowDown') {
				nextPosition += keyboardStep;
			} else {
				return;
			}

			hasInteracted = true;
			setPosition(nextPosition);
			event.preventDefault();
		}

		addListener(component, 'pointerdown', positionFromPointer);
		addListener(component, 'pointermove', updateDragging);
		addListener(component, 'pointerup', stopDragging);
		addListener(component, 'pointercancel', stopDragging);
		addListener(handle, 'keydown', adjustWithKeyboard);

		if ('ResizeObserver' in window) {
			resizeObserver = new window.ResizeObserver(function () {
				setPosition(hasInteracted ? position : responsiveStartPosition());
			});
			resizeObserver.observe(component);
		}

		component.classList.toggle('is-wm-rtl', isRtl(component));
		setPosition(responsiveStartPosition());

		return {
			component: component,
			refreshResponsiveStart: function () {
				if (!hasInteracted) {
					setPosition(responsiveStartPosition());
				}
			},
			destroy: function () {
				var index;

				if (resizeObserver) {
					resizeObserver.disconnect();
				}
				for (index = 0; index < listeners.length; index += 1) {
					listeners[index][0].removeEventListener(listeners[index][1], listeners[index][2], listeners[index][3]);
				}
				component.classList.remove('is-wm-dragging', 'is-wm-rtl');
				component.removeAttribute('data-widgets-manager-before-after-ready');
			}
		};
	}

	function pruneInstances() {
		instances = instances.filter(function (instance) {
			if (document.documentElement.contains(instance.component)) {
				return true;
			}

			instance.destroy();
			return false;
		});
	}

	function refreshResponsiveStarts() {
		var index;

		pruneInstances();
		for (index = 0; index < instances.length; index += 1) {
			instances[index].refreshResponsiveStart();
		}
	}

	function initialize(component) {
		var instance;

		if (!component || component.getAttribute('data-widgets-manager-before-after-ready') === 'true') {
			return;
		}

		instance = createInstance(component);
		if (instance) {
			component.setAttribute('data-widgets-manager-before-after-ready', 'true');
			instances.push(instance);
		}
	}

	function initializeScope(scope) {
		var components;
		var index;

		pruneInstances();
		if (scope && scope.matches && scope.matches('[data-widgets-manager-before-after]')) {
			initialize(scope);
		}
		components = (scope || document).querySelectorAll('[data-widgets-manager-before-after]');
		for (index = 0; index < components.length; index += 1) {
			initialize(components[index]);
		}
	}

	function initializeElementorWidget(scope) {
		var element = scope && scope[0] ? scope[0] : scope;
		initializeScope(element);
	}

	function registerElementorHook() {
		if (!window.elementorFrontend || !window.elementorFrontend.hooks || typeof window.elementorFrontend.hooks.addAction !== 'function') {
			return false;
		}

		window.elementorFrontend.hooks.addAction(
			'frontend/element_ready/widgets-manager-before-after-image.default',
			initializeElementorWidget
		);
		return true;
	}

	window.WidgetsManagerBeforeAfter = {
		initialize: initializeScope
	};

	document.addEventListener('DOMContentLoaded', function () {
		initializeScope(document);
	});
	window.addEventListener('resize', refreshResponsiveStarts);

	if (!registerElementorHook()) {
		window.addEventListener('elementor/frontend/init', registerElementorHook);
	}
}(window, document));
