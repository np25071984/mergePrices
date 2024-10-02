<!doctype html>
<html lang="en-US">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <title>Объединение прайсов</title>
    </head>
    <body>
        <div class="container">
            <h1 class="text-center mt-5">Обединение прайсов</h1>
            <?php
            if (isset($_SESSION["error_msg"])) {
            ?>
                <div class="alert alert-warning" role="alert">
                    <?= $_SESSION["error_msg"] ?>
                </div>
            <?php
                session_unset();
            }
            ?>
            <form enctype="multipart/form-data" method="POST" action="https://ec2-3-144-103-141.us-east-2.compute.amazonaws.com/merge.php">
                <div class="mb-3">
                    <label for="file1" class="form-label">Прайс регионы ИП Курзина (Марка):</label>
                    <input type="file" class="form-control" id="file1" name="file1" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
                </div>

                <div class="mb-3">
                    <label for="file2" class="form-label">Прайс ООО Фестиваль:</label>
                    <input type="file" class="form-control" id="file2" name="file2" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
                </div>

                <button type="submit" class="btn btn-primary">Отправить на слияние</button>
            </form>
        </div>
    </body>
</html>
