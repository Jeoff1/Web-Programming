<?php
require_once "../classes/book.php";
$booksObj = new Books();

$books = [];
$errors = [];

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $books["title"] = trim(htmlspecialchars($_POST["title"]));
    $books["author"] = trim(htmlspecialchars($_POST["author"]));
    $books["genre"] = trim(htmlspecialchars($_POST["genre"]));
    $books["publication_year"] = trim(htmlspecialchars($_POST["publication_year"]));
    $books["publisher"] = trim(htmlspecialchars($_POST["publisher"]));
    $books["copies"] = trim(htmlspecialchars($_POST["copies"]));

    if(empty($books["title"])) {
        $errors["title"] = "Title is required.";
    }

    if(empty($books["author"])) {
        $errors["author"] = "Author is required.";
    }

    if(empty($books["genre"])) {
        $errors["genre"] = "Genre is required.";
    }

    if(empty($books["publication_year"])) {
        $errors["publication_year"] = "Publication Year is required.";
    } elseif(strtotime($books["publication_year"]) > time())
        $errors["publication_year"] = "Publication year must not be in the Future.";

    if(empty($books["copies"])) {
        $errors["copies"] = "Number of copies of the book is required.";
    } elseif($books["copies"] < 1){
        $errors["copies"] = "Number of Copies must be atleast 1.";
    }

    if(empty(array_filter($errors))){
        $booksObj->title = $books["title"];
        $booksObj->author = $books["author"];
        $booksObj->genre = $books["genre"];
        $booksObj->publication_year = $books["publication_year"];
        $booksObj->publisher = $books["publisher"];
        $booksObj->copies = $books["copies"];
        
        if($booksObj->addBook()){
           header("Location: viewBook.php");
        } else {
            echo "failed";
        }
    }

    if($booksObj -> isBookExist($books["title"])){
        $errors["title"] = "This Book already Exists";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Books</title>
    <link rel="stylesheet" href="inputStyle.css">
    <style>
    label {display:block; }
    span, .error {color: red; margin: 0;}


    </style>
   
</head>
<body>
    <h1>Add Books </h1>
<form action="" method="post">
        <label for="">Field with <span>*</span> is required</label>
        <br>

        <label for="title">Book Name <span>*</span></label>
        <input type="text" name="title" id="title" value="<?= $books["title"] ?? ""?>">
        <p class="error"><?= $errors["title"] ?? ""?></p>
        <br>

        <label for="author">Author <span>*</span></label>
        <input type="text" name="author" id="author" value="<?= $books["author"] ?? ""?>">
        <p class="error"><?= $errors["author"] ?? ""?></p>
        <br>

        <label for="genre">Genre <span>*</span></label>
        <select name="genre" id="genre">
            <option value="">Select Genre</option>
            <option value="History" <?= (isset($books["genre"]) && $books["genre"] == "history")? "selected":"" ?>>History</option>
            <option value="Science" <?= (isset($books["genre"]) && $books["genre"] == "science")? "selected":"" ?>>Science</option>
            <option value="Fiction" <?= (isset($books["genre"]) && $books["genre"] == "fiction")? "selected":"" ?>>Fiction</option>
        </select>
        <p class="error"><?= $errors["genre"] ?? ""?></p>
        <br>

        <label for="publication_year">Publication Year <span>*</span></label> 
        <input type="date" name="publication_year" id="publication_year" value="<?= $books["publication_year"] ?? ""?>">
        <p class="error"><?= $errors["publication_year"] ?? ""?></p>
        <br>

        <label for="publisher">Publisher </label> 
        <input type="text" name="publisher" id="publisher" value="<?= $books["publisher"] ?? ""?>">
        <br><br>

        <label for="copies">Number of Copies <span>*</span></label> 
        <input type="number" name="copies" id="copies" value="<?= $books["copies"] ?? ""?>">
        <p class="error"><?= $errors["copies"] ?? ""?></p>
        <br>


        <input type="submit" class="view-books" value="Save Book"><span><button><a href="viewBook.php">View Books</a></button></span>
</form>
</body>
</html>
