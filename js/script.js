$(document).ready(function() {
    // Funkcja do analizy siły hasła
    function checkPasswordStrength(password) {
        let strength = 0;
        let errors = [];
        
        if (password.length > 7) strength += 1;
        else errors.push('Minimum 8 znaków');

        if (/[a-z]/.test(password)) strength += 1;
        else errors.push('Przynajmniej jedna mała litera');

        if (/[A-Z]/.test(password)) strength += 1;
        else errors.push('Przynajmniej jedna wielka litera');

        if (/[0-9]/.test(password)) strength += 1;
        else errors.push('Przynajmniej jedna cyfra');

        if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
        else errors.push('Przynajmniej jeden znak specjalny');

        return { strength, errors };
    }

    $('#registrationForm').on('submit', function(e) {
        e.preventDefault();

        // Pobierz hasło z formularza
        const password = $('#password').val();
        const result = checkPasswordStrength(password);
        const { strength, errors } = result;

        // Sprawdź, czy są błędy
        if (errors.length > 0) {
            $('#passwordStrengthModal').find('.modal-body ul').children().each(function() {
                const requirementText = $(this).text();
                if (errors.includes(requirementText)) {
                    $(this).css('color', 'red');
                } else {
                    $(this).css('color', 'green');
                }
            });
            $('#passwordStrengthModal').modal('show');
            return; // Zatrzymaj dalsze działanie formularza
        }

        // Jeśli nie ma błędów, kontynuuj przesyłanie formularza
        $.ajax({
            url: 'register.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                let data = JSON.parse(response);
                if (data.success) {
                    $('#successModal').modal('show');
                    let countdown = 4;
                    const countdownElement = document.getElementById('countdown');
                    const interval = setInterval(function() {
                        countdown--;
                        countdownElement.textContent = countdown;
                        if (countdown <= 0) {
                            clearInterval(interval);
                            window.location.href = 'login.php';
                        }
                    }, 1000);
                } else {
                    $('.alert').remove();
                    $('#registrationForm').prepend('<div class="alert alert-danger">' + data.error_message + '</div>');
                }
            },
            error: function(xhr, status, error) {
                alert('Wystąpił błąd. Proszę spróbować ponownie.');
            }
        });
    });
});

$(document).ready(function() {
    // Sprawdź, czy cookies są już zaakceptowane
    if (document.cookie.indexOf('cookies-accepted=true') === -1) {
        $('#cookieModal').modal('show');
    }

    $('#acceptCookies').click(function() {
        // Ustaw cookie na 30 dni
        document.cookie = "cookies-accepted=true; max-age=" + 30*24*60*60;
        $('#cookieModal').modal('hide');
    });
});

//Choosen w formularzu rejestracji
$(document).ready(function() {
    $(".chosen-select").chosen({
        width: '100%',
        allow_single_deselect: true,
        no_results_text: "Brak wyników",
        search_contains: true
    });
});


