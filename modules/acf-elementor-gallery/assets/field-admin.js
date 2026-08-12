(function ($) {
	'use strict';

	var strings = window.WPFeaturesManagerGallery;

	function escapeAttribute(value) {
		return String(value)
			.replace(/&/g, '&amp;')
			.replace(/"/g, '&quot;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;');
	}

	function addEmptyPlaceholder($inputs, inputName) {
		$inputs.append(
			'<input type="hidden" name="' + escapeAttribute(inputName) + '[]" value="" class="acfge-empty-placeholder">'
		);
	}

	function syncInputs($inputs, inputName, attachmentIds) {
		$inputs.empty();
		if (attachmentIds.length === 0) {
			addEmptyPlaceholder($inputs, inputName);
			return;
		}
		$.each(attachmentIds, function (index, attachmentId) {
			$inputs.append(
				'<input type="hidden" name="' + escapeAttribute(inputName) + '[]" value="' + parseInt(attachmentId, 10) + '">'
			);
		});
	}

	function previewUrl(attachment, previewSize) {
		var sizes = attachment.get('sizes');
		if (sizes && sizes[previewSize]) {
			return sizes[previewSize].url;
		}

		var fallbacks = ['medium', 'thumbnail', 'full'];
		for (var index = 0; index < fallbacks.length; index += 1) {
			if (sizes && sizes[fallbacks[index]]) {
				return sizes[fallbacks[index]].url;
			}
		}
		return attachment.get('url');
	}

	function thumbnailMarkup(attachmentId, imageUrl) {
		return '<li class="acfge-thumb" data-id="' + attachmentId + '">' +
			'<img src="' + escapeAttribute(imageUrl) + '" alt="">' +
			'<button type="button" class="acfge-remove" aria-label="' + escapeAttribute(strings.remove_image) + '">&#x2715;</button>' +
			'</li>';
	}

	function mediaLibraryFilter(library) {
		var filter = { type: 'image' };
		if (library === 'uploadedTo') {
			filter.uploadedTo = wp.media.view.settings.post.id;
		}
		return filter;
	}

	function galleryAttachmentIds($thumbs) {
		return $thumbs.find('.acfge-thumb').map(function () {
			return parseInt($(this).data('id'), 10);
		}).get();
	}

	function replaceGallerySelection($thumbs, $inputs, inputName, selection, previewSize) {
		var attachmentIds = selection.pluck('id');

		$thumbs.empty();
		selection.each(function (attachment) {
			$thumbs.append(thumbnailMarkup(
				attachment.get('id'),
				previewUrl(attachment, previewSize)
			));
		});
		syncInputs($inputs, inputName, attachmentIds);
		$thumbs.sortable('refresh');
	}

	function openGalleryEditor($thumbs, $inputs, inputName, previewSize, library) {
		var shortcode = '[gallery ids="' + galleryAttachmentIds($thumbs).join(',') + '"]';
		var frame = wp.media.gallery.edit(shortcode);
		var galleryEditState = frame.state('gallery-edit');

		galleryEditState.set('displaySettings', false);
		frame.content.render('browse');

		if (library === 'uploadedTo') {
			var galleryLibrary = frame.state('gallery-library');
			galleryLibrary.set('filterable', false);
			galleryLibrary.get('library').props.set(mediaLibraryFilter(library));
		}

		galleryEditState.on('update', function (selection) {
			replaceGallerySelection($thumbs, $inputs, inputName, selection, previewSize);
		});
	}

	function openImagePicker($thumbs, $inputs, inputName, previewSize, library) {
		var frame = wp.media({
			title: strings.select_images,
			button: { text: strings.add_to_gallery },
			multiple: true,
			library: mediaLibraryFilter(library)
		});

		frame.on('select', function () {
			var selection = frame.state().get('selection');
			replaceGallerySelection($thumbs, $inputs, inputName, selection, previewSize);
		});
		frame.open();
	}

	function initializeGalleryField($field) {
		if ($field.data('wp-features-manager-gallery-ready')) {
			return;
		}
		$field.data('wp-features-manager-gallery-ready', true);

		var $thumbs = $field.find('.acfge-thumbs');
		var $inputs = $field.find('.acfge-inputs');
		var $addButton = $field.find('.acfge-add-images');
		var previewSize = $addButton.data('preview-size') || 'medium';
		var library = $addButton.data('library') || 'all';
		var inputName = ($inputs.find('input').first().attr('name') || '').replace(/\[\]$/, '');

		$thumbs.sortable({
			items: '.acfge-thumb',
			cancel: '.acfge-remove',
			tolerance: 'pointer',
			placeholder: 'acfge-thumb ui-sortable-placeholder',
			forcePlaceholderSize: true,
			start: function (event, ui) {
				ui.placeholder.css({
					width: ui.helper.outerWidth(),
					height: ui.helper.outerHeight()
				});
			},
			update: function () {
				var orderedIds = [];
				$thumbs.find('.acfge-thumb').each(function () {
					orderedIds.push($(this).data('id'));
				});
				syncInputs($inputs, inputName, orderedIds);
			}
		});

		$thumbs.on('click', '.acfge-remove', function () {
			var attachmentId = parseInt($(this).closest('.acfge-thumb').data('id'), 10);
			$(this).closest('.acfge-thumb').remove();
			$inputs.find('input[value="' + attachmentId + '"]').remove();
			if ($thumbs.find('.acfge-thumb').length === 0) {
				addEmptyPlaceholder($inputs, inputName);
			}
		});

		$addButton.on('click', function (event) {
			event.preventDefault();
			if (galleryAttachmentIds($thumbs).length === 0) {
				openImagePicker($thumbs, $inputs, inputName, previewSize, library);
				return;
			}
			openGalleryEditor($thumbs, $inputs, inputName, previewSize, library);
		});
	}

	function initializeGalleryFields() {
		$('.acfge-gallery-field').each(function () {
			initializeGalleryField($(this));
		});
	}

	$(document).on('acf/setup_fields', initializeGalleryFields);
	$(initializeGalleryFields);
}(jQuery));
