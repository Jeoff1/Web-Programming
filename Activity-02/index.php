<html>
    <body>
        <h3>My First PHP Program</h3>
        <h1>Hello, from Jeoff Nikko A. Ricafort</h1>
        <?php
            echo "Hello, World!";
        ?>
<br>
<h3>PHP Variables</h3>
        <?php
            $x = 10;
            $y = 4;
            $sum = $x + $y;
            $difference = $x - $y;
            $product = $x * $y;
            $quotient = $x / $y;
            echo "The sum is $sum";
            ?>
<br>
<h3>Simple Arithmetic Operations</h3>
        <?php
            echo "This is your sum: $sum,";
            echo "      Difference: $difference,";
            echo "         Product: $product,";
            echo "        Quotient: $quotient.";
        ?>
<br>
<h3>Conditional Statements</h3>
        <?php 
            if($y % $x == 0){
                echo "Yes, $y is a factor of $x";
            }
            else{
                echo "No, $y is NOT a factor $x";
            }
        ?>
<br>
<h3>PHP Loops</h3>
        <?php
            for($i = 1; $i < 100; $i++){
                if($i % 3 == 0){
                echo "Multiples of three are:
                $i <br>";
                }
            } 
        ?>
<h3>Arrays</h3>
        <?php 
            $products = array("Product A" , "Product B", "Product C");
            echo $products[0]
        ?>

        <?php 
            $products = array("Product A" , "Product B", "Product C");
            $products[1] = "Product D";
            var_dump($products);
        ?>

        <?php 
            $products = array("Product A" , "Product B", "Product C");
            foreach($products as $p){
                echo "$p <br>";
            }
        ?>
        <?php 
            $products = array("name"=>"Product A", "price"=>10.50 , "stock" =>12);
            echo $products["name"];
        ?>

        <?php 
            $products = array(
                array("name"=>"Product A", "price"=>10.50 , "stock" =>12),
                array("name"=>"Product B", "price"=>5.60 , "stock" =>7),
                array("name"=>"Product C", "price"=>7.00 , "stock" =>5)
            );
            ?>
            <br> <br>
            <?php
            foreach($products as $p){
                echo $p["name"] . " is " . $p["price"] . " pesos <br>";
            }
        ?>
    </body>
</html>