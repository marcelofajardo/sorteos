<?php

error_reporting(0);

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST");
header("Allow: GET, POST");

/* DATABASE CONNECTION */
$server = "localhost";
$user = "habl_use";
$pass  = "Lucho@1320";
$db = "habl_lots";
$conn = new PDO('mysql:host='.$server.';dbname='.$db.'', ''.$user.'', ''.$pass.'');

/* API SERVICE */
$data = json_decode(file_get_contents("php://input"), true);
$lot = $data['lot'] ? $data['lot'] : NULL;

$email = $data['email'] ? $data['email'] : NULL;
$email_filtered = NULL;

$lot_active = true;

if ( $lot_active && strpos($email, '@') ) {
  list($email_alias, $email_domain) = explode('@', $email);

  if ( !strpos($email_alias, '=') ) {
    if ( strpos($email_alias, '+') ) {
      list($email_alias, $email_filter) = explode('+', $email_alias);
    }

    $email_alias = str_replace('.', '', $email_alias);

    if ( validateDomainEmail($email_domain) ) {
      $email_filtered = $email_alias.'@'.$email_domain;
    }

    if ( $email_filtered ) {
      if ( !registeredEmail($conn, $email_filtered, $lot) ) {
        try {
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          $stmt = $conn->prepare("INSERT INTO lot_$lot (created_at, email) VALUES (:created_at, :email)");
          $stmt->bindParam(':created_at', date("Y-m-d h:s:i"));
          $stmt->bindParam(':email', $email_filtered);
          $stmt->execute();
          echo json_encode(array(
            "status" => "success"
          ));
        } catch (PDOException $e){
          echo json_encode(array(
            "status" => "error"
          ));
        }
      } else {
        echo json_encode(array(
          "status" => "registered"
        ));
      }
    } else {
      echo json_encode(array(
        "status" => "invalid"
      ));
    }
  } else {
    echo json_encode(array(
      "status" => "invalid"
    ));
  }
} else {
  echo json_encode(array(
    "status" => "invalid"
  ));
}

function registeredEmail($conn, $email, $lot) {
  $stmt = $conn->query("SELECT email FROM lot_$lot WHERE email = '$email'", PDO::FETCH_ASSOC);
  foreach ($stmt as $key => $value) {
    return $value['email'] == $email ? true : false;
  }
}

function validateDomainEmail($domain) {
  return $domain === 'gmail.com' ? true : ($domain === 'hotmail.com' ? true : ($domain ===  'yahoo.com' ? true : ($domain === 'live.com' ? true : ($domain === 'ymail.com' ? true : false))));
}