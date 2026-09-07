/* =====================================================================
   ÉQUIPLOC — Validation des formulaires (côté client)
   Double sécurité avec la validation PHP.
   Champs concernés : <input data-validate="required|email|min:6">
   ===================================================================== */
(function () {
    'use strict';

    var RULES = {
        required: function (value) { return value.trim() !== ''; },
        email: function (value) {
            if (value === '') return true;
            return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value);
        },
        min: function (value, n) { return value === '' || value.trim().length >= Number(n); },
        max: function (value, n) { return value === '' || value.trim().length <= Number(n); },
        numeric: function (value) { return value === '' || !isNaN(Number(value)); },
        int: function (value) { return value === '' || /^-?\d+$/.test(value.trim()); },
        min_val: function (value, n) { return value === '' || Number(value) >= Number(n); },
        max_val: function (value, n) { return value === '' || Number(value) <= Number(n); },
        date: function (value) { return value === '' || /^\d{4}-\d{2}-\d{2}$/.test(value); },
        after: function (value, otherId, form) {
            if (value === '') return true;
            var other = form.querySelector('[name="' + otherId + '"]');
            return !other || !other.value || value > other.value;
        },
        after_or_equal: function (value, otherId, form) {
            if (value === '') return true;
            var other = form.querySelector('[name="' + otherId + '"]');
            return !other || !other.value || value >= other.value;
        },
        same: function (value, otherId, form) {
            if (value === '') return true;
            var other = form.querySelector('[name="' + otherId + '"]');
            return !other || value === other.value;
        }
    };

    var LABELS = {
        required: 'Ce champ est obligatoire.',
        email: 'Veuillez saisir une adresse e-mail valide.',
        min: 'Le texte est trop court.',
        max: 'Le texte est trop long.',
        numeric: 'Veuillez saisir un nombre valide.',
        int: 'Veuillez saisir un nombre entier.',
        min_val: 'La valeur est trop faible.',
        max_val: 'La valeur est trop élevée.',
        date: 'Veuillez saisir une date valide.',
        after: 'Cette date doit être postérieure.',
        after_or_equal: 'Cette date doit être postérieure ou égale.',
        same: 'Les valeurs ne correspondent pas.'
    };

    function parseRules(str) {
        return (str || '').split('|').map(function (r) {
            var parts = r.split(':');
            return { name: parts[0], param: parts[1] };
        }).filter(function (r) { return RULES[r.name]; });
    }

    function checkField(input, form) {
        var rules = parseRules(input.getAttribute('data-validate'));
        if (!rules.length) return true;

        var value = input.value;
        if (input.type === 'checkbox') value = input.checked ? 'oui' : '';

        var field = input.closest('.field') || input.parentElement;
        var errorEl = field.querySelector('.field-error');
        var message = '';

        for (var i = 0; i < rules.length; i++) {
            var rule = rules[i];
            var ok = RULES[rule.name](value, rule.param, form);
            if (!ok) { message = LABELS[rule.name] || 'Champ invalide.'; break; }
        }

        field.classList.toggle('has-error', !!message);
        if (errorEl) errorEl.textContent = message;
        if (input.type === 'checkbox' && message) field.querySelector('.checkbox') && field.querySelector('.checkbox').classList.add('has-error');
        return !message;
    }

    document.querySelectorAll('form[novalidate], form[data-form]').forEach(function (form) {
        var fields = form.querySelectorAll('[data-validate]');

        fields.forEach(function (input) {
            input.addEventListener('blur', function () { checkField(input, form); });
            input.addEventListener('input', function () {
                if (input.closest('.field') && input.closest('.field').classList.contains('has-error')) {
                    checkField(input, form);
                }
            });
        });

        form.addEventListener('submit', function (e) {
            var firstInvalid = null;
            fields.forEach(function (input) {
                var ok = checkField(input, form);
                if (!ok && !firstInvalid) firstInvalid = input;
            });
            if (firstInvalid) {
                e.preventDefault();
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
})();
