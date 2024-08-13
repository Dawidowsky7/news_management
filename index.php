<?php
require_once 'inc/functions.php';
require_once 'inc/auth.php';

if (!checkAuth()) {
    header('Location: login.php');
    exit;
}

$news = getNews();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Aktualności</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'templates/header.php'; ?>

    <div class="container">
        <h1>Aktualności</h1>
        <hr>
        <div class="row">
            <?php foreach ($news as $newsItem): ?>
                <div class="col-md-4 mb-4">
                    <div class="card rounded-lg">
                        <?php if (!empty($newsItem['photo'])): ?>
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
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="fullViewModal" tabindex="-1" role="dialog" aria-labelledby="fullViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fullViewModalLabel">Podgląd Aktualności</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="fullViewContent">
                    <!-- Treść aktualności będzie ładowana tutaj -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Zamknij</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
