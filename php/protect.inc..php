<?php
function protect($data) {
   $data = htmlspecialchars($data);
   $data = stripslashes($data);
   $data = addslashes($data);
   return $data;
}
