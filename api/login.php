<?php
include_once './config/database.php';
require "../vendor/autoload.php";
use \Firebase\JWT\JWT;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$email = '';
$password = '';

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();



$data = json_decode(file_get_contents("php://input"));

$email = $data->email;
$password = $data->password;

$table_name = 'Users';

$query = "SELECT id, first_name, last_name, password FROM " . $table_name . " WHERE email = ? LIMIT 0,1";

$stmt = $conn->prepare( $query );
$stmt->bindParam(1, $email);
$stmt->execute();
$num = $stmt->rowCount();

if($num > 0){
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $id = $row['id'];
    $firstname = $row['first_name'];
    $lastname = $row['last_name'];
    $password2 = $row['password'];
    
    if(password_verify($password, $password2))
    {
        $secret_key = "YOUR_SECRET_KEY";
        $issuer_claim = "THE_ISSUER";
        $audience_claim = "THE_AUDIENCE";
        $issuedat_claim = 1356999524; // issued at
        $notbefore_claim = 1357000000; //not before
        $token = array(
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "data" => array(
                "id" => $id,
                "firstname" => $firstname,
                "lastname" => $lastname,
                "email" => $email
        ));
 
        http_response_code(200);
 
        $jwt = JWT::encode($token, $secret_key);
        echo json_encode(
            array(
                "message" => "Successful login.",
                "jwt" => $jwt
            ));
    }
    else{
        
        http_response_code(401);
        echo json_encode(array("message" => "Login failed.", "password" => $password, "password2" => $password2));
    }
}
?>


