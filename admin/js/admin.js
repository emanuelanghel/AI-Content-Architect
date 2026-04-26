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

	function updateGenerateState() {
		var prompt = ($('#aica-prompt').val() || '').trim();
		var disabled = prompt.length < 12;
		$('#aica-generate-model').prop('disabled', disabled);
		$('.aica-prompt-hint').remove();
		if (disabled && $('#aica-prompt').length) {
			$('#aica-prompt').after('<p class="aica-prompt-hint aica-muted">Add a little more detail to generate a useful model.</p>');
		}
	}

	$(document).on('input change', '.aica-config-input, .aica-config-csv, .aica-config-array', syncConfig);
	$(document).on('input', '#aica-prompt', updateGenerateState);
	updateGenerateState();

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
		$('#aica-spinner').addClass('is-active');
		$button.prop('disabled', true);
		$.post(aicaAdmin.ajaxUrl, {
			action: 'aica_generate_model',
			nonce: aicaAdmin.nonce,
			prompt: prompt
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
			notice('Model generated. Review and edit it before applying.', 'success');
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

	$('.aica-row-delete').on('click', function () {
		if (!window.confirm(aicaAdmin.messages.confirmDelete)) {
			return;
		}
		var $row = $(this).closest('tr');
		var $button = $(this);
		post('delete_model', { model_id: $row.data('model-id') }, function (data) {
			notice(data.message, 'success');
			$row.remove();
		}, $button);
	});
})(jQuery);
