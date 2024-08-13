function showFullView(id) {
    $.get('get_news.php', { id: id }, function (data) {
        // Zakładamy, że data jest już obiektem JSON
        console.log(data); // Dodaj tę linię, aby sprawdzić strukturę danych
        const news = data.news; // Używamy danych bez parsowania

        let modalContent = `
            <h3>${news.title}</h3>
            <p><small>Dodano: ${news.created_at}</small></p>
            <p>${news.content}</p>`;
        if (news.photo) {
            modalContent += `<img src="${news.photo}" class="img-fluid mt-3" alt="News Image">`;
        }
        $('#fullViewContent').html(modalContent);
        $('#fullViewModal').modal('show');
    }).fail(function () {
        alert('Wystąpił błąd podczas ładowania szczegółów aktualności.');
    });
}



function previewImage() {
    const file = document.getElementById('image').files[0];
    const reader = new FileReader();
    const preview = document.getElementById('previewImage');

    reader.onloadend = function () {
        preview.src = reader.result;
        preview.style.display = 'block';
    };

    if (file) {
        reader.readAsDataURL(file);
    } else {
        preview.src = "";
        preview.style.display = 'none';
    }
}

function resetModal() {
    $('#newsModalLabel').text('Dodaj aktualność');
    $('#submitButton').text('Dodaj');
    $('#title, #content, #image, #newsId, #currentImage').val('');
    $('#previewImage').hide();
}

function editNews(id) {
    $.get('get_news.php', { id: id }, function (response) {
        const news = response.news;
        if (news) {
            $('#newsModalLabel').text('Edytuj aktualność');
            $('#submitButton').text('Aktualizuj');
            $('#title').val(news.title);
            $('#content').val(news.content);
            $('#newsId').val(news.id);
            $('#currentImage').val(news.photo);
            if (news.photo) {
                $('#previewImage').attr('src', news.photo).show();
            } else {
                $('#previewImage').hide();
            }
            $('#newsModal').modal('show');
        } else {
            alert('Nie znaleziono aktualności.');
        }
    }, 'json').fail(function () {
        alert('Wystąpił błąd podczas ładowania danych aktualności.');
    });
}

let newsIdToDelete = null; // Inicjalizacja zmiennej globalnej

function confirmDelete(id) {
    newsIdToDelete = id;
    $('#deleteConfirmationModal').modal('show');
}

function handleDeleteConfirmation() {
    $('#confirmDeleteButton').off('click').on('click', function () {
        $.post('manage_news.php', { delete: newsIdToDelete }, function (response) {
            try {
                response = JSON.parse(response);
                if (response.success) {
                    $('#deleteConfirmationModal').modal('hide');
                    $('#deleteSuccessModal').modal('show');
                    setTimeout(function () {
                        location.reload();
                    }, 3000);
                } else {
                    alert(response.error || 'Wystąpił błąd podczas usuwania aktualności.');
                }
            } catch (e) {
                console.error('Error parsing response:', e);
                alert('Wystąpił błąd podczas usuwania aktualności.');
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.error('Error details:', textStatus, errorThrown);
            alert('Wystąpił błąd podczas usuwania aktualności.');
        });
    });
}



// Funkcje związane z formularzem rejestracji
function checkPasswordStrength(password) {
    let strength = 0;
    const errors = [];
    const requirements = [
        { regex: /.{8,}/, error: 'Minimum 8 znaków', id: 'lengthRequirement' },
        { regex: /[a-z]/, error: 'Przynajmniej jedna mała litera', id: 'lowercaseRequirement' },
        { regex: /[A-Z]/, error: 'Przynajmniej jedna wielka litera', id: 'uppercaseRequirement' },
        { regex: /[0-9]/, error: 'Przynajmniej jedna cyfra', id: 'numberRequirement' },
        { regex: /[^a-zA-Z0-9]/, error: 'Przynajmniej jeden znak specjalny', id: 'specialCharRequirement' }
    ];

    requirements.forEach(req => {
        if (req.regex.test(password)) {
            strength++;
        } else {
            errors.push(req.error);
        }
    });

    return { strength, errors, requirements };
}

function showPasswordStrength(result) {
    const colors = ['#ff4d4d', '#ffa500', '#ffff00', '#4caf50'];
    const strengthPercent = (result.strength / result.requirements.length) * 100;
    const gradientColors = colors.slice(0, result.strength).join(', ');

    $('#password-strength').css('background', result.strength > 0 
        ? `linear-gradient(to right, ${gradientColors} ${strengthPercent}%, #e0e0e0 ${strengthPercent}%)` 
        : '#e0e0e0');
}

function handleFormSubmission() {
    $('#registrationForm').on('submit', function (e) {
        e.preventDefault();

        const password = $('#password').val();
        const result = checkPasswordStrength(password);

        if (result.errors.length > 0) {
            const errorsHtml = `<ul>${result.errors.map(error => `<li>${error}</li>`).join('')}</ul>`;
            $('#passwordStrengthModal .modal-body').html(errorsHtml);

            result.requirements.forEach(req => {
                const element = $(`#${req.id}`);
                element.toggleClass('text-danger', result.errors.includes(req.error))
                       .toggleClass('text-success', !result.errors.includes(req.error));
            });

            $('#passwordStrengthModal').modal('show');
        } else {
            submitForm($(this));
        }
    });
}

function submitForm(form) {
    $.ajax({
        url: 'register.php',
        method: 'POST',
        data: form.serialize(),
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                $('#successModal').modal('show');
                setTimeout(function () {
                    window.location.href = 'login.php';
                }, 4000);
            } else {
                displayErrorMessage(response.error_message);
            }
        },
        error: function (xhr, status, error) {
            console.error('Response:', xhr.responseText);
            console.error('Status:', status);
            console.error('Error:', error);
            alert('Wystąpił błąd. Proszę spróbować ponownie.');
        }
    });
}

function displayErrorMessage(message) {
    $('.alert-danger').remove();
    let errorMessage = `<div class="alert alert-danger">${message}</div>`;
    $('#registrationForm .form-group:first').before(errorMessage);
}

// Inicjalizacja pluginów i obsługa innych elementów
function initializeChosen() {
    $(".chosen-select").chosen({
        width: '100%',
        allow_single_deselect: true,
        no_results_text: "Brak wyników",
        search_contains: true
    });
}

function startCountdown(duration, display) {
    let timer = duration;

    const interval = setInterval(function updateCountdown() {
        if (timer > 0) {
            const form = timer === 1 ? "sekunda" : timer >= 2 && timer <= 4 ? "sekundy" : "sekund";
            display.textContent = `${timer} ${form}`;
            timer--;
        } else {
            clearInterval(interval);
        }
    }, 1000);

    updateCountdown();
}

function initializeSelect2() {
    $('#titleFilter').select2({
        placeholder: 'Wybierz tytuł',
        allowClear: true
    });
}

$(document).ready(function () {
    // Obsługa modalu sukcesu
    $('#successModal').on('shown.bs.modal', function () {
        const countdownTime = 5;
        const display = document.querySelector('#countdown');
        startCountdown(countdownTime, display);
    });

    // Obsługa zmiany hasła
    $('#password').on('input', function () {
        const password = $(this).val();
        const result = checkPasswordStrength(password);
        showPasswordStrength(result);
    });

    // Używanie funkcji w odpowiednich przyciskach
    $('.btn-edit').on('click', function () {
        editNews($(this).data('id'));
    });

    $('.btn-delete').on('click', function () {
        confirmDelete($(this).data('id'));
    });

    $('.btn-view').on('click', function () {
        showFullView($(this).data('id'));
    });

    // Inicjalizacja funkcji
    handleFormSubmission();
    initializeChosen();
    initializeSelect2();
    handleDeleteConfirmation();

    // Obsługa formularza
    $('#image').on('change', previewImage);
    $('#newsModal').on('hidden.bs.modal', resetModal);
});
