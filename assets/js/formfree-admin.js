/* FormFree - JavaScript de Administración */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Color pickers
        if ($('.color-picker').length && typeof $.fn.wpColorPicker === 'function') {
            $('.color-picker').wpColorPicker();
        }

        // Confirmación de eliminación
        $('a[onclick*="confirm"]').on('click', function(e) {
            if (!confirm($(this).attr('onclick').match(/'([^']+)'/)[1])) {
                e.preventDefault();
                return false;
            }
        });

        // Copiar shortcode al hacer click
        $('.formfree-shortcode-box input').on('click', function() {
            $(this).select();
            try {
                document.execCommand('copy');
                const $this = $(this);
                const originalBG = $this.css('background-color');
                $this.css('background-color', '#d1fae5');
                setTimeout(function() {
                    $this.css('background-color', originalBG);
                }, 300);
            } catch (err) {
                console.log('No se pudo copiar');
            }
        });
    });

})(jQuery);
