
<!DOCTYPE html>
<html>
   <head>
      <title> connexion.php;
      </title>
   </head>
   <body>

   <?php
   function connect()
   {
   $con=pg_connect("host=serveur-etu.polytech-lille.fr user=aballard port=5432 password=postgres dbname=anais_loic_bds") ;
   return $con;
   }
   ?>

   </body>
</html>
