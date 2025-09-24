<?php
require_once "../classes/book.php";
$booksObj = new Books();

$search = $genre ="";

if($_SERVER["REQUEST_METHOD"] == "GET"){
    $search = isset($_GET["search"]) ? trim(htmlspecialchars($_GET["search"])): "";
     $genre = isset($_GET["genre"]) ? trim(htmlspecialchars($_GET["genre"])): "";
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Books</title>
    <link rel="stylesheet" href="viewStyle.css">
</head>
<body>
    <h1>View Books</h1>

        <form action="" method="get">
        <label for="">Search:</label>
        <input type="search" name="search" id="search" value="<?= $search ?>">
        <select name="genre" id="genre">
            <option value="">All</option>
            <option value="history" <?= (isset($genre) && $genre == "history")? "selected":"" ?>>History</option>
            <option value="science" <?= (isset($genre) && $genre == "science")? "selected":"" ?>>Science</option>
            <option value="fiction" <?= (isset($genre) && $genre == "fiction")? "selected":"" ?>>Fiction</option>
        </select>
        <input type="submit" value="Search">
    </form>
    <button><a href="addBook.php">Add Book</a></button>

    <table border="1">
        <tr>
            <td>No.</td>
            <td>Book Title</td>
            <td>Book Author</td>
            <td>Book Genre</td>
            <td>Publication Date</td>
            <td>Publisher</td>
            <td>Copies</td>
        </tr>

        <?php

        $no = 1;
        foreach($booksObj->viewBooks($search, $genre) as $books) {
        ?>

        <tr>
            <td> <?= $no++ ?> </td>
            <td> <?= $books["title"] ?> </td>
            <td> <?= $books["author"] ?> </td>
            <td> <?= $books["genre"] ?> </td>
            <td> <?= $books["publication_year"] ?> </td>
            <td> <?= $books["publisher"] ?> </td>
            <td> <?= $books["copies"] ?> </td>
        </tr>

        <?php
        }
        ?>
</body>
</html>
