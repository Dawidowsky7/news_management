<?php
require_once 'inc/functions.php';
require_once 'inc/auth.php';

if (!checkAuth()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $id = $_POST['id'] ?? null;
    $current_user = $_SESSION['user'] ?? 'unknown'; // Assuming the username is stored in session

    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $image = $uploadDir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    } elseif (isset($_POST['current_image'])) {
        $image = $_POST['current_image'];
    }

    if ($id) {
        updateNews($id, $title, $content, $image);
    } else {
        addNews($title, $content, $image);
    }
    header('Location: manage_news.php');
    exit;
}

// Handle deletion
if (isset($_GET['delete'])) {
    $current_user = $_SESSION['user'] ?? 'unknown'; // Assuming the username is stored in session
    deleteNews($_GET['delete'], $current_user);
    header('Location: manage_news.php');
    exit;
}

$sort = $_GET['sort'] ?? 'date';
$filter = $_GET['filter'] ?? '';
$newsList = getNews($sort, $filter);

$news = isset($_GET['id']) ? getNewsById($_GET['id']) : null;

$titleFilter = isset($_GET['title']) ? $_GET['title'] : [];
$contentFilter = isset($_GET['content']) ? $_GET['content'] : '';
$dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : '';
$dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : '';

$sort = $_GET['sort'] ?? 'date';

// Stwórz tablicę filtrów do przekazania do funkcji getNews
$filter = [
    'title' => $titleFilter,
    'content' => $contentFilter,
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo
];

$newsList = getNews($sort, $filter);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Zarządzanie aktualnościami</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/manage_news.css">
    <link rel="stylesheet" href="css/animations.css">
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Zarządzanie aktualnościami</h1>
        <hr>

<!-- Filtry -->
<div class="mb-4">
            <form id="filtersForm" method="GET" action="manage_news.php">
                <div class="form-row">
                    <!-- Filtr tytułu -->
                    <div class="col-md-12 mb-3">
                        <label for="titleFilter">Filtruj po tytule</label>
                        <select id="titleFilter" name="title[]" multiple class="form-control select2">
                            <!-- Opcje będą dodane dynamicznie -->
                        </select>
                    </div>

                    <!-- Filtr treści -->
                    <div class="col-md-12 mb-3">
                        <label for="contentFilter">Filtruj po treści</label>
                        <input type="text" id="contentFilter" name="content" class="form-control" value="<?php echo htmlspecialchars($contentFilter); ?>">
                    </div>

                    <!-- Filtr daty od - do -->
                    <div class="col-md-12 mb-3">
                        <label>Filtruj po dacie</label>
                        <div class="form-row">
                            <div class="col-md-6 mb-2">
                                <input type="date" id="dateFrom" name="dateFrom" class="form-control" value="<?php echo htmlspecialchars($dateFrom); ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="date" id="dateTo" name="dateTo" class="form-control" value="<?php echo htmlspecialchars($dateTo); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Filtruj</button>
            </form>
        </div>


        <button type="button" class="btn btn-primary mb-4" data-toggle="modal" data-target="#newsModal" onclick="resetModal()">
            Dodaj aktualność
        </button>
        <div class="modal fade" id="newsModal" tabindex="-1" role="dialog" aria-labelledby="newsModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="newsModalLabel">Dodaj aktualność</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="manage_news.php" enctype="multipart/form-data">
                            <input type="hidden" name="id" id="newsId" value="">
                            <input type="hidden" name="current_image" id="currentImage" value="">
                            <div class="form-group">
                                <label for="title">Tytuł</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="content">Treść</label>
                                <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="image">Zdjęcie</label>
                                <input type="file" class="form-control-file" id="image" name="image" onchange="previewImage()">
                                <img id="previewImage" class="img-thumbnail mt-2" style="display: none;">
                            </div>
                            <button type="submit" class="btn btn-primary" id="submitButton">Dodaj</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="mt-4">Lista aktualności</h2>
        <div class="row">
            <?php foreach ($newsList as $newsItem): ?>
                <div class="col-md-4 mb-4">
                    <div class="card rounded-lg shadow-sm">
                        <?php if ($newsItem['photo']): ?>
                            <img src="<?php echo htmlspecialchars($newsItem['photo'], ENT_QUOTES); ?>" alt="News Image" class="card-img-top img-fluid rounded-top" style="max-height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($newsItem['title'], ENT_QUOTES); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($newsItem['content'], 0, 150), ENT_QUOTES); ?>...</p>
                            <p class="card-text"><small class="text-muted">Dodano: <?php echo htmlspecialchars($newsItem['created_at'], ENT_QUOTES); ?></small></p>
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-link btn-sm" onclick="showFullView(<?php echo $newsItem['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-info btn-sm ml-2" data-toggle="modal" data-target="#newsModal" onclick="editNews(<?php echo $newsItem['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm ml-2" onclick="confirmDelete(<?php echo $newsItem['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<!-- Modal potwierdzenia usunięcia aktualności -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">Potwierdzenie usunięcia</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <p>Czy na pewno chcesz usunąć tę aktualność? <br> Operacja jest nieodwracalna.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="confirmDeleteButton">Usuń</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Anuluj</button>
                </div>
            </div>
        </div>
    </div>

<!-- Modal po usunięciu aktualności -->
<div class="modal fade" id="deleteSuccessModal" tabindex="-1" role="dialog" aria-labelledby="deleteSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="animated-checkmark">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                            <path class="checkmark-check" fill="none" d="M14 27l7 7 16-16"/>
                        </svg>
                    </div>
                    <h5 class="mt-3">Aktualność została pomyślnie usunięta!</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pełnego podglądu -->
    <div class="modal fade" id="fullViewModal" tabindex="-1" role="dialog" aria-labelledby="fullViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fullViewModalLabel">Pełny widok aktualności</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="fullViewContent">
                    <!-- Treść pełnego widoku będzie tutaj wstawiona za pomocą JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <?php include 'templates/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <script>
        let newsIdToDelete = null;

        $(document).ready(function() {
    // Inicjalizacja Select2
    $('#titleFilter').select2({
        placeholder: 'Wybierz tytuł',
        allowClear: true
    });

    populateFilters();
});

function populateFilters() {
    $.get('get_filter_options.php', function(data) {
        console.log('Received data:', data); // Debugging line

        if (typeof data === 'string') {
            try {
                data = JSON.parse(data);
            } catch (error) {
                console.error('Error parsing JSON:', error);
                return;
            }
        }
        
        console.log('Parsed options:', data); // Debugging line

        $('#titleFilter').empty();
        if (data.titles) {
            data.titles.forEach(function(title) {
                $('#titleFilter').append(`<option value="${title}">${title}</option>`);
            });
            $('#titleFilter').trigger('change'); // Use 'change' event to update Select2
        } else {
            console.error('No titles found in response');
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error('Error fetching filter options:', textStatus, errorThrown);
    });
}



        function previewImage() {
            var file = document.getElementById('image').files[0];
            var reader = new FileReader();
            var preview = document.getElementById('previewImage');

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
            $('#title').val('');
            $('#content').val('');
            $('#image').val('');
            $('#newsId').val('');
            $('#currentImage').val('');
            $('#previewImage').hide();
        }

        function editNews(id) {
            $.get('get_news.php', { id: id }, function(data) {
                var news = JSON.parse(data);
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
            }).fail(function() {
                alert('Wystąpił błąd podczas ładowania danych aktualności.');
            });
        }

        function confirmDelete(id) {
            newsIdToDelete = id;
            $('#deleteConfirmationModal').modal('show');
        }

        $('#confirmDeleteButton').click(function() {
            $.get('manage_news.php', { delete: newsIdToDelete }, function() {
                $('#deleteConfirmationModal').modal('hide');
                $('#deleteSuccessModal').modal('show');
                setTimeout(function() {
                    location.reload();
                }, 3000);
            }).fail(function() {
                alert('Wystąpił błąd podczas usuwania aktualności.');
            });
        });

        $('#newsModal').on('hidden.bs.modal', function () {
            resetModal();
        });

        function showFullView(id) {
            $.get('get_news.php', { id: id }, function(data) {
                var news = JSON.parse(data);
                var modalContent = `
                    <h3>${news.title}</h3>
                    <p><small>Dodano: ${news.created_at}</small></p>
                    <p>${news.content}</p>
                `;
                if (news.photo) {
                    modalContent += `<img src="${news.photo}" class="img-fluid mt-3" alt="News Image">`;
                }
                $('#fullViewContent').html(modalContent);
                $('#fullViewModal').modal('show');
            }).fail(function() {
                alert('Wystąpił błąd podczas ładowania szczegółów aktualności.');
            });
        }
    </script>
</body>
</html>


