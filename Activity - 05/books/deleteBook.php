<?php 
require_once "../classes/book.php";
$booksObj = new Books();

if($_SERVER["REQUEST_METHOD"] == "GET"){
    if(isset($_GET["id"])){
        $pid = trim(htmlspecialchars($_GET["id"]));
        $books = $booksObj -> fetchBook($pid);
        if(!$books){
            echo "<a href='viewBook.php'>View Books</a>";
            exit("Book Not Found");
        }else{
        $booksObj->deleteBook($pid);
        header("Location: viewBook.php");
        }
    }else{
        echo "<a href='viewBook.php'>View Books</a>";
        exit("Book not Found");
    }
}
?>