<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("connexion.php");

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Configuration de l'email
$destinataire = "saizonouemeli@gmail.com";
$sujet_email = "Nouveau message de contact - Aide Finance";

// Vérification si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération et sécurisation des données
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $nom = htmlspecialchars(trim($_POST['nom']));
    $email = htmlspecialchars(trim($_POST['email']));
    $telephone = htmlspecialchars(trim($_POST['telephone'] ?? ''));
    $sujet = htmlspecialchars(trim($_POST['sujet']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Validation des champs obligatoires
    if (empty($prenom) || empty($nom) || empty($email) || empty($sujet) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis.']);
        exit;
    }

    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'L\'adresse email n\'est pas valide.']);
        exit;
    }

    try {

        // Insertion dans la base de données
        $stmt = $conn->prepare("INSERT INTO contact (nom, prenoms, email, telephone, sujet, message) 
                               VALUES (:nom, :prenoms, :email, :telephone, :sujet, :message)");
        
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenoms', $prenom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telephone', $telephone);
        $stmt->bindParam(':sujet', $sujet);
        $stmt->bindParam(':message', $message);
        
        $stmt->execute();
        
        // Préparation de l'email
        $contenu_email = '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau message de contact</title>
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f7f9fc; }
        .email-container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); }
        .email-header { background: linear-gradient(135deg, #6a1b9a 0%, #8e24aa 100%); color: white; padding: 25px 30px; text-align: center; }
        .logo { font-size: 24px; font-weight: bold; margin-bottom: 10px; display: inline-block; }
        .logo-icon { margin-right: 10px; vertical-align: middle; }
        .email-title { margin: 0; font-size: 22px; font-weight: 600; }
        .email-content { padding: 30px; }
        .message-intro { font-size: 16px; color: #666; margin-bottom: 25px; line-height: 1.5; }
        .info-card { background: #f9f9f9; border-left: 4px solid #8e24aa; padding: 20px; border-radius: 4px; margin-bottom: 25px; }
        .info-field { margin-bottom: 15px; display: flex; }
        .field-label { font-weight: 600; color: #6a1b9a; min-width: 100px; }
        .field-value { flex: 1; }
        .message-content { background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0; font-style: italic; border-left: 3px solid #6a1b9a; }
        .email-footer { background: #f1f3f6; padding: 20px 30px; text-align: center; font-size: 14px; color: #666; }
        .footer-links { margin-top: 15px; }
        .footer-link { color: #6a1b9a; text-decoration: none; margin: 0 10px; }
        .social-icons { margin-top: 15px; }
        .social-icon { display: inline-block; width: 36px; height: 36px; line-height: 36px; text-align: center; background: #6a1b9a; color: white; border-radius: 50%; margin: 0 5px; text-decoration: none; }
        @media (max-width: 600px) {
            .email-container { margin: 10px; border-radius: 8px; }
            .email-header, .email-content, .email-footer { padding: 20px; }
            .info-field { flex-direction: column; }
            .field-label { margin-bottom: 5px; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="logo">
                <span class="logo-icon">💼</span>
                Aide Finance
            </div>
            <h1 class="email-title">Nouveau Message de Contact</h1>
        </div>
        
        <div class="email-content">
            <p class="message-intro">Vous avez reçu un nouveau message de contact via votre site web. Voici les détails :</p>
            
            <div class="info-card">
                <div class="info-field">
                    <span class="field-label">Nom complet:</span>
                    <span class="field-value">' . $prenom . ' ' . $nom . '</span>
                </div>
                
                <div class="info-field">
                    <span class="field-label">Email:</span>
                    <span class="field-value">' . $email . '</span>
                </div>
                
                <div class="info-field">
                    <span class="field-label">Téléphone:</span>
                    <span class="field-value">' . ($telephone ?: 'Non renseigné') . '</span>
                </div>
                
                <div class="info-field">
                    <span class="field-label">Sujet:</span>
                    <span class="field-value">' . $sujet . '</span>
                </div>
                
                <div class="info-field">
                    <span class="field-label">Date:</span>
                    <span class="field-value">' . date('d/m/Y H:i:s') . '</span>
                </div>
            </div>
            
            <h3>Message:</h3>
            <div class="message-content">
                ' . nl2br($message) . '
            </div>
        </div>
        
        <div class="email-footer">
            <p>© 2023 Aide Finance. Tous droits réservés.</p>
            
            <div class="footer-links">
                <a href="#" class="footer-link">Mentions légales</a>
                <a href="#" class="footer-link">Politique de confidentialité</a>
                <a href="#" class="footer-link">Contact</a>
            </div>
            
            <div class="social-icons">
                <a href="#" class="social-icon">f</a>
                <a href="#" class="social-icon">in</a>
                <a href="#" class="social-icon">t</a>
            </div>
        </div>
    </div>
</body>
</html>
        ';

        $mail = new PHPMailer(true);

        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'saizonouemeli@gmail.com';                     //SMTP username
        $mail->Password   = 'ssyk ieqi zzuv tzbz';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;

       // Expéditeur et destinataire
        $mail->setFrom('saizonouemeli@gmail.com', 'Aide Finance');
        $mail->addAddress($destinataire);
        $mail->addReplyTo($email, $prenom . ' ' . $nom);

        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = $sujet_email;
        $mail->Body = $contenu_email;
        $mail->AltBody = strip_tags($contenu_email); // Version texte simple

        // Envoi de l'email
        $email_sent = $mail->send();

        // En-têtes de l'email

        // $headers = "MIME-Version: 1.0" . "\r\n";
        // $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        // $headers .= "From: Aide Finance <no-reply@aidefinance.fr>" . "\r\n";
        // $headers .= "Reply-To: $email" . "\r\n";
        // $headers .= "X-Mailer: PHP/" . phpversion();

        // Envoi de l'email

        //$email_sent = mail($destinataire, $sujet_email, $contenu_email, $headers);

        if ($email_sent) {
            echo json_encode(['success' => true, 'message' => 'Votre message a été envoyé avec succès ! Nous vous recontacterons dans les plus brefs délais.']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Votre message a été enregistré mais l\'envoi de l\'email a échoué.']);
        }

    } catch(PDOException $e) {
        error_log("Erreur PDO: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Une erreur s\'est produite lors de l\'enregistrement. Veuillez réessayer.']);
    }

    $conn = null;
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
}
?>