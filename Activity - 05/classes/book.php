<?php
require_once "database.php";

class Books extends Database{
    public $id = "";
    public $title = "";
    public $author = "";
    public $genre = "";
    public $publication_year = "";
    public $publisher = "";
    public $copies = "";


    public function isBookExist($ptitle, $pid=""){
        $sql = "SELECT COUNT(*) as total FROM book WHERE title = :title AND id <> :id";
        $query = $this->connect()->prepare($sql);
        $query->bindParam(":title", $ptitle);
        $query->bindParam(":id",$pid);
        $record = null;

        if ($query->execute()) {
            $record = $query->fetch();
    }
    if($record["total"] > 0){
            return true;
        }else{
            return false;
        }
    }

    public function addBook(){
        if($this->isBookExist($this->title)){
            return false;
        }
        $sql = "INSERT INTO book (title, author, genre, publication_year, publisher, copies) VALUES (:title, :author, :genre, :publication_year, :publisher, :copies)";

        $query = $this->connect()->prepare($sql);

        $query->bindParam(":title", $this->title);
        $query->bindParam(":author", $this->author);
        $query->bindParam(":genre", $this->genre);
        $query->bindParam(":publication_year", $this->publication_year);
        $query->bindParam(":publisher", $this->publisher);
        $query->bindParam(":copies", $this->copies);

        return $query->execute();
    }

    public function viewBooks ($search="", $genre=""){
        $sql = "SELECT * FROM book WHERE title LIKE CONCAT('%', :search, '%') AND genre LIKE CONCAT('%', :genre, '%') ORDER BY id ASC";
        $query = $this->connect()->prepare($sql);
        $query -> bindParam(":search", $search);
        $query -> bindParam(":genre", $genre);

        if($query->execute()){  
            return $query->fetchAll();
        } else {
            return null;
        }
    }

    public function fetchBook($pid){
        $sql = "SELECT * FROM book WHERE id = :id";
        $query = $this->connect()->prepare($sql);
        $query -> bindParam(":id", $pid);

        if ($query -> execute()){
            return $query -> fetch();
        }else{
            return null;
        }
    }

    public function editBook($pid){
        $sql = "UPDATE book SET title=:title, author=:author,genre=:genre,publication_year=:publication_year,publisher=:publisher,copies=:copies WHERE id=:id";

        $query = $this->connect()->prepare($sql);

        $query->bindParam(":title", $this->title);
        $query->bindParam(":author", $this->author);
        $query->bindParam(":genre", $this->genre);
        $query->bindParam(":publication_year", $this->publication_year);
        $query->bindParam(":publisher", $this->publisher);
        $query->bindParam(":copies", $this->copies);
        $query->bindParam(":id", $pid);

        return $query->execute();
    }

    public function deleteBook($pid){
        $sql = "DELETE FROM book WHERE id=:id";

        $query = $this->connect()->prepare($sql);
        $query->bindParam(":id", $pid);

        return $query->execute();
    }

    
}