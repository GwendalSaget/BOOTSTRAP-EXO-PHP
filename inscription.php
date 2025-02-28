<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Inscription - La Brigade Top Chef</title>
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
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.html"><img class="logo" src ="assets/img/LogoTopChef.png" alt ="Logo du restaurant"></a>
            </div>
        </nav>
    </header>
    <main>
        <section class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header bg-dark text-white">
                            <h2 class="text-center" style="Font-family:'GothamBook', sans-serif;">Inscription</h2>
                        </div>
                        <div class="card-body">
                            <?php

                            $nom = $prenom = $adresse = $email = $date_naissance = $password = $phone = "";
                            $error = "";
                            $success = "";
                            session_start();
                            if (empty($_SESSION['csrf_token'])) {
                                try {
                                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                                } catch (\Random\RandomException $e) {

                                }
                            }
                            $csrf_token = $_SESSION['csrf_token'];

                            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                                if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                                    $error = "Erreur de validation du formulaire. Veuillez réessayer.";
                                } else {
                                    try {
                                        $pdo = new PDO('mysql:host=localhost;dbname=brigade_topchef', 'root', '');
                                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                                        $nom = htmlspecialchars(trim($_POST['nom']));
                                        $prenom = htmlspecialchars(trim($_POST['prenom']));
                                        $adresse = htmlspecialchars(trim($_POST['adresse']));
                                        $date_naissance = htmlspecialchars(trim($_POST['date_naissance']));
                                        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
                                        $phone = number_format((float)$_POST['phone']);
                                        $password = $_POST['password'];

                                        if (empty($nom) || empty($prenom) || empty($adresse) || empty($email) || empty($date_naissance) || empty($password) || empty($phone)) {
                                            $error = "Tous les champs sont obligatoires.";
                                        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                            $error = "Format d'email invalide.";
                                        } else {
                                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = :email");
                                            $stmt->bindParam(':email', $email);
                                            $stmt->execute();

                                            if ($stmt->fetchColumn() > 0) {
                                                $error = "Cette adresse email est déjà utilisée.";
                                            } else {

                                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                                                $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, adresse, email, date_naissance, password, phone) 
                                                                VALUES (:nom, :prenom, :adresse, :email, :date_naissance, :password, :phone)");

                                                $stmt->bindParam(':nom', $nom);
                                                $stmt->bindParam(':prenom', $prenom);
                                                $stmt->bindParam(':email', $email);
                                                $stmt->bindParam(':date_naissance', $date_naissance);
                                                $stmt->bindParam(':adresse', $adresse);
                                                $stmt->bindParam(':password', $hashed_password);
                                                $stmt->bindParam(':phone', $phone);

                                                $stmt->execute();
                                                $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";

                                                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                                                $csrf_token = $_SESSION['csrf_token'];

                                                $nom = $prenom = $adresse = $email = $date_naissance = $phone = "";
                                            }
                                        }
                                    } catch(PDOException $e) {
                                        $error = "Erreur de connexion à la base de données: " . $e->getMessage();
                                    }
                                }
                            }
                            ?>

                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success"><?php echo $success; ?></div>
                            <?php endif; ?>

                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom</label>
                                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $nom; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="prenom" class="form-label">Prénom</label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo $prenom; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="adresse" class="form-label">Adresse postale</label>
                                    <textarea class="form-control" id="adresse" name="adresse" rows="2" required><?php echo $adresse; ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Téléphone</label>
                                    <textarea class="form-control" id="phone" name="phone" rows="2" required><?php echo $phone; ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Adresse email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="date_naissance" class="form-label">Date de naissance</label>
                                    <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="<?php echo $date_naissance; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Mot de passe</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success btn-lg" style="Font-family:'GothamBook', sans-serif;">S'inscrire</button>
                                </div>
                            </form>

                            <div class="mt-3 text-center">
                                <p>Vous avez déjà un compte ? <a href="connexion.php" class="text-primary">Connectez-vous ici</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="text-white text-center py-3" style="font-family: 'GothamLight', sans-serif">
        <p>&copy; 2025 La Brigade TopChef M6. Tous droits réservés.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</div>
</body>
</html>