<?php

function addUser($user, $pdo) {
    // Vérifie si l'email existe déjà
    $sql = "SELECT * FROM users WHERE email = :email";
    $stm = $pdo->prepare($sql);
    $stm->execute(['email' => $user['email']]);

    if ($stm->fetch()) {
        return "Ce compte existe déjà !";
    }

    // Insertion du nouvel utilisateur
    $sql = "INSERT INTO users (nom, email, password) VALUES (:nom, :email, :password)";
    $stm = $pdo->prepare($sql);
    $stm->execute([
        'nom' => $user['nom'],
        'email' => $user['email'],
        'password' => password_hash($user['password'], PASSWORD_DEFAULT)
    ]);

    return true;
}
// function pour vérifier si l'utilisateur existe , si le mot de passe est correct et enregister le user dans session .
// function log($email,$password,$pdo) {
//     $sql = "SELECT * FROM users WHERE email = :email ";
//     $stm = $pdo->prepare($sql);
//     $stm->execute(['email' => $email]);
//     $user = $stm->fetch(PDO::FETCH_ASSOC);
//     if($user){
//        if(password_verify($password , $user['password'])){
//         $_SESSION['user'] = $user;
//         return true;
//        }
//     }
// }
?>