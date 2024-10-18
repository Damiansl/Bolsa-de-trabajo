<?php 

class conecction{

    function conectar(){
        //$pdo = new PDO('mysql:host=localhost;id22089797_bolsadetrabajotecncia2','id22089797_bdtt2','WebHostTecnica2JuninEEST2!');
        $pdo= mysqli_connect('sql206.infinityfree.com', 'if0_37185014', 'iLxdXLKRZX3IX', 'if0_37185014_bolsa_de_trabajo');
        return $pdo;
    }

}

$c = new conecction();
$connection = $c->conectar();

?>