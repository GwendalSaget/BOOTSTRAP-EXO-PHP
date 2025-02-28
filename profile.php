<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=brigade_topchef', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT nom, prenom, email, phone, adresse, date_naissance FROM utilisateurs WHERE id = :id");
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$error = "";
$success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Erreur de validation du formulaire. Veuillez réessayer.";
    } else {
        $nom = htmlspecialchars(trim($_POST['nom']));
        $prenom = htmlspecialchars(trim($_POST['prenom']));
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $adresse = htmlspecialchars(trim($_POST['adresse']));
        $date_naissance = htmlspecialchars(trim($_POST['date_naissance']));
        $phone = htmlspecialchars(trim($_POST['phone']));

        if (empty($nom) || empty($prenom) || empty($email) || empty($phone)) {
            $error = "Tous les champs sont obligatoires.";
        } else {
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email, phone = :phone, date_naissance = :date_naissance, adresse = :adresse WHERE id = :id");
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':date_naissance', $date_naissance);
            $stmt->bindParam(':adresse', $adresse);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);


            if ($stmt->execute()) {
                $_SESSION['user_nom'] = $nom;
                $_SESSION['user_prenom'] = $prenom;
                $success = "Informations mises à jour avec succès.";
            } else {
                $error = "Une erreur s'est produite lors de la mise à jour.";
            }
        }
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Erreur de validation du formulaire. Veuillez réessayer.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = :id");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            session_destroy();
            header("Location: connexion.php");
            exit();
        } else {
            $error = "Une erreur s'est produite lors de la suppression.";
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>La Brigade Top Chef</title>
</head>
<body>
<div class="bg-image position-relative" style="width: 100%;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="
        background-image: url('assets/img/topchef2.jpeg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        opacity: 0.35;
        z-index: -1;">
    </div>
    <header>
        <!-- MENU NAVIGATION-->
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <a class="navbar-brand" href="accueil.html"><img class="logo" src ="assets/img/LogoTopChef.png" alt ="Logo du restaurant"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class=" navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link lien" href="accueil.html">Accueil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link lien" href="produits.html">Carte</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link lien" href="contact.php">Réservation</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link lien" href="profile.php">Profil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link lien" href="index.html">Déconnexion</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- FIN MENU NAVIGATION-->
    </header>
    <main>
        <section class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header text-white">
                            <h2 class="text-center" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;">Mon Profil</h2>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>

                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                                <div class="mb-3">
                                    <label for="nom" class="form-label" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;">Nom</label>
                                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;" required>
                                </div>
                                <div class="mb-3">
                                    <label for="prenom" class="form-label" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;">Prénom</label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;">Téléphone</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;" required>
                                </div>
                                <div class="mb-3">
                                    <label for="adresse" class="form-label" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;">Adresse</label>
                                    <input type="text" class="form-control" id="adresse" name="adresse" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;" value="<?php echo htmlspecialchars($user['adresse']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="date_naissance" class="form-label" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;">Date de naissance</label>
                                    <input type="date" class="form-control" id="date_naissance" name="date_naissance" style="font-family: 'GothamBook', sans-serif; color: #FFFFFF;" value="<?php echo htmlspecialchars($user['date_naissance']); ?>">
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" name="update" class="btn bic">Mettre à jour</button>
                                </div>
                            </form>

                            <hr>

                            <form method="post" onsubmit="return confirm('Voulez-vous vraiment supprimer votre compte ? Cette action est irréversible.');">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <div class="d-grid gap-2">
                                    <button type="submit" name="delete" class="btn bic">Supprimer mon compte</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="text-white text-center py-3" style ="font-family: 'GothamLight', sans-serif">
        <p>&copy; 2025 La Brigade TopChef M6. Tous droits réservés.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</div>
</body>
</html>