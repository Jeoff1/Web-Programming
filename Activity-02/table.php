<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Dynamic Table</title>
</head>
<body>
    <?php
    $products = array(
        array("name"=>"Product A", "price"=>10.50 , "stock" =>12),
        array("name"=>"Product B", "price"=>5.60 , "stock" =>7),
        array("name"=>"Product C", "price"=>7.00 , "stock" =>5),
        array("name"=>"Shab", "price"=>1000.00 , "stock" =>3),
        array("name"=>"Legends lang nakakaalam", "price"=>80.00 , "stock" =>11),
        array("name"=>"Saging", "price"=>12.00 , "stock" =>12)
    );
    ?>
    <table border=1>
        <tr>
            <th>No.</th>
            <th>Product Name</th>
            <th>Price</th>
            <th>Stock</th>
        </tr>
    <?php 
        $no = 0;
        foreach($products as $p){
        $no++;
        $lowstock = ($p["stock"] < 10) ? "style='background-color: red; color: white;'": ""
    ?>
        <tr <?= $lowstock ?>>
            <td><?= $no?></td>
            <td><?= $p["name"] ?></td>
            <td><?= $p["price"] ?></td>
            <td><?= $p["stock"] ?></td>
        </tr>
    <?php
        }
    ?>
</table>
    
</body>
</html>