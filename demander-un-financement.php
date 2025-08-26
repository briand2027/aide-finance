<?php
// Activation de l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclusion du fichier de connexion à la base de données
include("connexion.php");

// Initialisation des variables
$error_message = '';
$success_message = '';
$form_submitted = false;

// Traitement du formulaire s'il a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Récupération et sécurisation des données
        $type_financement = htmlspecialchars(trim($_POST['type_financement']));
        $civilite = htmlspecialchars(trim($_POST['civilite']));
        $nom_complet = htmlspecialchars(trim($_POST['nom_complet']));
        $email = htmlspecialchars(trim($_POST['email']));
        $telephone = htmlspecialchars(trim($_POST['telephone']));
        $adresse = htmlspecialchars(trim($_POST['adresse']));
        $code_postal = htmlspecialchars(trim($_POST['code_postal']));
        $ville = htmlspecialchars(trim($_POST['ville']));
        $titre_projet = htmlspecialchars(trim($_POST['titre_projet']));
        $description_projet = htmlspecialchars(trim($_POST['description_projet']));
        $montant_demande = floatval($_POST['montant_demande']);
        $duree_projet = htmlspecialchars(trim($_POST['duree_projet']));

        // Validation des champs obligatoires
        $required_fields = [
            'type_financement' => $type_financement,
            'civilite' => $civilite,
            'nom_complet' => $nom_complet,
            'email' => $email,
            'telephone' => $telephone,
            'adresse' => $adresse,
            'code_postal' => $code_postal,
            'ville' => $ville,
            'titre_projet' => $titre_projet,
            'description_projet' => $description_projet,
            'montant_demande' => $montant_demande,
            'duree_projet' => $duree_projet
        ];

        $missing_fields = [];
        foreach ($required_fields as $field => $value) {
            if (empty($value)) {
                $missing_fields[] = $field;
            }
        }

        if (!empty($missing_fields)) {
            throw new Exception('Tous les champs obligatoires doivent être remplis.');
        }

        // Validation de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('L\'adresse email n\'est pas valide.');
        }

        // Gestion de l'upload des fichiers
        $cv_filename = null;
        $projet_filename = null;
        
        // Dossier de destination pour les fichiers
        $upload_dir = "uploads/demandes/";
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception('Impossible de créer le dossier de destination pour les fichiers.');
            }
        }
        
        // Traitement du CV
        if (!empty($_FILES['cv_file']['name'])) {
            $cv_file_ext = strtolower(pathinfo($_FILES['cv_file']['name'], PATHINFO_EXTENSION));
            $cv_filename = "cv_" . time() . "_" . uniqid() . "." . $cv_file_ext;
            $cv_target = $upload_dir . $cv_filename;
            
            // Vérification du type de fichier
            $allowed_extensions = ['pdf', 'doc', 'docx'];
            if (!in_array($cv_file_ext, $allowed_extensions)) {
                throw new Exception('Le format du CV n\'est pas autorisé. Formats acceptés: PDF, DOC, DOCX.');
            }
            
            // Vérification de la taille (5MB max)
            if ($_FILES['cv_file']['size'] > 5 * 1024 * 1024) {
                throw new Exception('Le fichier CV est trop volumineux. Taille maximale: 5MB.');
            }
            
            // Déplacement du fichier
            if (!move_uploaded_file($_FILES['cv_file']['tmp_name'], $cv_target)) {
                throw new Exception('Erreur lors de l\'upload du CV.');
            }
        }
        
        // Traitement du fichier de projet
        if (!empty($_FILES['projet_file']['name'])) {
            $projet_file_ext = strtolower(pathinfo($_FILES['projet_file']['name'], PATHINFO_EXTENSION));
            $projet_filename = "projet_" . time() . "_" . uniqid() . "." . $projet_file_ext;
            $projet_target = $upload_dir . $projet_filename;
            
            // Vérification du type de fichier
            $allowed_extensions = ['pdf', 'doc', 'docx'];
            if (!in_array($projet_file_ext, $allowed_extensions)) {
                throw new Exception('Le format du fichier projet n\'est pas autorisé. Formats acceptés: PDF, DOC, DOCX.');
            }
            
            // Vérification de la taille (10MB max)
            if ($_FILES['projet_file']['size'] > 10 * 1024 * 1024) {
                throw new Exception('Le fichier projet est trop volumineux. Taille maximale: 10MB.');
            }
            
            // Déplacement du fichier
            if (!move_uploaded_file($_FILES['projet_file']['tmp_name'], $projet_target)) {
                throw new Exception('Erreur lors de l\'upload du fichier projet.');
            }
        }
        
        // Insertion dans la base de données
        $stmt = $conn->prepare("INSERT INTO demandes_financement 
                               (type_financement, civilite, nom_complet, email, telephone, adresse, code_postal, ville, 
                                titre_projet, description_projet, montant_demande, duree_projet, cv_filename, projet_filename) 
                               VALUES 
                               (:type_financement, :civilite, :nom_complet, :email, :telephone, :adresse, :code_postal, :ville, 
                                :titre_projet, :description_projet, :montant_demande, :duree_projet, :cv_filename, :projet_filename)");
        
        $stmt->bindParam(':type_financement', $type_financement);
        $stmt->bindParam(':civilite', $civilite);
        $stmt->bindParam(':nom_complet', $nom_complet);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telephone', $telephone);
        $stmt->bindParam(':adresse', $adresse);
        $stmt->bindParam(':code_postal', $code_postal);
        $stmt->bindParam(':ville', $ville);
        $stmt->bindParam(':titre_projet', $titre_projet);
        $stmt->bindParam(':description_projet', $description_projet);
        $stmt->bindParam(':montant_demande', $montant_demande);
        $stmt->bindParam(':duree_projet', $duree_projet);
        $stmt->bindParam(':cv_filename', $cv_filename);
        $stmt->bindParam(':projet_filename', $projet_filename);
        
        if (!$stmt->execute()) {
            throw new Exception('Erreur lors de l\'enregistrement en base de données.');
        }
        
        // Succès - on prépare le message et on nettoie les données du formulaire
        $success_message = 'Votre demande a été enregistrée avec succès!';
        $form_submitted = true;
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demander un Financement - Aide Finance</title>
    <meta name="description" content="Demandez votre financement non remboursable de 1 000€ à 900 000€ pour vos projets personnels ou professionnels.">
    <!-- Favicon -->
    <link rel="icon" href="assets/favicon.png" type="image/png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Votre CSS existant ici */
        .progress-bar {
            background-color: var(--primary-color);
        }
        .form-section {
            display: none;
        }
        .form-section.active {
            display: block;
        }
        .form-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
        .confirmation-icon {
            font-size: 5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .upload-area:hover {
            border-color: var(--primary-light);
            background-color: rgba(106, 27, 154, 0.05);
        }
        .upload-icon {
            font-size: 3rem;
            color: var(--primary-light);
            margin-bottom: 1rem;
        }
        .file-name {
            margin-top: 1rem;
            font-weight: 500;
            color: var(--primary-color);
        }
        .finance-type-card {
            transition: all 0.3s ease;
            cursor: pointer;
            height: 100%;
        }
        .finance-type-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        .finance-type-card.selected {
            border: 2px solid var(--primary-color) !important;
            background-color: rgba(106, 27, 154, 0.05);
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .card-title {
            color: var(--primary-color);
            font-weight: 600;
        }
        .card {
            border: 1px solid rgba(0,0,0,0.125);
            border-radius: 12px;
            overflow: hidden;
        }
        .alert-message {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1050;
            animation: fadeIn 0.5s, fadeOut 0.5s 2.5s forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-20px); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.html" aria-label="Retour à l'accueil">
                <img src="assets/logo-aide-finance.png" alt="Aide Finance" height="50">
                <span class="ms-1">Aide Finance</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Ouvrir la navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">ACCUEIL</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="demander-un-financement.php">Demander un Financement</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.html" aria-current="page">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="a-propos.html">À PROPOS</a>
                    </li>
                </ul>
                <!-- <div class="ms-lg-3 mt-3 mt-lg-0">
                    <a href="espace-client.html" class="btn btn-sm btn-gold">Espace client <i class="fas fa-lock ms-2" aria-hidden="true"></i></a>
                </div> -->
            </div>
        </div>
    </nav>

    <!-- Affichage des messages d'alerte -->
    <?php if (isset($error_message)): ?>
    <div class="alert-message">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (isset($success_message)): ?>
    <div class="alert-message">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="demande-section pt-6" style="padding-top:120px;">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="text-center mb-5" data-aos="fade-up">
                        <h1 class="section-title">Demandez votre <span class="highlight">financement</span></h1>
                        <p class="lead">Remplissez le formulaire ci-dessous pour obtenir une étude personnalisée de votre projet</p>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-5" data-aos="fade-up" data-aos-delay="100">
                        <div class="d-none d-md-flex justify-content-between mb-2">
                            <span>Type de financement</span>
                            <span>Vos informations</span>
                            <span>Votre projet</span>
                            <span>Documents</span>
                            <span>Validation</span>
                        </div>
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Formulaire -->
                    <form action="demander-un-financement.php" method="POST" enctype="multipart/form-data" id="financementForm">
                        <!-- Step 1: Type de financement -->
                        <div class="form-section active" id="step1">
                            <h3 class="mb-4">Type de financement</h3>
                            <p class="mb-4">Sélectionnez le type de financement qui correspond à votre projet</p>
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="card finance-type-card h-100" data-type="entreprise">
                                        <div class="card-body text-center p-4">
                                            <div class="mb-3">
                                                <i class="fas fa-business-time fa-3x text-primary"></i>
                                            </div>
                                            <h5 class="card-title">Financement entreprise</h5>
                                            <p class="text-muted">Pour le développement, l'innovation ou la création d'entreprise</p>
                                            <div class="price-range mt-3">
                                                <i class="fas fa-tags me-2"></i> 10.000€ à 900.000€
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card finance-type-card h-100" data-type="particulier">
                                        <div class="card-body text-center p-4">
                                            <div class="mb-3">
                                                <i class="fas fa-user-tie fa-3x text-primary"></i>
                                            </div>
                                            <h5 class="card-title">Aide aux particuliers</h5>
                                            <p class="text-muted">Pour des projets personnels, formation ou reconversion</p>
                                            <div class="price-range mt-3">
                                                <i class="fas fa-tags me-2"></i> 1.000€ à 50.000€
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 offset-md-3 mt-4">
                                    <div class="card finance-type-card h-100" data-type="logement">
                                        <div class="card-body text-center p-4">
                                            <div class="mb-3">
                                                <i class="fas fa-home fa-3x text-primary"></i>
                                            </div>
                                            <h5 class="card-title">Aide au logement</h5>
                                            <p class="text-muted">Pour l'accession à la propriété ou la rénovation énergétique</p>
                                            <div class="price-range mt-3">
                                                <i class="fas fa-tags me-2"></i> Jusqu'à 450.000€
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="type_financement" id="type_financement" value="<?php echo isset($type_financement) ? $type_financement : ''; ?>" required>

                            <div class="alert alert-info mt-4">
                                <i class="fas fa-info-circle me-2"></i> Vous pourrez préciser les détails de votre projet dans les étapes suivantes.
                            </div>

                            <div class="form-navigation mt-5">
                                <div></div> <!-- Empty div for spacing -->
                                <button type="button" class="btn btn-gold" onclick="validateStep1()">Suivant <i class="fas fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Step 2: Informations personnelles -->
                        <div class="form-section" id="step2">
                            <h3 class="mb-4">Vos informations</h3>
                            <p class="mb-4">Renseignez vos coordonnées pour que nous puissions vous recontacter</p>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="civilite" class="form-label">Civilité *</label>
                                    <select class="form-select" id="civilite" name="civilite" required>
                                        <option value="" selected disabled>Sélectionnez</option>
                                        <option value="monsieur" <?php echo (isset($civilite) && $civilite == 'monsieur') ? 'selected' : ''; ?>>Monsieur</option>
                                        <option value="madame" <?php echo (isset($civilite) && $civilite == 'madame') ? 'selected' : ''; ?>>Madame</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="nom_complet" class="form-label">Nom complet *</label>
                                    <input type="text" class="form-control" id="nom_complet" name="nom_complet" value="<?php echo isset($nom_complet) ? $nom_complet : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Adresse email *</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="telephone" class="form-label">Téléphone *</label>
                                    <input type="tel" class="form-control" id="telephone" name="telephone" value="<?php echo isset($telephone) ? $telephone : ''; ?>" required>
                                </div>
                                <div class="col-12">
                                    <label for="adresse" class="form-label">Adresse *</label>
                                    <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo isset($adresse) ? $adresse : ''; ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="code_postal" class="form-label">Code postal *</label>
                                    <input type="text" class="form-control" id="code_postal" name="code_postal" value="<?php echo isset($code_postal) ? $code_postal : ''; ?>" required>
                                </div>
                                <div class="col-md-8">
                                    <label for="ville" class="form-label">Ville *</label>
                                    <input type="text" class="form-control" id="ville" name="ville" value="<?php echo isset($ville) ? $ville : ''; ?>" required>
                                </div>
                            </div>

                            <div class="form-navigation mt-5">
                                <button type="button" class="btn btn-outline-secondary" onclick="prevStep(2)"><i class="fas fa-arrow-left me-2"></i> Retour</button>
                                <button type="button" class="btn btn-gold" onclick="nextStep(2)">Suivant <i class="fas fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Step 3: Détails du projet -->
                        <div class="form-section" id="step3">
                            <h3 class="mb-4">Votre projet</h3>
                            <p class="mb-4">Décrivez-nous votre projet en détail</p>
                            
                            <div class="mb-3">
                                <label for="titre_projet" class="form-label">Titre du projet *</label>
                                <input type="text" class="form-control" id="titre_projet" name="titre_projet" value="<?php echo isset($titre_projet) ? $titre_projet : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="description_projet" class="form-label">Description détaillée *</label>
                                <textarea class="form-control" id="description_projet" name="description_projet" rows="5" required><?php echo isset($description_projet) ? $description_projet : ''; ?></textarea>
                                <div class="info-text">Décrivez votre projet, ses objectifs, son avancement et ses besoins spécifiques</div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="montant_demande" class="form-label">Montant demandé (€) *</label>
                                    <input type="number" class="form-control" id="montant_demande" name="montant_demande" min="1000" max="900000" value="<?php echo isset($montant_demande) ? $montant_demande : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="duree_projet" class="form-label">Durée estimée du projet *</label>
                                    <select class="form-select" id="duree_projet" name="duree_projet" required>
                                        <option value="" selected disabled>Sélectionnez</option>
                                        <option value="<3" <?php echo (isset($duree_projet) && $duree_projet == '<3') ? 'selected' : ''; ?>>Moins de 3 mois</option>
                                        <option value="3-6" <?php echo (isset($duree_projet) && $duree_projet == '3-6') ? 'selected' : ''; ?>>3 à 6 mois</option>
                                        <option value="6-12" <?php echo (isset($duree_projet) && $duree_projet == '6-12') ? 'selected' : ''; ?>>6 à 12 mois</option>
                                        <option value=">12" <?php echo (isset($duree_projet) && $duree_projet == '>12') ? 'selected' : ''; ?>>Plus de 12 mois</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-navigation mt-5">
                                <button type="button" class="btn btn-outline-secondary" onclick="prevStep(3)"><i class="fas fa-arrow-left me-2"></i> Retour</button>
                                <button type="button" class="btn btn-gold" onclick="nextStep(3)">Suivant <i class="fas fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Step 4: Documents -->
                        <div class="form-section" id="step4">
                            <h3 class="mb-4">Documents à fournir</h3>
                            <p class="mb-4">Téléchargez les documents nécessaires à l'étude de votre dossier</p>
                            
                            <div class="mb-4">
                                <div class="upload-area" id="cvUpload">
                                    <div class="upload-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <h5>CV ou profil professionnel</h5>
                                    <p>Glissez-déposez votre fichier ici ou <span class="text-primary cursor-pointer">parcourir</span></p>
                                    <p class="text-muted small">Formats acceptés: PDF, DOC, DOCX (max. 5MB)</p>
                                    <div class="file-name" id="cvFileName"></div>
                                </div>
                                <input type="file" id="cvInput" name="cv_file" class="d-none" accept=".pdf,.doc,.docx">
                            </div>

                            <div class="mb-4">
                                <div class="upload-area" id="projetUpload">
                                    <div class="upload-icon">
                                        <i class="fas fa-file-pdf"></i>
                                    </div>
                                    <h5>Dossier de projet détaillé (optionnel)</h5>
                                    <p>Glissez-déposez votre fichier ici ou <span class="text-primary cursor-pointer">parcourir</span></p>
                                    <p class="text-muted small">Formats acceptés: PDF, DOC, DOCX (max. 10MB)</p>
                                    <div class="file-name" id="projetFileName"></div>
                                </div>
                                <input type="file" id="projetInput" name="projet_file" class="d-none" accept=".pdf,.doc,.docx">
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="conditions" name="conditions" required>
                                <label class="form-check-label" for="conditions">
                                    J'accepte les <a href="#" class="text-primary">conditions générales</a> et la <a href="#" class="text-primary">politique de confidentialité</a> *
                                </label>
                            </div>

                            <div class="form-navigation mt-5">
                                <button type="button" class="btn btn-outline-secondary" onclick="prevStep(4)"><i class="fas fa-arrow-left me-2"></i> Retour</button>
                                <button type="submit" class="btn btn-gold">Soumettre la demande <i class="fas fa-check ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Step 5: Confirmation -->
                        <div class="form-section" id="step5">
                            <div class="text-center py-5">
                                <div class="confirmation-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h3 class="mb-3">Votre demande a été envoyée avec succès !</h3>
                                <p class="mb-4">Nous avons bien reçu votre demande de financement et allons l'étudier dans les plus brefs délais.</p>
                                <p class="mb-4">Un conseiller vous contactera sous <strong>72 heures maximum</strong> pour discuter de votre projet.</p>
                                <div class="d-flex justify-content-center gap-3 flex-wrap">
                                    <a href="index.php" class="btn btn-outline-primary">Retour à l'accueil</a>
                                    <a href="#" class="btn btn-gold">Espace client</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-section bg-dark text-white pt-5 pb-3">
        <div class="container">
            <div class="row g-4">
                <!-- Logo + description -->
                <div class="col-md-4">
                    <a href="index.php" class="navbar-brand text-white">
                        <img src="assets/logo-aide-finance.png" alt="Aide Finance" height="40">
                        <span class="ms-1">Aide Finance</span>
                    </a>
                    <p class="mt-3">Nous vous accompagnons pour obtenir des financements non remboursables adaptés à vos projets personnels et professionnels.</p>
                </div>
                <!-- Liens rapides -->
                <div class="col-md-4">
                    <h5>Liens utiles</h5>
                    <ul class="list-unstyled mt-2">
                        <li><a href="index.php" class="text-white">Accueil</a></li>
                        <li><a href="demander-un-financement.php" class="text-white">Demander un financement</a></li>
                        <li><a href="a-propos.php" class="text-white">À propos</a></li>
                        <li><a href="contact.php" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <!-- Réseaux sociaux -->
                <div class="col-md-4">
                    <h5>Suivez-nous</h5>
                    <div class="d-flex gap-3 mt-2">
                        <a href="#" class="text-white" aria-label="Facebook"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white" aria-label="LinkedIn"><i class="fab fa-linkedin fa-lg"></i></a>
                        <a href="#" class="text-white" aria-label="WhatsApp"><i class="fab fa-whatsapp fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <small>&copy; 2025 Aide Finance. Tous droits réservés.</small>
            </div>
        </div>
    </footer>

    <!-- Modal pour la sélection du type de financement -->
    <div class="modal fade" id="financementModal" tabindex="-1" aria-labelledby="financementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <div class="modal-icon text-center mb-3">
                        <i class="fas fa-exclamation-circle fa-3x text-warning"></i>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-0">
                    <h5 class="modal-title mb-3" id="financementModalLabel">Sélection requise</h5>
                    <p>Veuillez sélectionner un type de financement pour continuer.</p>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-gold" data-bs-dismiss="modal">J'ai compris</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Init animations
        AOS.init({ once: true, duration: 700, offset: 80 });

        // Navbar scroll effect
        window.addEventListener("scroll", function () {
            var navbar = document.querySelector(".navbar");
            if (window.scrollY > 50) {
                navbar.classList.add("scrolled");
            } else {
                navbar.classList.remove("scrolled");
            }
        });

        // Form navigation
        let currentStep = 1;
        const totalSteps = 5;
        let financeType = '';

        // Initialize finance type selection
        document.querySelectorAll('.finance-type-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remove selected class from all cards
                document.querySelectorAll('.finance-type-card').forEach(c => {
                    c.classList.remove('selected');
                });
                
                // Add selected class to clicked card
                this.classList.add('selected');
                
                // Store the selected finance type
                financeType = this.getAttribute('data-type');
                document.getElementById('type_financement').value = financeType;
            });
        });

        function validateStep1() {
            if (!financeType) {
                // Afficher le modal au lieu d'une alerte
                var modal = new bootstrap.Modal(document.getElementById('financementModal'));
                modal.show();
                return;
            }
            nextStep(1);
        }

        function updateProgressBar() {
            const progressPercentage = ((currentStep - 1) / (totalSteps - 1)) * 100;
            document.querySelector('.progress-bar').style.width = `${progressPercentage}%`;
        }

        function nextStep(step) {
            // Basic validation before proceeding
            if (step === 2) {
                const requiredFields = document.querySelectorAll('#step2 input[required], #step2 select[required]');
                let valid = true;
                requiredFields.forEach(field => {
                    if (!field.value) {
                        field.classList.add('is-invalid');
                        valid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });
                if (!valid) return;
            }
            
            if (step === 3) {
                const requiredFields = document.querySelectorAll('#step3 input[required], #step3 textarea[required], #step3 select[required]');
                let valid = true;
                requiredFields.forEach(field => {
                    if (!field.value) {
                        field.classList.add('is-invalid');
                        valid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });
                if (!valid) return;
            }

            document.getElementById(`step${step}`).classList.remove('active');
            currentStep = step + 1;
            document.getElementById(`step${currentStep}`).classList.add('active');
            updateProgressBar();
        }

        function prevStep(step) {
            document.getElementById(`step${step}`).classList.remove('active');
            currentStep = step - 1;
            document.getElementById(`step${currentStep}`).classList.add('active');
            updateProgressBar();
        }

        // File upload handling
        document.getElementById('cvUpload').addEventListener('click', () => {
            document.getElementById('cvInput').click();
        });

        document.getElementById('projetUpload').addEventListener('click', () => {
            document.getElementById('projetInput').click();
        });

        document.getElementById('cvInput').addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                document.getElementById('cvFileName').textContent = e.target.files[0].name;
            }
        });

        document.getElementById('projetInput').addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                document.getElementById('projetFileName').textContent = e.target.files[0].name;
            }
        });

        // Drag and drop for file uploads
        const preventDefault = (e) => {
            e.preventDefault();
            e.stopPropagation();
        };

        const addDropHighlight = (e) => {
            preventDefault(e);
            e.currentTarget.classList.add('bg-light');
        };

        const removeDropHighlight = (e) => {
            preventDefault(e);
            e.currentTarget.classList.remove('bg-light');
        };

        const handleDrop = (e, inputId, fileNameId) => {
            preventDefault(e);
            e.currentTarget.classList.remove('bg-light');
            
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                const file = e.dataTransfer.files[0];
                document.getElementById(inputId).files = e.dataTransfer.files;
                document.getElementById(fileNameId).textContent = file.name;
            }
        };

        // Set up drag and drop for both upload areas
        ['cvUpload', 'projetUpload'].forEach(id => {
            const element = document.getElementById(id);
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                element.addEventListener(eventName, preventDefault);
            });
            element.addEventListener('dragenter', addDropHighlight);
            element.addEventListener('dragover', addDropHighlight);
            element.addEventListener('dragleave', removeDropHighlight);
            element.addEventListener('drop', (e) => {
                handleDrop(e, id === 'cvUpload' ? 'cvInput' : 'projetInput', id === 'cvUpload' ? 'cvFileName' : 'projetFileName');
            });
        });

        // If form was submitted successfully, show confirmation
        <?php if (isset($form_submitted) && $form_submitted): ?>
        document.querySelectorAll('.form-section').forEach(section => {
            section.classList.remove('active');
        });
        document.getElementById('step5').classList.add('active');
        currentStep = 5;
        updateProgressBar();
        <?php endif; ?>
    </script>
</body>
</html>