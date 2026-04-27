(function ($) {
	'use strict';

	function notice(message, type) {
		var $area = $('#aica-notice-area');
		$area.html('<div class="notice notice-' + (type || 'success') + ' is-dismissible"><p>' + $('<div>').text(message).html() + '</p></div>');
		if ($area.length) {
			$('html, body').animate({ scrollTop: Math.max(0, $area.offset().top - 60) }, 180);
		}
	}

	function config() {
		var raw = $('#aica-config-json').val() || '{}';
		try {
			return JSON.parse(raw);
		} catch (e) {
			return {};
		}
	}

	function setPath(object, path, value) {
		var parts = path.split('.');
		var target = object;
		for (var i = 0; i < parts.length - 1; i++) {
			var key = parts[i];
			if (typeof target[key] === 'undefined') {
				target[key] = /^\d+$/.test(parts[i + 1]) ? [] : {};
			}
			target = target[key];
		}
		target[parts[parts.length - 1]] = value;
	}

	function syncConfig() {
		var data = config();
		$('.aica-config-input').each(function () {
			var $field = $(this);
			var value = $field.is(':checkbox') || $field.data('type') === 'boolean' ? $field.is(':checked') : $field.val();
			setPath(data, $field.data('path'), value);
		});
		$('.aica-config-csv').each(function () {
			var values = ($(this).val() || '').split(',').map(function (item) {
				return item.trim();
			}).filter(Boolean);
			setPath(data, $(this).data('path'), values);
		});
		$('.aica-config-array').each(function () {
			var path = $(this).data('path');
			var values = $('.aica-config-array[data-path="' + path + '"]:checked').map(function () {
				return $(this).val();
			}).get();
			setPath(data, path, values);
		});
		$('#aica-config-json').val(JSON.stringify(data));
		return data;
	}

	function setLoading($button, isLoading) {
		if (!$button || !$button.length) {
			return;
		}
		if (isLoading) {
			$button.data('aica-label', $button.text()).prop('disabled', true).addClass('is-busy').text(aicaAdmin.messages.working || 'Working...');
		} else {
			$button.prop('disabled', false).removeClass('is-busy').text($button.data('aica-label') || $button.text());
		}
	}

	function message(key, fallback) {
		return aicaAdmin && aicaAdmin.messages && aicaAdmin.messages[key] ? aicaAdmin.messages[key] : fallback;
	}

	function post(action, data, done, $button) {
		data = data || {};
		data.action = 'aica_' + action;
		data.nonce = aicaAdmin.nonce;
		setLoading($button, true);
		$.post(aicaAdmin.ajaxUrl, data).done(function (response) {
			if (!response || !response.success) {
				var message = response && response.data && response.data.message ? response.data.message : 'Request failed.';
				if (response && response.data && response.data.errors) {
					message += ' ' + response.data.errors.join(' ');
				}
				notice(message, 'error');
				return;
			}
			done(response.data);
		}).fail(function () {
			notice('Request failed.', 'error');
		}).always(function () {
			setLoading($button, false);
		});
	}

	function closeDeletePopover() {
		$('.aica-delete-popover').remove();
		$('.aica-row-delete[aria-expanded="true"]').attr('aria-expanded', 'false');
	}

	function positionDeletePopover($popover, $button) {
		var rect = $button[0].getBoundingClientRect();
		var width = $popover.outerWidth();
		var left = window.pageXOffset + rect.left + (rect.width / 2) - (width / 2);
		var top = window.pageYOffset + rect.bottom + 8;
		var maxLeft = window.pageXOffset + $(window).width() - width - 16;
		left = Math.max(window.pageXOffset + 16, Math.min(left, maxLeft));
		$popover.css({ left: left, top: top });
	}

	function openDeletePopover($button) {
		closeDeletePopover();
		$button.attr('aria-expanded', 'true');

		var html = [
			'<div class="aica-delete-popover" role="dialog" aria-modal="false" aria-labelledby="aica-delete-popover-title">',
				'<div class="aica-delete-popover-header">',
					'<p class="aica-delete-popover-title" id="aica-delete-popover-title">' + $('<div>').text(message('deleteTitle', 'Delete model?')).html() + '</p>',
					'<p class="aica-delete-popover-description">' + $('<div>').text(message('deleteDescription', 'Choose whether to keep existing generated content or remove it with the model.')).html() + '</p>',
				'</div>',
				'<div class="aica-delete-popover-body">',
					'<p>' + $('<div>').text(message('deleteWarning', 'Deleting generated content removes posts and taxonomy terms for this model. Media files are kept.')).html() + '</p>',
				'</div>',
				'<div class="aica-delete-popover-footer">',
					'<button type="button" class="button button-small aica-delete-cancel">' + $('<div>').text(message('cancel', 'Cancel')).html() + '</button>',
					'<button type="button" class="button button-small aica-delete-model-only">' + $('<div>').text(message('deleteModelOnly', 'Delete model only')).html() + '</button>',
					'<button type="button" class="button button-small button-link-delete aica-delete-with-content">' + $('<div>').text(message('deleteModelWithContent', 'Delete model + content')).html() + '</button>',
				'</div>',
			'</div>'
		].join('');

		var $popover = $(html).appendTo('body');
		$popover.data('row', $button.closest('tr'));
		$popover.data('trigger', $button);
		positionDeletePopover($popover, $button);
		$popover.find('.aica-delete-cancel').trigger('focus');
	}

	function updateGenerateState() {
		var prompt = ($('#aica-prompt').val() || '').trim();
		var disabled = prompt.length < 12;
		$('#aica-generate-model').prop('disabled', disabled);
		$('.aica-prompt-hint').remove();
		if (disabled && $('#aica-prompt').length) {
			$('#aica-prompt').after('<p class="aica-prompt-hint aica-muted">Add a little more detail to generate a useful model.</p>');
		}
	}

	function settingsPayload() {
		return {
			provider: $('#aica_provider').val(),
			api_key: $('#aica_api_key').val(),
			base_url: $('#aica_base_url').val(),
			model: $('#aica_model').val(),
			custom_model: $('#aica_custom_model').val(),
			use_custom_model: $('#aica_use_custom_model').is(':checked') ? 1 : 0
		};
	}

	function updateSettingsState() {
		var provider = $('#aica_provider').val() || 'mock';
		var $selected = $('#aica_provider option:selected');
		var useCustom = $('#aica_use_custom_model').is(':checked');

		$('.aica-provider-description').removeClass('is-active').filter('[data-provider="' + provider + '"]').addClass('is-active');
		$('.aica-provider-badge').toggleClass('is-active', provider === 'mock');
		$('.aica-api-key-row, .aica-base-url-row, .aica-model-row').toggle(provider !== 'mock');
		$('#aica_model').prop('disabled', useCustom || provider === 'mock');
		$('#aica_custom_model').toggleClass('is-active', useCustom && provider !== 'mock');

		if (provider !== 'mock' && !($('#aica_base_url').val() || '').trim()) {
			$('#aica_base_url').val($selected.data('default-base-url') || 'https://api.openai.com/v1');
		}
	}

	function replaceModelOptions(models, selected) {
		var $select = $('#aica_model');
		$select.empty();
		(models || []).forEach(function (model) {
			var id = model.id || '';
			if (!id) {
				return;
			}
			var label = model.label || id;
			if (model.badge) {
				label += ' - ' + model.badge;
			}
			$select.append($('<option>').val(id).text(label));
		});
		if (selected) {
			$select.val(selected);
		}
	}

	$(document).on('input change', '.aica-config-input, .aica-config-csv, .aica-config-array', syncConfig);
	$(document).on('input', '#aica-prompt', updateGenerateState);
	$(document).on('change', '#aica_provider, #aica_use_custom_model', updateSettingsState);
	updateGenerateState();
	updateSettingsState();

	$(document).on('click', '.aica-example-chip', function () {
		$('#aica-prompt').val($(this).data('prompt')).trigger('input').focus();
	});

	$('#aica-generate-model').on('click', function () {
		var prompt = ($('#aica-prompt').val() || '').trim();
		var $button = $(this);
		if (prompt.length < 12) {
			notice('Add a more detailed prompt before generating a content model.', 'warning');
			return;
		}
		$('#aica-review').empty();
		$('#aica-spinner').addClass('is-active');
		$button.prop('disabled', true);
		$.post(aicaAdmin.ajaxUrl, {
			action: 'aica_generate_model',
			nonce: aicaAdmin.nonce,
			prompt: prompt,
			model_id: $('#aica-model-id').val()
		}).done(function (response) {
			if (!response || !response.success) {
				var message = response && response.data && response.data.message ? response.data.message : 'Generation failed.';
				if (response && response.data && response.data.errors) {
					message += ' ' + response.data.errors.join(' ');
				}
				notice(message, 'error');
				return;
			}
			$('#aica-review').html(response.data.html);
			if (response.data.warnings && response.data.warnings.length) {
				notice('Model generated with warnings: ' + response.data.warnings.join(' '), 'warning');
			} else {
				notice('Model generated. Review and edit it before applying.', 'success');
			}
		}).fail(function () {
			notice('Generation failed. Please try again.', 'error');
		}).always(function () {
			$('#aica-spinner').removeClass('is-active');
			$button.prop('disabled', false);
		});
	});

	$(document).on('click', '#aica-save-draft', function () {
		var $button = $(this);
		post('save_model', { config: JSON.stringify(syncConfig()), model_id: $('#aica-model-id').val() }, function (data) {
			$('#aica-model-id').val(data.model.id);
			notice(data.message, 'success');
		}, $button);
	});

	$(document).on('click', '#aica-apply-model', function () {
		if (!window.confirm(aicaAdmin.messages.confirmApply)) {
			return;
		}
		var $button = $(this);
		post('apply_model', {
			config: JSON.stringify(syncConfig()),
			model_id: $('#aica-model-id').val(),
			generate_sample: $('#aica-generate-sample').is(':checked') ? 1 : 0,
			sample_count: $('#aica-sample-count').val()
		}, function (data) {
			$('#aica-model-id').val(data.model.id);
			notice(data.message + ' Refresh the admin menu if the new post type is not visible yet.', 'success');
		}, $button);
	});

	$(document).on('click', '#aica-regenerate', function () {
		$('#aica-generate-model').trigger('click');
	});

	$(document).on('click', '#aica-refresh-models', function () {
		var $button = $(this);
		var selected = $('#aica_model').val();
		post('refresh_models', settingsPayload(), function (data) {
			replaceModelOptions(data.models || [], selected);
			if (data.refreshed_at) {
				$('#aica-model-source').text('Model list last refreshed: ' + data.refreshed_at + '.');
			}
			notice(data.message || 'Model list refreshed.', 'success');
		}, $button);
	});

	$(document).on('click', '#aica-test-provider', function () {
		var $button = $(this);
		post('test_provider_connection', settingsPayload(), function (data) {
			notice(data.message || 'Connection successful.', 'success');
		}, $button);
	});

	$('.aica-row-apply').on('click', function () {
		if (!window.confirm(aicaAdmin.messages.confirmApply)) {
			return;
		}
		var $row = $(this).closest('tr');
		var $button = $(this);
		post('apply_model', { model_id: $row.data('model-id'), config: $row.find('.aica-row-config').val() }, function (data) {
			notice(data.message, 'success');
			window.location.reload();
		}, $button);
	});

	$('.aica-row-disable').on('click', function () {
		var $row = $(this).closest('tr');
		var $button = $(this);
		post('disable_model', { model_id: $row.data('model-id') }, function (data) {
			notice(data.message, 'success');
			window.location.reload();
		}, $button);
	});

	$('.aica-row-delete').attr('aria-haspopup', 'dialog').attr('aria-expanded', 'false').on('click', function (event) {
		event.preventDefault();
		openDeletePopover($(this));
	});

	$(document).on('click', '.aica-delete-cancel', closeDeletePopover);

	$(document).on('click', '.aica-delete-model-only, .aica-delete-with-content', function () {
		var $action = $(this);
		var $popover = $action.closest('.aica-delete-popover');
		var $row = $popover.data('row');
		var deleteContent = $action.hasClass('aica-delete-with-content') ? 1 : 0;

		post('delete_model', { model_id: $row.data('model-id'), delete_content: deleteContent }, function (data) {
			notice(data.message, 'success');
			$row.remove();
			closeDeletePopover();
		}, $action);
	});

	$(document).on('click', function (event) {
		if (!$(event.target).closest('.aica-delete-popover, .aica-row-delete').length) {
			closeDeletePopover();
		}
	});

	$(document).on('keydown', function (event) {
		if (event.key === 'Escape') {
			closeDeletePopover();
		}
	});

	$(window).on('resize scroll', function () {
		var $popover = $('.aica-delete-popover');
		if (!$popover.length) {
			return;
		}
		positionDeletePopover($popover, $popover.data('trigger'));
	});
})(jQuery);
