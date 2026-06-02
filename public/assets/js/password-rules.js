(function () {
    const rules = {
        length: function (value) { return value.length >= 8; },
        upper: function (value) { return /[A-Z]/.test(value); },
        lower: function (value) { return /[a-z]/.test(value); },
        number: function (value) { return /\d/.test(value); },
        special: function (value) { return /[^A-Za-z\d]/.test(value); }
    };

    document.querySelectorAll('input[data-password-rules]').forEach(function (input) {
        const listId = input.getAttribute('data-password-rules');
        const list = document.getElementById(listId);

        if (!list) {
            return;
        }

        const items = list.querySelectorAll('[data-rule]');

        function updateRules() {
            const value = input.value;

            items.forEach(function (item) {
                const rule = item.getAttribute('data-rule');
                const passed = rules[rule] ? rules[rule](value) : false;
                item.classList.toggle('valid', passed);
                item.classList.toggle('invalid', !passed && value.length > 0);
            });
        }

        input.addEventListener('input', updateRules);
        updateRules();
    });
})();