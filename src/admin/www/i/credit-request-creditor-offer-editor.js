$(document).ready(function() {
    $('#date').mask(
        'A0.B0.CD00',
        {
            translation: {
                'A': { pattern: /[0-3]/, optional: false },
                'B': { pattern: /[0-1]/, optional: false },
                'C': { pattern: /[1-2]/, optional: false },
                'D': { pattern: /[0,9]/, optional: false }
            }
        }
    );
    
    $('#time').mask(
        'A0:B0',
        {
            translation: {
                'A': { pattern: /[0-2]/, optional: false },
                'B': { pattern: /[0-5]/, optional: false }
            }
        }
    );
});