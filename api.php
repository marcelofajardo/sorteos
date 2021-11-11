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

          // Email
          sendEmail();

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
  return $domain === 'gmail.com' ? true : ($domain === 'hotmail.com' ? true : ($domain ===  'yahoo.com' ? true : ($domain === 'live.com' ? true : ($domain === 'ymail.com' ? true : ($domain ==='outlook.com' ? true : ($domain === 'outlook.es' ? true : false))))));
}

function sendEmail() {
  $subject = "Sorteo Curso React PRO";

  $message = '<html><body style="background-color: #f0f0f0 !important; padding: 20px 0px">';
  $message .= '<div style="background-color: #ffffff !important; color: #000000 !important; font-size: 17px !important; margin: 0 auto; display: block; width: 66%; padding: 2%;">';
  $message .= '<a href="https://hablemosdecodigo.com" style="margin: 0 auto 20px auto; display: block;">';
  $message .= '<img src="https://hablemosdecodigo.com/wp-content/uploads/2021/10/logo-web-1.png" alt="Hablemos de Código" height="50" />';
  $message .= '</a>';
  $message .= '<h2>'.$subject.'</h2>';
  $message .= '<p>Ya estás participando en el sorteo.<br />';
  $message .= '¡Mucha suerte!.</p>';
  $message .= '</div>';
  $message .= '<div style="text-align: center">';
  $message .= '<p style="font-size: 12px; color: #999999 !important;">';
  $message .= 'Hablemos de Código es el único responsable de las entrega de premios.<br />Powered by <a href="https://codify.com.co" style="color: #555555 !important">Codify Agency</a>';
  $message .= '</p></div>';
  $message .= '</body></html>';

  $headers = "From: noreply@hablemosdecodigo.com\r\n";
  $headers .= "BCC: hablemosdecodigo+sorteo@gmail.com\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

  mail($email_filtered, $subject, $message, $headers);
}